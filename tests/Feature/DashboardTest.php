<?php

use App\Models\User;

dataset('dashboard-routes', [
    'dashboard',
    'dashboard1',
    'dashboard2',
    'dashboard3',
    'dashboard4',
    'dashboard5',
]);

test('guests are redirected to the login page', function (string $routeName) {
    $response = $this->get(route($routeName));
    $response->assertRedirect(route('login'));
})->with('dashboard-routes');

test('authenticated users can visit the dashboards', function (string $routeName) {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route($routeName));
    $response->assertOk();
})->with('dashboard-routes');
