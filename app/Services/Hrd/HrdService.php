<?php

namespace App\Services\Hrd;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceLog;
use App\Models\AttendanceMonthlySummary;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Reference;
use App\Models\Shift;
use App\Models\WorkLocation;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HrdService
{
    public function dashboardData(): array
    {
        $today = today();

        return [
            'title' => 'Dashboard HRD',
            'totalEmployees' => Employee::query()->where('is_active', true)->count(),
            'todayAttendances' => Attendance::query()->whereDate('attendance_date', $today)->count(),
            'lateAttendances' => Attendance::query()->whereDate('attendance_date', $today)->where('late_minutes', '>', 0)->count(),
            'outsideRadiusAttendances' => $this->outsideRadiusQuery()->whereDate('attendance_date', $today)->count(),
            'pendingLeaves' => LeaveRequest::query()->where('status_id', $this->approvalStatusId('pending'))->count(),
            'pendingCorrections' => AttendanceCorrectionRequest::query()->where('status_id', $this->approvalStatusId('pending'))->count(),
            'recentAttendances' => $this->attendanceBaseQuery()->whereDate('attendance_date', $today)->latest('attendance_date')->limit(8)->get(),
        ];
    }

    public function attendancePageData(Request $request, string $title, $query = null, bool $paginate = true): array
    {
        $query ??= $this->attendanceBaseQuery();
        $date = $this->filterDate($request);
        $query->whereDate('attendance_date', $date);
        $this->applyAttendanceFilters($query, $request);

        return [
            'title' => $title,
            'items' => $paginate ? $query->latest('attendance_date')->paginate(15)->withQueryString() : $query->latest('attendance_date')->get(),
            'date' => $date,
            'employees' => Employee::query()->where('is_active', true)->orderBy('full_name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'workLocations' => WorkLocation::query()->orderBy('name')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
        ];
    }

    public function notCheckedInPageData(Request $request): array
    {
        $date = $this->filterDate($request);
        $checkedInEmployeeIds = Attendance::query()->whereDate('attendance_date', $date)->pluck('employee_id');

        return [
            'title' => 'Belum Absen Masuk',
            'items' => Employee::query()->with(['department', 'position', 'workLocation', 'shift'])->where('is_active', true)->whereNotIn('id', $checkedInEmployeeIds)->orderBy('full_name')->paginate(15)->withQueryString(),
            'date' => $date,
        ];
    }

    public function attendanceDetailData(int $id): array
    {
        return ['title' => 'Detail Absensi', 'item' => $this->attendanceBaseQuery()->with('logs')->findOrFail($id)];
    }

    public function approveAttendance(int $id, ?string $note): void
    {
        Attendance::query()->findOrFail($id)->update(['is_need_approval' => false, 'approved_by' => auth()->id(), 'approved_at' => now(), 'approval_note' => $note, 'updated_by' => auth()->id()]);
    }

    public function rejectAttendance(int $id, string $note): void
    {
        $this->approveAttendance($id, $note);
    }

    public function leaveRequestListData(): array
    {
        return ['title' => 'Pengajuan Izin/Cuti', 'items' => LeaveRequest::query()->with(['employee.department', 'leaveType', 'status'])->latest('start_date')->paginate(15)];
    }

    public function leaveRequestDetailData(int $id): array
    {
        return ['title' => 'Detail Pengajuan Izin/Cuti', 'item' => LeaveRequest::query()->with(['employee.department', 'leaveType', 'status', 'details'])->findOrFail($id)];
    }

    public function approveLeaveRequest(int $id, ?string $note): void
    {
        $leaveRequest = LeaveRequest::query()->with('leaveType')->findOrFail($id);
        $leaveRequest->update(['status_id' => $this->approvalStatusId('approved'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'approval_note' => $note, 'updated_by' => auth()->id()]);
        $this->syncLeaveAttendances($leaveRequest);
    }

    public function rejectLeaveRequest(int $id, string $reason): void
    {
        LeaveRequest::query()->findOrFail($id)->update(['status_id' => $this->approvalStatusId('rejected'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'rejected_reason' => $reason, 'updated_by' => auth()->id()]);
    }

    public function correctionListData(): array
    {
        return ['title' => 'Koreksi Absensi', 'items' => AttendanceCorrectionRequest::query()->with(['employee.department', 'attendance', 'status'])->latest('correction_date')->paginate(15)];
    }

    public function correctionDetailData(int $id): array
    {
        return ['title' => 'Detail Koreksi Absensi', 'item' => AttendanceCorrectionRequest::query()->with(['employee.department', 'attendance', 'status'])->findOrFail($id)];
    }

    public function approveCorrection(int $id, ?string $note): void
    {
        $correction = AttendanceCorrectionRequest::query()->with('employee')->findOrFail($id);
        $attendance = Attendance::query()->with('shift')->firstOrCreate(['employee_id' => $correction->employee_id, 'attendance_date' => $correction->correction_date], ['uuid' => (string) Str::uuid(), 'shift_id' => $correction->employee?->shift_id, 'work_location_id' => $correction->employee?->work_location_id, 'status_id' => $this->attendanceStatusId('present'), 'created_by' => auth()->id()]);
        $checkInAt = $correction->requested_check_in_at ?? $attendance->check_in_at;
        $checkOutAt = $correction->requested_check_out_at ?? $attendance->check_out_at;

        $attendance->update(array_merge(['check_in_at' => $checkInAt, 'check_out_at' => $checkOutAt, 'status_id' => $this->attendanceStatusId('present'), 'updated_by' => auth()->id()], $this->correctedAttendanceMetrics($attendance->refresh(), $checkInAt, $checkOutAt)));
        AttendanceLog::query()->create(['uuid' => (string) Str::uuid(), 'attendance_id' => $attendance->id, 'employee_id' => $correction->employee_id, 'action_type_id' => $this->attendanceActionTypeId('update_by_hrd'), 'action_at' => now(), 'note' => $note ?? $correction->reason, 'source' => 'hrd', 'created_by' => auth()->id()]);
        $correction->update(['attendance_id' => $attendance->id, 'status_id' => $this->approvalStatusId('approved'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'approval_note' => $note, 'updated_by' => auth()->id()]);
    }

    public function rejectCorrection(int $id, string $reason): void
    {
        AttendanceCorrectionRequest::query()->findOrFail($id)->update(['status_id' => $this->approvalStatusId('rejected'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'rejected_reason' => $reason, 'updated_by' => auth()->id()]);
    }

    public function scheduleListData(): array
    {
        return ['title' => 'Jadwal Karyawan', 'items' => EmployeeSchedule::query()->with(['employee.department', 'shift'])->latest('schedule_date')->paginate(15)];
    }

    public function leaveBalanceListData(): array
    {
        return ['title' => 'Saldo Cuti', 'items' => LeaveBalance::query()->with(['employee.department', 'leaveType'])->latest('year')->paginate(15)];
    }

    public function monthlyReportData(Request $request): array
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        return ['title' => 'Laporan Absensi Bulanan', 'items' => AttendanceMonthlySummary::query()->with('employee.department')->where('year', $year)->where('month', $month)->paginate(15)->withQueryString(), 'year' => $year, 'month' => $month];
    }

    public function generateMonthlyReport(array $payload): void
    {
        Employee::query()->where('is_active', true)->chunk(100, function ($employees) use ($payload): void {
            foreach ($employees as $employee) {
                $attendances = Attendance::query()->with('status')->where('employee_id', $employee->id)->whereYear('attendance_date', $payload['year'])->whereMonth('attendance_date', $payload['month'])->get();
                AttendanceMonthlySummary::query()->updateOrCreate(['employee_id' => $employee->id, 'year' => $payload['year'], 'month' => $payload['month']], ['uuid' => (string) Str::uuid(), 'total_work_days' => $attendances->count(), 'total_present' => $attendances->whereNotNull('check_in_at')->count(), 'total_late' => $attendances->where('late_minutes', '>', 0)->count(), 'total_absent' => 0, 'total_sick' => $this->countAttendanceStatus($attendances, 'sick'), 'total_leave' => $this->countAttendanceStatus($attendances, 'leave'), 'total_permission' => $this->countAttendanceStatus($attendances, 'permission'), 'total_incomplete' => $attendances->whereNotNull('check_in_at')->whereNull('check_out_at')->count(), 'total_outside_radius' => $attendances->filter(fn (Attendance $attendance): bool => $attendance->check_in_is_inside_radius === false || $attendance->check_out_is_inside_radius === false)->count(), 'total_work_minutes' => $attendances->sum('total_work_minutes'), 'total_late_minutes' => $attendances->sum('late_minutes'), 'total_early_leave_minutes' => $attendances->sum('early_leave_minutes'), 'generated_at' => now(), 'updated_by' => auth()->id()]);
            }
        });
    }

    public function dailyExportRows(Request $request): array
    {
        return $this->attendancePageData($request, 'Laporan Absensi Harian', null, false)['items']->map(fn (Attendance $item): array => [$item->attendance_date?->format('Y-m-d'), $item->employee?->full_name, $item->employee?->department?->name, $item->check_in_at?->format('H:i'), $item->check_out_at?->format('H:i'), $item->late_minutes, $item->status?->description])->all();
    }

    public function monthlyExportRows(Request $request): array
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        return AttendanceMonthlySummary::query()->with('employee.department')->where('year', $year)->where('month', $month)->get()->map(fn (AttendanceMonthlySummary $item): array => [$item->employee?->full_name, $item->employee?->department?->name, $item->total_present, $item->total_late, $item->total_sick, $item->total_leave, $item->total_permission, $item->total_incomplete, $item->total_outside_radius, $item->total_work_minutes])->all();
    }

    public function attendanceBaseQuery()
    {
        return Attendance::query()->with(['employee.department', 'employee.position', 'shift', 'workLocation', 'status']);
    }

    public function outsideRadiusQuery()
    {
        return $this->attendanceBaseQuery()->where(function ($query): void {
            $query->where('is_need_approval', true)->orWhere('check_in_is_inside_radius', false)->orWhere('check_out_is_inside_radius', false);
        });
    }

    private function correctedAttendanceMetrics(Attendance $attendance, ?Carbon $checkInAt, ?Carbon $checkOutAt): array
    {
        $attendance->loadMissing('shift');
        $lateMinutes = 0;
        $totalWorkMinutes = 0;
        $earlyLeaveMinutes = 0;

        if ($checkInAt !== null && $attendance->shift?->start_time !== null) {
            $shiftStart = Carbon::parse($attendance->attendance_date?->format('Y-m-d').' '.$attendance->shift->start_time);
            $lateMinutes = max(0, $shiftStart->diffInMinutes($checkInAt, false));
        }

        if ($checkInAt !== null && $checkOutAt !== null) {
            $totalWorkMinutes = max(0, $checkInAt->diffInMinutes($checkOutAt, false));
        }

        if ($checkOutAt !== null && $attendance->shift?->end_time !== null) {
            $shiftEnd = Carbon::parse($attendance->attendance_date?->format('Y-m-d').' '.$attendance->shift->end_time);
            if ($attendance->shift->is_overnight && $shiftEnd->lte($checkInAt ?? $shiftEnd)) {
                $shiftEnd->addDay();
            }
            $earlyLeaveMinutes = max(0, $checkOutAt->diffInMinutes($shiftEnd, false));
        }

        return ['late_minutes' => $lateMinutes, 'total_work_minutes' => $totalWorkMinutes, 'early_leave_minutes' => $earlyLeaveMinutes];
    }

    private function applyAttendanceFilters($query, Request $request): void
    {
        $query->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->integer('employee_id')))->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $request->integer('department_id'))))->when($request->filled('work_location_id'), fn ($q) => $q->where('work_location_id', $request->integer('work_location_id')))->when($request->filled('shift_id'), fn ($q) => $q->where('shift_id', $request->integer('shift_id')))->when($request->filled('status_id'), fn ($q) => $q->where('status_id', $request->integer('status_id')));
    }

    private function filterDate(Request $request): Carbon
    {
        return Carbon::parse($request->input('date', today()->format('Y-m-d')));
    }

    private function approvalStatusId(string $description): ?int
    {
        return Reference::query()->where('description', $description)->value('id');
    }

    private function attendanceStatusId(string $description): ?int
    {
        return Reference::query()->where('description', $description)->value('id');
    }

    private function attendanceActionTypeId(string $description): ?int
    {
        return Reference::query()->where('description', $description)->value('id');
    }

    private function syncLeaveAttendances(LeaveRequest $leaveRequest): void
    {
        $status = match ($leaveRequest->leaveType?->description) {
            'sick_leave' => 'sick',
            'permission' => 'permission',
            default => 'leave',
        };

        foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
            Attendance::query()->updateOrCreate(['employee_id' => $leaveRequest->employee_id, 'attendance_date' => $date->format('Y-m-d')], ['uuid' => (string) Str::uuid(), 'status_id' => $this->attendanceStatusId($status), 'updated_by' => auth()->id()]);
        }
    }

    private function countAttendanceStatus($attendances, string $status): int
    {
        return $attendances->filter(fn (Attendance $attendance): bool => $attendance->status?->description === $status)->count();
    }
}
