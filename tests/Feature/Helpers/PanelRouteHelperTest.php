<?php

namespace Tests\Feature\Helpers;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PanelRouteHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for database feature tests.');
        }

        parent::setUp();
    }

    public function test_panel_route_switches_admin_prefix_to_petugas_prefix_based_on_session_role(): void
    {
        $petugasRole = Role::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => RoleName::Petugas->value,
        ]);

        $user = User::factory()->create([
            'role_id' => $petugasRole->id,
        ]);

        $this->actingAs($user);
        session(['auth.role' => 'petugas']);

        $this->assertSame(route('petugas.dashboard'), panel_route('admin.dashboard'));
        $this->assertSame(route('petugas.bookings.list'), panel_route('admin.bookings.list'));
    }
}
