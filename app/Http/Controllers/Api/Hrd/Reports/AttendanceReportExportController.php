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
        return $this->csv('daily-attendance.csv', ['Date', 'Employee', 'Department', 'Check In', 'Check Out', 'Late', 'Status'], $hrdService->dailyExportRows($request));
    }

    public function monthly(Request $request, HrdService $hrdService): StreamedResponse
    {
        return $this->csv('monthly-attendance.csv', ['Employee', 'Department', 'Present', 'Late', 'Sick', 'Leave', 'Permission', 'Incomplete', 'Outside Radius', 'Work Minutes'], $hrdService->monthlyExportRows($request));
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
