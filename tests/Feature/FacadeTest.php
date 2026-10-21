<?php

use AmjadIqbal\NinjaKeys\Facades\NinjaKeys;
use AmjadIqbal\NinjaKeys\Manager;

it('resolves the facade to the manager', function () {
    NinjaKeys::addAction('f1')->title('From Facade');

    expect(NinjaKeys::getFacadeRoot())->toBe(app(Manager::class));
    expect(array_column(app(Manager::class)->getActions(), 'id'))->toBe(['f1']);
});

it('shows the package in artisan about', function () {
    $this->artisan('about')->expectsOutputToContain('Ninja Keys')->assertSuccessful();
});
