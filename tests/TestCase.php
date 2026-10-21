<?php

namespace AmjadIqbal\NinjaKeys\Tests;

use AmjadIqbal\NinjaKeys\NinjaKeysServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NinjaKeysServiceProvider::class,
        ];
    }
}
