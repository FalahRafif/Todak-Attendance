<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Services\Hr\HrMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct(private HrMasterService $hrMasterService) {}

    public function index(): View
    {
        return view('pages.admin.modules.positions.index', $this->hrMasterService->positionPageData());
    }

    public function create(): View
    {
        return view('pages.admin.modules.positions.form', $this->hrMasterService->positionFormData());
    }

    public function edit(int $id): View
    {
        return view('pages.admin.modules.positions.form', $this->hrMasterService->positionFormData($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->hrMasterService->createPosition($request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'department_id' => ['nullable', 'integer', 'exists:departments,id']]));

        return redirect()->route('admin.positions')->with('success', 'Position berhasil dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updatePosition($id, $request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'department_id' => ['nullable', 'integer', 'exists:departments,id']]));

        return redirect()->route('admin.positions')->with('success', 'Position berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->hrMasterService->deletePosition($id, auth()->id());

        return back()->with('success', 'Position berhasil dihapus.');
    }
}
