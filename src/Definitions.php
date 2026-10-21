<?php

namespace AmjadIqbal\NinjaKeys;

use Closure;

/**
 * App-lifetime list of define() callbacks. Kept apart from the scoped Manager so callbacks
 * registered while booting survive Octane's per-request reset.
 */
class Definitions
{
    /** @var array<int, Closure> */
    protected array $callbacks = [];

    public function add(Closure $callback): void
    {
        $this->callbacks[] = $callback;
    }

    public function run(Manager $manager): void
    {
        foreach ($this->callbacks as $callback) {
            $callback($manager);
        }
    }

    public function flush(): void
    {
        $this->callbacks = [];
    }
}
