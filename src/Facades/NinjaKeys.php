<?php

namespace AmjadIqbal\NinjaKeys\Facades;

use AmjadIqbal\NinjaKeys\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AmjadIqbal\NinjaKeys\ActionBuilder addAction(string $id)
 * @method static Manager addActions(array<int, array<string, mixed>> $actions)
 * @method static Manager define(\Closure $callback)
 * @method static bool has(string $id)
 * @method static Manager forget(string $id)
 * @method static Manager clear()
 * @method static array<int, array<string, mixed>> getActions()
 *
 * @see Manager
 */
class NinjaKeys extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Manager::class;
    }
}
