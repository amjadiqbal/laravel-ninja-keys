<?php
 
namespace AmjadIqbal\NinjaKeys\Components;

use Illuminate\View\Component;
use AmjadIqbal\NinjaKeys\Manager;

class NinjaKeys extends Component
{
    public array $attrs;

    public function __construct(array $attrs = [])
    {
        $this->attrs = $attrs;
    }

    public function render()
    {
        $cfg = config('ninja-keys');
        $placeholder = $cfg['searchPlaceholder'] ?? $cfg['placeholder'] ?? null;
        $attrs = [];
        if ($placeholder) $attrs[] = 'placeholder="'.e($placeholder).'"';
        foreach (['openHotkey','navigationUpHotkey','navigationDownHotkey','closeHotkey','goBackHotkey','selectHotkey'] as $attr) {
            if (!empty($cfg[$attr])) $attrs[] = $attr.'="'.e($cfg[$attr]).'"';
        }
        foreach (['hideBreadcrumbs','disableHotkeys','hotKeysJoinedView','noAutoLoadMdIcons'] as $flag) {
            if (!empty($cfg[$flag])) $attrs[] = $flag;
        }
        $themeClass = '';
        if (($cfg['theme'] ?? null) === 'dark') $themeClass = ' class="dark"';

        return function () use ($cfg, $themeClass, $attrs) {
            $material = !empty($cfg['material_icons_url']) ? '<link href="'.e($cfg['material_icons_url']).'" rel="stylesheet">' : '';
            $scriptSrc = !empty($cfg['use_cdn']) ? e($cfg['cdn_url']) : e($cfg['asset_path'] ?? '');
            $footer = !empty($cfg['noFooter']) ? '<div slot="footer"></div>' : '';
            $actions = json_encode(app(Manager::class)->getActions());
            return $material
                . '<script type="module" src="'.$scriptSrc.'"></script>'
                . '<ninja-keys'.$themeClass.' '.implode(' ', $attrs).'>'.$footer.'</ninja-keys>'
                . '<script>const ninja=document.querySelector("ninja-keys"); if(ninja){ ninja.data='.$actions.'; }</script>';
        };
    }
}
