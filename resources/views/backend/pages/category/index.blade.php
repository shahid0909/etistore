@extends('backend.layouts.master')

@section('title', 'Category Management')

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
                <h4 class="page-title pull-left">Category</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>All Category</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<div class="container mt-5">
    <h2 class="mb-4">Category Management</h2>
    @if (Auth::guard('admin')->user()->can('category.create'))
    <button class="btn btn-primary mb-3" id="add-category">Add New category</button>
    @endif
    <table id="category-table" class="table table-bordered table-striped">
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
<div class="modal fade" id="category-modal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="category-form">
                @csrf
                <input type="hidden" id="category_id" name="category_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Category</h5>
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
        let table = $('#category-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("category.datatable") }}',
            columns: [{
                data: 'DT_RowIndex', orderable: false, searchable: false
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

        $('#add-category').click(function() {
            $('#category-form')[0].reset();
            $('#category_id').val('');
            $('#category-modal').modal('show');
        });

        $('#category-form').submit(function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: '{{ route("category.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    iziToast.success({
                    message: $('#category_id').val()
                        ? 'category updated successfully!'
                        : 'category created successfully!',
                    position: 'topRight',
                });
                    $('#category-modal').modal('hide');
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

        $('#category-table').on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('{{ route("category.edit", "") }}/' + id, function(data) {
                $('#category_id').val(data.id);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#status').prop('checked', data.status === 'Y');
                $('#category-modal').modal('show');
            });
        });

        $('#category-table').on('click', '.delete-btn', function() {
            let id = $(this).data('id');

            if (confirm('Are you sure you want to delete this category?')) {
                $.ajax({
                    url: `/category/delete/${id}`,
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
                        $('#category-table').DataTable().ajax.reload(null, false); // false keeps the current page
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error || 'Failed to delete category.',
                            position: 'topRight'
                        });
                    }
                });
            }
        });


    });
</script>
@endsection
