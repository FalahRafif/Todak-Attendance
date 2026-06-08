# Business Requirement Document (BRD) - KlikAbsen

## 1. Ringkasan

Dokumen ini adalah acuan business requirement untuk pengembangan fitur KlikAbsen. Tujuannya agar multi-agent AI/developer dapat mengerjakan modul berdasarkan role, flow, halaman, business rule, dan acceptance criteria yang jelas.

## 2. Product Goal

KlikAbsen adalah aplikasi absensi karyawan berbasis Laravel untuk:

- Mengelola master data karyawan, department, jabatan, lokasi kerja, shift, dan hari libur.
- Mencatat absensi masuk dan keluar dengan foto wajib dari kamera dan GPS wajib aktif.
- Mengizinkan absensi dalam radius kantor dan di luar radius untuk kondisi WFH, dinas luar, meeting luar, atau kunjungan client.
- Memproses pengajuan sakit, cuti, izin, dan koreksi absensi.
- Menyediakan monitoring dan report absensi untuk HRD.

## 3. Scope MVP

### In Scope

- Authentication dan role-based access.
- Dashboard Admin, HRD, dan Employee.
- CRUD departments, positions, work locations, shifts, holidays, employees.
- Employee check-in dan check-out.
- Foto absensi wajib dari kamera.
- GPS wajib aktif saat absensi.
- Validasi radius lokasi kerja default 100 meter.
- Outside radius tetap bisa absen jika memilih work mode dan mengisi catatan.
- Attendance history bulan berjalan untuk Employee.
- Pengajuan sakit, cuti, izin.
- Approval/reject pengajuan oleh HRD.
- Pengajuan koreksi absensi.
- Approval/reject koreksi absensi oleh HRD.
- Monitoring absensi harian HRD.
- Report absensi harian dan bulanan.
- Export report minimal Excel.
- Activity log untuk aksi penting.

### Out of Scope MVP

- Face recognition.
- Liveness detection.
- Anti fake GPS tingkat lanjut.
- Integrasi payroll.
- Integrasi fingerprint.
- Mobile app native.
- Approval berjenjang Supervisor -> HRD.
- Push notification mobile.
- Integrasi API libur nasional otomatis.

## 4. Role Final

Role final:

- Admin
- HRD
- Employee

Catatan:

- Karyawan tetap, karyawan kontrak, dan interns bukan role.
- Jenis karyawan disimpan pada `employees.employee_type_id` dengan reference group `EMPLOYEE_TYPE`.
- Role digunakan untuk hak akses.
- Employee type digunakan untuk status kepegawaian.

## 5. Modul dan Halaman Berdasarkan Role

## 5.1 Authentication

### Aktor

- Admin
- HRD
- Employee

### Halaman / Endpoint

| Halaman / Endpoint | Route | Keterangan |
|---|---|---|
| Redirect root | `GET /` | Redirect ke login |
| Login page | `GET /login` | Halaman login mobile-first |
| Login submit | `POST /api/login` | Proses login |
| Logout | `POST /api/logout` | Proses logout |

### Flow Login

1. User membuka `/login`.
2. User input email/username dan password.
3. Sistem validasi credential.
4. Sistem cek role user.
5. Jika Admin, redirect ke `/admin/dashboard`.
6. Jika HRD, redirect ke `/hrd/dashboard`.
7. Jika Employee, redirect ke `/employee/dashboard`.
8. Jika gagal, tampilkan error login.

### Business Rule

- User hanya bisa masuk ke halaman sesuai role.
- Login page harus mobile-first.
- Session menyimpan minimal `auth.user` dan `auth.role`.

---

## 5.2 Admin Module

### Tujuan

Admin mengelola konfigurasi dan master data aplikasi.

### Menu Admin

1. Dashboard
2. Master Data
   - Departments
   - Positions
   - Work Locations
   - Shifts
   - Holidays
3. Employee Management
   - Employees
   - Employee Work Locations
   - Employee Schedules
4. System
   - Settings
   - References
   - Roles
   - Users
   - Activity Logs

### Halaman Target Admin

| Modul | Route Target | Prioritas |
|---|---|---|
| Dashboard | `GET /admin/dashboard` | MVP |
| Departments | `GET /admin/departments` | MVP |
| Create Department | `GET /admin/departments/create` | MVP |
| Store Department | `POST /admin/departments` | MVP |
| Edit Department | `GET /admin/departments/{id}/edit` | MVP |
| Update Department | `PUT /admin/departments/{id}` | MVP |
| Delete Department | `DELETE /admin/departments/{id}` | MVP |
| Positions | `GET /admin/positions` | MVP |
| Work Locations | `GET /admin/work-locations` | MVP |
| Shifts | `GET /admin/shifts` | MVP |
| Holidays | `GET /admin/holidays` | MVP |
| Employees | `GET /admin/employees` | MVP |
| Employee Detail | `GET /admin/employees/{id}` | MVP |
| Employee Work Locations | `GET /admin/employee-work-locations` | Later |
| Settings | `GET /admin/settings` | Later |
| References | `GET /admin/references` | Later |
| Roles | `GET /admin/roles` | Later |
| Users | `GET /admin/users` | Later |
| Activity Logs | `GET /admin/activity-logs` | Later |

### Business Rule Admin

- Admin bisa CRUD master data utama.
- Delete menggunakan manual soft delete: `deleted_at`, `deleted_by`, `delete_status`.
- Data yang sudah dipakai transaksi tidak boleh hard delete.
- Admin fokus pada konfigurasi, bukan approval operasional harian.

---

## 5.3 HRD Module

### Tujuan

HRD melakukan monitoring, approval, dan report absensi.

### Menu HRD

1. Dashboard
2. Monitoring Absensi
   - Absensi Harian
   - Belum Check-in
   - Belum Check-out
   - Terlambat
   - Outside Radius
3. Approval
   - Leave Requests
   - Attendance Corrections
   - Outside Radius Attendances
4. Reports
   - Daily Attendance Report
   - Monthly Attendance Report
   - Employee Attendance Report
5. HR Data
   - Employees, read-only/detail
   - Employee Schedules
   - Leave Balances

### Halaman Target HRD

| Modul | Route Target | Prioritas |
|---|---|---|
| Dashboard HRD | `GET /hrd/dashboard` | MVP |
| Monitoring Harian | `GET /hrd/attendances` | MVP |
| Detail Attendance | `GET /hrd/attendances/{id}` | MVP |
| Belum Check-in | `GET /hrd/attendances/not-checked-in` | MVP |
| Belum Check-out | `GET /hrd/attendances/incomplete` | MVP |
| Terlambat | `GET /hrd/attendances/late` | MVP |
| Outside Radius | `GET /hrd/attendances/outside-radius` | MVP |
| Approve Outside Radius | `POST /hrd/attendances/{id}/approve` | MVP |
| Reject/Flag Outside Radius | `POST /hrd/attendances/{id}/reject` | MVP |
| Leave Requests | `GET /hrd/leave-requests` | MVP |
| Detail Leave Request | `GET /hrd/leave-requests/{id}` | MVP |
| Approve Leave Request | `POST /hrd/leave-requests/{id}/approve` | MVP |
| Reject Leave Request | `POST /hrd/leave-requests/{id}/reject` | MVP |
| Attendance Corrections | `GET /hrd/attendance-corrections` | MVP |
| Detail Correction | `GET /hrd/attendance-corrections/{id}` | MVP |
| Approve Correction | `POST /hrd/attendance-corrections/{id}/approve` | MVP |
| Reject Correction | `POST /hrd/attendance-corrections/{id}/reject` | MVP |
| Employee Schedules | `GET /hrd/employee-schedules` | MVP |
| Daily Report | `GET /hrd/reports/daily-attendance` | MVP |
| Monthly Report | `GET /hrd/reports/monthly-attendance` | MVP |
| Generate Monthly Summary | `POST /hrd/reports/monthly-attendance/generate` | MVP |
| Export Daily Report | `GET /hrd/reports/daily-attendance/export` | MVP |
| Export Monthly Report | `GET /hrd/reports/monthly-attendance/export` | MVP |
| Leave Balances | `GET /hrd/leave-balances` | Later |

### Business Rule HRD

- HRD dapat melihat semua employee dan attendance.
- HRD dapat approve/reject leave request.
- HRD dapat approve/reject attendance correction.
- HRD dapat review absensi outside radius.
- HRD tidak mengubah attendance langsung kecuali melalui approval/koreksi.
- Default dashboard dan monitoring memakai tanggal hari ini.
- Filter minimal: tanggal, employee, department, work location, shift, status, work mode, inside/outside radius.

---

## 5.4 Employee Module

### Tujuan

Employee melakukan absensi dan pengajuan.

### Menu Employee

1. Dashboard
2. Absensi
   - Absen Hari Ini
   - Check-in
   - Check-out
   - History Absensi
3. Pengajuan
   - Sakit/Cuti/Izin
   - Koreksi Absensi
4. Profil Saya

### Halaman Target Employee

| Modul | Route Target | Prioritas |
|---|---|---|
| Dashboard Employee | `GET /employee/dashboard` | MVP |
| Attendance Today | `GET /employee/attendance` | MVP |
| Check-in Form | `GET /employee/attendance/check-in` | MVP |
| Check-in Submit | `POST /employee/attendance/check-in` | MVP |
| Check-out Form | `GET /employee/attendance/check-out` | MVP |
| Check-out Submit | `POST /employee/attendance/check-out` | MVP |
| Attendance History | `GET /employee/attendance/history` | MVP |
| Attendance Detail | `GET /employee/attendance/history/{id}` | MVP |
| Leave Requests | `GET /employee/leave-requests` | MVP |
| Create Leave Request | `GET /employee/leave-requests/create` | MVP |
| Store Leave Request | `POST /employee/leave-requests` | MVP |
| Detail Leave Request | `GET /employee/leave-requests/{id}` | MVP |
| Cancel Leave Request | `POST /employee/leave-requests/{id}/cancel` | MVP |
| Attendance Corrections | `GET /employee/attendance-corrections` | MVP |
| Create Correction | `GET /employee/attendance-corrections/create` | MVP |
| Store Correction | `POST /employee/attendance-corrections` | MVP |
| Detail Correction | `GET /employee/attendance-corrections/{id}` | MVP |
| Cancel Correction | `POST /employee/attendance-corrections/{id}/cancel` | MVP |
| Profile | `GET /employee/profile` | Later |

### Business Rule Employee

- Employee hanya melihat dan mengelola data miliknya sendiri.
- Employee inactive tidak boleh absen.
- Tombol check-in hanya aktif jika belum check-in hari ini.
- Tombol check-out hanya aktif jika sudah check-in dan belum check-out.
- History default menampilkan bulan berjalan.
- Halaman Employee wajib mobile-first.

---

## 6. Flow Detail

## 6.1 Flow Check-in

1. Employee login.
2. Buka `/employee/attendance`.
3. Sistem cek employee aktif.
4. Sistem cek jadwal hari ini.
5. Sistem cek apakah sudah check-in hari ini.
6. Employee membuka form check-in.
7. Browser meminta akses kamera.
8. Browser meminta akses GPS.
9. Employee mengambil foto selfie dari kamera.
10. Sistem mengambil latitude dan longitude.
11. Sistem menghitung jarak ke `work_locations`.
12. Jika jarak <= radius, absensi disimpan sebagai inside radius.
13. Jika jarak > radius, employee wajib memilih work mode dan mengisi catatan.
14. Sistem menyimpan `attendances`.
15. Sistem menyimpan `attendance_logs` action `check_in`.
16. Sistem menampilkan status berhasil.

### Business Rule Check-in

- Foto wajib dari kamera, bukan upload gallery.
- GPS wajib aktif.
- `check_in_photo_attachment_id` wajib.
- `check_in_latitude` dan `check_in_longitude` wajib.
- Radius default 100 meter dari setting `ATTENDANCE_RADIUS_METER` atau dari `work_locations.radius_meter`.
- Outside radius boleh jika `ALLOW_OUTSIDE_RADIUS = true`.
- Outside radius wajib work mode dan note jika `REQUIRE_OUTSIDE_RADIUS_NOTE = true`.
- Outside radius set `is_need_approval = true`.
- Check-in kedua pada tanggal yang sama harus ditolak.

## 6.2 Flow Check-out

1. Employee login.
2. Buka `/employee/attendance`.
3. Sistem cek attendance hari ini.
4. Jika belum check-in, check-out ditolak.
5. Employee membuka form check-out.
6. Browser meminta akses kamera dan GPS.
7. Employee mengambil selfie.
8. Sistem mengambil latitude dan longitude.
9. Sistem menghitung jarak.
10. Jika outside radius, employee wajib isi work mode dan catatan.
11. Sistem update check-out.
12. Sistem hitung `total_work_minutes` dan `early_leave_minutes`.
13. Sistem menyimpan `attendance_logs` action `check_out`.
14. Sistem menampilkan status berhasil.

### Business Rule Check-out

- Check-out hanya boleh setelah check-in.
- Check-out kedua pada tanggal yang sama harus ditolak.
- Foto dan GPS tetap wajib.
- Outside radius tetap wajib work mode dan catatan.

## 6.3 Flow Leave Request

1. Employee membuka `/employee/leave-requests`.
2. Employee klik tambah pengajuan.
3. Employee memilih jenis pengajuan: annual leave, sick leave, atau permission.
4. Employee mengisi tanggal mulai, tanggal selesai, alasan, dan attachment jika perlu.
5. Status awal `pending`.
6. HRD membuka `/hrd/leave-requests`.
7. HRD melihat detail.
8. HRD approve atau reject.
9. Jika approved, sistem membuat `leave_request_details` per tanggal.
10. Jika approved, sistem update/membuat attendance sesuai tanggal pengajuan.
11. Jika rejected, sistem menyimpan rejected reason dan tidak mengubah attendance.

### Business Rule Leave Request

- Employee hanya bisa cancel jika status masih `pending`.
- HRD wajib isi rejected reason saat reject.
- Approved sick leave menjadi status attendance `sick`.
- Approved annual leave menjadi status attendance `leave`.
- Approved permission menjadi status attendance `permission`.
- Annual leave dapat mengurangi `leave_balances` jika modul saldo aktif.

## 6.4 Flow Attendance Correction

1. Employee membuka `/employee/attendance-corrections`.
2. Employee mengajukan koreksi tanggal tertentu.
3. Employee mengisi requested check-in/check-out dan alasan.
4. Status awal `pending`.
5. HRD membuka `/hrd/attendance-corrections`.
6. HRD approve atau reject.
7. Jika approved, sistem membuat/update attendance.
8. Sistem menyimpan attendance log action `update_by_hrd`.
9. Jika rejected, HRD wajib isi rejected reason.

## 6.5 Flow Outside Radius Approval

1. Employee check-in/check-out di luar radius.
2. Sistem menyimpan attendance dengan `is_need_approval = true`.
3. HRD membuka `/hrd/attendances/outside-radius`.
4. HRD melihat foto, GPS, jarak, work mode, catatan, dan device info.
5. HRD approve atau memberi catatan reject/flag.
6. Jika approved, isi `approved_by`, `approved_at`, dan `approval_note`.

Untuk MVP, reject outside radius tidak menghapus attendance. Attendance tetap tersimpan tetapi diberi catatan/flag untuk tindak lanjut manual.

---

## 7. Report Requirement

## 7.1 Daily Attendance Report

Route target:

- `GET /hrd/reports/daily-attendance`
- `GET /hrd/reports/daily-attendance/export`

Kolom minimal:

- Employee number
- Employee name
- Department
- Position
- Work location
- Shift
- Attendance date
- Check-in time
- Check-out time
- Status
- Late minutes
- Work mode
- Inside/outside radius
- Approval status

Filter:

- Date
- Department
- Work location
- Shift
- Status
- Work mode
- Employee keyword

## 7.2 Monthly Attendance Report

Route target:

- `GET /hrd/reports/monthly-attendance`
- `POST /hrd/reports/monthly-attendance/generate`
- `GET /hrd/reports/monthly-attendance/export`

Kolom minimal:

- Employee number
- Employee name
- Department
- Position
- Total work days
- Total present
- Total late
- Total absent
- Total sick
- Total leave
- Total permission
- Total incomplete
- Total outside radius
- Total work minutes
- Total late minutes
- Total early leave minutes

Business rule:

- Report bulanan dapat memakai query langsung dari `attendances` atau data `attendance_monthly_summaries`.
- Generate summary memperbarui `attendance_monthly_summaries`.

---

## 8. Prioritas Implementasi Multi-Agent

## Phase 1 - Routing, Layout, dan Placeholder

Output:

- Route prefix `/admin`, `/hrd`, dan `/employee` aktif.
- Dashboard Admin, HRD, Employee aktif.
- Sidebar/menu sesuai role.
- Menu aman dengan `Route::has()`.

## Phase 2 - Master Data Admin

Output:

- CRUD departments.
- CRUD positions.
- CRUD work locations.
- CRUD shifts.
- CRUD holidays.
- CRUD employees.

## Phase 3 - Employee Attendance

Output:

- Employee attendance page mobile-first.
- Browser camera aktif.
- Browser geolocation aktif.
- Check-in dan check-out tersimpan.
- Radius dihitung.
- Outside radius wajib catatan.
- Foto tersimpan sebagai attachment.
- Attendance log tersimpan.

## Phase 4 - Leave dan Correction

Output:

- Employee bisa membuat leave request.
- HRD bisa approve/reject leave request.
- Employee bisa membuat correction request.
- HRD bisa approve/reject correction request.
- Attendance berubah sesuai hasil approval.

## Phase 5 - HRD Monitoring dan Report

Output:

- Dashboard HRD memakai data real.
- Monitoring harian aktif.
- Detail attendance aktif.
- Outside radius review aktif.
- Report harian dan bulanan aktif.
- Export Excel aktif.

## Phase 6 - Audit dan Testing

Output:

- Activity log untuk aksi penting.
- Authorization policy/request validation.
- Feature test login.
- Feature test CRUD master.
- Feature test check-in/check-out.
- Feature test leave approval.
- Feature test correction approval.

---

## 9. Acceptance Criteria Global

Aplikasi dianggap memenuhi BRD jika:

1. Admin bisa login dan CRUD master data utama.
2. HRD bisa login dan monitoring absensi.
3. Employee bisa login dan melihat dashboard employee.
4. Employee bisa check-in dengan foto kamera dan GPS.
5. Employee bisa check-out dengan foto kamera dan GPS.
6. Sistem menghitung jarak dari lokasi kerja.
7. Absensi dalam radius <= 100 meter dianggap normal.
8. Absensi luar radius tetap bisa dilakukan jika work mode dan catatan diisi.
9. Absensi luar radius ditandai perlu approval.
10. Employee bisa melihat history absensi bulan berjalan.
11. Employee bisa mengajukan sakit/cuti/izin.
12. HRD bisa approve/reject sakit/cuti/izin.
13. Employee bisa mengajukan koreksi absensi.
14. HRD bisa approve/reject koreksi.
15. HRD bisa melihat report harian dan bulanan.
16. Minimal report bisa diekspor ke Excel.
17. Activity log mencatat aksi penting.
18. Menu dan route aman berdasarkan role.

---

## 10. Development Rules untuk Agent

- Ikuti pattern: Controller -> Service -> Repository -> Model.
- Jangan query langsung dari controller.
- Business rule diletakkan di service.
- Data access diletakkan di repository.
- Gunakan manual soft delete.
- Jangan revive modul Etherno.
- Jangan rombak template admin besar-besaran.
- Employee pages harus mobile-first.
- Setelah edit routes/config/provider jalankan `artisan optimize:clear`.
- Minimal jalankan `php -l` untuk file PHP yang diedit.
- Jika menambah model baru, buat contract repository, eloquent repository, dan binding provider.
