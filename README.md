# Etherno

Etherno adalah aplikasi pemesanan layanan dokumentasi foto/video untuk kebutuhan wedding dan non-wedding. README ini adalah source of truth untuk sesi development berikutnya, terutama jika project dikerjakan bersama beberapa agent AI.

Sebelum membuat fitur baru, mengubah flow booking, menambah migration, atau memindahkan struktur route/layout, baca dokumen ini dulu lalu cocokkan dengan file aktual di repo.

## Quick Context For Future Agents

- Stack utama: Laravel 13, PHP 8.3, PostgreSQL untuk migration lanjutan tertentu.
- Local PHP yang dipakai user: `C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe`.
- Arsitektur aplikasi diarahkan ke flow tipis: Controller -> Service -> Repository -> Model.
- Public/guest dan internal panel dipisah dari route sampai layout.
- Internal panel punya dua role aktif: `Admin` dan `Petugas`.
- Role `Customer` sudah ada di database, tetapi login/dashboard customer belum dipakai untuk saat ini.
- Hak akses menu/route masih hardcoded di `config/role_access.php`, belum memakai tabel `role_menu` atau permission table.
- Semua migration sudah dikelompokkan per folder versi di `database/migrations/*` dan diload lewat `AppServiceProvider`.
- Banyak tabel memakai soft delete manual lewat kolom `delete_status`, `deleted_at`, dan `deleted_by`, bukan trait `SoftDeletes`.
- Error page production sudah custom dan auto-switch layout: context public pakai layout public, context internal (`admin`/`petugas`) pakai shell admin.
- Session internal auth menyimpan `auth.role` dan snapshot user di `auth.user` untuk kebutuhan panel/layout.
- TTL signed URL untuk preview attachment internal diatur lewat `ATTACHMENT_TEMP_URL_TTL_MINUTES` (default `30`).
- Booking module menggunakan `barryvdh/laravel-dompdf` untuk generate PDF bukti pengajuan booking.
<!-- Updated: Tambah catatan dependency DomPDF untuk booking module -->

## Product Principles

- Booking belum dianggap fix sebelum DP berhasil diverifikasi.
- Slot hanya terblokir setelah DP dibayarkan dan diverifikasi.
- Harga awal hanya menampilkan base price plus estimasi/range biaya tambahan, bukan angka final.
- WhatsApp adalah kanal komunikasi utama untuk pembayaran, koordinasi, reschedule, cancellation, dan follow-up setelah booking.
- Upload bukti pembayaran di sistem bersifat opsional sebagai support flow, bukan pengganti WhatsApp.
- Admin/petugas tetap melakukan verifikasi manual untuk approval, DP, pelunasan, reschedule, cancellation, dan force majeure.

## Business Flow

### 1. Booking Request

Customer mengisi form booking dengan data nama, nomor WhatsApp, tanggal acara, lokasi acara, pin Google Maps, paket yang dipilih, dan detail acara.

Form booking menghasilkan request/penawaran, bukan booking final. Status awal harus mencerminkan bahwa booking masih menunggu review internal.

### 2. Approval Before Payment

Sistem memakai approval sebelum pembayaran.

- Customer submit request booking.
- Petugas/Admin review data booking.
- Jika disetujui, customer diminta membayar DP.
- Booking dianggap fix hanya setelah DP dibayarkan dan diverifikasi.

Customer tidak boleh dianggap memiliki slot sebelum DP verified.

### 3. Slot And Schedule

<!-- Updated: Kuota per sesi sekarang configurable lewat settings, bukan hardcoded 2 per hari -->

Aturan jadwal:

- Kuota booking per sesi dikonfigurasi melalui settings `PKDR_MAX_QUOTA_PAGI_SIANG` dan `PKDR_MAX_QUOTA_SORE_MALAM` (group `package_date_rule`), default 1 per sesi.
- Sesi tersedia: `PAGI - SIANG` (`ES_PAGI_SIANG`) dan `SORE - MALAM` (`ES_SORE_MALAM`).
- Sistem menerapkan First Come First Serve berdasarkan DP.
- Slot hanya terblokir setelah DP berhasil diverifikasi.
- Booking pending approval, approved but unpaid, expired, atau cancelled tidak memblokir slot.
- Pengecekan ketersediaan slot (availability) dilakukan via `GET /api/booking/availability`.

Implikasi teknis: availability harus dihitung dari booking aktif yang DP-nya sudah verified atau status bisnis yang setara. Status yang tidak menghitung kuota: `BS_EXPIRED`, `BS_EXPIRED_DP`, `BS_CANCEL`, `BS_RESCHEDULE`, `BS_FORCE_MAJEURE`, `BS_REFUND`.

### 4. Pricing And Location

Tampilan awal harus menampilkan base price paket dan estimasi/range biaya tambahan berdasarkan lokasi.

Kategori lokasi yang sudah disiapkan lewat reference `price_type`:

- `PRT_TAMBAHAN_RINGAN`: Tambahan Ringan.
- `PRT_TAMBAHAN_SEDANG`: Tambahan Sedang.
- `PRT_TAMBAHAN_CUSTOM`: Tambahan Custom (Transport / Akomodasi).

Perhitungan lokasi berbasis area/kota, bukan kilometer. Biaya tambahan dapat meliputi transport, akomodasi, dan overtime. Harga final dikonfirmasi setelah pengecekan.

### 5. Payment Scheme

DP menjadi metode utama untuk mengunci booking.

- Wedding: DP 15%.
- Non-wedding: DP 10%.
- DP maksimal dibayar H+3 setelah approval/penawaran.
- Pelunasan maksimal H-1 acara.
- Pelunasan dan DP diverifikasi manual.

Rules ini disimpan di tabel `settings`:

- `paymet_date_rule` dengan code `PDR_DP` dan `PDR_MAX_FINAL`.
- `payment_type_price_percentage` dengan code `PTPP_WED` dan `PTPP_NON_WED`.
- `package_date_rule` dengan code `PKDR_MAX_RECHEDULE_DATE`.
- `package_date_rule` dengan code `PKDR_MAX_QUOTA_PAGI_SIANG` dan `PKDR_MAX_QUOTA_SORE_MALAM` (kuota per sesi).

<!-- Updated: Tambah settings code untuk kuota per sesi -->

Catatan kompatibilitas: `paymet_date_rule` memakai typo existing. Jangan rename tanpa migration perbaikan yang eksplisit.

### 6. Expiration, Reschedule, Cancellation, Force Majeure

- Jika DP belum dibayar maksimal 3 hari setelah approval, booking menjadi expired dan slot kembali tersedia.
- Reschedule maksimal 14 hari sebelum acara dan bergantung ketersediaan jadwal.
- Cancellation setelah DP berarti DP hangus/non-refundable.
- Force majeure diproses manual. Jika fotografer berhalangan, Etherno menyediakan pengganti. Jika kondisi ekstrem, refund dapat dilakukan setelah dikurangi biaya operasional.

## Current Technical Architecture

### Route Structure

Registrasi route ada di `bootstrap/app.php` melalui `withRouting(...)`:

- `web: routes/web.php`
- `api: routes/api.php`
- `commands: routes/console.php`

Entry point route web ada di `routes/web.php` dan me-require file route lain:

- `routes/web/auth.php`: halaman login (view-only, method GET).
- `routes/web/guest.php`: halaman public/guest.
- `routes/web/admin.php`: panel admin.
- `routes/web/petugas.php`: panel petugas.

Entry point route API ada di `routes/api.php` dan saat ini me-require:

- `routes/api/auth.php`: endpoint auth yang menerima request.
- `routes/api/guest.php`: endpoint public booking (store, availability, estimate, status lookup, upload payment proof).
<!-- Updated: Tambah routes/api/guest.php sebagai file route baru -->
- `routes/api/admin.php`: endpoint internal admin (CRUD akun internal, booking management, dll).
- `routes/api/attachment.php`: endpoint secure preview attachment (signed URL).

Endpoint auth saat ini:

- `POST /api/login` (name `login.post`), middleware `web` + `guest`.
- `POST /api/logout` (name `logout`), middleware `web` + `auth`.

Endpoint public booking request:

<!-- Updated: Endpoint public booking diperluas — tambah availability, estimate, status lookup, upload payment proof -->

- `GET /api/booking/availability` (name `booking.availability`), middleware `web` — cek ketersediaan slot per sesi pada tanggal tertentu.
- `GET /api/booking/location-options` (name `booking.location.options`), middleware `web` — opsi lokasi berdasarkan level dan parent.
- `GET /api/booking/estimate` (name `booking.estimate`), middleware `web` — estimasi harga paket berdasarkan lokasi (location pricing rule) dan DP.
- `GET /api/booking/status` (name `booking.status.lookup`), middleware `web` — cek status booking via booking code + 4 digit terakhir nomor telepon customer.
- `POST /api/booking` (name `booking.store`), middleware `web` — submit booking request baru.
- `POST /api/booking/upload-payment-proof` (name `booking.upload-payment-proof`), middleware `web` — upload bukti pembayaran oleh customer (DP atau pelunasan).

Booking code format yang didukung untuk status lookup dan referensi:
- Case ID: `ETH-{YYYYMMDD}-{NNNNN}` (format utama, disimpan di `bookings.case_id`).
- Request Code: `ETH-REQ-{YYYY}-{NNNNNN}` (generated, tidak disimpan di DB).
- Raw ID (angka).
- UUID.

Controller dan route dibagi jelas antara Web dan API:

- Web controllers ada di `app/Http/Controllers/Web/*` untuk render page (GET) dan redirect.
- API controllers ada di `app/Http/Controllers/Api/*` untuk submit/CRUD (POST/PUT/DELETE) dengan response JSON.
- Mayoritas endpoint API tetap memakai middleware `web` agar session dan CSRF browser tetap jalan.
- Internal API dipisah di prefix `/api/internal` untuk signed URL attachment.

Endpoint admin API (mutasi data internal):

- Users: `GET/POST/PUT/DELETE /api/admin/users` (name `api.admin.users.*`), middleware `web` + `auth` + `role:Admin`.
- Packages: `POST/PUT/DELETE /api/admin/packages` (name `api.admin.packages.*`), middleware `web` + `auth` + `role:Admin`.
- Location pricing rules: `POST/PUT/DELETE /api/admin/location-rules` (name `api.admin.location-rules.*`), middleware `web` + `auth` + `role:Admin`.
- Location options: `GET /api/admin/location-rules/options` (name `api.admin.location-rules.options`), middleware `web` + `auth` + `role:Admin`.
- Payment date rules: `POST/PUT/DELETE /api/admin/payment-date-rules` (name `api.admin.payment-date-rules.*`), middleware `web` + `auth` + `role:Admin`.
- DP percentage rules: `POST/PUT/DELETE /api/admin/dp-percentage-rules` (name `api.admin.dp-percentage-rules.*`), middleware `web` + `auth` + `role:Admin`.
- Package date rules: `POST/PUT/DELETE /api/admin/package-date-rules` (name `api.admin.package-date-rules.*`), middleware `web` + `auth` + `role:Admin`.
- Profile internal: `PUT /api/admin/profile` (name `api.admin.profile.update`), middleware `web` + `auth`.

<!-- Updated: Tambah seluruh section booking management admin API endpoint -->

Endpoint admin booking management API (seluruhnya middleware `web` + `auth` + `role:Admin`):

- Approve booking: `POST /api/admin/bookings/{booking}/approve` (name `api.admin.bookings.approve`).
- Reject booking: `POST /api/admin/bookings/{booking}/reject` (name `api.admin.bookings.reject`).
- Upload payment manual: `POST /api/admin/bookings/{booking}/upload-payment` (name `api.admin.bookings.upload-payment`).
- Verify DP: `POST /api/admin/bookings/{booking}/verify-dp` (name `api.admin.bookings.verify-dp`).
- Reject manual (setelah approval): `POST /api/admin/bookings/{booking}/reject-manual` (name `api.admin.bookings.reject-manual`).
- Verify final payment: `POST /api/admin/bookings/{booking}/verify-final` (name `api.admin.bookings.verify-final`).
- Approve pending payment: `POST /api/admin/bookings/{booking}/payments/{payment}/approve` (name `api.admin.bookings.payments.approve`).
- Reject pending payment: `POST /api/admin/bookings/{booking}/payments/{payment}/reject` (name `api.admin.bookings.payments.reject`).
- Cancel booking: `POST /api/admin/bookings/{booking}/cancel` (name `api.admin.bookings.cancel`).
- Complete booking: `POST /api/admin/bookings/{booking}/complete` (name `api.admin.bookings.complete`).
- Force majeure: `POST /api/admin/bookings/{booking}/force-majeure` (name `api.admin.bookings.force-majeure`).
- Upload refund proof: `POST /api/admin/bookings/{booking}/upload-refund-proof` (name `api.admin.bookings.upload-refund-proof`).
- Store billing detail: `POST /api/admin/bookings/{booking}/billing-details` (name `api.admin.bookings.billing-details.store`).
- Generate installment: `POST /api/admin/bookings/{booking}/installments` (name `api.admin.bookings.installments.store`).

Alur status booking internal: `BS_WAITING_APPROVAL` → `BS_APPROVED_WAITING_DP` (billing DP dibuat otomatis saat approve) → `BS_APPROVED_WAITING_FINAL_PAYMENT` (setelah DP verified) → `BS_CONFIRMED` (setelah pelunasan verified) → `BS_COMPLETE`. Status lain: `BS_REJECTED`, `BS_CANCEL`, `BS_EXPIRED`, `BS_EXPIRED_DP`, `BS_FORCE_MAJEURE`, `BS_REFUND`.

Endpoint secure preview attachment internal:

- `GET /api/internal/attachments/{attachmentUuid}/preview` (name `api.internal.attachments.preview`), middleware `web` + `auth` + `signed` + validasi role internal (`Admin`/`Petugas`) di controller.
- URL selalu temporary signed URL (default TTL 30 menit) dan wajib digenerate ulang setelah expired.

<!-- Updated: Tambah route about.etherno, booking.flow redirect, booking.proof.download -->

Public route memakai nama seperti `home`, `packages.page`, `about.etherno`, `booking.page`, `booking.flow` (redirect ke `/booking`), `booking.success`, `booking.status`, `booking.payment.dp`, `booking.payment.final`, `booking.reschedule`, `booking.cancellation.policy`, `booking.proof.download` (signed URL untuk download PDF bukti pengajuan booking).

Admin route memakai prefix URL `/admin`, name prefix `admin.`, middleware `auth` dan `role:Admin`.

<!-- Updated: Tambah bookings list (GET /admin/bookings) dan calendar events (GET /admin/calendar/events) -->

Route admin web (GET only) untuk render UI:

- Users management: `GET /admin/users`, `GET /admin/users/create`, `GET /admin/users/{user}/edit`.
- Packages: `GET /admin/packages`, `GET /admin/packages/create`, `GET /admin/packages/{package}/edit`.
- Location pricing rules: `GET /admin/location-rules`, `GET /admin/location-rules/create`, `GET /admin/location-rules/{locationPricingRule}/edit`.
- Payment date rules: `GET /admin/payment-date-rules`, `GET /admin/payment-date-rules/create`, `GET /admin/payment-date-rules/{setting}/edit`.
- DP percentage rules: `GET /admin/dp-percentage-rules`, `GET /admin/dp-percentage-rules/create`, `GET /admin/dp-percentage-rules/{setting}/edit`.
- Package date rules: `GET /admin/package-date-rules`, `GET /admin/package-date-rules/create`, `GET /admin/package-date-rules/{setting}/edit`.
- Preview pages: `GET /admin/dashboard`, `GET /admin/bookings` (list dengan filter/pagination), `GET /admin/bookings/requests`, `GET /admin/bookings/active`, `GET /admin/bookings/{booking}` (detail — parameter menggunakan case ID, misalnya `ETH-20260529-00001`), `GET /admin/calendar`, `GET /admin/calendar/events` (JSON endpoint untuk FullCalendar), `GET /admin/payments/dp`, `GET /admin/payments/final`, `GET /admin/pricing/reviews`, `GET /admin/reschedules`, `GET /admin/cancellations`, `GET /admin/force-majeure`, `GET /admin/customers`, `GET /admin/settings`, `GET /admin/blank`.

Petugas route memakai prefix URL `/petugas`, name prefix `petugas.`, middleware `auth` dan `role:Petugas` untuk route operasional. Petugas memiliki akses ke halaman booking (list, requests, active, detail), calendar (termasuk calendar events API), payments, dan customers — sama seperti admin namun tanpa akses CRUD master. Di file `routes/web/petugas.php` juga ada route admin-only dengan prefix `/petugas` dan middleware `role:Admin` untuk page master seperti `packages`, `location-rules`, dan `settings`. Jika behavior ini berubah, update route dan dokumentasi bersamaan.

<!-- Updated: Perjelas bahwa petugas punya akses booking dan calendar -->

### Layout Structure

- Internal panel: `resources/views/layouts/admin/admin.blade.php`.
- Admin sidebar: `resources/views/layouts/admin/sidebar.blade.php`.
- Public base layout: `resources/views/layouts/public/public.blade.php`.
- Guest alias layout: `resources/views/layouts/guest/guest.blade.php`.
- Public header config-driven: `resources/views/layouts/public/header.blade.php`.
- Auth layout: `resources/views/layouts/auth/auth.blade.php`.
- Error layout wrapper (context-aware): `resources/views/errors/layout.blade.php`.
- Error layout public: `resources/views/errors/public-layout.blade.php`.
- Error layout internal panel: `resources/views/errors/admin-layout.blade.php`.
- Error status views: `resources/views/errors/{401,403,404,419,422,429,500,503}.blade.php` + fallback `4xx.blade.php` dan `5xx.blade.php`.

Guest/public tidak boleh mengambil menu internal. Admin dan Petugas berbagi layout admin yang sama, tetapi menu dan route difilter berdasarkan role.

### View Scope Convention

Untuk menjaga scaffold tetap rapi dan kolaboratif, view internal dipisah berdasarkan scope fitur:

- `resources/views/pages/admin/management-user/*` untuk seluruh halaman management user (index/create/edit/partial).
- `resources/views/pages/admin/location-pricing-rules/*` untuk seluruh halaman Aturan Harga Lokasi (index/create/edit/partial).
- `resources/views/pages/admin/profile/*` untuk halaman profile internal.
- `resources/views/pages/admin/master/*` hanya untuk page master lain (contoh packages), bukan untuk modul CRUD dedicated.

Asset page-specific juga dipisah mengikuti scope view:

- `public/assets/pages/admin/management-user/*`.
- `public/assets/pages/admin/location-pricing-rules/*`.
- `public/assets/pages/admin/profile/*`.

### Error Handling (Production Mode)

Custom error view aktif saat `APP_DEBUG=false`:

- Laravel akan memprioritaskan view status spesifik, misalnya `errors/404.blade.php`, `errors/500.blade.php`.
- Jika status spesifik tidak ada, fallback ke `errors/4xx.blade.php` atau `errors/5xx.blade.php`.
- Semua status view meng-extend `errors/layout.blade.php` sebagai wrapper context-aware.

`errors/layout.blade.php` melakukan deteksi context internal (`admin`/`petugas`) dengan urutan sinyal:

- Segment URL pertama (`/admin/*` atau `/petugas/*`).
- Prefix route name (`admin.*` atau `petugas.*`).
- Session `auth.role`.
- Mapping role user login melalui `config('role_access.route_prefix_by_role')`.

Jika internal context terdeteksi:

- Render `errors/admin-layout.blade.php` (menggunakan shell internal: header + sidebar + footer admin).
- Tombol utama diarahkan ke dashboard sesuai prefix (`admin.dashboard` atau `petugas.dashboard`).

Jika internal context tidak terdeteksi:

- Render `errors/public-layout.blade.php` (gaya halaman public).

Catatan teknis:

- Error layout memakai fallback URL aman (`/`, `/booking`, `/login`) jika route name tidak tersedia.
- `resources/views/layouts/admin/assets.blade.php` sudah mendukung title dinamis (`$title`) agar judul tab browser mengikuti status error.

### Access Configuration

File utama: `config/role_access.php`.

Isi penting:

- `roles.internal`: role yang boleh login ke internal panel (`Admin`, `Petugas`).
- `roles.admin_only`: role admin-only (`Admin`).
- `route_prefix_by_role`: mapping role ke route prefix (`Admin -> admin`, `Petugas -> petugas`).
- `dashboard_route_by_role`: redirect dashboard setelah login.
- `panel_title_by_prefix`: label panel untuk title.
- `guest.menu`: menu public berbasis section/items dan siap dropdown.
- `guest.cta`: tombol CTA public.
- `menu`: menu internal berbasis section/items, roles, route name, dan active state.

Contoh menu guest dropdown:

```php
[
    'section' => 'Company',
    'items' => [
        [
            'type' => 'dropdown',
            'label' => 'Tentang Kami',
            'items' => [
                ['label' => 'Profil', 'route' => 'home', 'fragment' => 'about'],
                ['label' => 'Tim', 'route' => 'home', 'fragment' => 'team'],
            ],
        ],
    ],
]
```

Contoh menu internal:

```php
[
    'label' => 'Packages',
    'route_name' => 'packages',
    'active' => ['packages'],
    'roles' => ['Admin'],
]
```

Untuk internal menu, `route_name` adalah suffix panel. Sidebar akan membentuk route final lewat helper `panel_route('admin.' . $routeName)`, sehingga Admin menuju `admin.*` dan Petugas menuju `petugas.*`.

### Auth And Session Flow

Controller Web: `app/Http/Controllers/Web/Auth/AuthController.php`.

Controller API: `app/Http/Controllers/Api/Auth/AuthController.php`.

Service: `app/Services/AuthService.php`.

Flow login:

- Form login ada di route `login`.
- Submit form login menuju route `login.post` (`POST /api/login`).
- `Api\Auth\AuthController@login` memakai `LoginRequest` lalu memanggil `AuthService->attempt()`.
- Setelah berhasil login, user diload dengan relation `role`.
- Jika role bukan `Admin` atau `Petugas`, user langsung logout dan mendapat error: akun belum memiliki akses panel internal.
- Jika valid, `AuthService->syncInternalSession()` menyimpan session internal:
  - `auth.role` (prefix route panel, misalnya `admin`/`petugas`).
  - `auth.user` (snapshot user login: id, uuid, name, username, email, role, route prefix, dashboard route, panel title, logged in time).
- Redirect dashboard mengikuti `config('role_access.dashboard_route_by_role')`.

Flow logout:

- Route logout ada di `POST /api/logout`.
- `AuthService->clearInternalSession()` menghapus `auth.role` dan `auth.user`.
- `AuthService->logout()` logout, invalidate session, dan regenerate token.
- Dropdown profile di layout internal (`resources/views/layouts/admin/header.blade.php`) sudah memakai form POST + CSRF ke route `logout`.

Catatan: meskipun login/logout berada di file route API dan ber-prefix `/api`, endpoint ini tetap memakai middleware `web` supaya session auth dan CSRF flow browser tetap berjalan.

Middleware role:

- File: `app/Http/Middleware/EnsureUserHasRole.php`.
- Alias middleware `role` diregister di `bootstrap/app.php`.
- Penggunaan: `role:Admin`, `role:Petugas`, atau `role:Admin,Petugas`.
- Jika user tidak punya role sesuai, response `403`.
- Middleware role juga melakukan sinkronisasi session internal (`syncInternalSession`) untuk menjaga data panel tetap konsisten di setiap request internal.

User helper:

- `User::roleName(): ?string`.
- `User::hasRole(string|array $roles): bool`.

### Controller, Service, Repository Standard

Standard project saat ini:

<!-- Updated: Perjelas pola controller public booking vs admin booking -->

- Controller Web tipis: fokus render page, return view/redirect, dan tidak menerima `Request`/`FormRequest` di method signature. Kecuali `AdminPreviewController::bookingsList` dan `calendar` yang menerima `Request` untuk filter query.
- Controller API tipis: menerima `Request`/`FormRequest`, delegasi ke service, dan tidak mengembalikan view.
- Controller API Public (`app/Http/Controllers/Api/Public/`): endpoint untuk guest/customer tanpa auth.
- Controller API Admin (`app/Http/Controllers/Api/Admin/`): endpoint internal memakai middleware `auth` + `role:Admin`.
- Service: orchestration business flow, pemilihan view/page, keputusan domain, transaksi jika dibutuhkan.
- Repository contract: interface di `app/Repositories/Contracts`.
- Repository implementation: Eloquent di `app/Repositories/Eloquent`.
- Model: relation, casts, fillable, helper model kecil.

Base repository:

- Contract: `app/Repositories/Contracts/BaseRepositoryInterface.php`.
- Implementation: `app/Repositories/Eloquent/BaseRepository.php`.
- Method umum: `query`, `all`, `paginate`, `find`, `findOrFail`, `findBy`, `getBy`, `create`, `update`, `delete`, `restore`.
- `query(true)` otomatis filter `delete_status = false` jika model punya fillable `delete_status`.
- `delete()` melakukan soft delete manual jika model mendukung `delete_status`.

Repository binding:

- File: `app/Providers/RepositoryServiceProvider.php`.
- Semua repository contract dibind ke implementation Eloquent.
- Saat membuat model baru, buat contract, implementation, lalu register binding di provider ini.

Portal services:

<!-- Updated: Tambah GuestBookingService, GuestPackageService, BookingDetailService, BookingListService, BookingCalendarService -->

- `app/Services/Portal/GuestPageService.php`: mapping page public ke view dan title.
- `app/Services/Portal/GuestPackageService.php`: payload paket untuk landing page dan halaman paket (wedding/non-wedding, aktif saja).
- `app/Services/Portal/GuestBookingService.php`: orchestration booking request guest — create booking, form payload, availability, price estimate, status lookup, submission proof PDF, upload payment proof oleh customer, WhatsApp template generation.
- `app/Services/Portal/InternalPageService.php`: mapping page internal ke view dan title.
- `app/Services/Admin/BookingDetailService.php`: lifecycle booking management internal — approve, reject, verify DP, verify final payment, cancel, complete, force majeure (reschedule/refund), upload refund proof, billing details & installments management.
- `app/Services/Admin/BookingListService.php`: payload halaman list booking admin dengan filter status, date range, case ID, dan stats.
- `app/Services/Admin/BookingCalendarService.php`: payload halaman calendar admin dan JSON calendar events.

Catatan: `GuestBookingService` memakai `barryvdh/laravel-dompdf` untuk generate PDF bukti pengajuan booking. Jika package belum diinstall, jalankan `composer require barryvdh/laravel-dompdf`.

Jika menambah page baru, update route, controller method tipis, service page map, view, dan menu config jika perlu.

### Booking Module Detail

<!-- Updated: Section baru — dokumentasi fitur booking module yang sudah terimplementasi -->

#### Booking Identifiers

Setiap booking memiliki beberapa identifier:

- **Case ID** (`bookings.case_id`): format `ETH-{YYYYMMDD}-{NNNNN}`, misalnya `ETH-20260529-00001`. Di-generate saat booking dibuat dan bersifat unique. Digunakan sebagai primary identifier di admin panel dan URL detail page.
- **Request Code** (tidak disimpan di DB): format `ETH-REQ-{YYYY}-{NNNNNN}`, misalnya `ETH-REQ-2026-000001`. Di-generate on-the-fly untuk keperluan display ke customer.
- **UUID** (`bookings.uuid`): UUID standar, digunakan untuk signed URL (submission proof download).
- **Auto-increment ID**: primary key internal.

#### Submission Proof PDF

Saat booking request berhasil dibuat, sistem otomatis generate PDF bukti pengajuan:

- Menggunakan `barryvdh/laravel-dompdf` package.
- Template view: `resources/views/pages/public/booking-page/support/submission-proof-pdf.blade.php`.
- File disimpan terenkripsi di disk `local` pada path `booking-submission-proofs/{booking-uuid}.pdf`.
- Customer mendapat temporary signed URL (TTL 7 hari) untuk download via route `booking.proof.download`.
- PDF berisi: case ID, request code, data customer, data paket, tanggal acara, sesi, lokasi, dan Google Maps pin.

#### Booking Status Lifecycle

Alur status booking dan aksi yang tersedia:

```
BS_WAITING_APPROVAL
  ├→ approve  → BS_APPROVED_WAITING_DP (billing DP dibuat otomatis)
  └→ reject   → BS_REJECTED

BS_APPROVED_WAITING_DP
  ├→ verify-dp      → BS_APPROVED_WAITING_FINAL_PAYMENT
  ├→ upload-payment → auto-transition jika DP lunas
  ├→ reject-manual  → BS_REJECTED (billing cancelled)
  └→ approve/reject per-payment (PYS_PEDING → PYS_SUCCESS/PYS_FAILED)

BS_APPROVED_WAITING_FINAL_PAYMENT
  ├→ verify-final   → BS_CONFIRMED
  ├→ upload-payment → record payment
  ├→ cancel         → BS_CANCEL (billing cancelled)
  └→ approve/reject per-payment (PYS_PEDING → PYS_SUCCESS/PYS_FAILED)

BS_CONFIRMED
  ├→ complete       → BS_COMPLETE
  └→ force-majeure  → BS_FORCE_MAJEURE (reschedule atau refund)

BS_FORCE_MAJEURE
  └→ upload-refund-proof → BS_REFUND
```

Billing lifecycle di-automate saat status berubah:

- **Approve**: billing + billing detail (base) + billing installment (DP) dibuat otomatis.
- **Upload payment manual**: payment dicatat, installment status di-sync, billing status di-sync, auto-transition booking status jika DP lunas.
- **Verify DP**: semua pending payments di-approve, booking transisi ke waiting final payment.
- **Verify final payment**: semua pending payments di-approve, booking transisi ke confirmed.
- **Cancel/reject-manual**: billing status di-set ke cancelled.
- **Force majeure (refund)**: billing status ke refund, installment refund dibuat otomatis.

#### Admin Booking Controllers

- `app/Http/Controllers/Api/Admin/BookingDetailController.php`: API endpoint untuk seluruh mutasi booking (approve, reject, verify, cancel, dll). Menggunakan `BookingDetailService`.
- `app/Http/Controllers/Web/Admin/AdminPreviewController.php`: Web controller untuk render halaman booking (list, detail, calendar). Menggunakan `BookingListService`, `BookingDetailService`, `BookingCalendarService`.

#### Public Booking Controllers

- `app/Http/Controllers/Api/Public/BookingController.php`: API endpoint untuk guest (store booking, availability, estimate, status lookup, upload payment proof). Menggunakan `GuestBookingService`.
- `app/Http/Controllers/Web/Public/BookingSupportController.php`: Web controller untuk render halaman support booking (success, status, payment info, reschedule, cancellation policy, download submission proof). Menggunakan `GuestPageService` dan `GuestBookingService`.
- `app/Http/Controllers/Web/Public/LandingPageController.php`: Web controller untuk landing page, packages page, booking form, dan about page. Menggunakan `GuestPageService`, `GuestPackageService`, dan `GuestBookingService`.

## Data Model Overview

### Master And Reference

- `references`: master reference generic untuk status/type/level.
- `settings`: rule konfigurasi bisnis, memiliki optional `type_id` ke `references`.
- `attachments`: file metadata, `type_file` join ke `references`.
- `roles`: role aplikasi.
- `users`: user internal/customer, `role_id` ke `roles`, `profile_image_attachment_id` ke `attachments`.

Catatan implementasi user management internal:

- Halaman admin management akun membatasi role yang bisa dipilih hanya `Admin` dan `Petugas`.
- CRUD akun internal tidak mengelola role `Customer`.
- Penghapusan akun memakai soft delete manual (`delete_status = true`) melalui repository.
- Upload foto profile internal disimpan sebagai file terenkripsi di disk private (`local`) dan nilai `attachments.path` disimpan dalam format path terenkripsi.
- Rendering avatar (list user, profile page, header internal) memakai temporary signed URL, bukan URL storage publik langsung.

Catatan implementasi aturan harga lokasi:

- CRUD Aturan Harga Lokasi dikelola melalui `LocationPricingRuleController` + `LocationPricingRuleService`.
- Scope lokasi untuk modul ini mencakup level `Provinsi` (`LL_PV`), `Kota/Kabupaten` (`LL_CT`), `Kecamatan` (`LL_KC`), dan `Kelurahan` (`LL_KL`).
- Satu lokasi hanya boleh memiliki satu aturan harga aktif (`location_pricing_rules.location_id` unik untuk data aktif).

Reference group yang sudah ada:

- `location_level`: Kelurahan, Kecamatan, Kota, Provinsi.
- `price_type`: Tambahan Ringan, Tambahan Sedang, Tambahan Custom.
- `type_file`: Dokumen, Gambar.
- `package_status`: ACTIVE, INACTIVE, DRAFT.
- `package_type`: WEDDING, NON WEDDING.
- `booking_status`: WAITING APPROVAL, APPROVED WAITING DP, APPROVED WAITING FINAL PAYMENT, CONFIRMED, COMPLETE, EXPIRED, EXPIRED DP, CANCEL, RESCHEDULE, FORCE MAJEURE, REFUND, REJECTED.
<!-- Updated: Tambah BS_REJECTED pada daftar booking_status -->
- `event_session`: PAGI - SIANG, SORE - MALAM.
- `billing_status`: UNPAID, PARTIAL, PAID, CANCELLED, REFUND.
- `billing_type`: BASE, ADDON.
- `intallment_type`: DP, PARTIAL, FINAL, REFUND.
- `payment_type`: DP, PARTIAL, FINAL, REFUND.
- `payment_status`: PEDING, SUCCESS, FAILED.
- `payment_method`: BANK TRANSFER, QRIS, CASH, E-WALLET.

Catatan kompatibilitas: `intallment_type` dan `PEDING` memakai typo existing. Jangan rename tanpa migration perbaikan yang eksplisit.

### Location

- `wilayah`: dataset wilayah mentah dari `database/migrations/1.1.3/dataset/wilayah.sql`.
- `locations`: hasil transform dari `wilayah`, dengan parent-child berdasarkan kode wilayah.
- `locations.level_id` join ke `references` group `location_level`.
- `locations.wilayah_id` join ke `wilayah.kode`.
- `locations.parent_id` self-reference ke `locations.id`.
- `location_pricing_rules`: mapping lokasi ke `price_type`.

Format kode wilayah:

- `11`: provinsi.
- `11.01`: kota/kabupaten.
- `11.01.01`: kecamatan.
- `11.01.01.2001`: kelurahan.

Migration transform wilayah ke locations dibuat khusus PostgreSQL.

### Packages

<!-- Updated: Tambah field case_id dan address pada packages, tambah relasi packageType dan bookings -->

- `packages`: paket layanan, punya `status_id`, `thumbnail_attachment_id`, `package_type`, `case_id` (format `PKG-{YYYYMMDD}-{NNNNN}`), dan `address`.
- `package_benefits`: benefit per package.
- `packages.status_id` join ke reference `package_status`.
- `packages.package_type` join ke reference `package_type`.
- `packages.thumbnail_attachment_id` join ke `attachments`.
- `packages.case_id` unique identifier paket, di-generate on-demand saat booking dibuat jika belum ada.

### Customers And Bookings

<!-- Updated: Tambah case_id pada bookings, description pada booking_history, perjelas field customer (first_name/last_name), tambah relasi billings pada booking -->

- `customers`: data customer booking. Field utama: `first_name`, `last_name`, `phone_number`, `email`.
- `bookings`: booking utama. Field utama: `uuid`, `case_id`, `customer_id`, `package_id`, `status_id`, `location_id`, `event_date`, `event_session`, `event_detail`, `google_maps_pin`, `reschedule_date`, `reschedule_reason`, `force_majeure_date`, `force_majeure_reason`, `operator_id`.
- `booking_history`: riwayat status booking. Field utama: `booking_id`, `status_id`, `operator_id`, `description`.

Relasi booking:

- `customer_id` ke `customers`.
- `package_id` ke `packages`.
- `status_id` ke `references` group `booking_status`.
- `location_id` ke `locations`.
- `event_session` ke `references` group `event_session`.
- `operator_id` ke `users`.
- `billings` (hasMany) ke `billings`.

`booking_history` menyimpan `booking_id`, `status_id`, `operator_id`, dan `description` (alasan/catat perubahan status).

Case ID booking: format `ETH-{YYYYMMDD}-{NNNNN}` (misal `ETH-20260529-00001`). Di-generate saat booking dibuat dan di-backfill untuk booking lama via migration 1.1.12. Case ID juga dipakai sebagai URL parameter di admin detail page.

### Billing And Payments

<!-- Updated: Perjelas bahwa billing dibuat otomatis saat approve, tambah context automated flow -->

- `billings`: tagihan per booking. Dibuat otomatis saat booking di-approve dengan base price dari paket.
- `billing_details`: breakdown tagihan, `billing_type` ke reference `billing_type`. Saat approve, satu billing detail `BLT_BASE` dibuat otomatis. Admin dapat menambah komponen `BLT_ADDON` kemudian.
- `billing_installments`: cicilan/tagihan DP/final/refund, `installment_type` ke reference `intallment_type`, `status_id` ke reference `billing_status`. Saat approve, satu installment DP (`INS_DP`) dibuat otomatis. Admin dapat generate installment tambahan (`INS_PARTIAL`, `INS_FINAL`). Force majeure refund membuat installment `INS_REFUND` otomatis.
- `payments`: pembayaran aktual, join ke `billing_installments`, reference `payment_type`, `payment_status`, `payment_method`, dan optional attachment bukti transfer.

Relasi penting:

- `billings.booking_id` ke `bookings`.
- `billings.status_id` ke reference `billing_status`.
- `payments.transfer_receipt_attachment_id` ke `attachments`.

## Model Map

Model domain yang sudah ada di `app/Models`:

- Master: `Reference`, `Setting`, `Attachment`, `Role`, `User`.
- Location: `Wilayah`, `Location`, `LocationPricingRule`.
- Package: `Package`, `PackageBenefit`.
- Booking: `Customer`, `Booking`, `BookingHistory`.
- Billing/payment: `Billing`, `BillingDetail`, `BillingInstallment`, `Payment`.
- Laravel infrastructure: `Cache`, `CacheLock`, `Job`, `JobBatch`, `FailedJob`, `PasswordResetToken`, `Session`.

Model relation sudah dibuat mengikuti foreign key migration. Saat membuat query feature baru, prefer eager loading relation yang sudah tersedia daripada join manual, kecuali ada alasan performa yang jelas.
Model yang memakai `delete_status` sekarang menggunakan trait `App\Models\Concerns\HasManualSoftDeletes` untuk global scope aktif, `withInactive()`, dan `onlyInactive()`.

## Migration Version Map

Migrations diload dari `app/Providers/AppServiceProvider.php`:

- `1.0.0`: users base, cache, jobs, test user.
- `1.1.3`: wilayah, references, locations, location reference, transform wilayah, price type, location pricing rules.
- `1.1.4`: attachments, file type, packages, package status/type, settings, settings rules, package benefits.
- `1.1.6`: roles, default users, alter users role/profile, customers, bookings, booking history, booking/event session references.
- `1.1.7`: billing, billing details, billing installments, payments, billing/payment references.
- `1.1.8`: PostgreSQL performance indexes.
- `1.1.9`: perbaikan typo kolom bookings (`gogle_maps_pin`, `rechedule_*` -> `google_maps_pin`, `reschedule_*`).
- `1.1.10`: perbaikan kompatibilitas partial index `idx_bookings_reschedule_date_act` pasca rename kolom bookings.
- `1.1.11`: insert settings quota per sesi (`PKDR_MAX_QUOTA_PAGI_SIANG`, `PKDR_MAX_QUOTA_SORE_MALAM`), alter packages tambah `address`, alter packages tambah `case_id` (unique), insert reference `BS_REJECTED` pada group `booking_status`.
- `1.1.12`: alter `booking_history` tambah `description` (text nullable), alter `bookings` tambah `case_id` (unique) dengan backfill data existing.
- `1.1.13`: alter `payments` tambah `rejection_reason` (text nullable) untuk anti-fraud payment proof flow.

<!-- Updated: Tambah versi migration 1.1.11 dan 1.1.12 -->

Migration per versi:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate --path=database/migrations/1.1.4
```

Rollback/reset per versi:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan migrate:reset --path=database/migrations/1.1.4
```

Untuk production tambahkan `--force`.

Catatan migration:

- Nama file migration harus unik dan urut dengan prefix angka.
- Jika menambah folder versi baru, tambahkan `loadMigrationsFrom(...)` di `AppServiceProvider`.
- Index migration `1.1.8` hanya berjalan untuk driver `pgsql`.
- Migration `1.1.10` memperbaiki index reschedule agar mengikuti nama kolom final (`reschedule_date`) tanpa mengubah migration lama.
- Reference insert migration umumnya punya `down()` yang menghapus berdasarkan `group_id` dan `code`.

## Default Development Users

Migration `database/migrations/1.1.6/001_001_006_000003_insert_default_users.php` membuat user dev:

- `admin@etherno.local` / `password` dengan role `Admin`.
- `petugas@etherno.local` / `password` dengan role `Petugas`.
- `customer@etherno.local` / `password` dengan role `Customer`.

Customer saat ini akan ditolak login ke panel internal. Untuk production, ganti credential default dan strategi seeding.

## Performance Indexes

Migration `1.1.8` menambahkan partial indexes PostgreSQL untuk query aktif (`delete_status = false`), antara lain:

- `references(group_id, code)`.
- `settings(group_id, code)` dan `settings(group_id, type_id)`.
- `locations(parent_id, level_id)` dan `locations(level_id, wilayah_id)`.
- booking indexes untuk status, event date, customer, operator, location, package, reschedule, force majeure.
- billing/payment indexes untuk status, due date, paid date, installment, dan method.
- infrastructure indexes untuk password reset, sessions, dan jobs.

Jika menambah tabel besar atau query list utama, tambahkan index di migration versi baru, bukan mengubah migration lama yang sudah pernah dijalankan.

## How To Add Common Features

### Add Public Menu

Edit `config/role_access.php` pada `guest.menu`. Formatnya section/items dan support dropdown. Header public otomatis render dari config.

Jika link menuju section landing page, gunakan:

```php
['label' => 'FAQ', 'route' => 'home', 'fragment' => 'faq']
```

Jika link menuju page baru, tambahkan route di `routes/web/guest.php` dan mapping page di `GuestPageService`.

### Add Internal Menu

Edit `config/role_access.php` pada `menu`.

- Gunakan `route_name` suffix, misalnya `packages`, bukan `admin.packages`.
- Isi `roles` sesuai akses.
- Tambahkan active key agar sidebar state benar.
- Pastikan route tersedia di `routes/web/admin.php` dan/atau `routes/web/petugas.php`.

Dalam Blade internal, gunakan `panel_route('admin.route.name')` saat membuat link agar route otomatis mengikuti role session.

### Add Internal Page

Langkah umum:

- Untuk page dashboard/preview sederhana, tambahkan method tipis di `app/Http/Controllers/Web/Admin/AdminPreviewController.php`.
- Untuk modul feature (contoh management user), gunakan controller dedicated per scope di `app/Http/Controllers/Web/Admin/*Controller.php`.
- Tambahkan key page di `InternalPageService` hanya jika page memang dikelola lewat service map preview.
- Tambahkan view dalam folder scope yang spesifik, contoh `resources/views/pages/admin/management-user/index.blade.php`.
- Tambahkan asset page-specific dalam folder scope yang sama pattern-nya, contoh `public/assets/pages/admin/management-user/index.css`.
- Tambahkan route di `admin.php` dan/atau `petugas.php`.
- Tambahkan menu di `config/role_access.php` jika perlu.

### Add Public Page

Langkah umum:

- Tambahkan method tipis di `app/Http/Controllers/Web/Public/*Controller.php`.
- Tambahkan key page di `GuestPageService`.
- Tambahkan route di `routes/web/guest.php`.
- Tambahkan view di `resources/views/pages/public`.
- Tambahkan menu/CTA di `config/role_access.php` jika perlu.

### Add API Endpoint

Langkah umum:

<!-- Updated: Tambah catatan FormRequest untuk booking module -->

- Tambahkan method handler di `app/Http/Controllers/Api/*Controller.php`.
- Gunakan `FormRequest` untuk validasi input request. FormRequest booking admin ada di `app/Http/Requests/Api/Admin/Booking*Request.php`. FormRequest booking public ada di `app/Http/Requests/Public/Booking/StoreBookingRequest.php`.
- Tambahkan route endpoint di `routes/api/*.php` lalu require file-nya dari `routes/api.php`.
- Jika endpoint dipakai browser dengan session Laravel, pastikan middleware `web` ikut dipasang.
- Jika endpoint benar-benar stateless untuk consumer eksternal, gunakan middleware `api` sesuai kebutuhan.

### Add New Table And Model

Langkah umum:

- Buat migration di folder versi terbaru atau folder versi baru.
- Buat model dengan fillable, casts, dan relation.
- Buat repository interface di `app/Repositories/Contracts`.
- Buat repository Eloquent di `app/Repositories/Eloquent`.
- Register binding di `RepositoryServiceProvider`.
- Tambahkan service untuk business flow, lalu controller tipis.
- Tambahkan index migration jika tabel akan sering difilter/listing.

## Development Commands

PHP Laragon:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" --version
```

Clear cache setelah mengubah config, route, view, provider, atau helper:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan optimize:clear
```

Syntax check file PHP:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" -l app/Http/Middleware/EnsureUserHasRole.php
```

Run tests:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan test
```

Start local server:

```powershell
& "C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" artisan serve
```

## Agent Collaboration Rules

Untuk agent AI berikutnya:

- Jangan langsung refactor besar. Baca README ini, lalu cek file aktual yang relevan.
- Jangan revert perubahan user atau perubahan agent lain tanpa instruksi eksplisit.
- Gunakan `rg` untuk mencari file/teks.
- Ikuti flow Controller -> Service -> Repository -> Model.
- Jangan membuat tabel permission/role_menu kecuali user meminta.
- Jangan rename reference `group_id`/`code` existing tanpa migration dan koordinasi, terutama key typo yang sudah masuk data.
- Jangan hardcode link internal di Blade. Gunakan `panel_route()` untuk panel route.
- Jalankan minimal `php -l` untuk file PHP yang diedit dan `artisan optimize:clear` setelah mengubah config/routes/provider.
- Jika mengubah arsitektur, update README ini agar session berikutnya tidak kehilangan konteks.
