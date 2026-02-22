<?php
 
namespace AmjadIqbal\NinjaKeys;

use Illuminate\Support\Facades\Gate;

class Manager
{
    protected array $actions = [];

    public function addAction(string $id): ActionBuilder
    {
        if (!isset($this->actions[$id])) {
            $this->actions[$id] = ['id' => $id];
        }
        return new ActionBuilder($this, $id);
    }

    public function addActions(array $actions): void
    {
        foreach ($actions as $action) {
            if (!isset($action['id'])) {
                continue;
            }
            $id = (string) $action['id'];
            $builder = $this->addAction($id);
            foreach ($action as $key => $value) {
                $builder->set($key, $value);
            }
        }
    }

    public function upsert(string $id, array $data): void
    {
        $existing = $this->actions[$id] ?? ['id' => $id];
        $this->actions[$id] = array_replace($existing, $data);
    }

    public function getActions(): array
    {
        $filtered = [];
        foreach ($this->actions as $action) {
            if (isset($action['can'])) {
                $abilities = is_array($action['can']) ? $action['can'] : [$action['can']];
                $allowed = false;
                foreach ($abilities as $ability) {
                    if (Gate::allows($ability)) {
                        $allowed = true;
                        break;
                    }
                }
                if (!$allowed) {
                    continue;
                }
                unset($action['can']);
            }
            $filtered[] = $this->normalizeAction($action);
        }
        return $filtered;
    }

    protected function normalizeAction(array $action): array
    {
        if (isset($action['shortcut']) && !isset($action['hotkey'])) {
            $action['hotkey'] = $action['shortcut'];
            unset($action['shortcut']);
        }
        if (isset($action['searchPlaceholder']) && !isset($action['placeholder'])) {
            $action['placeholder'] = $action['searchPlaceholder'];
        }
        return $action;
    }
}
