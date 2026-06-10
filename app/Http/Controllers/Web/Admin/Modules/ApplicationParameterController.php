<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationParameterController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.admin.modules.application-parameters.index', ['title' => 'Parameter Aplikasi', 'annualLeaveQuota' => $this->annualLeaveQuota()]);
    }

    public function updateAnnualLeaveQuota(Request $request): RedirectResponse
    {
        $payload = $request->validate(['default_quota' => ['required', 'integer', 'min:0', 'max:365']]);
        $setting = Setting::query()->firstOrNew(['group_id' => 'attendance', 'code' => 'DEFAULT_ANNUAL_LEAVE_QUOTA']);
        if (! $setting->exists) {
            $setting->uuid = (string) Str::uuid();
            $setting->created_by = auth()->id();
        }
        $setting->description = 'Default kuota cuti tahunan per tahun untuk employee baru dan generate saldo HRD';
        $setting->value = (string) $payload['default_quota'];
        $setting->updated_by = auth()->id();
        $setting->save();

        return redirect()->route('admin.application-parameters')->with('success', 'Kuota cuti tahunan berhasil disimpan.');
    }

    private function annualLeaveQuota(): int
    {
        return (int) (Setting::query()->where('group_id', 'attendance')->where('code', 'DEFAULT_ANNUAL_LEAVE_QUOTA')->value('value') ?? 12);
    }
}
