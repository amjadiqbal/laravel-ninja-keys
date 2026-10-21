<?php

namespace AmjadIqbal\NinjaKeys;

use AmjadIqbal\NinjaKeys\View\Components\NinjaKeys;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class NinjaKeysServiceProvider extends ServiceProvider
{
    public const SUPPORT_URL = 'https://amjad.com.pk/contact?utm_source=artisan&utm_medium=about&utm_campaign=laravel-ninja-keys';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ninja-keys.php', 'ninja-keys');

        $this->app->singleton(Definitions::class);
        $this->app->scoped(Manager::class);
        $this->app->alias(Manager::class, 'ninja-keys');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ninja-keys');

        Blade::component('ninja-keys', NinjaKeys::class);

        $render = fn () => '<?php echo \Illuminate\Support\Facades\Blade::renderComponent(new \AmjadIqbal\NinjaKeys\View\Components\NinjaKeys()); ?>';
        Blade::directive('ninjaKeys', $render);
        Blade::directive('ninjaKeysScripts', $render); // v0.x name

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ninja-keys.php' => config_path('ninja-keys.php'),
            ], 'ninja-keys-config');

            $this->publishes([
                __DIR__.'/../resources/dist/ninja-keys.js' => public_path('vendor/ninja-keys/ninja-keys.js'),
            ], ['ninja-keys-assets', 'laravel-assets']);

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/ninja-keys'),
            ], 'ninja-keys-views');
        }

        AboutCommand::add('Ninja Keys', fn () => [
            'Version' => InstalledVersions::isInstalled('amjadiqbal/laravel-ninja-keys')
                ? (string) InstalledVersions::getPrettyVersion('amjadiqbal/laravel-ninja-keys')
                : 'unknown',
            'Script' => (string) (config('ninja-keys.script') ?? 'self-loaded'),
            'Support' => self::SUPPORT_URL,
        ]);
    }
}
