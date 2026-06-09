<?php

namespace App\Services\Employee;

use App\Models\Attachment;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class EmployeePortalService
{
    public function dashboardData(): array
    {
        $employee = $this->employee();

        return ['title' => 'Employee Dashboard', 'employee' => $employee, 'todayAttendance' => $this->todayAttendance(), 'monthlyAttendances' => $this->historyQuery()->limit(5)->get(), 'pendingLeaves' => LeaveRequest::query()->where('employee_id', $employee->id)->where('status_id', $this->approvalStatusId('pending'))->count(), 'pendingCorrections' => AttendanceCorrectionRequest::query()->where('employee_id', $employee->id)->where('status_id', $this->approvalStatusId('pending'))->count()];
    }

    public function attendanceData(): array
    {
        return ['title' => 'Attendance Today', 'employee' => $this->employee(), 'todayAttendance' => $this->todayAttendance()];
    }

    public function attendanceFormData(string $type): array
    {
        return ['title' => $type === 'check_in' ? 'Check-in' : 'Check-out', 'employee' => $this->employee(), 'todayAttendance' => $this->todayAttendance(), 'type' => $type];
    }

    public function checkIn(array $payload, Request $request): void
    {
        $employee = $this->employee();
        $this->ensureEmployeeCanAttend($employee);
        if ($this->todayAttendance() !== null) {
            throw new RuntimeException('Check-in hari ini sudah ada.');
        }

        DB::transaction(function () use ($employee, $payload, $request): void {
            $distance = $this->distanceToWorkLocation($employee, (float) $payload['latitude'], (float) $payload['longitude']);
            $inside = $this->isInsideRadius($employee, $distance);
            $this->ensureOutsideRadiusNote($inside, $payload['note'] ?? null);
            $attachmentId = $this->storeCameraPhoto($payload['photo_data'], 'check-in');
            $attendance = Attendance::query()->create(['uuid' => (string) Str::uuid(), 'employee_id' => $employee->id, 'shift_id' => $employee->shift_id, 'work_location_id' => $employee->work_location_id, 'attendance_date' => today(), 'check_in_at' => now(), 'check_in_photo_attachment_id' => $attachmentId, 'check_in_latitude' => $payload['latitude'], 'check_in_longitude' => $payload['longitude'], 'check_in_distance_meter' => $distance, 'check_in_gps_accuracy_meter' => $payload['gps_accuracy_meter'] ?? null, 'check_in_is_inside_radius' => $inside, 'check_in_work_mode_id' => $payload['work_mode_id'] ?? null, 'check_in_note' => $payload['note'] ?? null, 'check_in_device_info' => $request->userAgent(), 'late_minutes' => $this->lateMinutes($employee), 'status_id' => $this->requiredAttendanceStatusId('present'), 'is_need_approval' => ! $inside, 'created_by' => auth()->id()]);
            $this->logAttendance($attendance, 'check_in', $payload, $attachmentId, $inside, $distance, $request);
        });
    }

    public function checkOut(array $payload, Request $request): void
    {
        $employee = $this->employee();
        $this->ensureEmployeeCanAttend($employee);
        $attendance = $this->todayAttendance();
        if ($attendance === null || $attendance->check_in_at === null) {
            throw new RuntimeException('Check-in belum ada.');
        }
        if ($attendance->check_out_at !== null) {
            throw new RuntimeException('Check-out hari ini sudah ada.');
        }

        DB::transaction(function () use ($employee, $attendance, $payload, $request): void {
            $distance = $this->distanceToWorkLocation($employee, (float) $payload['latitude'], (float) $payload['longitude']);
            $inside = $this->isInsideRadius($employee, $distance);
            $this->ensureOutsideRadiusNote($inside, $payload['note'] ?? null);
            $attachmentId = $this->storeCameraPhoto($payload['photo_data'], 'check-out');
            $attendance->update(['check_out_at' => now(), 'check_out_photo_attachment_id' => $attachmentId, 'check_out_latitude' => $payload['latitude'], 'check_out_longitude' => $payload['longitude'], 'check_out_distance_meter' => $distance, 'check_out_gps_accuracy_meter' => $payload['gps_accuracy_meter'] ?? null, 'check_out_is_inside_radius' => $inside, 'check_out_work_mode_id' => $payload['work_mode_id'] ?? null, 'check_out_note' => $payload['note'] ?? null, 'check_out_device_info' => $request->userAgent(), 'total_work_minutes' => $attendance->check_in_at->diffInMinutes(now()), 'early_leave_minutes' => 0, 'is_need_approval' => $attendance->is_need_approval || ! $inside, 'updated_by' => auth()->id()]);
            $this->logAttendance($attendance->refresh(), 'check_out', $payload, $attachmentId, $inside, $distance, $request);
        });
    }

    public function historyData(Request $request): array
    {
        $month = Carbon::parse($request->input('month', now()->format('Y-m')));

        return ['title' => 'Attendance History', 'month' => $month, 'items' => $this->historyQuery()->whereYear('attendance_date', $month->year)->whereMonth('attendance_date', $month->month)->paginate(15)->withQueryString()];
    }

    public function attendanceDetailData(int $id): array
    {
        return ['title' => 'Attendance Detail', 'item' => Attendance::query()->with(['shift', 'workLocation', 'status', 'logs'])->where('employee_id', $this->employee()->id)->findOrFail($id)];
    }

    public function leaveListData(): array
    {
        return ['title' => 'Leave Requests', 'items' => LeaveRequest::query()->with(['leaveType', 'status'])->where('employee_id', $this->employee()->id)->latest('start_date')->paginate(15)];
    }

    public function leaveFormData(): array
    {
        return ['title' => 'Create Leave Request', 'leaveTypes' => Reference::query()->whereIn('description', ['annual_leave', 'sick_leave', 'permission'])->get()];
    }

    public function leaveDetailData(int $id): array
    {
        return ['title' => 'Leave Request Detail', 'item' => LeaveRequest::query()->with(['leaveType', 'status'])->where('employee_id', $this->employee()->id)->findOrFail($id)];
    }

    public function createLeave(array $payload): void
    {
        $start = Carbon::parse($payload['start_date']);
        $end = Carbon::parse($payload['end_date']);
        LeaveRequest::query()->create(['uuid' => (string) Str::uuid(), 'employee_id' => $this->employee()->id, 'leave_type_id' => $payload['leave_type_id'], 'start_date' => $start, 'end_date' => $end, 'total_days' => $start->diffInDays($end) + 1, 'reason' => $payload['reason'], 'status_id' => $this->approvalStatusId('pending'), 'created_by' => auth()->id()]);
    }

    public function cancelLeave(int $id): void
    {
        $item = LeaveRequest::query()->where('employee_id', $this->employee()->id)->findOrFail($id);
        if ($item->status_id !== $this->approvalStatusId('pending')) {
            throw new RuntimeException('Hanya pending yang bisa dibatalkan.');
        }
        $item->delete();
    }

    public function correctionListData(): array
    {
        return ['title' => 'Attendance Corrections', 'items' => AttendanceCorrectionRequest::query()->with('status')->where('employee_id', $this->employee()->id)->latest('correction_date')->paginate(15)];
    }

    public function correctionFormData(): array
    {
        return ['title' => 'Create Attendance Correction'];
    }

    public function correctionDetailData(int $id): array
    {
        return ['title' => 'Attendance Correction Detail', 'item' => AttendanceCorrectionRequest::query()->with('status')->where('employee_id', $this->employee()->id)->findOrFail($id)];
    }

    public function createCorrection(array $payload): void
    {
        AttendanceCorrectionRequest::query()->create(['uuid' => (string) Str::uuid(), 'employee_id' => $this->employee()->id, 'correction_date' => $payload['correction_date'], 'requested_check_in_at' => $payload['requested_check_in_at'] ?? null, 'requested_check_out_at' => $payload['requested_check_out_at'] ?? null, 'reason' => $payload['reason'], 'status_id' => $this->approvalStatusId('pending'), 'created_by' => auth()->id()]);
    }

    public function cancelCorrection(int $id): void
    {
        $item = AttendanceCorrectionRequest::query()->where('employee_id', $this->employee()->id)->findOrFail($id);
        if ($item->status_id !== $this->approvalStatusId('pending')) {
            throw new RuntimeException('Hanya pending yang bisa dibatalkan.');
        }
        $item->delete();
    }

    public function profileData(): array
    {
        return ['title' => 'My Profile', 'employee' => $this->employee()->load(['user', 'department', 'position', 'workLocation', 'shift'])];
    }

    private function employee(): Employee
    {
        $employee = Employee::query()->with(['workLocation', 'shift'])->where('user_id', auth()->id())->first();

        if ($employee === null) {
            abort(403, 'Akun Employee belum terhubung ke data karyawan. Lengkapi data Employee pada menu Admin > Employees.');
        }

        return $employee;
    }

    private function todayAttendance(): ?Attendance
    {
        return Attendance::query()->with(['shift', 'workLocation', 'status'])->where('employee_id', $this->employee()->id)->whereDate('attendance_date', today())->first();
    }

    private function historyQuery()
    {
        return Attendance::query()->with(['shift', 'workLocation', 'status'])->where('employee_id', $this->employee()->id)->latest('attendance_date');
    }

    private function ensureEmployeeCanAttend(Employee $employee): void
    {
        if (! $employee->is_active) {
            throw new RuntimeException('Employee inactive tidak boleh absen.');
        }
        if ($employee->shift_id === null) {
            throw new RuntimeException('Shift karyawan belum diset. Lengkapi shift pada Admin > Employees sebelum absen.');
        }
        if ($employee->work_location_id === null) {
            throw new RuntimeException('Lokasi kerja karyawan belum diset. Lengkapi work location pada Admin > Employees sebelum absen.');
        }
        if ($employee->workLocation?->latitude === null || $employee->workLocation?->longitude === null) {
            throw new RuntimeException('Koordinat lokasi kerja belum lengkap. Lengkapi latitude dan longitude pada Admin > Work Locations sebelum absen.');
        }
        $this->requiredAttendanceStatusId('present');
        $this->requiredActionTypeId('check_in');
        $this->requiredActionTypeId('check_out');
        $this->requiredAttachmentImageTypeId();
    }

    private function ensureOutsideRadiusNote(bool $inside, ?string $note): void
    {
        if (! $inside && blank($note)) {
            throw new RuntimeException('Catatan wajib diisi jika berada di luar radius lokasi kerja.');
        }
    }

    private function distanceToWorkLocation(Employee $employee, float $latitude, float $longitude): ?float
    {
        if ($employee->workLocation?->latitude === null || $employee->workLocation?->longitude === null) {
            return null;
        }
        $earth = 6371000;
        $latFrom = deg2rad((float) $employee->workLocation->latitude);
        $lonFrom = deg2rad((float) $employee->workLocation->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt((sin($latDelta / 2) ** 2) + cos($latFrom) * cos($latTo) * (sin($lonDelta / 2) ** 2)));

        return round($earth * $angle, 2);
    }

    private function isInsideRadius(Employee $employee, ?float $distance): bool
    {
        if ($distance === null) {
            return true;
        }

        return $distance <= ($employee->workLocation?->radius_meter ?? 100);
    }

    private function lateMinutes(Employee $employee): int
    {
        if ($employee->shift?->start_time === null) {
            return 0;
        }
        $start = Carbon::parse(today()->format('Y-m-d').' '.$employee->shift->start_time);

        return max(0, $start->diffInMinutes(now(), false));
    }

    private function storeCameraPhoto(string $photoData, string $prefix): int
    {
        $content = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
        $path = 'attendance/'.date('Y/m').'/'.$prefix.'-'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, base64_decode($content));

        return Attachment::query()->create(['uuid' => (string) Str::uuid(), 'name' => basename($path), 'path' => $path, 'type_file' => $this->requiredAttachmentImageTypeId(), 'created_by' => auth()->id()])->id;
    }

    private function logAttendance(Attendance $attendance, string $action, array $payload, int $attachmentId, bool $inside, ?float $distance, Request $request): void
    {
        AttendanceLog::query()->create(['uuid' => (string) Str::uuid(), 'attendance_id' => $attendance->id, 'employee_id' => $attendance->employee_id, 'action_type_id' => $this->requiredActionTypeId($action), 'action_at' => now(), 'latitude' => $payload['latitude'], 'longitude' => $payload['longitude'], 'distance_meter' => $distance, 'gps_accuracy_meter' => $payload['gps_accuracy_meter'] ?? null, 'is_inside_radius' => $inside, 'work_mode_id' => $payload['work_mode_id'] ?? null, 'note' => $payload['note'] ?? null, 'photo_attachment_id' => $attachmentId, 'device_info' => $request->userAgent(), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'source' => 'web', 'created_by' => auth()->id()]);
    }

    private function approvalStatusId(string $description): ?int
    {
        return Reference::query()->where('description', $description)->value('id');
    }

    private function attendanceStatusId(string $description): ?int
    {
        return Reference::query()->where('description', $description)->value('id');
    }

    private function requiredAttendanceStatusId(string $description): int
    {
        $id = $this->attendanceStatusId($description);
        if ($id === null) {
            throw new RuntimeException("Reference attendance status {$description} belum tersedia.");
        }

        return $id;
    }

    private function requiredActionTypeId(string $description): int
    {
        $id = Reference::query()->where('description', $description)->value('id');
        if ($id === null) {
            throw new RuntimeException("Reference attendance action {$description} belum tersedia.");
        }

        return $id;
    }

    private function requiredAttachmentImageTypeId(): int
    {
        $id = Reference::query()->where('code', 'TF_IMG')->value('id');
        if ($id === null) {
            throw new RuntimeException('Reference TF_IMG belum tersedia.');
        }

        return $id;
    }
}
