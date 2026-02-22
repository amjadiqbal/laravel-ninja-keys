<?php

use AmjadIqbal\NinjaKeys\Manager;

it('renders ninja-keys tag and injects data via directive', function () {
    /** @var Manager $m */
    $m = app(Manager::class);
    $m->addAction('Theme')->title('Change theme...')->hotkey('ctrl+t');

    $blade = app('blade.compiler');
    $compiled = $blade->compileString('@ninjaKeysScripts');
    ob_start();
    eval('?>' . $compiled);
    $output = ob_get_clean();

    expect($output)->toContain('<ninja-keys');
    expect($output)->toContain('ninja.data =');
    expect($output)->toContain('type="module" src="https://unpkg.com/ninja-keys?module"');
});

it('bridges handler strings to window functions', function () {
    /** @var Manager $m */
    $m = app(Manager::class);
    $m->addAction('MyAction')->title('Do something')->handler('myFunction');

    $blade = app('blade.compiler');
    $compiled = $blade->compileString('@ninjaKeysScripts');
    ob_start();
    eval('?>' . $compiled);
    $output = ob_get_clean();

    expect($output)->toContain('addEventListener(\'selected\'');
    expect($output)->toContain('window[action.handler]');
});
