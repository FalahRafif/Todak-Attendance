<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCorrectionController extends Controller
{
    public function index(HrdService $hrdService): View
    {
        return view('pages.hrd.attendance-corrections.index', $hrdService->correctionListData());
    }

    public function show(int $id, HrdService $hrdService): View
    {
        return view('pages.hrd.attendance-corrections.show', $hrdService->correctionDetailData($id));
    }

    public function approve(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['approval_note' => ['nullable', 'string']]);
        $hrdService->approveCorrection($id, $payload['approval_note'] ?? null);

        return back()->with('success', 'Attendance correction berhasil di-approve.');
    }

    public function reject(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['rejected_reason' => ['required', 'string']]);
        $hrdService->rejectCorrection($id, $payload['rejected_reason']);

        return back()->with('success', 'Attendance correction berhasil ditolak.');
    }
}
