<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class LeaveRequestController extends Controller
{
    public function index(Request $request, HrdService $hrdService): View
    {
        return view('pages.hrd.leave-requests.index', $hrdService->leaveRequestListData($request));
    }

    public function show(int $id, HrdService $hrdService): View
    {
        return view('pages.hrd.leave-requests.show', $hrdService->leaveRequestDetailData($id));
    }

    public function approve(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['approval_note' => ['nullable', 'string']]);
        try {
            $hrdService->approveLeaveRequest($id, $payload['approval_note'] ?? null);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leave request berhasil di-approve.');
    }

    public function reject(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['rejected_reason' => ['required', 'string']]);
        try {
            $hrdService->rejectLeaveRequest($id, $payload['rejected_reason']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leave request berhasil ditolak.');
    }
}
