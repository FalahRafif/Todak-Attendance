<?php

namespace App\Providers;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\AttendanceCorrectionRequestRepositoryInterface;
use App\Repositories\Contracts\AttendanceLogRepositoryInterface;
use App\Repositories\Contracts\AttendanceMonthlySummaryRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\CacheLockRepositoryInterface;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\EmployeeScheduleRepositoryInterface;
use App\Repositories\Contracts\EmployeeWorkLocationRepositoryInterface;
use App\Repositories\Contracts\FailedJobRepositoryInterface;
use App\Repositories\Contracts\HolidayRepositoryInterface;
use App\Repositories\Contracts\JobBatchRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Repositories\Contracts\LeaveBalanceRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestDetailRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WilayahRepositoryInterface;
use App\Repositories\Contracts\WorkLocationRepositoryInterface;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Repositories\Eloquent\ApprovalRepository;
use App\Repositories\Eloquent\AttachmentRepository;
use App\Repositories\Eloquent\AttendanceCorrectionRequestRepository;
use App\Repositories\Eloquent\AttendanceLogRepository;
use App\Repositories\Eloquent\AttendanceMonthlySummaryRepository;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Eloquent\CacheLockRepository;
use App\Repositories\Eloquent\CacheRepository;
use App\Repositories\Eloquent\DepartmentRepository;
use App\Repositories\Eloquent\EmployeeRepository;
use App\Repositories\Eloquent\EmployeeScheduleRepository;
use App\Repositories\Eloquent\EmployeeWorkLocationRepository;
use App\Repositories\Eloquent\FailedJobRepository;
use App\Repositories\Eloquent\HolidayRepository;
use App\Repositories\Eloquent\JobBatchRepository;
use App\Repositories\Eloquent\JobRepository;
use App\Repositories\Eloquent\LeaveBalanceRepository;
use App\Repositories\Eloquent\LeaveRequestDetailRepository;
use App\Repositories\Eloquent\LeaveRequestRepository;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Eloquent\PasswordResetTokenRepository;
use App\Repositories\Eloquent\PositionRepository;
use App\Repositories\Eloquent\ReferenceRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SessionRepository;
use App\Repositories\Eloquent\SettingRepository;
use App\Repositories\Eloquent\ShiftRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WilayahRepository;
use App\Repositories\Eloquent\WorkLocationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);
        $this->app->bind(ApprovalRepositoryInterface::class, ApprovalRepository::class);
        $this->app->bind(AttachmentRepositoryInterface::class, AttachmentRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(AttendanceLogRepositoryInterface::class, AttendanceLogRepository::class);
        $this->app->bind(AttendanceCorrectionRequestRepositoryInterface::class, AttendanceCorrectionRequestRepository::class);
        $this->app->bind(AttendanceMonthlySummaryRepositoryInterface::class, AttendanceMonthlySummaryRepository::class);
        $this->app->bind(CacheRepositoryInterface::class, CacheRepository::class);
        $this->app->bind(CacheLockRepositoryInterface::class, CacheLockRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeScheduleRepositoryInterface::class, EmployeeScheduleRepository::class);
        $this->app->bind(EmployeeWorkLocationRepositoryInterface::class, EmployeeWorkLocationRepository::class);
        $this->app->bind(FailedJobRepositoryInterface::class, FailedJobRepository::class);
        $this->app->bind(HolidayRepositoryInterface::class, HolidayRepository::class);
        $this->app->bind(JobRepositoryInterface::class, JobRepository::class);
        $this->app->bind(JobBatchRepositoryInterface::class, JobBatchRepository::class);
        $this->app->bind(LeaveRequestRepositoryInterface::class, LeaveRequestRepository::class);
        $this->app->bind(LeaveRequestDetailRepositoryInterface::class, LeaveRequestDetailRepository::class);
        $this->app->bind(LeaveBalanceRepositoryInterface::class, LeaveBalanceRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(PasswordResetTokenRepositoryInterface::class, PasswordResetTokenRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(ReferenceRepositoryInterface::class, ReferenceRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(ShiftRepositoryInterface::class, ShiftRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WilayahRepositoryInterface::class, WilayahRepository::class);
        $this->app->bind(WorkLocationRepositoryInterface::class, WorkLocationRepository::class);
    }
}
