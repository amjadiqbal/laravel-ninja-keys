<?php

namespace AmjadIqbal\NinjaKeys\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use AmjadIqbal\NinjaKeys\NinjaKeysServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [NinjaKeysServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('ninja-keys', [
            'use_cdn' => true,
            'cdn_url' => 'https://unpkg.com/ninja-keys?module',
            'material_icons_url' => null,
            'placeholder' => 'Type a command or search...',
            'searchPlaceholder' => null,
            'disableHotkeys' => false,
            'hideBreadcrumbs' => false,
            'openHotkey' => 'cmd+k,ctrl+k',
            'hotKeys' => null,
            'navigationUpHotkey' => 'up,shift+tab',
            'navigationDownHotkey' => 'down,tab',
            'closeHotkey' => 'esc',
            'goBackHotkey' => 'backspace',
            'selectHotkey' => 'enter',
            'hotKeysJoinedView' => false,
            'noAutoLoadMdIcons' => false,
            'noHeader' => false,
            'noFooter' => false,
            'theme' => 'auto',
            'onChange' => null,
            'onOpen' => null,
            'asset_path' => null,
        ]);
    }
}
