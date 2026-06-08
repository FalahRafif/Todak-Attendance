<?php

namespace App\Providers;

use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\BillingDetailRepositoryInterface;
use App\Repositories\Contracts\BillingInstallmentRepositoryInterface;
use App\Repositories\Contracts\BillingRepositoryInterface;
use App\Repositories\Contracts\BookingHistoryRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CacheLockRepositoryInterface;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\FailedJobRepositoryInterface;
use App\Repositories\Contracts\JobBatchRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Repositories\Contracts\LocationPricingRuleRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\PackageBenefitRepositoryInterface;
use App\Repositories\Contracts\PackageRepositoryInterface;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WilayahRepositoryInterface;
use App\Repositories\Eloquent\AttachmentRepository;
use App\Repositories\Eloquent\BillingDetailRepository;
use App\Repositories\Eloquent\BillingInstallmentRepository;
use App\Repositories\Eloquent\BillingRepository;
use App\Repositories\Eloquent\BookingHistoryRepository;
use App\Repositories\Eloquent\BookingRepository;
use App\Repositories\Eloquent\CacheLockRepository;
use App\Repositories\Eloquent\CacheRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\FailedJobRepository;
use App\Repositories\Eloquent\JobBatchRepository;
use App\Repositories\Eloquent\JobRepository;
use App\Repositories\Eloquent\LocationPricingRuleRepository;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Eloquent\PackageBenefitRepository;
use App\Repositories\Eloquent\PackageRepository;
use App\Repositories\Eloquent\PasswordResetTokenRepository;
use App\Repositories\Eloquent\PaymentRepository;
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
        $this->app->bind(BillingRepositoryInterface::class, BillingRepository::class);
        $this->app->bind(BillingDetailRepositoryInterface::class, BillingDetailRepository::class);
        $this->app->bind(BillingInstallmentRepositoryInterface::class, BillingInstallmentRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(BookingHistoryRepositoryInterface::class, BookingHistoryRepository::class);
        $this->app->bind(CacheRepositoryInterface::class, CacheRepository::class);
        $this->app->bind(CacheLockRepositoryInterface::class, CacheLockRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(FailedJobRepositoryInterface::class, FailedJobRepository::class);
        $this->app->bind(JobRepositoryInterface::class, JobRepository::class);
        $this->app->bind(JobBatchRepositoryInterface::class, JobBatchRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(LocationPricingRuleRepositoryInterface::class, LocationPricingRuleRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(PackageBenefitRepositoryInterface::class, PackageBenefitRepository::class);
        $this->app->bind(PasswordResetTokenRepositoryInterface::class, PasswordResetTokenRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ReferenceRepositoryInterface::class, ReferenceRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WilayahRepositoryInterface::class, WilayahRepository::class);
    }
}
