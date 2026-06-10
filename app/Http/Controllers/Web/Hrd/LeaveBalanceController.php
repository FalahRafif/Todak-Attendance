<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class LeaveBalanceController extends Controller
{
    public function index(Request $request, HrdService $hrdService): View
    {
        return view('pages.hrd.leave-balances', $hrdService->leaveBalanceListData($request));
    }

    public function adjust(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['additional_quota' => ['nullable', 'integer', 'min:0'], 'total_quota' => ['nullable', 'integer', 'min:0']]);
        try {
            $hrdService->adjustLeaveBalance($id, $payload);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Saldo cuti berhasil diperbarui.');
    }

    public function generate(Request $request, HrdService $hrdService): RedirectResponse
    {
        $year = (int) $request->input('year', now()->year);
        $count = $hrdService->generateMissingLeaveBalances($year);

        return back()->with('success', "Berhasil membuat saldo cuti untuk {$count} karyawan tahun {$year}.");
    }
}
