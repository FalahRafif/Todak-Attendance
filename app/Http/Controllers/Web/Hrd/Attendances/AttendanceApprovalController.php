<?php

namespace App\Http\Controllers\Web\Hrd\Attendances;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceApprovalController extends Controller
{
    public function approve(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['approval_note' => ['nullable', 'string']]);
        $hrdService->approveAttendance($id, $payload['approval_note'] ?? null);

        return back()->with('success', 'Attendance berhasil di-approve.');
    }

    public function reject(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['approval_note' => ['required', 'string']]);
        $hrdService->rejectAttendance($id, $payload['approval_note']);

        return back()->with('success', 'Attendance berhasil ditandai rejected/flagged.');
    }
}
