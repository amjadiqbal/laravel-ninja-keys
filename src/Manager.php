<?php

namespace AmjadIqbal\NinjaKeys;

use Closure;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

/**
 * Collects the palette's actions for the current request. Scoped: under Octane every request starts
 * empty, and the callbacks registered with define() are run again for it.
 */
class Manager
{
    /** @var array<string, array<string, mixed>> */
    protected array $actions = [];

    protected bool $definitionsRun = false;

    public function __construct(protected Definitions $definitions) {}

    public function addAction(string $id): ActionBuilder
    {
        $this->actions[$id] ??= ['id' => $id];

        return new ActionBuilder($this, $id);
    }

    /**
     * Register actions as arrays in ninja-keys' own shape, e.g. ['id' => 'home', 'title' => 'Home'].
     *
     * @param  array<int, array<string, mixed>>  $actions
     */
    public function addActions(array $actions): static
    {
        foreach ($actions as $action) {
            if (! isset($action['id']) || ! is_scalar($action['id'])) {
                throw new InvalidArgumentException('Every ninja-keys action needs an "id".');
            }

            $this->upsert((string) $action['id'], $action);
        }

        return $this;
    }

    /**
     * Register a callback that adds actions when the palette renders, so it can use the logged-in
     * user, routes and the current request. Typically called from AppServiceProvider::boot().
     *
     * @param  Closure(Manager): mixed  $callback
     */
    public function define(Closure $callback): static
    {
        $this->definitions->add($callback);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function upsert(string $id, array $data): void
    {
        $this->actions[$id] = array_replace($this->actions[$id] ?? ['id' => $id], $data, ['id' => $id]);
    }

    public function has(string $id): bool
    {
        return isset($this->actions[$id]);
    }

    public function forget(string $id): static
    {
        unset($this->actions[$id]);

        return $this;
    }

    public function clear(): static
    {
        $this->actions = [];

        return $this;
    }

    /**
     * The actions the current user may see, in the shape the <ninja-keys> element expects.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActions(): array
    {
        if (! $this->definitionsRun) {
            $this->definitionsRun = true;
            $this->definitions->run($this);
        }

        // ninja-keys 1.2.2 mis-parents root actions that follow a nested one (its _flattern reuses
        // the parent variable), so tree data is always flattened here before it reaches the browser.
        $visible = $this->flatten($this->filter(array_values($this->actions)));

        // Flat data: drop actions whose parent was hidden, and hidden ids from children lists.
        do {
            $ids = array_column($visible, 'id');
            $before = count($visible);
            $visible = array_values(array_filter(
                $visible,
                fn (array $action) => ! isset($action['parent']) || in_array($action['parent'], $ids, true),
            ));
        } while (count($visible) !== $before);

        $ids = array_column($visible, 'id');

        return array_map(function (array $action) use ($ids) {
            if (isset($action['children']) && $this->isFlatList($action['children'])) {
                $action['children'] = array_values(array_intersect($action['children'], $ids));
            }

            return $action;
        }, $visible);
    }

    /**
     * @param  array<int, mixed>  $actions
     * @return array<int, array<string, mixed>>
     */
    protected function filter(array $actions): array
    {
        $out = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            if (isset($action['can'])) {
                if (! Gate::any((array) $action['can'])) {
                    continue;
                }
                unset($action['can']);
            }

            if (! isset($action['id']) || ! is_scalar($action['id'])) {
                throw new InvalidArgumentException('Every ninja-keys action needs an "id".');
            }
            $id = (string) $action['id'];
            if (! isset($action['title']) || $action['title'] === '') {
                throw new InvalidArgumentException("ninja-keys action \"{$id}\" has no title.");
            }

            if (isset($action['shortcut'])) {
                $action['hotkey'] ??= $action['shortcut'];
                unset($action['shortcut']);
            }

            if (isset($action['children']) && is_array($action['children']) && ! $this->isFlatList($action['children'])) {
                $action['children'] = $this->filter($action['children']);
            }

            $out[] = $action;
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $actions
     * @return array<int, array<string, mixed>>
     */
    protected function flatten(array $actions, ?string $parent = null): array
    {
        $flat = [];
        $nested = [];

        foreach ($actions as $action) {
            if ($parent !== null) {
                $action['parent'] ??= $parent;
            }

            if (isset($action['children']) && is_array($action['children']) && ! $this->isFlatList($action['children'])) {
                $children = $action['children'];
                $action['children'] = array_map(fn (array $child) => (string) $child['id'], $children);
                $nested = array_merge($nested, $this->flatten($children, (string) $action['id']));
            }

            $flat[] = $action;
        }

        return array_merge($flat, $nested);
    }

    /**
     * @param  mixed  $children
     */
    protected function isFlatList($children): bool
    {
        if (! is_array($children)) {
            return false;
        }

        foreach ($children as $child) {
            if (! is_string($child)) {
                return false;
            }
        }

        return true;
    }
}
