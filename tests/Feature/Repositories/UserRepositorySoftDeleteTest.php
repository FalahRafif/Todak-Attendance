<?php

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositorySoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for database feature tests.');
        }

        parent::setUp();
    }

    public function test_repository_delete_hides_record_from_default_queries_and_restore_makes_it_visible_again(): void
    {
        $repository = app(UserRepositoryInterface::class);
        $user = User::factory()->create();

        $deleted = $repository->delete($user->id);

        $this->assertTrue($deleted);
        $this->assertNull($repository->find($user->id));
        $this->assertNull(User::query()->find($user->id));

        $inactiveUser = User::withInactive()->find($user->id);
        $this->assertNotNull($inactiveUser);
        $this->assertTrue((bool) $inactiveUser->delete_status);

        $restored = $repository->restore($user->id);

        $this->assertTrue($restored);
        $this->assertNotNull($repository->find($user->id));
        $this->assertFalse((bool) User::query()->findOrFail($user->id)->delete_status);
    }
}
