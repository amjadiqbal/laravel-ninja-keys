// Wires one <ninja-keys> element to the data the package rendered: turns handler names into calls to
// window functions, makes href actions navigate, applies the theme and the event hooks. Runs as a
// classic inline script right after the element; reads its options from the script's data-options.
(function (script) {
  var el = script && script.previousElementSibling;
  if (!el || el.tagName !== 'NINJA-KEYS') return;
  var opts = JSON.parse(script.getAttribute('data-options') || '{}');

  function callGlobal(name, arg) {
    if (!name) return undefined;
    if (typeof window[name] === 'function') return window[name](arg);
    console.warn('[ninja-keys] window.' + name + ' is not a function');
    return undefined;
  }

  function navigate(action) {
    if (action.target && action.target !== '_self') {
      window.open(action.href, action.target, 'noopener');
    } else if (opts.livewireNavigate && window.Livewire && typeof window.Livewire.navigate === 'function') {
      window.Livewire.navigate(action.href);
    } else {
      window.location.assign(action.href);
    }
  }

  function prepare(actions) {
    return actions.map(function (source) {
      var action = Object.assign({}, source);
      if (typeof source.handler === 'string') {
        action.handler = function (selected) { return callGlobal(source.handler, selected); };
      } else if (source.href) {
        action.handler = function () { navigate(source); };
      }
      if (Array.isArray(source.children) && source.children.some(function (c) { return typeof c === 'object'; })) {
        action.children = prepare(source.children);
      }
      return action;
    });
  }

  function hotkeysOf(actions) {
    return actions.reduce(function (keys, a) {
      if (a.hotkey) keys.push(a.hotkey);
      if (Array.isArray(a.children)) keys = keys.concat(hotkeysOf(a.children.filter(function (c) { return typeof c === 'object'; })));
      return keys;
    }, []);
  }

  customElements.whenDefined('ninja-keys').then(function () {
    // ninja-keys binds action hotkeys globally and never unbinds them. After a Livewire wire:navigate
    // the old element is gone but its hotkeys still fire, so drop the previous palette's first.
    var registry = window.__ninjaKeysHotkeys || [];
    if (window.hotkeys && typeof window.hotkeys.unbind === 'function') {
      registry.forEach(function (key) { window.hotkeys.unbind(key); });
    }
    window.__ninjaKeysHotkeys = opts.disableHotkeys ? [] : hotkeysOf(opts.data || []);

    el.data = prepare(opts.data || []);

    if (opts.onOpen) {
      var open = el.open.bind(el);
      el.open = function (options) {
        callGlobal(opts.onOpen, options || {});
        return open(options);
      };
    }
    if (opts.onChange) el.addEventListener('change', function (e) { callGlobal(opts.onChange, e.detail); });
    if (opts.onSelected) el.addEventListener('selected', function (e) { callGlobal(opts.onSelected, e.detail); });

    if (opts.theme === 'auto' && window.matchMedia) {
      var mq = window.matchMedia('(prefers-color-scheme: dark)');
      var apply = function () { el.classList.toggle('dark', mq.matches); };
      apply();
      mq.addEventListener('change', apply);
    }

    // Any element with data-ninja-keys-open opens the palette, optionally at a parent action.
    if (!window.__ninjaKeysClick) {
      window.__ninjaKeysClick = true;
      document.addEventListener('click', function (e) {
        var trigger = e.target.closest && e.target.closest('[data-ninja-keys-open]');
        var palette = document.querySelector('ninja-keys');
        if (!trigger || !palette) return;
        e.preventDefault();
        var parent = trigger.getAttribute('data-ninja-keys-open');
        palette.open(parent ? { parent: parent } : {});
      });
    }
  });
})(document.currentScript);
