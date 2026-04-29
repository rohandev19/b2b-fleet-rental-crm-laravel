<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_allowed_role_can_access_route(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => 'ok');

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/_test/admin-only')
            ->assertOk();
    }

    public function test_disallowed_role_is_forbidden(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => 'ok');

        $sales = User::factory()->create([
            'role' => UserRole::Sales,
        ]);

        $this->actingAs($sales)
            ->get('/_test/admin-only')
            ->assertForbidden();
    }
}
