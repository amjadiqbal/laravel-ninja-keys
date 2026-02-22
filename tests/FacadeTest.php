<?php

use AmjadIqbal\NinjaKeys\Facades\NinjaKeys as NK;
use AmjadIqbal\NinjaKeys\Manager;

it('facade resolves to manager and records actions', function () {
    NK::addAction('f1')->title('From Facade');
    /** @var Manager $m */
    $m = app(Manager::class);
    $ids = collect($m->getActions())->pluck('id')->all();
    expect($ids)->toContain('f1');
});
