# Changelog

All notable changes to `laravel-ninja-keys` are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org).

## [Unreleased]

## [1.0.0] - 2026-10-21

First tagged release; until now the package was only installable as `dev-main`.

### Added
- `<x-ninja-keys />` Blade component and `@ninjaKeys` directive (`@ninjaKeysScripts` still works).
- ninja-keys 1.2.2 bundled as one ES module with its exact Lit 2 and hotkeys-js versions, published to
  `public/vendor/ninja-keys` with `laravel-assets` / `ninja-keys-assets`; jsDelivr fallback until published.
- `href()` and `route()` actions that navigate (new tab with `_blank`, or `Livewire.navigate()` with
  `livewire_navigate`).
- `NinjaKeys::define()` callbacks that run at render time; `has()`, `forget()`, `clear()`.
- `data-ninja-keys-open` click triggers, optionally opening a submenu.
- `onSelected` hook; per-page overrides as component attributes.
- CSP nonce on both scripts, `php artisan about` entry.

### Fixed
- Selecting an action with a `handler()` threw `action.handler is not a function`: ninja-keys calls the
  handler itself, and the package passed the function name as a string.
- Nested (tree) actions: ninja-keys 1.2.2 moved every root action after a nested one into its submenu.
  Trees are now flattened in PHP.
- Typing a regular-expression character such as `(` or `[` in the search box threw a SyntaxError on every
  keystroke (ninja-keys builds a RegExp from the input); the search text is now escaped.
- After `wire:navigate`, action hotkeys from the previous page fired again or twice.
- Gate filtering now also hides sub-actions of hidden actions and nested children.
- The inline script declared a global `const ninja`, which broke when the palette rendered twice.
- The default CDN URL (`unpkg.com/ninja-keys?module`) resolved its imports to mixed Lit versions.

### Changed
- Requires PHP 8.3+ and Laravel 12 or 13.
- Manager is scoped per request (Octane-safe).
- Handlers receive the action, as in ninja-keys; config keys `use_cdn`/`asset_path` became
  `script`/`asset_url`; `hotKeys`, `searchPlaceholder`, `noHeader` removed; config publish tag is now
  `ninja-keys-config`.
- Actions without an `id` or `title` throw `InvalidArgumentException` instead of being skipped or
  rendering "undefined".

[Unreleased]: https://github.com/amjadiqbal/laravel-ninja-keys/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/amjadiqbal/laravel-ninja-keys/releases/tag/v1.0.0
