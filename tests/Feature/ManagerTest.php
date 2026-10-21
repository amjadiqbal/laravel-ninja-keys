<?php

use AmjadIqbal\NinjaKeys\Manager;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

it('builds actions with the fluent API and maps shortcut to hotkey', function () {
    $m = app(Manager::class);
    $m->addAction('create-post')
        ->title('New Post')
        ->keywords('write article')
        ->shortcut('ctrl+n')
        ->parent('posts')
        ->section('Content')
        ->mdIcon('note_add');
    $m->addAction('posts')->title('Posts')->children(['create-post']);

    $actions = collect($m->getActions())->keyBy('id');

    expect($actions['create-post'])->toMatchArray([
        'id' => 'create-post', 'title' => 'New Post', 'hotkey' => 'ctrl+n', 'parent' => 'posts',
        'section' => 'Content', 'mdIcon' => 'note_add', 'keywords' => 'write article',
    ])->not->toHaveKey('shortcut');
});

it('accepts plain arrays and rejects actions without an id or title', function () {
    $m = app(Manager::class);
    $m->addActions([['id' => 'home', 'title' => 'Home', 'href' => '/']]);
    expect($m->getActions())->toBe([['id' => 'home', 'title' => 'Home', 'href' => '/']]);

    expect(fn () => $m->addActions([['title' => 'No id']]))->toThrow(InvalidArgumentException::class);

    $m->addAction('untitled');
    expect(fn () => $m->getActions())->toThrow(InvalidArgumentException::class, 'untitled');
});

it('later calls update the same action', function () {
    $m = app(Manager::class);
    $m->addAction('a')->title('First');
    $m->addAction('a')->title('Second');
    $m->addActions([['id' => 'a', 'section' => 'S']]);

    expect($m->getActions())->toBe([['id' => 'a', 'title' => 'Second', 'section' => 'S']]);
    expect($m->has('a'))->toBeTrue();
    expect($m->forget('a')->has('a'))->toBeFalse();
});

it('hides actions the user may not see, with their flat children', function () {
    Gate::define('create-post', fn ($user = null) => true);
    Gate::define('manage-users', fn ($user = null) => false);

    $m = app(Manager::class);
    $m->addAction('allowed')->title('Allowed')->can('create-post');
    $m->addAction('either')->title('Either')->can(['manage-users', 'create-post']);
    $m->addAction('users')->title('Users')->can('manage-users')->children(['invite']);
    $m->addAction('invite')->title('Invite')->parent('users');
    $m->addAction('root')->title('Root')->children(['allowed', 'users']);

    $actions = collect($m->getActions())->keyBy('id');

    expect($actions->keys()->all())->toBe(['allowed', 'either', 'root']);
    expect($actions['allowed'])->not->toHaveKey('can');
    expect($actions['root']['children'])->toBe(['allowed']);
});

it('filters nested tree children too', function () {
    Gate::define('admin', fn ($user = null) => false);

    $m = app(Manager::class);
    $m->addActions([[
        'id' => 'theme', 'title' => 'Theme', 'children' => [
            ['id' => 'light', 'title' => 'Light'],
            ['id' => 'secret', 'title' => 'Secret', 'can' => 'admin'],
        ],
    ]]);

    expect(array_column($m->getActions()[0]['children'], 'id'))->toBe(['light']);
});

it('runs define() callbacks at render time with the current user', function () {
    Route::get('/billing', fn () => 'ok')->name('billing');

    app(Manager::class)->define(function (Manager $palette) {
        $palette->addAction('billing')->title('Billing')->route('billing', [], '_blank');
        if (auth()->check()) {
            $palette->addAction('profile')->title('Profile of '.auth()->user()->name);
        }
    });

    $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Ada']));

    $actions = collect(app(Manager::class)->getActions())->keyBy('id');
    expect($actions['billing'])->toMatchArray(['href' => url('/billing'), 'target' => '_blank']);
    expect($actions['profile']['title'])->toBe('Profile of Ada');
});

it('keeps define() callbacks but not per-request actions across scoped resets', function () {
    app(Manager::class)->define(fn (Manager $p) => $p->addAction('global')->title('Global'));
    app(Manager::class)->addAction('request-only')->title('Request only');

    app()->forgetScopedInstances();

    expect(array_column(app(Manager::class)->getActions(), 'id'))->toBe(['global']);
});
