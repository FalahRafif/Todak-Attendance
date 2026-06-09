<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Services\Hr\HrMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkLocationController extends Controller
{
    public function __construct(private HrMasterService $hrMasterService) {}

    public function index(): View
    {
        return view('pages.admin.modules.work-locations.index', $this->hrMasterService->workLocationPageData());
    }

    public function create(): View
    {
        return view('pages.admin.modules.work-locations.form', $this->hrMasterService->workLocationFormData());
    }

    public function edit(int $id): View
    {
        return view('pages.admin.modules.work-locations.form', $this->hrMasterService->workLocationFormData($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->hrMasterService->createWorkLocation($this->validated($request));

        return redirect()->route('admin.work-locations')->with('success', 'Lokasi kerja berhasil dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateWorkLocation($id, $this->validated($request));

        return redirect()->route('admin.work-locations')->with('success', 'Lokasi kerja berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteWorkLocation($id, auth()->id());

        return back()->with('success', 'Lokasi kerja berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'address' => ['nullable', 'string'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'radius_meter' => ['required', 'integer', 'min:1'], 'is_default' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean']]);
    }
}
