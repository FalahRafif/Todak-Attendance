@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
@php($isEdit = $user !== null)
@php($selectedRoleId = (int) old('role_id', $user?->role_id ?? $employeeRoleId))
@php($isEmployeeSelected = $selectedRoleId === (int) $employeeRoleId)
<div class="ka-toolbar">
    <div>
        <h2 class="ka-page-title">{{ $title }}</h2>
        <p class="ka-page-subtitle">Buat akun Admin, HRD, atau Employee sekaligus data karyawan.</p>
    </div>
    <a href="{{ route('admin.employees') }}" class="btn btn-light">Back</a>
</div>
<div class="card custom-card ka-card ka-form-card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.employees.update', $user->id) : route('admin.employees.store') }}" class="row g-3" id="employee-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <div class="col-12">
                <div class="ka-form-section">
                    <div class="ka-form-section-title">User Account</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" id="role_id" class="form-control" data-employee-role-id="{{ $employeeRoleId }}" required>
                                <option value="">-</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected($selectedRoleId === $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Username</label><input name="username" value="{{ old('username', $user?->username) }}" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $user?->email) }}" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" @required(!$isEdit)></div>
                        <div class="col-md-8"><label class="form-label">Profile Image</label><input type="file" name="profile_image" class="form-control" accept="image/png,image/jpeg,image/webp"><div class="text-muted small mt-1">JPG, PNG, WEBP. Max 2MB. File disimpan terenkripsi.</div></div>
                        <div class="col-md-4">@if($profileImageUrl)<img src="{{ $profileImageUrl }}" class="rounded-circle border" style="width:72px;height:72px;object-fit:cover" alt="Profile image">@else<span class="ka-avatar" style="width:72px;height:72px;font-size:1.5rem">{{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}</span>@endif</div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="ka-form-section">
                    <div class="ka-form-section-title">Employee Data</div>
                    <p class="text-muted mb-3">Wajib jika role Employee. Shift dan Work Location wajib untuk absensi.</p>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Employee Number</label><input name="employee_number" value="{{ old('employee_number', $employee?->employee_number) }}" class="form-control employee-required" @required($isEmployeeSelected)></div>
                        <div class="col-md-4"><label class="form-label">Full Name</label><input name="full_name" value="{{ old('full_name', $employee?->full_name ?? $user?->name) }}" class="form-control employee-required" @required($isEmployeeSelected)></div>
                        <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', $employee?->phone) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Gender</label><select name="gender" class="form-control"><option value="">-</option><option value="male" @selected(old('gender', $employee?->gender) === 'male')>male</option><option value="female" @selected(old('gender', $employee?->gender) === 'female')>female</option></select></div>
                        <div class="col-md-4"><label class="form-label">Type</label><select name="employee_type_id" class="form-control"><option value="">-</option>@foreach($employeeTypes as $type)<option value="{{ $type->id }}" @selected((int) old('employee_type_id', $employee?->employee_type_id) === $type->id)>{{ $type->description }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Join Date</label><input type="date" name="join_date" value="{{ old('join_date', $employee?->join_date?->format('Y-m-d')) }}" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Department</label><select name="department_id" class="form-control"><option value="">-</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((int) old('department_id', $employee?->department_id) === $department->id)>{{ $department->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Position</label><select name="position_id" class="form-control"><option value="">-</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected((int) old('position_id', $employee?->position_id) === $position->id)>{{ $position->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Work Location</label><select name="work_location_id" class="form-control employee-required" @required($isEmployeeSelected)><option value="">-</option>@foreach($workLocations as $workLocation)<option value="{{ $workLocation->id }}" @selected((int) old('work_location_id', $employee?->work_location_id) === $workLocation->id)>{{ $workLocation->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Shift</label><select name="shift_id" class="form-control employee-required" @required($isEmployeeSelected)><option value="">-</option>@foreach($shifts as $shift)<option value="{{ $shift->id }}" @selected((int) old('shift_id', $employee?->shift_id) === $shift->id)>{{ $shift->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">End Date</label><input type="date" name="end_date" value="{{ old('end_date', $employee?->end_date?->format('Y-m-d')) }}" class="form-control"></div>
                        <div class="col-md-3"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee?->is_active ?? true))> Active</label></div>
                    </div>
                </div>
            </div>
            <div class="col-12"><button class="btn btn-primary">Save</button><a href="{{ route('admin.employees') }}" class="btn btn-light">Cancel</a></div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var role = document.getElementById('role_id');
        var employeeRoleId = String(role.dataset.employeeRoleId);
        var fields = document.querySelectorAll('.employee-required');

        function syncRequired() {
            var required = String(role.value) === employeeRoleId;
            fields.forEach(function (field) {
                field.required = required;
            });
        }

        role.addEventListener('change', syncRequired);
        syncRequired();
    });
</script>
@endpush
