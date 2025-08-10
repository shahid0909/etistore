@extends('backend.layouts.master')

@section('title', 'Staff Management')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
@endsection

@section('admin-content')
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Staff</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>All Staff</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="mb-4">Staff Management</h2>
    <button class="btn btn-primary mb-3" id="add-staff">Add New Staff</button>
    <table id="staff-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal fade" id="staff-modal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="staff-form">
                @csrf
                <input type="hidden" id="staff_id" name="staff_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="staffModalLabel">Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select class="form-control" id="department_id" name="department_id">
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="designation">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <input type="checkbox" id="status" name="status">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable for Staff
        let staffTable = $('#staff-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("staff.datatable") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'phone'
                },
                {
                    data: 'department'
                },
                {
                    data: 'designation'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                },
            ],
        });

        // Global departments array for dynamic dropdown population
        let departments = @json($departments);

        // Function to populate department dropdown
        function populateDepartmentDropdown(selectedId = null) {
            let departmentDropdown = $('#department_id');
            departmentDropdown.empty();
            departmentDropdown.append('<option value="">Select Department</option>');
            departments.forEach(department => {
                departmentDropdown.append(
                    `<option value="${department.id}" ${department.id == selectedId ? 'selected' : ''}>${department.name}</option>`
                );
            });
        }

        // Add Staff Button Click
        $('#add-staff').click(function() {
            $('#staff-form')[0].reset();
            $('#staff_id').val('');
            populateDepartmentDropdown(); // Populate dropdown without preselection
            // $('#staff-modal').modal('show');
            var myModal = new bootstrap.Modal(document.getElementById('staff-modal'), {
            backdrop: 'static', // Prevent modal close when clicking outside
            keyboard: false // Prevent modal close when pressing ESC
            });
            myModal.show();
        });

        // Save or Update Staff
        $('#staff-form').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            $.ajax({
                url: '{{ route("staff.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    iziToast.success({
                        message: $('#staff_id').val() ?
                            'Staff updated successfully!' :
                            'Staff created successfully!',
                        position: 'topRight',
                    });
                    $('#staff-modal').modal('hide');
                    staffTable.ajax.reload();
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessage = 'An error occurred.';
                    if (errors) {
                        errorMessage = Object.values(errors).map(err => err.join(', ')).join(' ');
                    }
                    iziToast.error({
                        message: errorMessage,
                        position: 'topRight',
                    });
                },
            });
        });

        // Edit Staff
        $('#staff-table').on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('{{ route("staff.edit", "") }}/' + id, function(data) {
                $('#staff_id').val(data.id);
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#phone').val(data.phone);
                populateDepartmentDropdown(data.department_id); // Populate dropdown with selected department
                $('#designation').val(data.designation);
                $('#status').prop('checked', data.status === 'Y');
                $('#staff-modal').modal('show');
            });
        });

        // Delete Staff
        $('#staff-table').on('click', '.delete-btn', function() {
            let id = $(this).data('id');

            if (confirm('Are you sure you want to delete this staff member?')) {
                $.ajax({
                    url: `/staff/delete/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        iziToast.success({
                            message: response.success,
                            position: 'topRight',
                        });
                        staffTable.ajax.reload();
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error || 'Failed to delete staff member.',
                            position: 'topRight',
                        });
                    },
                });
            }
        });
    });
</script>


@endsection