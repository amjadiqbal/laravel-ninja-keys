<?php

namespace AmjadIqbal\NinjaKeys\View\Components;

use AmjadIqbal\NinjaKeys\Manager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * <x-ninja-keys /> (and @ninjaKeys): the script tag, the <ninja-keys> element and the bridge script.
 * Any config key can be overridden per page as an attribute, e.g. <x-ninja-keys placeholder="Jump to..." />.
 */
class NinjaKeys extends Component
{
    public const STRING_ATTRIBUTES = [
        'placeholder', 'openHotkey', 'navigationUpHotkey', 'navigationDownHotkey',
        'closeHotkey', 'goBackHotkey', 'selectHotkey',
    ];

    public const BOOLEAN_ATTRIBUTES = ['disableHotkeys', 'hideBreadcrumbs', 'hotKeysJoinedView', 'noAutoLoadMdIcons'];

    /** @var array<string, mixed> */
    public array $options;

    /**
     * @param  array<string, mixed>  $config  overrides for config('ninja-keys')
     */
    public function __construct(
        array $config = [],
        ?string $placeholder = null,
        ?string $theme = null,
        ?string $openHotkey = null,
    ) {
        $this->options = array_merge(
            (array) config('ninja-keys', []),
            $config,
            array_filter(compact('placeholder', 'theme', 'openHotkey'), fn ($v) => $v !== null),
        );

        if (! in_array($this->options['theme'] ?? 'auto', ['auto', 'light', 'dark'], true)) {
            throw new InvalidArgumentException('ninja-keys theme must be "auto", "light" or "dark".');
        }
    }

    public function scriptUrl(): ?string
    {
        $mode = array_key_exists('script', $this->options) ? $this->options['script'] : 'local';

        return match ($mode) {
            'cdn' => $this->options['cdn_url'] ?? null,
            'local' => $this->localAssetUrl() ?? ($this->options['cdn_url'] ?? null),
            null, false, 'none' => null,
            default => throw new InvalidArgumentException('ninja-keys script must be "cdn", "local" or null.'),
        };
    }

    /** The published bundle's URL, or null when it has not been published yet. */
    protected function localAssetUrl(): ?string
    {
        $path = $this->options['asset_url'] ?? null;

        return is_string($path) && is_file(public_path(ltrim($path, '/'))) ? asset($path) : null;
    }

    public function elementAttributes(): string
    {
        $html = [];

        foreach (self::STRING_ATTRIBUTES as $name) {
            if (isset($this->options[$name]) && $this->options[$name] !== '') {
                $html[] = strtolower($name).'="'.e((string) $this->options[$name]).'"';
            }
        }

        foreach (self::BOOLEAN_ATTRIBUTES as $name) {
            if (! empty($this->options[$name])) {
                $html[] = strtolower($name);
            }
        }

        if (($this->options['theme'] ?? 'auto') === 'dark') {
            $html[] = 'class="dark"';
        }

        return implode(' ', $html);
    }

    /**
     * @return array<string, mixed>
     */
    public function bridgeOptions(): array
    {
        return [
            'data' => app(Manager::class)->getActions(),
            'theme' => $this->options['theme'] ?? 'auto',
            'disableHotkeys' => ! empty($this->options['disableHotkeys']),
            'livewireNavigate' => ! empty($this->options['livewire_navigate']),
            'onOpen' => $this->options['onOpen'] ?? null,
            'onChange' => $this->options['onChange'] ?? null,
            'onSelected' => $this->options['onSelected'] ?? null,
        ];
    }

    public function bridgeScript(): string
    {
        return (string) file_get_contents(__DIR__.'/../../../resources/js/bridge.js');
    }

    public function render(): View
    {
        return view('ninja-keys::components.ninja-keys');
    }
}
