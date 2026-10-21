<?php

use AmjadIqbal\NinjaKeys\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Decode the bridge options the component writes into data-options.
 *
 * @return array<string, mixed>
 */
function bridgeOptions(string $html): array
{
    preg_match('/data-options="([^"]*)"/', $html, $m);

    return json_decode(html_entity_decode($m[1] ?? '', ENT_QUOTES), true, flags: JSON_THROW_ON_ERROR);
}
