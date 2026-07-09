<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_index(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk()
            ->assertSee('Manage CRM access');
    }

    public function test_non_admin_cannot_view_user_index(): void
    {
        $sales = User::factory()->create([
            'role' => UserRole::Sales,
        ]);

        $this->actingAs($sales)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New Finance User',
            'email' => 'new.finance@example.com',
            'role' => UserRole::Finance->value,
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/users');

        $this->assertDatabaseHas('users', [
            'email' => 'new.finance@example.com',
            'role' => UserRole::Finance->value,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $user = User::factory()->create([
            'role' => UserRole::Sales,
        ]);

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Updated Manager',
            'email' => 'updated.manager@example.com',
            'role' => UserRole::Manager->value,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/users');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Manager',
            'email' => 'updated.manager@example.com',
            'role' => UserRole::Manager->value,
        ]);
    }

    public function test_admin_can_toggle_another_user_active_status(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch("/users/{$user->id}/toggle-active");

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch("/users/{$admin->id}/toggle-active")
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_active' => true,
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}
