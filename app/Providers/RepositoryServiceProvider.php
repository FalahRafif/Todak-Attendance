<?php

namespace App\Providers;

use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\CacheLockRepositoryInterface;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\FailedJobRepositoryInterface;
use App\Repositories\Contracts\JobBatchRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WilayahRepositoryInterface;
use App\Repositories\Eloquent\AttachmentRepository;
use App\Repositories\Eloquent\CacheLockRepository;
use App\Repositories\Eloquent\CacheRepository;
use App\Repositories\Eloquent\FailedJobRepository;
use App\Repositories\Eloquent\JobBatchRepository;
use App\Repositories\Eloquent\JobRepository;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Eloquent\PasswordResetTokenRepository;
use App\Repositories\Eloquent\ReferenceRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SessionRepository;
use App\Repositories\Eloquent\SettingRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WilayahRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AttachmentRepositoryInterface::class, AttachmentRepository::class);
        $this->app->bind(CacheRepositoryInterface::class, CacheRepository::class);
        $this->app->bind(CacheLockRepositoryInterface::class, CacheLockRepository::class);
        $this->app->bind(FailedJobRepositoryInterface::class, FailedJobRepository::class);
        $this->app->bind(JobRepositoryInterface::class, JobRepository::class);
        $this->app->bind(JobBatchRepositoryInterface::class, JobBatchRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(PasswordResetTokenRepositoryInterface::class, PasswordResetTokenRepository::class);
        $this->app->bind(ReferenceRepositoryInterface::class, ReferenceRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WilayahRepositoryInterface::class, WilayahRepository::class);
    }
}
