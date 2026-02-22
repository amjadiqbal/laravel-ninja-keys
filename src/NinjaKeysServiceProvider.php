<?php
 
namespace AmjadIqbal\NinjaKeys;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class NinjaKeysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/ninja-keys.php', 'ninja-keys');

        $this->app->singleton(Manager::class, function () {
            return new Manager();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/ninja-keys.php' => $this->app->configPath('ninja-keys.php'),
        ], 'config');

        Blade::directive('ninjaKeysScripts', function () {
            return <<<'PHP'
<?php
$cfg = config('ninja-keys');
$placeholder = $cfg['searchPlaceholder'] ?? $cfg['placeholder'] ?? null;
$attrs = [];
if ($placeholder) $attrs[] = 'placeholder="'.e($placeholder).'"';
if (!empty($cfg['hotKeys'])) $attrs[] = 'openHotkey="'.e($cfg['hotKeys']).'"';
foreach (['openHotkey','navigationUpHotkey','navigationDownHotkey','closeHotkey','goBackHotkey','selectHotkey'] as $attr) {
    if (!empty($cfg[$attr])) $attrs[] = $attr.'="'.e($cfg[$attr]).'"';
}
foreach (['hideBreadcrumbs','disableHotkeys','hotKeysJoinedView','noAutoLoadMdIcons'] as $flag) {
    if (!empty($cfg[$flag])) $attrs[] = $flag;
}
$themeClass = '';
if (($cfg['theme'] ?? null) === 'dark') $themeClass = ' class="dark"';
?>
<?php if (!empty($cfg['material_icons_url'])): ?>
<link href="<?= e($cfg['material_icons_url']) ?>" rel="stylesheet">
<?php endif; ?>
<?php if (!empty($cfg['use_cdn'])): ?>
<script type="module" src="<?= e($cfg['cdn_url']) ?>"></script>
<?php elseif (!empty($cfg['asset_path'])): ?>
<script type="module" src="<?= e($cfg['asset_path']) ?>"></script>
<?php endif; ?>
<ninja-keys<?= $themeClass ?> <?= implode(' ', $attrs) ?>>
<?php if (!empty($cfg['noFooter'])): ?>
  <div slot="footer"></div>
<?php endif; ?>
</ninja-keys>
<script>
  const ninja = document.querySelector('ninja-keys');
  if (ninja) {
    ninja.data = @json(app(\AmjadIqbal\NinjaKeys\Manager::class)->getActions());

    ninja.addEventListener('selected', (event) => {
      const action = event.detail?.action;
      if (action && typeof action.handler === 'string' && typeof window[action.handler] === 'function') {
        window[action.handler](event.detail);
      }
    });

    const onChange = @json(config('ninja-keys.onChange'));
    if (onChange && typeof window[onChange] === 'function') {
      ninja.addEventListener('change', (event) => window[onChange](event.detail));
    }

    const onOpen = @json(config('ninja-keys.onOpen'));
    if (onOpen && typeof window[onOpen] === 'function') {
      const origOpen = ninja.open.bind(ninja);
      ninja.open = (...args) => {
        try { window[onOpen]({ args }); } catch (e) {}
        return origOpen(...args);
      };
    }

    if ((@json(config('ninja-keys.theme')) ?? 'auto') === 'auto') {
      const mq = window.matchMedia('(prefers-color-scheme: dark)');
      const apply = () => {
        if (mq.matches) ninja.classList.add('dark'); else ninja.classList.remove('dark');
      };
      apply();
      mq.addEventListener('change', apply);
    }
  }
</script>
PHP;
        });
    }
}
