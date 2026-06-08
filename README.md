# KlikAbsen

KlikAbsen adalah project Laravel baru hasil reuse struktur lama Etherno. Dokumen ini jadi source of truth singkat untuk sesi development berikutnya.

## Quick Context

- Stack: Laravel 13, PHP 8.3, MySQL.
- Local PHP: `C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe`.
- DB local `.env`: `DB_CONNECTION=mysql`, database `klikabsen`.
- Migration aktif hanya folder `database/migrations/0.0.1` sampai `0.0.4`.
- Loader migration ada di `app/Providers/AppServiceProvider.php`.
- Scope kode yang dipertahankan saat ini: auth, role, user, attachment, reference, setting, wilayah/location, cache/session/job infrastructure.
- Semua informasi bisnis Etherno sudah dihapus dari konteks project.

## Current Flow Penting

### Auth

- Login page: `GET /login`.
- Login submit: `POST /api/login`.
- Logout: `POST /api/logout`.
- Auth controller:
  - Web: `app/Http/Controllers/Web/Auth/AuthController.php`
  - API: `app/Http/Controllers/Api/Auth/AuthController.php`
- Auth service: `app/Services/AuthService.php`.
- Middleware role: `app/Http/Middleware/EnsureUserHasRole.php`.
- Session internal auth menyimpan:
  - `auth.role`
  - `auth.user`

### Role Aktif

Migration default role:

- `admin`
- `HRD`
- `karyawan`
- `Karyawan Kontrak`
- `Interns`

Role internal yang bisa masuk panel saat ini:

- `admin`
- `HRD`

Config akses ada di `config/role_access.php`.

Default user development:

- `admin@klikabsen.local` / `password`
- `hrd@klikabsen.local` / `password`
- `karyawan@klikabsen.local` / `password`
- `karyawan-kontrak@klikabsen.local` / `password`
- `interns@klikabsen.local` / `password`

## Route Aktif Minimal

- `GET /` redirect ke `/login`.
- `GET /login` halaman login.
- `POST /api/login` proses login.
- `POST /api/logout` proses logout.
- `GET /admin/dashboard` dashboard admin minimal.
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

Tabel utama yang tersisa:

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

Catatan:

- `locations` ditransform dari dataset `database/migrations/0.0.2/dataset/wilayah.sql`.
- Transform parent `locations.parent_id` sudah dibuat MySQL-safe.
- Index performance `0.0.4` saat ini tetap skip selain PostgreSQL.

Run migration:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate
```

Reset migration local:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate:fresh
```

## Model Yang Dipertahankan

- `App\Models\User`
- `App\Models\Role`
- `App\Models\Attachment`
- `App\Models\Reference`
- `App\Models\Setting`
- `App\Models\Wilayah`
- `App\Models\Location`
- `App\Models\Cache`
- `App\Models\CacheLock`
- `App\Models\Job`
- `App\Models\JobBatch`
- `App\Models\FailedJob`
- `App\Models\PasswordResetToken`
- `App\Models\Session`

Trait manual soft delete:

- `App\Models\Concerns\HasManualSoftDeletes`

## Repository Yang Dipertahankan

Base:

- `app/Repositories/Contracts/BaseRepositoryInterface.php`
- `app/Repositories/Eloquent/BaseRepository.php`

Repository aktif mengikuti model/tabel yang tersisa:

- Attachment
- Cache
- CacheLock
- FailedJob
- Job
- JobBatch
- Location
- PasswordResetToken
- Reference
- Role
- Session
- Setting
- User
- Wilayah

Binding repository ada di `app/Providers/RepositoryServiceProvider.php`.

## Service Yang Dipertahankan

- `app/Services/AuthService.php`
- `app/Services/AttachmentSecurityService.php`

## Development Commands

PHP version:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" --version
```

Clear cache:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan optimize:clear
```

Jika `CACHE_STORE=database` tapi tabel `cache` belum ada, pakai sementara:

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

- Jangan revive modul bisnis Etherno tanpa instruksi eksplisit.
- Jangan tambah model/repository/service baru kecuali tabel/flow baru sudah jelas.
- Saat tambah folder migration baru, update `AppServiceProvider`.
- Setelah edit config/route/provider, jalankan `artisan optimize:clear` atau gunakan `CACHE_STORE=array` bila DB cache belum siap.
- Jalankan minimal `php -l` untuk file PHP yang diedit.
