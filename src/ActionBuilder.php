<?php

namespace AmjadIqbal\NinjaKeys;

/**
 * Fluent builder for one ninja-keys action. Every call writes straight into the Manager, so the
 * builder can be discarded at any point.
 */
class ActionBuilder
{
    public function __construct(
        protected Manager $manager,
        protected string $id,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function set(string $key, mixed $value): static
    {
        $this->manager->upsert($this->id, [$key => $value]);

        return $this;
    }

    public function title(string $title): static
    {
        return $this->set('title', $title);
    }

    /** Not displayed by ninja-keys; passed through in the action data for your own handlers. */
    public function description(string $description): static
    {
        return $this->set('description', $description);
    }

    /** Raw HTML/SVG icon. Rendered unescaped by ninja-keys, so never pass user input. */
    public function icon(string $html): static
    {
        return $this->set('icon', $html);
    }

    /** Material Icons ligature name, e.g. "home". Needs the material_icons_url font. */
    public function mdIcon(string $name): static
    {
        return $this->set('mdIcon', $name);
    }

    /** Open this URL when the action is chosen ("_blank" opens a new tab). */
    public function href(string $url, ?string $target = null): static
    {
        $this->set('href', $url);

        return $target === null ? $this : $this->set('target', $target);
    }

    /**
     * @param  array<string, mixed>|string  $parameters
     */
    public function route(string $name, array|string $parameters = [], ?string $target = null): static
    {
        return $this->href(route($name, $parameters), $target);
    }

    public function target(string $target): static
    {
        return $this->set('target', $target);
    }

    public function parent(string $parentId): static
    {
        return $this->set('parent', $parentId);
    }

    /**
     * Child action ids (flat data) or nested action arrays (tree data).
     *
     * @param  array<int, string|array<string, mixed>>  $children
     */
    public function children(array $children): static
    {
        return $this->set('children', $children);
    }

    /** hotkeys-js shortcut that runs the action even while the palette is closed, e.g. "ctrl+shift+n". */
    public function hotkey(string $hotkey): static
    {
        return $this->set('hotkey', $hotkey);
    }

    /** Alias of hotkey(). */
    public function shortcut(string $shortcut): static
    {
        return $this->hotkey($shortcut);
    }

    public function section(string $section): static
    {
        return $this->set('section', $section);
    }

    /** Extra search terms, space separated. */
    public function keywords(string $keywords): static
    {
        return $this->set('keywords', $keywords);
    }

    /**
     * Name of a global function, window[name](action). Return {keepOpen: true} from it to keep the
     * palette open.
     */
    public function handler(string $functionName): static
    {
        return $this->set('handler', $functionName);
    }

    /**
     * Only show the action when the current user passes at least one of these Gate abilities.
     *
     * @param  string|array<int, string>  $abilities
     */
    public function can(string|array $abilities): static
    {
        return $this->set('can', $abilities);
    }
}
