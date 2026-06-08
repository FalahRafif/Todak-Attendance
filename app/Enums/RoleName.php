<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case Hrd = 'HRD';
    case Karyawan = 'karyawan';
    case KaryawanKontrak = 'Karyawan Kontrak';
    case Interns = 'Interns';
}
