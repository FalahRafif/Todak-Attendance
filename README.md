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
