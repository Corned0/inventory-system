<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns a list of users', function () {
    User::factory()->count(3)->create();

    $response = $this->getJson(route('users.index'));

    $response
        ->assertOk()
        ->assertJsonCount(4, 'data');
});