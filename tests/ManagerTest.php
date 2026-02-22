<?php

use AmjadIqbal\NinjaKeys\Manager;
use Illuminate\Support\Facades\Gate;

it('builds actions via fluent API and normalizes shortcut to hotkey', function () {
    /** @var Manager $m */
    $m = app(Manager::class);
    $m->addAction('create-post')
        ->title('New Post')
        ->description('Create a post')
        ->shortcut('ctrl+n')
        ->parent('posts');

    $actions = $m->getActions();
    expect($actions)->toHaveCount(1);
    expect($actions[0]['id'])->toBe('create-post');
    expect($actions[0]['title'])->toBe('New Post');
    expect($actions[0]['hotkey'])->toBe('ctrl+n');
    expect($actions[0]['parent'])->toBe('posts');
});

it('filters actions via Gate abilities', function () {
    Gate::define('create-post', fn ($user = null) => true);
    Gate::define('manage-users', fn ($user = null) => false);

    /** @var Manager $m */
    $m = app(Manager::class);
    $m->addAction('allowed')->title('Allowed')->can('create-post');
    $m->addAction('denied')->title('Denied')->can('manage-users');

    $actions = collect($m->getActions())->pluck('id')->all();
    expect($actions)->toContain('allowed');
    expect($actions)->not->toContain('denied');
});
