@php($nonce = \Illuminate\Support\Facades\Vite::cspNonce())
@if (! empty($options['material_icons_url']) && empty($options['noAutoLoadMdIcons']))
<link rel="stylesheet" href="{{ $options['material_icons_url'] }}">
@endif
@if ($src = $scriptUrl())
<script type="module" src="{{ $src }}" @if ($nonce) nonce="{{ $nonce }}" @endif></script>
@endif
<ninja-keys {!! $elementAttributes() !!}>@if (! empty($options['noFooter']))<div slot="footer"></div>@endif</ninja-keys>
<script data-options="{{ json_encode($bridgeOptions(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) }}" @if ($nonce) nonce="{{ $nonce }}" @endif>
{!! $bridgeScript() !!}
</script>
