# Plan Migration Tables - Aplikasi Absensi Karyawan

Dokumen ini berisi rencana awal struktur tabel database untuk aplikasi absensi karyawan. Dokumen ini dibuat sebagai bahan koreksi dan perbaikan oleh AI internal / developer sebelum dibuatkan migration final.

## Requirement Utama

- Admin melakukan CRUD master data.
- HRD melakukan monitoring dan report absensi karyawan.
- Karyawan login aplikasi sebelum absen.
- Karyawan melakukan absen masuk dan absen keluar.
- Foto absensi wajib diambil langsung dari kamera.
- GPS wajib aktif saat absen.
- Radius kantor default 100 meter.
- Jika absen di luar radius, absensi tetap bisa dilakukan dengan keterangan seperti WFH, dinas luar kantor, meeting luar, atau kunjungan client.
- Karyawan dapat melihat history absensi bulan berjalan.
- Karyawan dapat mengajukan sakit, cuti, dan izin.
- HRD dapat approve/reject pengajuan sakit, cuti, izin, dan koreksi absensi.

---

# 1. Existing Base Tables

Tabel berikut sudah ada pada base schema dan akan tetap digunakan sebagai fondasi sistem.

---

## 1.1 roles

```text
roles
--------------------------------------------------
PK  id
    uuid
    name
    created_at
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Catatan Data Role

```text
role.name
- Admin
- HRD
- Employee
```

### Catatan Desain

`Karyawan`, `Karyawan Kontrak`, dan `Interns` sebaiknya tidak dijadikan role jika hak akses aplikasinya sama. Lebih baik jenis karyawan disimpan di `employees.employee_type_id`.

---

## 1.2 users

```text
users
--------------------------------------------------
PK  id
    uuid
    name
    username
    password
    email_verified_at
    remember_token
FK  role_id
FK  profile_image_attachment_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel ini digunakan untuk akun login aplikasi.

### Relasi

```text
users.role_id                       -> roles.id
users.profile_image_attachment_id   -> attachments.id
```

---

## 1.3 references

```text
references
--------------------------------------------------
PK  id
    uuid
    code
    description
FK  group_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel ini digunakan untuk data referensi / enum kecil.

### Contoh Group Data

```text
EMPLOYEE_TYPE
- permanent
- contract
- intern

ATTENDANCE_STATUS
- present
- late
- absent
- leave
- sick
- permission
- incomplete
- pending_approval

LEAVE_TYPE
- annual_leave
- sick_leave
- permission

APPROVAL_STATUS
- pending
- approved
- rejected
- cancelled

WORK_MODE
- office
- wfh
- business_trip
- outside_meeting
- client_visit

ATTENDANCE_ACTION_TYPE
- check_in
- check_out
- update_by_hrd
- approval_by_hrd
```

### Relasi

```text
references.group_id -> references.id
```

---

## 1.4 attachments

```text
attachments
--------------------------------------------------
PK  id
    uuid
    name
    path
FK  type_file
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel ini digunakan untuk menyimpan metadata file.

### Digunakan Untuk

```text
- foto profile user
- foto absen masuk
- foto absen keluar
- surat sakit
- dokumen izin
- lampiran cuti
- dokumen pendukung koreksi absensi
```

### Relasi

```text
attachments.type_file -> references.id
```

---

## 1.5 wilayah

```text
wilayah
--------------------------------------------------
PK  kode
    nama
```

### Fungsi

Tabel wilayah administratif.

---

## 1.6 locations

```text
locations
--------------------------------------------------
PK  id
    uuid
FK  wilayah_id
    name
FK  level_id
FK  parent_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel lokasi administratif / hirarki lokasi.

### Relasi

```text
locations.wilayah_id -> wilayah.kode
locations.level_id   -> references.id
locations.parent_id  -> locations.id
```

---

## 1.7 settings

```text
settings
--------------------------------------------------
PK  id
    uuid
    code
    description
FK  group_id
    value
FK  type_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel ini digunakan untuk konfigurasi global sistem.

### Contoh Data Setting

```text
ATTENDANCE_RADIUS_METER = 100
REQUIRE_SELFIE = true
REQUIRE_GPS = true
ALLOW_OUTSIDE_RADIUS = true
REQUIRE_OUTSIDE_RADIUS_NOTE = true
LATE_TOLERANCE_MINUTES = 15
```

### Relasi

```text
settings.group_id -> references.id
settings.type_id  -> references.id
```

---

# 2. New Tables - Master HR

---

## 2.1 employees

```text
employees
--------------------------------------------------
PK  id
    uuid
FK  user_id
    employee_number
    full_name
    phone
    gender
FK  employee_type_id
FK  department_id
FK  position_id
FK  work_location_id
FK  shift_id
    join_date
    end_date
    is_active
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel profil karyawan. Data login tetap berada di `users`, sedangkan data kepegawaian berada di `employees`.

### Relasi

```text
employees.user_id           -> users.id
employees.employee_type_id  -> references.id
employees.department_id     -> departments.id
employees.position_id       -> positions.id
employees.work_location_id  -> work_locations.id
employees.shift_id          -> shifts.id
```

### Catatan

`employee_type_id` berisi jenis karyawan seperti:

```text
- permanent
- contract
- intern
```

---

## 2.2 departments

```text
departments
--------------------------------------------------
PK  id
    uuid
    name
    description
FK  parent_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel master divisi/departemen.

### Relasi

```text
departments.parent_id -> departments.id
```

---

## 2.3 positions

```text
positions
--------------------------------------------------
PK  id
    uuid
    name
    description
FK  department_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel master jabatan.

### Relasi

```text
positions.department_id -> departments.id
```

---

# 3. New Tables - Lokasi Kerja dan Jadwal

---

## 3.1 work_locations

```text
work_locations
--------------------------------------------------
PK  id
    uuid
    name
    address
    latitude
    longitude
    radius_meter
    is_default
    is_active
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel lokasi kerja/kantor untuk validasi lokasi absensi.

### Catatan

- `radius_meter` default 100 meter.
- Sistem tetap memperbolehkan absen di luar radius jika user memberi keterangan WFH/dinas/meeting luar.
- Data latitude dan longitude digunakan untuk menghitung jarak absen.

---

## 3.2 employee_work_locations

```text
employee_work_locations
--------------------------------------------------
PK  id
    uuid
FK  employee_id
FK  work_location_id
    is_primary
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel mapping jika satu karyawan bisa memiliki lebih dari satu lokasi kerja.

### Relasi

```text
employee_work_locations.employee_id       -> employees.id
employee_work_locations.work_location_id  -> work_locations.id
```

### Catatan

Tabel ini opsional untuk MVP. Jika satu karyawan hanya memiliki satu lokasi kerja, cukup gunakan `employees.work_location_id`.

---

## 3.3 shifts

```text
shifts
--------------------------------------------------
PK  id
    uuid
    name
    start_time
    end_time
    check_in_start_time
    check_in_end_time
    check_out_start_time
    check_out_end_time
    late_tolerance_minutes
    is_overnight
    is_active
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel master shift atau jam kerja.

### Contoh Data

```text
name                    : Shift Normal
start_time              : 08:00:00
end_time                : 17:00:00
check_in_start_time     : 06:00:00
check_in_end_time       : 10:00:00
check_out_start_time    : 16:00:00
check_out_end_time      : 20:00:00
late_tolerance_minutes  : 15
is_overnight            : false
```

---

## 3.4 employee_schedules

```text
employee_schedules
--------------------------------------------------
PK  id
    uuid
FK  employee_id
FK  shift_id
    schedule_date
    is_day_off
    note
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel jadwal kerja karyawan per tanggal.

### Relasi

```text
employee_schedules.employee_id -> employees.id
employee_schedules.shift_id    -> shifts.id
```

### Catatan

Untuk MVP, jadwal bisa dibuat otomatis dari shift default karyawan di `employees.shift_id`.

---

## 3.5 holidays

```text
holidays
--------------------------------------------------
PK  id
    uuid
    name
    holiday_date
    description
    is_national_holiday
    is_company_holiday
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel hari libur nasional dan hari libur perusahaan.

---

# 4. New Tables - Absensi

---

## 4.1 attendances

```text
attendances
--------------------------------------------------
PK  id
    uuid
FK  employee_id
FK  shift_id
FK  work_location_id
    attendance_date

    check_in_at
FK  check_in_photo_attachment_id
    check_in_latitude
    check_in_longitude
    check_in_distance_meter
    check_in_is_inside_radius
FK  check_in_work_mode_id
    check_in_note
    check_in_device_info

    check_out_at
FK  check_out_photo_attachment_id
    check_out_latitude
    check_out_longitude
    check_out_distance_meter
    check_out_is_inside_radius
FK  check_out_work_mode_id
    check_out_note
    check_out_device_info

    total_work_minutes
    late_minutes
    early_leave_minutes

FK  status_id
    is_need_approval
FK  approved_by
    approved_at
    approval_note

    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel utama absensi harian karyawan.

### Relasi

```text
attendances.employee_id                    -> employees.id
attendances.shift_id                       -> shifts.id
attendances.work_location_id               -> work_locations.id
attendances.check_in_photo_attachment_id   -> attachments.id
attendances.check_out_photo_attachment_id  -> attachments.id
attendances.check_in_work_mode_id          -> references.id
attendances.check_out_work_mode_id         -> references.id
attendances.status_id                      -> references.id
attendances.approved_by                    -> users.id
```

### Business Rule

```text
- 1 karyawan hanya boleh memiliki 1 data attendance per tanggal.
- Gunakan unique(employee_id, attendance_date).
- Foto check-in wajib berasal dari kamera.
- Foto check-out wajib berasal dari kamera.
- GPS wajib aktif saat check-in dan check-out.
- Jika di dalam radius kantor, check_in_is_inside_radius/check_out_is_inside_radius = true.
- Jika di luar radius kantor, user wajib mengisi work_mode dan note.
- Jika di luar radius kantor, is_need_approval = true.
- Status tetap bisa present/late, tetapi HRD dapat melihat bahwa absensi tersebut butuh validasi.
```

---

## 4.2 attendance_logs

```text
attendance_logs
--------------------------------------------------
PK  id
    uuid
FK  attendance_id
FK  employee_id
FK  action_type_id
    action_at
    latitude
    longitude
    distance_meter
    is_inside_radius
FK  work_mode_id
    note
FK  photo_attachment_id
    device_info
    ip_address
    user_agent
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel log aktivitas absensi untuk audit trail.

### Relasi

```text
attendance_logs.attendance_id        -> attendances.id
attendance_logs.employee_id          -> employees.id
attendance_logs.action_type_id       -> references.id
attendance_logs.work_mode_id         -> references.id
attendance_logs.photo_attachment_id  -> attachments.id
```

### Contoh action_type_id

```text
- check_in
- check_out
- update_by_hrd
- approval_by_hrd
```

---

# 5. New Tables - Pengajuan Sakit, Cuti, dan Izin

---

## 5.1 leave_requests

```text
leave_requests
--------------------------------------------------
PK  id
    uuid
FK  employee_id
FK  leave_type_id
    start_date
    end_date
    total_days
    reason
FK  attachment_id
FK  status_id
FK  approved_by
    approved_at
    approval_note
    rejected_reason
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel pengajuan sakit, cuti, dan izin.

### Relasi

```text
leave_requests.employee_id    -> employees.id
leave_requests.leave_type_id  -> references.id
leave_requests.attachment_id  -> attachments.id
leave_requests.status_id      -> references.id
leave_requests.approved_by    -> users.id
```

### Business Rule

```text
- Pengajuan pertama kali memiliki status pending.
- HRD dapat approve/reject.
- Jika approved, status absensi pada tanggal terkait berubah menjadi sick/leave/permission.
- Jika rejected, absensi tetap mengikuti status aktual, misalnya absent.
- Untuk sakit, attachment dapat diwajibkan jika ada aturan perusahaan.
```

---

## 5.2 leave_request_details

```text
leave_request_details
--------------------------------------------------
PK  id
    uuid
FK  leave_request_id
    leave_date
FK  attendance_id
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel detail tanggal dari pengajuan sakit/cuti/izin.

### Relasi

```text
leave_request_details.leave_request_id -> leave_requests.id
leave_request_details.attendance_id    -> attendances.id
```

### Catatan

Jika karyawan cuti 3 hari, maka sistem dapat membuat 3 data detail sesuai tanggal cuti.

---

## 5.3 leave_balances

```text
leave_balances
--------------------------------------------------
PK  id
    uuid
FK  employee_id
    year
    total_quota
    used_quota
    remaining_quota
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel saldo cuti karyawan per tahun.

### Relasi

```text
leave_balances.employee_id -> employees.id
```

### Catatan

Tabel ini opsional untuk MVP. Bisa dibuat sekarang, tetapi logic pemotongan cuti bisa dikembangkan nanti.

---

# 6. New Tables - Koreksi Absensi

---

## 6.1 attendance_correction_requests

```text
attendance_correction_requests
--------------------------------------------------
PK  id
    uuid
FK  employee_id
FK  attendance_id
    correction_date
    requested_check_in_at
    requested_check_out_at
    reason
FK  attachment_id
FK  status_id
FK  approved_by
    approved_at
    approval_note
    rejected_reason
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel pengajuan koreksi absensi.

### Relasi

```text
attendance_correction_requests.employee_id    -> employees.id
attendance_correction_requests.attendance_id  -> attendances.id
attendance_correction_requests.attachment_id  -> attachments.id
attendance_correction_requests.status_id      -> references.id
attendance_correction_requests.approved_by    -> users.id
```

### Contoh Kasus

```text
- lupa absen masuk
- lupa absen keluar
- GPS error
- kamera error
- dinas luar tetapi lupa isi keterangan
```

---

# 7. New Tables - Approval, Summary, dan Audit

---

## 7.1 approvals

```text
approvals
--------------------------------------------------
PK  id
    uuid
    approvable_type
    approvable_id
FK  requested_by
FK  approved_by
FK  status_id
    approval_level
    note
    approved_at
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel approval umum untuk kebutuhan yang lebih scalable.

### Relasi

```text
approvals.requested_by -> users.id
approvals.approved_by  -> users.id
approvals.status_id    -> references.id
```

### Contoh Data

```text
approvable_type = leave_request
approvable_id   = 10

approvable_type = attendance_correction
approvable_id   = 5

approvable_type = outside_radius_attendance
approvable_id   = 22
```

### Catatan

Tabel ini opsional untuk MVP. Untuk versi awal, approval bisa langsung disimpan di tabel sumber seperti `leave_requests`, `attendances`, dan `attendance_correction_requests`.

---

## 7.2 attendance_monthly_summaries

```text
attendance_monthly_summaries
--------------------------------------------------
PK  id
    uuid
FK  employee_id
    year
    month
    total_work_days
    total_present
    total_late
    total_absent
    total_sick
    total_leave
    total_permission
    total_incomplete
    total_outside_radius
    total_work_minutes
    total_late_minutes
    total_early_leave_minutes
    generated_at
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel rekap absensi bulanan untuk kebutuhan report HRD dan kemungkinan payroll.

### Relasi

```text
attendance_monthly_summaries.employee_id -> employees.id
```

### Catatan

Tabel ini dapat di-generate setiap akhir bulan atau saat HRD klik generate report.

---

## 7.3 activity_logs

```text
activity_logs
--------------------------------------------------
PK  id
    uuid
FK  user_id
    module
    action
    description
    old_value
    new_value
    ip_address
    user_agent
    created_at
    created_by
    updated_at
    updated_by
    deleted_at
    deleted_by
    delete_status
```

### Fungsi

Tabel audit log aktivitas sistem.

### Relasi

```text
activity_logs.user_id -> users.id
```

### Contoh Aktivitas

```text
- admin tambah karyawan
- admin ubah shift karyawan
- HRD approve cuti
- HRD reject izin
- karyawan absen masuk
- karyawan absen keluar
- HRD koreksi absensi
```

---

# 8. Ringkasan Relasi Antar Table

```text
roles.id
  -> users.role_id

users.id
  -> employees.user_id
  -> activity_logs.user_id
  -> attendances.approved_by
  -> leave_requests.approved_by
  -> attendance_correction_requests.approved_by
  -> approvals.requested_by
  -> approvals.approved_by

attachments.id
  -> users.profile_image_attachment_id
  -> attendances.check_in_photo_attachment_id
  -> attendances.check_out_photo_attachment_id
  -> attendance_logs.photo_attachment_id
  -> leave_requests.attachment_id
  -> attendance_correction_requests.attachment_id

departments.id
  -> employees.department_id
  -> positions.department_id
  -> departments.parent_id

positions.id
  -> employees.position_id

work_locations.id
  -> employees.work_location_id
  -> employee_work_locations.work_location_id
  -> attendances.work_location_id

shifts.id
  -> employees.shift_id
  -> employee_schedules.shift_id
  -> attendances.shift_id

employees.id
  -> attendances.employee_id
  -> attendance_logs.employee_id
  -> employee_schedules.employee_id
  -> employee_work_locations.employee_id
  -> leave_requests.employee_id
  -> leave_balances.employee_id
  -> attendance_correction_requests.employee_id
  -> attendance_monthly_summaries.employee_id

attendances.id
  -> attendance_logs.attendance_id
  -> leave_request_details.attendance_id
  -> attendance_correction_requests.attendance_id

leave_requests.id
  -> leave_request_details.leave_request_id

references.id
  -> employees.employee_type_id
  -> attendances.status_id
  -> attendances.check_in_work_mode_id
  -> attendances.check_out_work_mode_id
  -> attendance_logs.action_type_id
  -> attendance_logs.work_mode_id
  -> leave_requests.leave_type_id
  -> leave_requests.status_id
  -> attendance_correction_requests.status_id
  -> approvals.status_id
  -> settings.group_id
  -> settings.type_id
  -> attachments.type_file
```

---

# 9. Ringkasan Table Wajib dan Opsional

## 9.1 Existing Table

```text
1. roles
2. users
3. references
4. attachments
5. wilayah
6. locations
7. settings
```

## 9.2 Table Baru Wajib untuk MVP

```text
1. employees
2. departments
3. positions
4. work_locations
5. shifts
6. employee_schedules
7. holidays
8. attendances
9. attendance_logs
10. leave_requests
11. leave_request_details
12. attendance_correction_requests
13. attendance_monthly_summaries
14. activity_logs
```

## 9.3 Table Baru Opsional / Bisa Ditunda

```text
1. employee_work_locations
2. leave_balances
3. approvals
```

---

# 10. Database Rules dan Constraint yang Disarankan

## 10.1 Unique Constraint

```text
attendances:
- unique(employee_id, attendance_date)

leave_balances:
- unique(employee_id, year)

attendance_monthly_summaries:
- unique(employee_id, year, month)

employee_schedules:
- unique(employee_id, schedule_date)

employee_work_locations:
- unique(employee_id, work_location_id)
```

---

## 10.2 Required Field Saat Check In

```text
attendances.employee_id
attendances.shift_id
attendances.work_location_id
attendances.attendance_date
attendances.check_in_at
attendances.check_in_photo_attachment_id
attendances.check_in_latitude
attendances.check_in_longitude
attendances.check_in_distance_meter
attendances.check_in_is_inside_radius
attendances.check_in_device_info
attendances.status_id
```

---

## 10.3 Required Field Saat Check Out

```text
attendances.check_out_at
attendances.check_out_photo_attachment_id
attendances.check_out_latitude
attendances.check_out_longitude
attendances.check_out_distance_meter
attendances.check_out_is_inside_radius
attendances.check_out_device_info
```

---

## 10.4 Rule Absen di Luar Radius

Jika check-in di luar radius:

```text
check_in_is_inside_radius = false
```

Maka wajib isi:

```text
check_in_work_mode_id
check_in_note
is_need_approval = true
```

Jika check-out di luar radius:

```text
check_out_is_inside_radius = false
```

Maka wajib isi:

```text
check_out_work_mode_id
check_out_note
is_need_approval = true
```

---

## 10.5 Rule Status Absensi

```text
present
- hadir tepat waktu

late
- hadir tetapi terlambat

incomplete
- sudah check-in tetapi belum check-out

absent
- tidak hadir tanpa pengajuan yang approved

sick
- sakit dan pengajuan approved

leave
- cuti dan pengajuan approved

permission
- izin dan pengajuan approved

pending_approval
- digunakan jika absensi/pengajuan masih menunggu validasi HRD
```

Catatan desain yang disarankan:

```text
Jika karyawan absen di luar radius, status_id tetap boleh present/late, tetapi is_need_approval = true.
Dengan begitu HRD tetap bisa melihat karyawan hadir, namun absensinya perlu divalidasi.
```

---

# 11. Catatan untuk AI Internal / Developer

Hal-hal yang perlu dikoreksi sebelum dibuat migration final:

```text
1. Tentukan apakah semua id menggunakan big integer auto increment atau UUID sebagai primary key.
2. Tentukan apakah foreign key memakai id integer atau uuid.
3. Tentukan standar tipe data latitude/longitude, disarankan decimal(10,7).
4. Tentukan standar tipe data distance_meter, disarankan decimal(10,2).
5. Tentukan standar penyimpanan device_info, bisa text atau json.
6. Tentukan apakah approval menggunakan tabel umum approvals atau cukup field approval di masing-masing tabel.
7. Tentukan apakah employee_work_locations diperlukan sejak MVP.
8. Tentukan apakah leave_balances diperlukan sejak MVP.
9. Tentukan mekanisme generate employee_schedules.
10. Tentukan apakah work_locations perlu relasi ke locations/wilayah administratif.
11. Tentukan apakah delete_status tetap digunakan bersama deleted_at.
12. Tentukan apakah old_value dan new_value di activity_logs menggunakan json/text.
13. Tentukan index tambahan untuk report, terutama employee_id, attendance_date, status_id, department_id, dan work_location_id.
```

---

# 12. Rekomendasi Index

```text
users:
- index(role_id)
- unique(username)

employees:
- index(user_id)
- index(employee_type_id)
- index(department_id)
- index(position_id)
- index(work_location_id)
- index(shift_id)
- unique(employee_number)

attendances:
- unique(employee_id, attendance_date)
- index(attendance_date)
- index(status_id)
- index(work_location_id)
- index(is_need_approval)

attendance_logs:
- index(attendance_id)
- index(employee_id)
- index(action_at)

leave_requests:
- index(employee_id)
- index(leave_type_id)
- index(status_id)
- index(start_date)
- index(end_date)

leave_request_details:
- index(leave_request_id)
- index(leave_date)
- index(attendance_id)

attendance_correction_requests:
- index(employee_id)
- index(attendance_id)
- index(status_id)
- index(correction_date)

attendance_monthly_summaries:
- unique(employee_id, year, month)
- index(year, month)

activity_logs:
- index(user_id)
- index(module)
- index(action)
- index(created_at)
```

---

# 13. Target Modul Aplikasi Berdasarkan Table

```text
Authentication & User Management
- roles
- users
- attachments

Master Employee
- employees
- departments
- positions

Location & Schedule
- work_locations
- employee_work_locations
- shifts
- employee_schedules
- holidays

Attendance
- attendances
- attendance_logs

Submission / Request
- leave_requests
- leave_request_details
- leave_balances
- attendance_correction_requests

Approval & Report
- approvals
- attendance_monthly_summaries
- activity_logs

Support Data
- references
- settings
- wilayah
- locations
```
