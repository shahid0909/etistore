    @extends('backend.layouts.master')

    @section('title', 'Product Management')

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
                    <h4 class="page-title pull-left">Products</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>All Products</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 clearfix">
                @include('backend.layouts.partials.logout')
            </div>
        </div>
    </div>

    @php
        $usr = Auth::guard('admin')->user();
    @endphp
    <div class="container mt-5">
        <h2 class="mb-4">Product Management</h2>
        @if ($usr->can('product.create'))
        <button class="btn btn-primary mb-3" id="add-product">Add New Product</button>
        @endif
        <table id="product-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Returnable</th>
                    <th>Unit</th>
                    <th>Current Stock</th> <!-- New Column for Reorder Level -->
                    <th>Reorder Level</th> <!-- New Column for Reorder Level -->
                    <th>Action</th>
                </tr>
            </thead>
        </table>

    </div>

    <div class="modal fade" id="product-modal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="product-form">
                    @csrf
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Product</h5>
                        <button type="button" class="btn-close btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subcategory_id">Subcategory</label>
                            <select id="subcategory_id" name="subcategory_id" class="form-control">
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}">
                                    {{ $subcategory->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reorder_level">Reorder Level</label>
                            <input type="number" class="form-control" id="reorder_level" name="reorder_level" value="{{ old('reorder_level') }}" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_id">Unit</label>
                            <select id="unit_id" name="unit_id" class="form-control" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
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
            // Initialize DataTables
            let productTable = $('#product-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("product.datatable") }}',
                columns: [
                    {"data": 'DT_RowIndex', "name": 'DT_RowIndex'},
                    {
                        data: 'name'
                    },
                    {
                        data: 'category'
                    },
                    {
                        data: 'subcategory'
                    },
                    {
                        data: 'is_returnable'
                    },
                    {
                        data: 'unit'
                    },
                    {
                        data: 'current_stock'
                    },
                    {
                        data: 'reorder_level'
                    }, // Add reorder_level column
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                    },
                ],
            });

            // Open Add Product Modal
            $('#add-product').click(function() {
                $('#product-form')[0].reset();
                $('#product_id').val('');
                $('#product-modal').modal('show');
            });

            // Fetch Subcategories Based on Category
            $('#category_id').change(function() {
                fetchSubcategories($(this).val());
            });

            function fetchSubcategories(categoryId) {
                $('#subcategory_id').empty().append('<option value="">Select Subcategory</option>');

                if (categoryId) {
                    $.ajax({
                        url: '{{ route("product.subcategories") }}',
                        type: 'GET',
                        data: {
                            category_id: categoryId
                        },
                        success: function(data) {
                            $.each(data, function(key, subcategory) {
                                $('#subcategory_id').append(
                                    `<option value="${subcategory.id}">${subcategory.name}</option>`
                                );
                            });
                        },
                        error: function() {
                            alert('Failed to fetch subcategories.');
                        },
                    });
                }
            }

            // Handle Form Submission
            $('#product-form').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route("product.store") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#submit-btn').prop('disabled', true);
                    },
                    success: function(response) {
                        iziToast.success({
                            message: response.success,
                            position: 'topRight'
                        });
                        $('#product-modal').modal('hide');
                        productTable.ajax.reload();
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error,
                            position: 'topRight'
                        });
                    },
                    complete: function() {
                        $('#submit-btn').prop('disabled', false);
                    },
                });
            });

            // Open Edit Product Modal
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                $.get('{{ route("product.edit", "") }}/' + id, function(data) {
                    $('#product_id').val(data.id);
                    $('#name').val(data.name);
                    $('#category_id').val(data.category_id);
                    fetchSubcategories(data.category_id);
                    $('#subcategory_id').val(data.subcategory_id);
                    $('#unit_id').val(data.unit_id);
                    $('#description').val(data.description);
                    $('#reorder_level').val(data.inventory ? data.inventory.reorder_level : '');
                    $('#product-modal').modal('show');
                });
            });

            // Handle Delete Product
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                if (confirm('Are you sure to delete this product?')) {
                    $.ajax({
                        url: '{{ route("product.delete", "") }}/' + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            iziToast.success({
                                message: response.success,
                                position: 'topRight'
                            });
                            productTable.ajax.reload();
                        },
                        error: function() {
                            iziToast.error({
                                message: 'Failed to delete product.',
                                position: 'topRight'
                            });
                        },
                    });
                }
            });
        });
    </script>
    @endsection
