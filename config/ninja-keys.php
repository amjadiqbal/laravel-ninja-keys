<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where the ninja-keys script comes from
    |--------------------------------------------------------------------------
    |
    | "local": the self-contained bundle shipped with this package (ninja-keys
    |          1.2.2 with its exact Lit 2 and hotkeys-js inlined, no third-party
    |          requests). Laravel publishes it to public/vendor/ninja-keys on
    |          every `composer update` (laravel-assets tag); until it is
    |          published the CDN below is used instead.
    | "cdn":   jsDelivr's ES module build of ninja-keys 1.2.2.
    | null:    load it yourself, e.g. `import 'ninja-keys'` in your Vite entry.
    |
    */

    'script' => env('NINJA_KEYS_SCRIPT', 'local'),

    'cdn_url' => 'https://cdn.jsdelivr.net/npm/ninja-keys@1.2.2/+esm',

    'asset_url' => '/vendor/ninja-keys/ninja-keys.js',

    // Material Icons font for actions that use mdIcon(). Set to null if you only use icon() or no icons.
    'material_icons_url' => 'https://fonts.googleapis.com/css2?family=Material+Icons&display=block',

    /*
    |--------------------------------------------------------------------------
    | <ninja-keys> attributes
    |--------------------------------------------------------------------------
    |
    | Same names and defaults as the web component. Hotkeys use hotkeys-js
    | syntax: comma-separated alternatives, e.g. "cmd+k,ctrl+k".
    |
    */

    'placeholder' => 'Type a command or search...',
    'openHotkey' => 'cmd+k,ctrl+k',
    'navigationUpHotkey' => 'up,shift+tab',
    'navigationDownHotkey' => 'down,tab',
    'closeHotkey' => 'esc',
    'goBackHotkey' => 'backspace',
    'selectHotkey' => 'enter',

    // Do not register the hotkeys of individual actions.
    'disableHotkeys' => false,
    'hideBreadcrumbs' => false,
    'hotKeysJoinedView' => false,
    'noAutoLoadMdIcons' => false,

    // Replace the keyboard-hint footer with an empty one.
    'noFooter' => false,

    // "auto" follows prefers-color-scheme, "dark" or "light" forces one.
    'theme' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Behaviour
    |--------------------------------------------------------------------------
    */

    // Navigate href() actions with Livewire.navigate() (SPA mode) when Livewire is on the page.
    'livewire_navigate' => false,

    // Names of global window functions called on palette events.
    'onOpen' => null,     // ({ parent })
    'onChange' => null,   // ({ search, actions })
    'onSelected' => null, // ({ search, action })

];
