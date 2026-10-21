<?php

use AmjadIqbal\NinjaKeys\Manager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;

afterEach(fn () => File::deleteDirectory(public_path('vendor/ninja-keys')));

it('renders the element, the script and the action data with the component', function () {
    app(Manager::class)->addAction('Theme')->title('Change theme...')->hotkey('ctrl+t')->handler('setTheme');

    $html = Blade::render('<x-ninja-keys />');

    expect($html)
        ->toContain('<ninja-keys placeholder="Type a command or search..." openhotkey="cmd+k,ctrl+k"')
        ->toContain('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Icons&amp;display=block">')
        ->toContain('customElements.whenDefined');

    expect(bridgeOptions($html)['data'])->toBe([
        ['id' => 'Theme', 'title' => 'Change theme...', 'hotkey' => 'ctrl+t', 'handler' => 'setTheme'],
    ]);
});

it('renders the same markup from both directives', function () {
    app(Manager::class)->addAction('a')->title('A');

    expect(Blade::render('@ninjaKeys'))->toBe(Blade::render('<x-ninja-keys />'))
        ->and(Blade::render('@ninjaKeysScripts'))->toBe(Blade::render('<x-ninja-keys />'));
});

it('loads the published local bundle, and the CDN until it is published', function () {
    $cdn = 'https://cdn.jsdelivr.net/npm/ninja-keys@1.2.2/+esm';
    expect(Blade::render('<x-ninja-keys />'))->toContain('<script type="module" src="'.$cdn.'"');

    $this->artisan('vendor:publish', ['--tag' => 'ninja-keys-assets'])->assertSuccessful();
    expect(public_path('vendor/ninja-keys/ninja-keys.js'))->toBeFile();

    expect(Blade::render('<x-ninja-keys />'))
        ->toContain('src="'.asset('vendor/ninja-keys/ninja-keys.js').'"')
        ->not->toContain($cdn);

    config(['ninja-keys.script' => 'cdn']);
    expect(Blade::render('<x-ninja-keys />'))->toContain($cdn);

    config(['ninja-keys.script' => null, 'ninja-keys.material_icons_url' => null]);
    expect(Blade::render('<x-ninja-keys />'))->not->toContain('type="module"')->not->toContain('<link');
});

it('publishes the bundle with laravel-assets, as composer update does', function () {
    $this->artisan('vendor:publish', ['--tag' => 'laravel-assets', '--force' => true])->assertSuccessful();

    expect(file_get_contents(public_path('vendor/ninja-keys/ninja-keys.js')))->toContain('ninja-keys 1.2.2');
});

it('maps config to attributes, theme and hooks, and accepts per-page overrides', function () {
    config([
        'ninja-keys.hideBreadcrumbs' => true,
        'ninja-keys.disableHotkeys' => true,
        'ninja-keys.noFooter' => true,
        'ninja-keys.theme' => 'dark',
        'ninja-keys.onSelected' => 'picked',
        'ninja-keys.livewire_navigate' => true,
    ]);

    $html = Blade::render('<x-ninja-keys placeholder="Jump to..." open-hotkey="ctrl+j" />');

    expect($html)
        ->toContain('placeholder="Jump to..."')
        ->toContain('openhotkey="ctrl+j"')
        ->toContain('disablehotkeys hidebreadcrumbs class="dark"')
        ->toContain('<div slot="footer"></div>');

    expect(bridgeOptions($html))->toMatchArray([
        'theme' => 'dark', 'disableHotkeys' => true, 'livewireNavigate' => true, 'onSelected' => 'picked',
    ]);

    expect(fn () => Blade::render('<x-ninja-keys theme="purple" />'))->toThrow(Exception::class);
});

it('escapes action text and user-controlled config', function () {
    config(['ninja-keys.placeholder' => '"><script>alert(1)</script>']);
    app(Manager::class)->addAction('x')->title('</script><script>alert(2)</script>');

    $html = Blade::render('<x-ninja-keys />');

    expect($html)->not->toContain('<script>alert')
        ->and(bridgeOptions($html)['data'][0]['title'])->toBe('</script><script>alert(2)</script>');
});

it('adds the CSP nonce to its scripts', function () {
    Vite::useCspNonce('abc123');

    expect(substr_count(Blade::render('<x-ninja-keys />'), 'nonce="abc123"'))->toBe(2);
});
