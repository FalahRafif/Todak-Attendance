<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for database feature tests.');
        }

        parent::setUp();
    }

    public function test_admin_user_login_redirects_to_admin_dashboard_and_sets_role_session(): void
    {
        $adminRole = $this->createRole(RoleName::Admin);
        $password = 'password123';

        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => $password,
            'role_id' => $adminRole->id,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@example.com',
            'password' => $password,
        ]);

        $response->assertRedirectToRoute('admin.dashboard');
        $response->assertSessionHas('auth.role', 'admin');
        $this->assertAuthenticated();
    }

    public function test_petugas_user_login_redirects_to_petugas_dashboard_and_sets_role_session(): void
    {
        $petugasRole = $this->createRole(RoleName::Petugas);
        $password = 'password123';

        User::factory()->create([
            'email' => 'petugas@example.com',
            'password' => $password,
            'role_id' => $petugasRole->id,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'petugas@example.com',
            'password' => $password,
        ]);

        $response->assertRedirectToRoute('petugas.dashboard');
        $response->assertSessionHas('auth.role', 'petugas');
        $this->assertAuthenticated();
    }

    public function test_non_internal_user_cannot_login_to_internal_panel(): void
    {
        $customerRole = $this->createRole(RoleName::Customer);
        $password = 'password123';

        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => $password,
            'role_id' => $customerRole->id,
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => 'customer@example.com',
            'password' => $password,
        ]);

        $response->assertRedirectToRoute('login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    private function createRole(RoleName $roleName): Role
    {
        return Role::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $roleName->value,
        ]);
    }
}
