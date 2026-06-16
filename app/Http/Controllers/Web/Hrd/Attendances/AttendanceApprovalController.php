<?php

namespace App\Http\Controllers\Web\Hrd\Attendances;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceApprovalController extends Controller
{
    public function __invoke(Request $request, int $id, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate([
            'session' => ['required', Rule::in(['check_in', 'check_out'])],
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'note' => ['nullable', 'string'],
        ]);

        if ($payload['decision'] === 'rejected' && trim((string) $payload['note']) === '') {
            return back()->withErrors(['note' => 'Alasan wajib diisi saat menolak.'])->withInput();
        }

        $hrdService->reviewOutsideRadius($id, $payload['session'], $payload['decision'], $payload['note'] ?? null);

        $label = $payload['session'] === 'check_in' ? 'absen masuk' : 'absen pulang';
        $verdict = $payload['decision'] === 'approved' ? 'disetujui' : 'ditandai perlu perhatian';

        return back()->with('success', "Review {$label} berhasil — {$verdict}.");
    }
}
