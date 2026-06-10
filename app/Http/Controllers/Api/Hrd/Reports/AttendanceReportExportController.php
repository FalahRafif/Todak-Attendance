<?php

namespace App\Http\Controllers\Api\Hrd\Reports;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportExportController extends Controller
{
    public function daily(Request $request, HrdService $hrdService): StreamedResponse
    {
        return $this->csv('daily-attendance.csv', ['Tanggal', 'Employee Number', 'Nama Karyawan', 'Department', 'Position', 'Work Location', 'Shift', 'Check In', 'Check Out', 'Work Mode Check In', 'Work Mode Check Out', 'Radius Check In', 'Radius Check Out', 'Late Minutes', 'Early Leave Minutes', 'Total Work Minutes', 'Attendance Status', 'Approval/Review Status', 'Check In Note', 'Check Out Note'], $hrdService->dailyExportRows($request));
    }

    public function monthly(Request $request, HrdService $hrdService): StreamedResponse
    {
        return $this->csv('monthly-attendance.csv', ['Year', 'Month', 'Employee Number', 'Nama Karyawan', 'Department', 'Position', 'Total Work Days', 'Total Present', 'Total Late', 'Total Absent', 'Total Sick', 'Total Leave', 'Total Permission', 'Total Incomplete', 'Total Outside Radius', 'Total Work Minutes', 'Total Late Minutes', 'Total Early Leave Minutes'], $hrdService->monthlyExportRows($request));
    }

    private function csv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return Response::streamDownload(function () use ($headers, $rows): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
