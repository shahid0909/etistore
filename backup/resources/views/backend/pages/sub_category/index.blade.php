@extends('backend.layouts.master')

@section('title', 'Sub Category Management')

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
                <h4 class="page-title pull-left">Sub Category</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>All Sub Category</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="mb-4">Sub Category Management</h2>
    <button class="btn btn-primary mb-3" id="add-sub_category">Add New sub_category</button>
    <table id="sub_category-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal fade" id="sub_category-modal" tabindex="-1" aria-labelledby="sub_categoryModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="sub_category-form">
                @csrf
                <input type="hidden" id="sub_category_id" name="sub_category_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="sub_categoryModalLabel">sub_category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            @foreach($categorys as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description">
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
        // Initialize DataTable for sub_category
        let sub_categoryTable = $('#sub_category-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("sub_category.datatable") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'category'
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
                    searchable: false,
                },
            ],
        });

        // Global categorys array for dynamic dropdown population
        let categorys = @json($categorys);

        // Function to populate category dropdown
        function populatecategoryDropdown(selectedId = null) {
            let categoryDropdown = $('#category_id');
            categoryDropdown.empty();
            categoryDropdown.append('<option value="">Select category</option>');
            categorys.forEach(category => {
                categoryDropdown.append(
                    `<option value="${category.id}" ${category.id == selectedId ? 'selected' : ''}>${category.name}</option>`
                );
            });
        }

        // Add sub_category Button Click
        $('#add-sub_category').click(function() {
            $('#sub_category-form')[0].reset();
            $('#sub_category_id').val('');
            populatecategoryDropdown(); // Populate dropdown without preselection
            // $('#sub_category-modal').modal('show');
            var myModal = new bootstrap.Modal(document.getElementById('sub_category-modal'), {
            backdrop: 'static', // Prevent modal close when clicking outside
            keyboard: false // Prevent modal close when pressing ESC
            });
            myModal.show();
        });

        // Save or Update sub_category
        $('#sub_category-form').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            $.ajax({
                url: '{{ route("sub_category.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    iziToast.success({
                        message: $('#sub_category_id').val() ?
                            'sub_category updated successfully!' :
                            'sub_category created successfully!',
                        position: 'topRight',
                    });
                    $('#sub_category-modal').modal('hide');
                    sub_categoryTable.ajax.reload();
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

        // Edit sub_category
        $('#sub_category-table').on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('{{ route("sub_category.edit", "") }}/' + id, function(data) {
                $('#sub_category_id').val(data.id);
                $('#name').val(data.name);
                populatecategoryDropdown(data.category_id); // Populate dropdown with selected category
                $('#description').val(data.description);
                $('#status').prop('checked', data.status === 'Y');
                $('#sub_category-modal').modal('show');
            });
        });

        // Delete sub_category
        $('#sub_category-table').on('click', '.delete-btn', function() {
            let id = $(this).data('id');

            if (confirm('Are you sure you want to delete this sub_category member?')) {
                $.ajax({
                    url: `/sub_category/delete/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        iziToast.success({
                            message: response.success,
                            position: 'topRight',
                        });
                        sub_categoryTable.ajax.reload();
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error || 'Failed to delete sub_category member.',
                            position: 'topRight',
                        });
                    },
                });
            }
        });
    });
</script>


@endsection