<?php
 
namespace AmjadIqbal\NinjaKeys\Facades;

use Illuminate\Support\Facades\Facade;
use AmjadIqbal\NinjaKeys\Manager;

class NinjaKeys extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
