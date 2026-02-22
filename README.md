# Laravel Ninja Keys

[![Packagist Version](https://img.shields.io/packagist/v/amjadiqbal/laravel-ninja-keys.svg?style=flat-square)](https://packagist.org/packages/amjadiqbal/laravel-ninja-keys)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/amjadiqbal/laravel-ninja-keys.svg?style=flat-square)](https://packagist.org/packages/amjadiqbal/laravel-ninja-keys)
[![Laravel](https://img.shields.io/badge/laravel-%5E10%20%7C%20%5E11-red?style=flat-square)](#requirements)
[![Tests](https://img.shields.io/badge/tested%20with-Pest-orange?style=flat-square)](#testing)

Laravel integration for the [ninja-keys](https://github.com/ssleptsov/ninja-keys) web component. Ships a fluent PHP API, a Blade directive for rendering and JS-bridge logic, and Gate-based authorization filtering for server-side visibility.

## Table of Contents
- Overview
- Requirements
- Installation
- Quickstart
- Configuration
- Rendering Options
- Fluent API
- Authorization
- Event Hooks
- Styling
- Data Model Mapping
- Testing
- Versioning
- Contributing
- Security
- License
- Credits

## Overview
- Keyboard command palette powered by ninja-keys (Web Component).
- Laravel-friendly API for building actions via PHP.
- Server-side filtering using Gate abilities (hide unauthorized actions).
- CDN or local asset loading; Material Icons support.
- Theme handling (auto/dark/light) and optional footer control.
- Works with flat or tree action data structures.
- Bridges string handlers to global window functions safely.

## Requirements
- PHP: ^8.1
- Laravel: ^10.0 or ^11.0

## Installation

```bash
composer require amjadiqbal/laravel-ninja-keys
```

Auto-discovery registers the service provider and facade.

If you use Pest for tests:

```bash
composer config --no-plugins allow-plugins.pestphp/pest-plugin true
```

## Quickstart

Publish config:

```bash
php artisan vendor:publish --tag=config
```

Add to your layout:

```blade
@ninjaKeysScripts
```

Register actions:

```php
use AmjadIqbal\NinjaKeys\Facades\NinjaKeys;

NinjaKeys::addAction('create-post')
    ->title('New Post')
    ->description('Create a post')
    ->shortcut('ctrl+n')     // normalized to JS hotkey
    ->parent('posts')
    ->mdIcon('note_add')
    ->handler('onCreatePost') // window.onCreatePost(detail) will be called
    ->can(['create-post', 'manage-posts']); // visible if any ability allows
```

Add global handlers:

```html
<script>
  window.onCreatePost = (detail) => { /* your logic */ };
</script>
```

## Configuration

Key options (config/ninja-keys.php):

- use_cdn (bool) — load from CDN; otherwise asset_path
- cdn_url (string) — default https://unpkg.com/ninja-keys?module
- asset_path (string|null) — published asset path if not using CDN
- material_icons_url (string|null) — Google Fonts URL to load Material Icons
- placeholder, searchPlaceholder (string|null) — search input placeholders
- disableHotkeys, hideBreadcrumbs, hotKeysJoinedView, noAutoLoadMdIcons (bool)
- openHotkey, hotKeys (string) — hotKeys is an alias for openHotkey
- navigationUpHotkey, navigationDownHotkey, closeHotkey, goBackHotkey, selectHotkey (string)
- theme (light|dark|auto)
- noHeader, noFooter (bool)
- onChange (string|null) — window function name to receive change events
- onOpen (string|null) — window function name called before ninja.open()

Publish and edit:

```bash
php artisan vendor:publish --tag=config
```

## Rendering Options

- Blade directive

```blade
@ninjaKeysScripts
```

- Blade component (optional)
  - Component class exists at `src/Components/NinjaKeys.php`
  - Register alias manually if desired:
    ```php
    // in a service provider
    \Illuminate\Support\Facades\Blade::component('ninja-keys', \AmjadIqbal\NinjaKeys\Components\NinjaKeys::class);
    ```
  
#### Load from local asset instead of CDN

Publish assets to your public directory (or bundle in your build), then set:

```php
// config/ninja-keys.php
'use_cdn' => false,
'asset_path' => '/assets/js/ninja-keys.js', // example path
```

## Fluent API

```php
use AmjadIqbal\NinjaKeys\Facades\NinjaKeys;

NinjaKeys::addActions([
    ['id' => 'Theme', 'title' => 'Change theme...', 'hotkey' => 'ctrl+t', 'children' => ['Light', 'Dark', 'System']],
    ['id' => 'Light', 'title' => 'Light Theme', 'parent' => 'Theme', 'mdIcon' => 'light_mode', 'handler' => 'setLightTheme'],
    ['id' => 'Dark', 'title' => 'Dark Theme', 'parent' => 'Theme', 'mdIcon' => 'dark_mode', 'handler' => 'setDarkTheme'],
]);
```

Convenience fields:
- href(string) and target(string) are included for your own handlers/UI logic (they are not executed automatically by the component).

## Authorization
- Use `can(string|array)` to attach Gate abilities.
- If any provided ability allows, the action is included; otherwise excluded server-side.
- Gate definitions in tests or app boot:
  ```php
  Gate::define('create-post', fn ($user = null) => true);
  ```

## Event Hooks
- onChange: listen to component change and receive `{ search, actions }`.
- onOpen: called before `ninja.open(...)` with `{ args }`.
- Handler bridging:
  - If an action’s `handler` is a string (e.g., `"myFunction"`), the directive listens to `selected` and calls `window[handler](event.detail)` when present.
  - Recommended: keep handlers small and side-effect free; heavy logic can be deferred.

## Styling
- Theme:
  - `theme = auto`: toggles dark class based on `prefers-color-scheme`.
  - `theme = dark`: forces `class="dark"` on the component.
- Footer:
  - `noFooter = true` renders an empty footer slot.
- Material Icons:
  - Load via `material_icons_url` or disable with `noAutoLoadMdIcons`.
- CSS Shadow Parts (from ninja-keys):
  - `actions-list`, `ninja-action`, `ninja-selected`, `ninja-input`, `ninja-input-wrapper`
  - Example:
    ```css
    ninja-keys::part(ninja-action) { border-radius: 8px; }
    ```
  
#### CSS Variables (examples)

```css
ninja-keys {
  --ninja-width: 640px;
  --ninja-accent-color: #6e5ed2;
  --ninja-icon-size: 1.2em;
  --ninja-selected-background: #f8f9fb;
}
```

## Data Model Mapping

ActionBuilder → Ninja Keys fields:
- addAction(id) → id
- title(string) → title
- description(string) → description
- icon(string) → icon (HTML/SVG)
- mdIcon(string) → mdIcon (Material icon name)
- parent(string) → parent
- children(array) → children
- shortcut(string) → normalized to hotkey
- hotkey(string) → hotkey
- section(string) → section
- keywords(string) → keywords
- handler(string) → handler (global window function name)
- href(string), target(string) → convenience extras (not auto-executed)
- can(string|array) → Gate abilities for server-side filtering

Manager:
- addActions(array) → bulk registration
- getActions() → normalized and authorization-filtered array

#### Flat vs Tree
- Flat: use `parent` and `children` ids to nest menus.
- Tree: provide nested `children` arrays; library supports both.

## Testing

Pest and Testbench included:

```bash
composer config --no-plugins allow-plugins.pestphp/pest-plugin true
composer update
./vendor/bin/pest
```

Run a single test:

```bash
./vendor/bin/pest tests/ManagerTest.php
```

Static analysis, style and coverage (optional suggestions):
- phpstan: `vendor/bin/phpstan analyse src`
- pint: `vendor/bin/pint`
- coverage: `./vendor/bin/pest --coverage-html coverage/`

## Troubleshooting
- Handler not firing:
  - Ensure `handler` is a string and the global function exists: `window.myFunction = (detail) => { ... }`
- Action hidden unexpectedly:
  - Check `can()` abilities; Gate must return true for at least one ability.
- Material Icons missing:
  - Ensure `material_icons_url` is set, or disable with `noAutoLoadMdIcons`.
- Theme not switching on auto:
  - Confirm browser supports `prefers-color-scheme`; ensure no CSS overrides are preventing `.dark` class effects.
- CDN blocked:
  - Switch to local asset by setting `use_cdn=false` and `asset_path`.

## Performance
- Avoid registering an excessive number of actions; use `keywords` for better searchability.
- Group actions logically via `section` to improve UX.
- Prefer short, deterministic handlers; delegate long work to async functions if needed.
- Cache computed action arrays on the server if you build them dynamically on each request.

## FAQ
- Difference between `shortcut` and `hotkey`?
  - `shortcut` is normalized to `hotkey`. You can use either, but only `hotkey` is sent to the component.
- Can I navigate externally?
  - Store `href` and `target` on actions and implement logic in your handler to perform navigation.
- How to hide breadcrumbs?
  - Set `hideBreadcrumbs=true` in config.
- How to disable registering action hotkeys?
  - Set `disableHotkeys=true` in config to stop auto-registering action hotkeys.
- Flat vs Tree data?
  - Use `parent`/`children` in flat mode or nested arrays in tree mode; pick whichever suits your data model.

## CI & Publishing
- Validate and test:
  ```bash
  composer validate
  ./vendor/bin/pest
  ```
- Tag a release (after publishing to Packagist):
  - Follow SemVer; update composer version range if needed.
- GitHub Actions (suggested):
  - Setup PHP matrix (^8.1), run `composer install`, `composer validate`, `./vendor/bin/pest`.

## Versioning
- Follows semantic versioning once published.
- Compatibility targets: PHP ^8.1, Laravel ^10|^11.

## Contributing
- PRs and issues are welcome. Please include tests where appropriate.

## Security
- No secrets stored; do not commit keys/tokens. Report vulnerabilities privately.

## License
- MIT © AmjadIqbal

## Credits
- Built on top of the excellent [ninja-keys](https://github.com/ssleptsov/ninja-keys) by Sergei Sleptsov.
