<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for database feature tests.');
        }

        parent::setUp();
    }

    public function test_guest_is_redirected_when_accessing_internal_route(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirectToRoute('login');
    }

    public function test_petugas_cannot_access_admin_route(): void
    {
        $petugasRole = $this->createRole(RoleName::Petugas);
        $user = User::factory()->create(['role_id' => $petugasRole->id]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_route_and_petugas_cannot_access_admin_only_petugas_prefix_routes(): void
    {
        $adminRole = $this->createRole(RoleName::Admin);
        $petugasRole = $this->createRole(RoleName::Petugas);

        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $petugasUser = User::factory()->create(['role_id' => $petugasRole->id]);

        $this->actingAs($adminUser)
            ->get(route('admin.dashboard'))
            ->assertOk();

        auth()->logout();

        $this->actingAs($petugasUser)
            ->get(route('petugas.packages'))
            ->assertForbidden();
    }

    private function createRole(RoleName $roleName): Role
    {
        return Role::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $roleName->value,
        ]);
    }
}
