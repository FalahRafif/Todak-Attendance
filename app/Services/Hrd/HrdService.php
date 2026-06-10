<?php

namespace App\Services\Hrd;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceLog;
use App\Models\AttendanceMonthlySummary;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetail;
use App\Models\Reference;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\WorkLocation;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HrdService
{
    public function dashboardData(): array
    {
        $today = today();

        return [
            'title' => 'Dashboard HRD',
            'totalEmployees' => Employee::query()->where('is_active', true)->count(),
            'todayAttendances' => Attendance::query()->whereHas('employee')->whereDate('attendance_date', $today)->count(),
            'lateAttendances' => Attendance::query()->whereHas('employee')->whereDate('attendance_date', $today)->where('late_minutes', '>', 0)->count(),
            'outsideRadiusAttendances' => $this->outsideRadiusQuery()->whereDate('attendance_date', $today)->count(),
            'pendingLeaves' => LeaveRequest::query()->whereHas('employee')->where('status_id', $this->approvalStatusId('pending'))->count(),
            'pendingCorrections' => AttendanceCorrectionRequest::query()->whereHas('employee')->where('status_id', $this->approvalStatusId('pending'))->count(),
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

    public function attendanceMapData(Request $request): array
    {
        $date = $this->filterDate($request);
        $query = $this->attendanceBaseQuery()->with(['employee.department', 'employee.position', 'workLocation', 'shift', 'status', 'checkInPhoto', 'checkOutPhoto'])->whereDate('attendance_date', $date);
        $this->applyAttendanceFilters($query, $request);
        $items = $query->orderBy('check_in_at')->get();
        $offices = WorkLocation::query()->whereNotNull('latitude')->whereNotNull('longitude')->orderBy('name')->get()->map(fn (WorkLocation $location): array => [
            'id' => $location->id,
            'name' => $location->name,
            'lat' => (float) $location->latitude,
            'lng' => (float) $location->longitude,
            'radius' => (int) ($location->radius_meter ?? 100),
        ])->values();
        $points = $items->flatMap(function (Attendance $attendance): array {
            $rows = [];
            if ($attendance->check_in_latitude !== null && $attendance->check_in_longitude !== null) {
                $rows[] = $this->attendanceMapPoint($attendance, 'check_in');
            }
            if ($attendance->check_out_latitude !== null && $attendance->check_out_longitude !== null) {
                $rows[] = $this->attendanceMapPoint($attendance, 'check_out');
            }

            return $rows;
        })->values();

        return [
            'title' => 'Peta Absensi',
            'date' => $date,
            'items' => $items,
            'offices' => $offices,
            'points' => $points,
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
        $dayOffEmployeeIds = EmployeeSchedule::query()->whereDate('schedule_date', $date)->where('is_day_off', true)->pluck('employee_id');
        $approvedLeaveEmployeeIds = LeaveRequest::query()->where('status_id', $this->approvalStatusId('approved'))->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->pluck('employee_id');
        $isHoliday = Holiday::query()->whereDate('holiday_date', $date)->exists();

        return [
            'title' => 'Belum Absen Masuk',
            'items' => $isHoliday ? Employee::query()->whereRaw('1 = 0')->paginate(15)->withQueryString() : Employee::query()->with(['department', 'position', 'workLocation', 'shift'])->where('is_active', true)->whereNotIn('id', $checkedInEmployeeIds)->whereNotIn('id', $dayOffEmployeeIds)->whereNotIn('id', $approvedLeaveEmployeeIds)->orderBy('full_name')->paginate(15)->withQueryString(),
            'date' => $date,
        ];
    }

    public function attendanceDetailData(int $id): array
    {
        $item = $this->attendanceBaseQuery()->with(['logs', 'checkInPhoto', 'checkOutPhoto'])->findOrFail($id);
        $attachmentSecurityService = app(\App\Services\AttachmentSecurityService::class);

        return ['title' => 'Detail Absensi', 'item' => $item, 'checkInPhotoUrl' => $attachmentSecurityService->generateTemporaryPreviewUrl($item->checkInPhoto), 'checkOutPhotoUrl' => $attachmentSecurityService->generateTemporaryPreviewUrl($item->checkOutPhoto)];
    }

    public function approveAttendance(int $id, ?string $note): void
    {
        Attendance::query()->findOrFail($id)->update(['is_need_approval' => false, 'approved_by' => auth()->id(), 'approved_at' => now(), 'approval_note' => $note, 'updated_by' => auth()->id()]);
    }

    public function rejectAttendance(int $id, string $note): void
    {
        $this->approveAttendance($id, $note);
    }

    public function leaveRequestListData(Request $request): array
    {
        $query = LeaveRequest::query()->whereHas('employee')->with(['employee.department', 'leaveType', 'status']);
        $query->when($request->filled('status_id'), fn ($q) => $q->where('status_id', $request->integer('status_id')))
            ->when($request->filled('leave_type_id'), fn ($q) => $q->where('leave_type_id', $request->integer('leave_type_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('start_date_from'), fn ($q) => $q->whereDate('start_date', '>=', $request->input('start_date_from')))
            ->when($request->filled('start_date_to'), fn ($q) => $q->whereDate('start_date', '<=', $request->input('start_date_to')))
            ->when($request->filled('keyword'), fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', '%'.$request->input('keyword').'%')));

        return ['title' => 'Pengajuan Izin/Cuti', 'items' => $query->latest('start_date')->paginate(15)->withQueryString(), 'statuses' => Reference::query()->whereIn('description', ['pending', 'approved', 'rejected', 'cancelled'])->orderBy('description')->get(), 'leaveTypes' => Reference::query()->whereIn('description', ['annual_leave', 'sick_leave', 'permission'])->get(), 'departments' => Department::query()->orderBy('name')->get()];
    }

    public function leaveRequestDetailData(int $id): array
    {
        return ['title' => 'Detail Pengajuan Izin/Cuti', 'item' => LeaveRequest::query()->whereHas('employee')->with(['employee.department', 'leaveType', 'status', 'details'])->findOrFail($id)];
    }

    public function approveLeaveRequest(int $id, ?string $note): void
    {
        DB::transaction(function () use ($id, $note): void {
            $leaveRequest = LeaveRequest::query()->whereHas('employee')->with('leaveType')->lockForUpdate()->findOrFail($id);
            if ($leaveRequest->status_id !== $this->approvalStatusId('pending')) {
                throw new \RuntimeException('Hanya pengajuan pending yang bisa disetujui.');
            }
            $this->ensureNoOverlappingApprovedLeave($leaveRequest);
            if ($leaveRequest->leaveType?->description === 'annual_leave') {
                $this->ensureAndReduceLeaveBalanceLocked($leaveRequest);
            }
            $leaveRequest->update(['status_id' => $this->approvalStatusId('approved'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'approval_note' => $note, 'updated_by' => auth()->id()]);
            $this->syncLeaveAttendances($leaveRequest);
        });
    }

    public function rejectLeaveRequest(int $id, string $reason): void
    {
        $leaveRequest = LeaveRequest::query()->whereHas('employee')->findOrFail($id);
        if ($leaveRequest->status_id !== $this->approvalStatusId('pending')) {
            throw new \RuntimeException('Hanya pengajuan pending yang bisa ditolak.');
        }
        $leaveRequest->update(['status_id' => $this->approvalStatusId('rejected'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'rejected_reason' => $reason, 'updated_by' => auth()->id()]);
    }

    public function correctionListData(Request $request): array
    {
        $query = AttendanceCorrectionRequest::query()->whereHas('employee')->with(['employee.department', 'attendance', 'status']);
        $query->when($request->filled('status_id'), fn ($q) => $q->where('status_id', $request->integer('status_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('correction_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('correction_date', '<=', $request->input('date_to')))
            ->when($request->filled('keyword'), fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', '%'.$request->input('keyword').'%')));

        return ['title' => 'Koreksi Absensi', 'items' => $query->latest('correction_date')->paginate(15)->withQueryString(), 'statuses' => Reference::query()->whereIn('description', ['pending', 'approved', 'rejected', 'cancelled'])->orderBy('description')->get(), 'departments' => Department::query()->orderBy('name')->get()];
    }

    public function correctionDetailData(int $id): array
    {
        return ['title' => 'Detail Koreksi Absensi', 'item' => AttendanceCorrectionRequest::query()->whereHas('employee')->with(['employee.department', 'attendance', 'status'])->findOrFail($id)];
    }

    public function approveCorrection(int $id, ?string $note): void
    {
        DB::transaction(function () use ($id, $note): void {
            $correction = AttendanceCorrectionRequest::query()->whereHas('employee')->with('employee')->lockForUpdate()->findOrFail($id);
            if ($correction->status_id !== $this->approvalStatusId('pending')) {
                throw new \RuntimeException('Hanya koreksi pending yang bisa disetujui.');
            }
            $attendance = Attendance::query()->with('shift')->firstOrCreate(['employee_id' => $correction->employee_id, 'attendance_date' => $correction->correction_date], ['uuid' => (string) Str::uuid(), 'shift_id' => $correction->employee?->shift_id, 'work_location_id' => $correction->employee?->work_location_id, 'status_id' => $this->attendanceStatusId('present'), 'created_by' => auth()->id()]);
            $checkInAt = $correction->requested_check_in_at ?? $attendance->check_in_at;
            $checkOutAt = $correction->requested_check_out_at ?? $attendance->check_out_at;

            $attendance->update(array_merge(['check_in_at' => $checkInAt, 'check_out_at' => $checkOutAt, 'status_id' => $this->attendanceStatusId('present'), 'updated_by' => auth()->id()], $this->correctedAttendanceMetrics($attendance->refresh(), $checkInAt, $checkOutAt)));
            AttendanceLog::query()->create(['uuid' => (string) Str::uuid(), 'attendance_id' => $attendance->id, 'employee_id' => $correction->employee_id, 'action_type_id' => $this->attendanceActionTypeId('update_by_hrd'), 'action_at' => now(), 'note' => $note ?? $correction->reason, 'source' => 'hrd', 'created_by' => auth()->id()]);
            $correction->update(['attendance_id' => $attendance->id, 'status_id' => $this->approvalStatusId('approved'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'approval_note' => $note, 'updated_by' => auth()->id()]);
        });
    }

    public function rejectCorrection(int $id, string $reason): void
    {
        $correction = AttendanceCorrectionRequest::query()->whereHas('employee')->findOrFail($id);
        if ($correction->status_id !== $this->approvalStatusId('pending')) {
            throw new \RuntimeException('Hanya koreksi pending yang bisa ditolak.');
        }
        $correction->update(['status_id' => $this->approvalStatusId('rejected'), 'approved_by' => auth()->id(), 'approved_at' => now(), 'rejected_reason' => $reason, 'updated_by' => auth()->id()]);
    }

    public function scheduleListData(): array
    {
        return ['title' => 'Jadwal Karyawan', 'items' => EmployeeSchedule::query()->whereHas('employee')->with(['employee.department', 'shift'])->latest('schedule_date')->paginate(15)];
    }

    public function leaveBalanceListData(Request $request): array
    {
        $year = (int) $request->input('year', now()->year);
        $query = LeaveBalance::query()->whereHas('employee')->with(['employee.department', 'leaveType'])->where('year', $year);
        $query->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('keyword'), fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', '%'.$request->input('keyword').'%')));

        return ['title' => 'Saldo Cuti', 'items' => $query->latest('id')->paginate(15)->withQueryString(), 'departments' => Department::query()->orderBy('name')->get(), 'year' => $year, 'defaultQuota' => $this->annualLeaveQuota()];
    }

    public function adjustLeaveBalance(int $id, array $payload): void
    {
        $balance = LeaveBalance::query()->whereHas('employee')->findOrFail($id);
        $additional = (int) ($payload['additional_quota'] ?? 0);
        if ($additional === 0 && ! isset($payload['total_quota'])) {
            return;
        }
        DB::transaction(function () use ($balance, $additional, $payload): void {
            if (isset($payload['total_quota'])) {
                $newTotal = (int) $payload['total_quota'];
                $balance->total_quota = $newTotal;
                $balance->remaining_quota = max(0, $newTotal - (int) $balance->used_quota);
            } else {
                $balance->total_quota = (int) $balance->total_quota + $additional;
                $balance->remaining_quota = (int) $balance->remaining_quota + $additional;
            }
            $balance->updated_by = auth()->id();
            $balance->save();
        });
    }

    public function generateMissingLeaveBalances(?int $year = null): int
    {
        $year = $year ?? now()->year;
        $annualLeaveTypeId = Reference::query()->where('description', 'annual_leave')->value('id');
        if ($annualLeaveTypeId === null) {
            return 0;
        }
        $quota = $this->annualLeaveQuota();
        $empRole = \App\Models\Role::query()->where('name', 'Employee')->first();
        if ($empRole === null) {
            return 0;
        }
        $empUserIds = \App\Models\User::query()->where('role_id', $empRole->id)->pluck('id');
        $employeeIds = Employee::query()->whereIn('user_id', $empUserIds)->where('is_active', true)->pluck('id');
        $existing = LeaveBalance::query()->where('leave_type_id', $annualLeaveTypeId)->where('year', $year)->whereIn('employee_id', $employeeIds)->pluck('employee_id')->toArray();
        $missing = $employeeIds->diff($existing);
        $count = 0;
        foreach ($missing as $empId) {
            LeaveBalance::query()->create(['uuid' => (string) Str::uuid(), 'employee_id' => $empId, 'leave_type_id' => $annualLeaveTypeId, 'year' => $year, 'total_quota' => $quota, 'used_quota' => 0, 'remaining_quota' => $quota, 'created_by' => auth()->id() ?? 1]);
            $count++;
        }

        return $count;
    }

    public function activityCalendarData(Request $request): array
    {
        $start = Carbon::parse($request->input('start_date', now()->startOfMonth()->format('Y-m-d')))->startOfDay();
        $end = Carbon::parse($request->input('end_date', now()->endOfMonth()->format('Y-m-d')))->endOfDay();
        if ($start->diffInMonths($end) > 2 || $end->lt($start)) {
            $end = $start->copy()->addMonths(2)->endOfMonth();
        }
        $leaveDetailEvents = LeaveRequestDetail::query()
            ->with(['leaveRequest.employee.department', 'leaveRequest.leaveType'])
            ->whereHas('leaveRequest.employee')
            ->whereHas('leaveRequest', fn ($query) => $query->where('status_id', $this->approvalStatusId('approved')))
            ->whereDate('leave_date', '>=', $start)
            ->whereDate('leave_date', '<=', $end)
            ->orderBy('leave_date')
            ->get()
            ->map(function (LeaveRequestDetail $detail): array {
                $type = $detail->leaveRequest?->leaveType?->description;
                $color = match ($type) {
                    'sick_leave' => '#ef4444',
                    'permission' => '#f59e0b',
                    default => '#8b5cf6',
                };

                return ['id' => 'leave-detail-'.$detail->id, 'title' => ($detail->leaveRequest?->employee?->full_name ?? '-').' - '.friendly_label($type), 'start' => $detail->leave_date?->format('Y-m-d'), 'allDay' => true, 'backgroundColor' => $color, 'borderColor' => $color, 'extendedProps' => ['type' => 'leave', 'employee' => $detail->leaveRequest?->employee?->full_name, 'department' => $detail->leaveRequest?->employee?->department?->name, 'leaveType' => friendly_label($type), 'reason' => $detail->leaveRequest?->reason]];
            });
        $leaveRequestIdsWithDetails = LeaveRequestDetail::query()->pluck('leave_request_id')->unique();
        $leaveRangeEvents = LeaveRequest::query()
            ->with(['employee.department', 'leaveType'])
            ->whereHas('employee')
            ->where('status_id', $this->approvalStatusId('approved'))
            ->whereNotIn('id', $leaveRequestIdsWithDetails)
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->flatMap(function (LeaveRequest $leave): array {
                $events = [];
                $type = $leave->leaveType?->description;
                $color = match ($type) {
                    'sick_leave' => '#ef4444',
                    'permission' => '#f59e0b',
                    default => '#8b5cf6',
                };
                foreach (Carbon::parse($leave->start_date)->daysUntil(Carbon::parse($leave->end_date)->addDay()) as $date) {
                    $events[] = ['id' => 'leave-'.$leave->id.'-'.$date->format('Ymd'), 'title' => ($leave->employee?->full_name ?? '-').' - '.friendly_label($type), 'start' => $date->format('Y-m-d'), 'allDay' => true, 'backgroundColor' => $color, 'borderColor' => $color, 'extendedProps' => ['type' => 'leave', 'employee' => $leave->employee?->full_name, 'department' => $leave->employee?->department?->name, 'leaveType' => friendly_label($type), 'reason' => $leave->reason]];
                }

                return $events;
            });
        $holidayEvents = Holiday::query()
            ->whereDate('holiday_date', '>=', $start)
            ->whereDate('holiday_date', '<=', $end)
            ->orderBy('holiday_date')
            ->get()
            ->map(fn (Holiday $holiday): array => ['id' => 'holiday-'.$holiday->id, 'title' => $holiday->name, 'start' => $holiday->holiday_date?->format('Y-m-d'), 'allDay' => true, 'backgroundColor' => '#0f4c81', 'borderColor' => '#0f4c81', 'extendedProps' => ['type' => 'holiday', 'employee' => null, 'department' => null, 'leaveType' => 'Libur', 'reason' => $holiday->description ?? null]]);

        return ['title' => 'Activity Calendar', 'startDate' => $start, 'endDate' => $end, 'events' => $leaveDetailEvents->merge($leaveRangeEvents)->merge($holidayEvents)->values()];
    }

    public function monthlyReportData(Request $request): array
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        return ['title' => 'Laporan Absensi Bulanan', 'items' => AttendanceMonthlySummary::query()->whereHas('employee')->with('employee.department')->where('year', $year)->where('month', $month)->paginate(15)->withQueryString(), 'year' => $year, 'month' => $month];
    }

    public function generateMonthlyReport(array $payload): void
    {
        Employee::query()->where('is_active', true)->chunk(100, function ($employees) use ($payload): void {
            foreach ($employees as $employee) {
                if ($employee->user_id === null) {
                    continue;
                }
                $attendances = Attendance::query()->with('status')->where('employee_id', $employee->id)->whereYear('attendance_date', $payload['year'])->whereMonth('attendance_date', $payload['month'])->get();
                $summary = AttendanceMonthlySummary::query()->firstOrNew(['employee_id' => $employee->id, 'year' => $payload['year'], 'month' => $payload['month']]);
                if (! $summary->exists) {
                    $summary->uuid = (string) Str::uuid();
                    $summary->created_by = auth()->id();
                }
                $summary->total_work_days = $attendances->count();
                $summary->total_present = $attendances->whereNotNull('check_in_at')->count();
                $summary->total_late = $attendances->where('late_minutes', '>', 0)->count();
                $summary->total_absent = 0;
                $summary->total_sick = $this->countAttendanceStatus($attendances, 'sick');
                $summary->total_leave = $this->countAttendanceStatus($attendances, 'leave');
                $summary->total_permission = $this->countAttendanceStatus($attendances, 'permission');
                $summary->total_incomplete = $attendances->whereNotNull('check_in_at')->whereNull('check_out_at')->count();
                $summary->total_outside_radius = $attendances->filter(fn (Attendance $attendance): bool => $attendance->check_in_is_inside_radius === false || $attendance->check_out_is_inside_radius === false)->count();
                $summary->total_work_minutes = $attendances->sum('total_work_minutes');
                $summary->total_late_minutes = $attendances->sum('late_minutes');
                $summary->total_early_leave_minutes = $attendances->sum('early_leave_minutes');
                $summary->generated_at = now();
                $summary->updated_by = auth()->id();
                $summary->save();
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

        return AttendanceMonthlySummary::query()->whereHas('employee')->with('employee.department')->where('year', $year)->where('month', $month)->get()->map(fn (AttendanceMonthlySummary $item): array => [$item->employee?->full_name, $item->employee?->department?->name, $item->total_present, $item->total_late, $item->total_sick, $item->total_leave, $item->total_permission, $item->total_incomplete, $item->total_outside_radius, $item->total_work_minutes])->all();
    }

    public function attendanceBaseQuery()
    {
        return Attendance::query()->whereHas('employee')->with(['employee.department', 'employee.position', 'shift', 'workLocation', 'status']);
    }

    public function outsideRadiusQuery()
    {
        return $this->attendanceBaseQuery()->where(function ($query): void {
            $query->where('is_need_approval', true)->orWhere('check_in_is_inside_radius', false)->orWhere('check_out_is_inside_radius', false);
        });
    }

    private function attendanceMapPoint(Attendance $attendance, string $type): array
    {
        $isCheckIn = $type === 'check_in';
        $inside = $isCheckIn ? $attendance->check_in_is_inside_radius : $attendance->check_out_is_inside_radius;
        $time = $isCheckIn ? $attendance->check_in_at : $attendance->check_out_at;
        $distance = $isCheckIn ? $attendance->check_in_distance_meter : $attendance->check_out_distance_meter;
        $note = $isCheckIn ? $attendance->check_in_note : $attendance->check_out_note;

        return [
            'attendance_id' => $attendance->id,
            'type' => $type,
            'type_label' => $isCheckIn ? 'Masuk' : 'Pulang',
            'employee_name' => $attendance->employee?->full_name ?? '-',
            'department' => $attendance->employee?->department?->name ?? '-',
            'position' => $attendance->employee?->position?->name ?? '-',
            'work_location_id' => $attendance->work_location_id,
            'work_location' => $attendance->workLocation?->name ?? '-',
            'office_lat' => $attendance->workLocation?->latitude !== null ? (float) $attendance->workLocation->latitude : null,
            'office_lng' => $attendance->workLocation?->longitude !== null ? (float) $attendance->workLocation->longitude : null,
            'lat' => (float) ($isCheckIn ? $attendance->check_in_latitude : $attendance->check_out_latitude),
            'lng' => (float) ($isCheckIn ? $attendance->check_in_longitude : $attendance->check_out_longitude),
            'time' => $time?->format('H:i') ?? '-',
            'distance' => $distance,
            'inside' => $inside,
            'need_approval' => (bool) $attendance->is_need_approval,
            'status' => friendly_label($attendance->status?->description),
            'note' => $note,
            'detail_url' => route('hrd.attendances.show', $attendance->id),
        ];
    }

    private function correctedAttendanceMetrics(Attendance $attendance, ?Carbon $checkInAt, ?Carbon $checkOutAt): array
    {
        $attendance->loadMissing('shift');
        $lateMinutes = 0;
        $totalWorkMinutes = 0;
        $earlyLeaveMinutes = 0;

        if ($checkInAt !== null && $attendance->shift?->start_time !== null) {
            $shiftStart = Carbon::parse($attendance->attendance_date?->format('Y-m-d').' '.$attendance->shift->start_time)->addMinutes((int) ($attendance->shift->late_tolerance_minutes ?? 0));
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

        return ['late_minutes' => $lateMinutes, 'total_work_minutes' => $totalWorkMinutes, 'early_leave_minutes' => $earlyLeaveMinutes, 'status_id' => $this->attendanceStatusId($lateMinutes > 0 ? 'late' : 'present')];
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
        $leaveRequest->loadMissing(['employee', 'leaveType']);
        $status = match ($leaveRequest->leaveType?->description) {
            'sick_leave' => 'sick',
            'permission' => 'permission',
            default => 'leave',
        };
        if ($leaveRequest->employee?->shift_id === null || $leaveRequest->employee?->work_location_id === null) {
            throw new \RuntimeException('Shift dan lokasi kerja karyawan wajib diisi sebelum pengajuan disetujui.');
        }

        foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
            $attendance = Attendance::query()->firstOrNew(['employee_id' => $leaveRequest->employee_id, 'attendance_date' => $date->format('Y-m-d')]);
            if (! $attendance->exists) {
                $attendance->uuid = (string) Str::uuid();
                $attendance->shift_id = $leaveRequest->employee->shift_id;
                $attendance->work_location_id = $leaveRequest->employee->work_location_id;
                $attendance->created_by = auth()->id();
            }
            $attendance->status_id = $this->attendanceStatusId($status);
            $attendance->check_in_at = null;
            $attendance->check_out_at = null;
            $attendance->late_minutes = 0;
            $attendance->early_leave_minutes = 0;
            $attendance->total_work_minutes = 0;
            $attendance->is_need_approval = false;
            $attendance->updated_by = auth()->id();
            $attendance->save();
            $detail = LeaveRequestDetail::query()->firstOrNew(['leave_request_id' => $leaveRequest->id, 'leave_date' => $date->format('Y-m-d')]);
            if (! $detail->exists) {
                $detail->uuid = (string) Str::uuid();
                $detail->created_by = auth()->id();
            }
            $detail->attendance_id = $attendance->id;
            $detail->updated_by = auth()->id();
            $detail->save();
        }
    }

    private function ensureNoOverlappingApprovedLeave(LeaveRequest $leaveRequest): void
    {
        $exists = LeaveRequest::query()->where('employee_id', $leaveRequest->employee_id)->whereKeyNot($leaveRequest->id)->where('status_id', $this->approvalStatusId('approved'))->whereDate('start_date', '<=', $leaveRequest->end_date)->whereDate('end_date', '>=', $leaveRequest->start_date)->exists();
        if ($exists) {
            throw new \RuntimeException('Tanggal pengajuan overlap dengan leave approved lain.');
        }
    }

    private function ensureAndReduceLeaveBalanceLocked(LeaveRequest $leaveRequest): void
    {
        $balance = LeaveBalance::query()
            ->where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', Carbon::parse($leaveRequest->start_date)->year)
            ->lockForUpdate()
            ->first();
        if ($balance === null) {
            throw new \RuntimeException('Saldo cuti tahunan belum tersedia.');
        }
        if ((int) $balance->remaining_quota < (int) $leaveRequest->total_days) {
            throw new \RuntimeException('Saldo cuti tahunan tidak cukup.');
        }
        $used = (int) $balance->used_quota + (int) $leaveRequest->total_days;
        $balance->update(['used_quota' => $used, 'remaining_quota' => max(0, (int) $balance->total_quota - $used), 'updated_by' => auth()->id()]);
    }

    private function annualLeaveQuota(): int
    {
        return (int) (Setting::query()->where('group_id', 'attendance')->where('code', 'DEFAULT_ANNUAL_LEAVE_QUOTA')->value('value') ?? 12);
    }

    private function countAttendanceStatus($attendances, string $status): int
    {
        return $attendances->filter(fn (Attendance $attendance): bool => $attendance->status?->description === $status)->count();
    }
}
