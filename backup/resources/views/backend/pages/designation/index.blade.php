@extends('backend.layouts.master')

@section('title', 'Designation Management')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
@endsection

@section('admin-content')
<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Designation</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>All Designation</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<div class="container mt-5">
    <h2 class="mb-4">Designation Management</h2>
    <button class="btn btn-primary mb-3" id="add-designation">Add New Designation</button>
    <table id="designation-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="designation-modal" tabindex="-1" aria-labelledby="designationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="designation-form">
                @csrf
                <input type="hidden" id="designation_id" name="designation_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="designationModalLabel">Designation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
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
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#designation-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("designation.datatable") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'description'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#add-designation').click(function() {
            $('#designation-form')[0].reset();
            $('#designation_id').val('');
            $('#designation-modal').modal('show');
        });

        $('#designation-form').submit(function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: '{{ route("designation.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    iziToast.success({
                    message: $('#designation_id').val()
                        ? 'Designation updated successfully!'
                        : 'Designation created successfully!',
                    position: 'topRight',
                });
                    $('#designation-modal').modal('hide');
                    table.ajax.reload();
                },
                error: function(xhr) {
                    iziToast.error({
                    message: xhr.responseJSON.message || 'An error occurred.',
                    position: 'topRight',
                });
                }
            });
        });

        $('#designation-table').on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('{{ route("designation.edit", "") }}/' + id, function(data) {
                $('#designation_id').val(data.id);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#status').prop('checked', data.status === 'Y');
                $('#designation-modal').modal('show');
            });
        });

        $('#designation-table').on('click', '.delete-btn', function() {
            let id = $(this).data('id');

            if (confirm('Are you sure you want to delete this designation?')) {
                $.ajax({
                    url: `/designation/delete/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF Token for Laravel
                    },
                    success: function(response) {
                        iziToast.success({
                            message: response.success,
                            position: 'topRight'
                        });
                        // Reload DataTable without reloading the page
                        $('#designation-table').DataTable().ajax.reload(null, false); // false keeps the current page
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error || 'Failed to delete designation.',
                            position: 'topRight'
                        });
                    }
                });
            }
        });


    });
</script>
@endsection