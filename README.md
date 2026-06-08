# KlikAbsen

KlikAbsen adalah aplikasi absensi karyawan berbasis Laravel. README ini adalah source of truth untuk agent/developer berikutnya agar tidak menghidupkan ulang modul lama Etherno dan tetap mengikuti struktur terbaru.

## Quick Context

- Stack: Laravel 13, PHP 8.3, MySQL.
- Local PHP: `C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe`.
- DB local `.env`: `DB_CONNECTION=mysql`, database `klikabsen`.
- Migration aktif: `database/migrations/0.0.1` sampai `database/migrations/0.0.5`.
- Loader migration: `app/Providers/AppServiceProvider.php`.
- Project sudah diarahkan ke domain absensi: employee, work location, shift, attendance, leave request, correction, approval, report, audit log.
- Jangan restore modul bisnis Etherno.

## Flow Penting

### Auth

- Default page: `GET /` redirect ke `/login`.
- Login page: `GET /login` via `app/Http/Controllers/Web/Auth/AuthController.php`.
- Login submit: `POST /api/login` via `app/Http/Controllers/Api/Auth/AuthController.php`.
- Logout: `POST /api/logout`.
- Auth service: `app/Services/AuthService.php`.
- Role middleware: `app/Http/Middleware/EnsureUserHasRole.php`.
- Session internal auth:
  - `auth.role`
  - `auth.user`

Catatan penting auth:

- `GET /login` tidak memakai middleware `guest` agar tidak terjadi redirect loop saat user sudah login.
- `AuthController@showLogin` menangani user yang sudah login: redirect ke dashboard jika internal, logout jika bukan internal.
- Role internal yang boleh masuk panel saat ini hanya `Admin` dan `HRD`.

### Role Aktif

Role final setelah migration `0.0.5`:

- `Admin`
- `HRD`
- `Employee`

`karyawan`, `Karyawan Kontrak`, dan `Interns` tidak lagi role final. Jenis karyawan disimpan di `employees.employee_type_id` lewat reference `EMPLOYEE_TYPE`.

Default user development:

- `admin@klikabsen.local` / `password` -> `Admin`
- `hrd@klikabsen.local` / `password` -> `HRD`
- `employee@klikabsen.local` / `password` -> `Employee`

Akun lama `karyawan@klikabsen.local`, `karyawan-kontrak@klikabsen.local`, dan `interns@klikabsen.local` dapat tetap ada di DB lokal, tetapi diarahkan ke role `Employee` oleh migration sync role.

Config akses:

- `config/role_access.php`
- Route prefix: `Admin -> admin`, `HRD -> hrd`
- Dashboard route: `admin.dashboard`, `hrd.dashboard`

## Route Aktif Minimal

- `GET /` redirect ke `/login`.
- `GET /login` halaman login.
- `POST /api/login` proses login.
- `POST /api/logout` proses logout.
- `GET /admin/dashboard` dashboard Admin minimal.
- `GET /hrd/dashboard` dashboard HRD minimal.

Cek route:

```powershell
$env:CACHE_STORE='array'; & "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan route:list
```

## Migration Aktif

Migration diload dari:

- `database/migrations/0.0.1`
- `database/migrations/0.0.2`
- `database/migrations/0.0.3`
- `database/migrations/0.0.4`
- `database/migrations/0.0.5`

### Version Map

- `0.0.1`: Laravel base users/cache/jobs/test user.
- `0.0.2`: wilayah, references, locations, attachments, settings.
- `0.0.3`: roles, default users, alter users role/profile.
- `0.0.4`: performance index migration; saat ini skip selain PostgreSQL.
- `0.0.5`: schema absensi dan update role final.

### 0.0.5 Migration Files

- `001_000_005_000001_sync_attendance_roles.php`
- `001_000_005_000002_insert_attendance_references.php`
- `001_000_005_000003_insert_attendance_settings.php`
- `001_000_005_000004_create_hr_master_tables.php`
- `001_000_005_000005_create_work_location_and_shift_tables.php`
- `001_000_005_000006_create_employee_tables.php`
- `001_000_005_000007_create_schedule_and_holiday_tables.php`
- `001_000_005_000008_create_attendances_table.php`
- `001_000_005_000009_create_attendance_logs_table.php`
- `001_000_005_000010_create_leave_tables.php`
- `001_000_005_000011_create_attendance_correction_requests_table.php`
- `001_000_005_000012_create_approval_report_and_audit_tables.php`

Catatan migration:

- Jangan buat satu migration besar untuk banyak domain; pisahkan per domain/table group.
- Karena migration belum merge ke main, revisi boleh dilakukan langsung pada file `0.0.5` terkait, bukan membuat patch migration baru.
- Jika migration sudah merge/staging/production, baru gunakan migration patch baru.
- `locations` ditransform dari `database/migrations/0.0.2/dataset/wilayah.sql` dan sudah MySQL-safe.
- `users.uuid` final dibuat NOT NULL dan `users.username` unique lewat `0.0.5` sync role migration.

## Tabel Aktif

Base/support:

- `users`
- `roles`
- `attachments`
- `references`
- `settings`
- `wilayah`
- `locations`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `password_reset_tokens`
- `sessions`

Absensi/HR:

- `departments`
- `positions`
- `work_locations`
- `shifts`
- `employees`
- `employee_work_locations`
- `employee_schedules`
- `holidays`
- `attendances`
- `attendance_logs`
- `leave_requests`
- `leave_request_details`
- `leave_balances`
- `attendance_correction_requests`
- `approvals`
- `attendance_monthly_summaries`
- `activity_logs`

## Reference Group Aktif Absensi

Migration `0.0.5` insert:

- `EMPLOYEE_TYPE`: `permanent`, `contract`, `intern`
- `ATTENDANCE_STATUS`: `present`, `late`, `absent`, `leave`, `sick`, `permission`, `incomplete`, `pending_approval`
- `LEAVE_TYPE`: `annual_leave`, `sick_leave`, `permission`
- `APPROVAL_STATUS`: `pending`, `approved`, `rejected`, `cancelled`
- `WORK_MODE`: `office`, `wfh`, `business_trip`, `outside_meeting`, `client_visit`
- `ATTENDANCE_ACTION_TYPE`: `check_in`, `check_out`, `update_by_hrd`, `approval_by_hrd`

## Settings Absensi

Migration `0.0.5` insert group `attendance`:

- `ATTENDANCE_RADIUS_METER = 100`
- `REQUIRE_SELFIE = true`
- `REQUIRE_GPS = true`
- `ALLOW_OUTSIDE_RADIUS = true`
- `REQUIRE_OUTSIDE_RADIUS_NOTE = true`
- `LATE_TOLERANCE_MINUTES = 15`

## Model Aktif

Infrastructure/base:

- `User`, `Role`, `Attachment`, `Reference`, `Setting`, `Wilayah`, `Location`
- `Cache`, `CacheLock`, `Job`, `JobBatch`, `FailedJob`, `PasswordResetToken`, `Session`

Absensi/HR:

- `Department`
- `Position`
- `WorkLocation`
- `Shift`
- `Employee`
- `EmployeeWorkLocation`
- `EmployeeSchedule`
- `Holiday`
- `Attendance`
- `AttendanceLog`
- `LeaveRequest`
- `LeaveRequestDetail`
- `LeaveBalance`
- `AttendanceCorrectionRequest`
- `Approval`
- `AttendanceMonthlySummary`
- `ActivityLog`

Trait manual soft delete:

- `App\Models\Concerns\HasManualSoftDeletes`

## Repository Aktif

Base repository:

- `app/Repositories/Contracts/BaseRepositoryInterface.php`
- `app/Repositories/Eloquent/BaseRepository.php`

Setiap model aktif punya:

- `app/Repositories/Contracts/{Model}RepositoryInterface.php`
- `app/Repositories/Eloquent/{Model}Repository.php`

Binding repository:

- `app/Providers/RepositoryServiceProvider.php`

Jika tambah model baru, buat contract, Eloquent repository, lalu bind di provider.

## Service Aktif

Auth/support:

- `app/Services/AuthService.php`
- `app/Services/AttachmentSecurityService.php`

Domain HR:

- `app/Services/Hr/HrMasterService.php`
- `app/Services/Hr/EmployeeService.php`

Domain Attendance:

- `app/Services/Attendance/AttendanceMasterService.php`
- `app/Services/Attendance/AttendanceService.php`
- `app/Services/Attendance/LeaveRequestService.php`
- `app/Services/Attendance/AttendanceReportService.php`

Service saat ini masih dasar untuk create/orchestration awal. Business rule lengkap akan ditambah saat modul fitur dibuat.

## View & Layout Status

Views aktif saat ini:

- `resources/views/layouts/auth/auth.blade.php`
- `resources/views/pages/auth/login.blade.php`
- `resources/views/layouts/admin/*`
- `resources/views/pages/admin/blank.blade.php`

Catatan penting:

- Login view mobile-first dan independen; tidak bergantung layout public.
- Admin layout memakai template profesional Nova/Nowa yang sudah ada.
- Jangan rombak struktur template admin tanpa instruksi eksplisit.
- Jika perlu ubah tampilan admin, ubah warna/override CSS saja di `resources/views/layouts/admin/assets.blade.php`.
- Sidebar harus aman terhadap route yang belum dibuat. Jangan panggil `route()` tanpa cek `Route::has()` untuk menu dinamis.

## Development Commands

PHP version:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" --version
```

Run migration:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate
```

Fresh migration local:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate:fresh
```

Fresh migration test DB:

```powershell
$env:DB_DATABASE='klikabsen_migration_test'; & "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate:fresh
```

Clear cache:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan optimize:clear
```

Jika `CACHE_STORE=database` bermasalah atau tabel belum siap:

```powershell
$env:CACHE_STORE='array'; & "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan optimize:clear
```

Syntax check file PHP:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" -l app/Services/AuthService.php
```

Run tests:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan test
```

Start server:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan serve
```

## Agent Rules

- Baca README ini sebelum membuat fitur/migration baru.
- Jangan revive modul bisnis Etherno.
- Jangan rombak layout admin template profesional kecuali diminta eksplisit; color override saja boleh.
- Login page boleh diubah, tetapi harus mobile-first karena karyawan banyak akses dari HP.
- Untuk route/menu dinamis, cek `Route::has()` sebelum generate URL.
- Untuk feature baru, ikuti alur: Controller -> Service -> Repository -> Model.
- Setelah edit config/routes/provider/views, jalankan `artisan optimize:clear`.
- Jalankan minimal `php -l` untuk file PHP yang diedit.

---

# Business Requirement & Feature Roadmap

Dokumen BRD lengkap tersedia pada `business_requirement_document.md`. Bagian README ini adalah ringkasan teknis agar agent/developer cepat memahami modul, role, flow, dan prioritas implementasi.

## Product Goal

KlikAbsen adalah aplikasi absensi karyawan untuk:

- Master data HR: employee, department, position, work location, shift, holiday.
- Absensi masuk/keluar dengan foto dari kamera dan GPS.
- Validasi radius 100 meter, tetapi outside radius tetap diperbolehkan untuk WFH, dinas luar, meeting luar, atau client visit dengan work mode dan catatan.
- Pengajuan sakit/cuti/izin.
- Pengajuan koreksi absensi.
- Approval HRD.
- Monitoring dan report absensi.

## Route Prefix Target

- Admin: `/admin/*`
- HRD: `/hrd/*`
- Employee: `/employee/*`
- Auth API: `/api/login`, `/api/logout`

## Admin Modules

Menu target Admin:

- `/admin/dashboard`
- `/admin/departments`
- `/admin/positions`
- `/admin/work-locations`
- `/admin/shifts`
- `/admin/holidays`
- `/admin/employees`
- `/admin/employee-work-locations`
- `/admin/settings`
- `/admin/references`
- `/admin/roles`
- `/admin/users`
- `/admin/activity-logs`

Prioritas MVP Admin:

1. Dashboard Admin.
2. CRUD Departments.
3. CRUD Positions.
4. CRUD Work Locations.
5. CRUD Shifts.
6. CRUD Holidays.
7. CRUD Employees.

## HRD Modules

Menu target HRD:

- `/hrd/dashboard`
- `/hrd/attendances`
- `/hrd/attendances/not-checked-in`
- `/hrd/attendances/incomplete`
- `/hrd/attendances/late`
- `/hrd/attendances/outside-radius`
- `/hrd/attendances/{id}`
- `/hrd/leave-requests`
- `/hrd/attendance-corrections`
- `/hrd/employee-schedules`
- `/hrd/leave-balances`
- `/hrd/reports/daily-attendance`
- `/hrd/reports/monthly-attendance`
- `/hrd/reports/employees/{id}`

Prioritas MVP HRD:

1. Dashboard HRD.
2. Monitoring absensi harian.
3. Detail attendance.
4. Approval leave request.
5. Approval correction request.
6. Review outside radius attendance.
7. Report harian.
8. Report bulanan.
9. Export Excel.

## Employee Modules

Menu target Employee:

- `/employee/dashboard`
- `/employee/attendance`
- `/employee/attendance/check-in`
- `/employee/attendance/check-out`
- `/employee/attendance/history`
- `/employee/attendance/history/{id}`
- `/employee/leave-requests`
- `/employee/leave-requests/create`
- `/employee/leave-requests/{id}`
- `/employee/attendance-corrections`
- `/employee/attendance-corrections/create`
- `/employee/attendance-corrections/{id}`
- `/employee/profile`

Prioritas MVP Employee:

1. Dashboard Employee.
2. Status absensi hari ini.
3. Check-in dengan kamera dan GPS.
4. Check-out dengan kamera dan GPS.
5. History absensi bulan berjalan.
6. Pengajuan sakit/cuti/izin.
7. Pengajuan koreksi absensi.
8. Profil saya.

## Core Flow Summary

### Check-in

1. Employee login.
2. Buka `/employee/attendance`.
3. Sistem cek employee aktif, shift, work location, dan attendance hari ini.
4. Employee ambil foto dari kamera.
5. Browser mengambil GPS.
6. Sistem hitung jarak ke work location.
7. Jika dalam radius, simpan normal.
8. Jika outside radius, employee wajib pilih work mode dan isi catatan.
9. Simpan `attendances` dan `attendance_logs`.

### Check-out

1. Employee harus sudah check-in.
2. Employee ambil foto dari kamera dan GPS.
3. Sistem hitung jarak.
4. Jika outside radius, wajib work mode dan catatan.
5. Update attendance check-out.
6. Hitung total work minutes dan early leave minutes.
7. Simpan attendance log.

### Leave Request

1. Employee submit annual leave, sick leave, atau permission.
2. Status awal `pending`.
3. HRD approve/reject.
4. Jika approved, sistem update/membuat attendance sesuai tanggal.
5. Jika rejected, wajib rejected reason.

### Attendance Correction

1. Employee submit koreksi untuk tanggal tertentu.
2. Status awal `pending`.
3. HRD approve/reject.
4. Jika approved, sistem membuat/update attendance dan attendance log.
5. Jika rejected, wajib rejected reason.

### Outside Radius Review

1. Outside radius disimpan dengan `is_need_approval = true`.
2. HRD review foto, GPS, jarak, work mode, catatan, dan device info.
3. HRD approve atau flag/reject.
4. Untuk MVP, reject/flag tidak menghapus attendance.

## Implementation Phases

### Phase 1 - Routing, Layout, Placeholder

- Tambah route prefix `/employee`.
- Pastikan redirect login sesuai role.
- Buat dashboard Admin, HRD, Employee.
- Update sidebar/menu sesuai role.
- Semua menu harus aman dengan `Route::has()`.

### Phase 2 - Master Data Admin

- CRUD Departments.
- CRUD Positions.
- CRUD Work Locations.
- CRUD Shifts.
- CRUD Holidays.
- CRUD Employees.

### Phase 3 - Attendance Employee

- Halaman attendance mobile-first.
- Kamera browser.
- Geolocation browser.
- Check-in.
- Check-out.
- Hitung radius.
- Wajib catatan outside radius.
- Simpan attachment dan attendance log.

### Phase 4 - Leave dan Correction

- Employee leave request.
- HRD leave approval.
- Employee correction request.
- HRD correction approval.
- Update attendance hasil approval.

### Phase 5 - HRD Monitoring dan Report

- Dashboard HRD real data.
- Monitoring harian.
- Detail attendance.
- Outside radius review.
- Report harian.
- Report bulanan.
- Generate monthly summary.
- Export Excel.

### Phase 6 - Audit dan Testing

- Activity log.
- Authorization policy.
- Form request validation.
- Feature test login.
- Feature test CRUD master.
- Feature test check-in/check-out.
- Feature test leave approval.
- Feature test correction approval.

## Global Acceptance Criteria

- Admin bisa CRUD master data utama.
- HRD bisa monitoring absensi.
- Employee bisa check-in dan check-out.
- Foto absensi wajib dari kamera.
- GPS wajib saat absensi.
- Radius default 100 meter.
- Outside radius tetap bisa dilakukan dengan work mode dan catatan.
- Outside radius ditandai perlu approval.
- Employee bisa melihat history absensi bulan berjalan.
- Employee bisa mengajukan sakit/cuti/izin.
- HRD bisa approve/reject sakit/cuti/izin.
- Employee bisa mengajukan koreksi absensi.
- HRD bisa approve/reject koreksi.
- HRD bisa membuat report harian dan bulanan.
- Export Excel tersedia minimal untuk report.
- Activity log mencatat aksi penting.

