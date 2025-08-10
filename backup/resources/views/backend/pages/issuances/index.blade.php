@extends('backend.layouts.master')

@section('title', 'Issuances Management')

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
                    <li><span>All Issuances</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="container mt-5">
    <button id="add-issuance" class="btn btn-primary mb-3">Add Issuance</button>
    <table id="issuance-table" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Department</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Issued By</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal -->
<div id="issuance-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="issuance-form">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="issuance_id" name="id">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="staff_id">Staff</label>
                        <select id="staff_id" name="staff_id" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select id="product_id" name="product_id" class="form-control">
                            <option value="">Select Product</option>
                            <!-- Dynamically populated options -->
                        </select>
                        <small id="available_stock" class="text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="1">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
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
        let issuanceTable = $('#issuance-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("issuance.datatable") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'staff_name'
                },
                {
                    data: 'department_name'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'issued_by_name'
                },
                {
                    data: 'description'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
        });

        // Open Add Issuance Modal
        $('#add-issuance').click(function() {
            $('#issuance-form')[0].reset();
            $('#issuance_id').val('');
            loadDropdowns();
            $('#issuance-modal').modal('show');
            $('.modal-title').text('Add Issuance');
        });

        function loadDropdowns() {
            $.ajax({
                url: '{{ route("issuance.products") }}',
                type: 'GET',
                success: function(data) {
                    populateDropdown('#product_id', data.products, 'Select Product');
                    populateDropdown('#department_id', data.departments, 'Select Department');
                    populateDropdown('#staff_id', data.staff, 'Select Staff');
                },
                error: function() {
                    iziToast.error({
                        message: 'Failed to load dropdown data.',
                        position: 'topRight'
                    });
                },
            });
        }

        function populateDropdown(selector, items, placeholder) {
            let dropdown = $(selector);
            dropdown.empty();
            dropdown.append(`<option value="">${placeholder}</option>`);
            $.each(items, function(key, item) {
                dropdown.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }
        $(document).on('change', '#product_id', function() {
            const productId = $(this).val();
            if (productId) {
                checkAvailability(productId);
            }
        });

        function checkAvailability(productId) {
            $.ajax({
                url: `{{ route('issuance.checkAvailability', '') }}/${productId}`,
                type: 'GET',
                success: function(data) {
                    if (data.available_stock > 0) {
                        $('#available_stock').text(`Available Stock: ${data.available_stock}`);
                        $('#available_stock').removeClass('text-danger').addClass('text-success');
                    } else {
                        $('#available_stock').text('Out of Stock');
                        $('#available_stock').removeClass('text-success').addClass('text-danger');
                    }
                },
                error: function() {
                    iziToast.error({
                        message: 'Failed to check stock availability.',
                        position: 'topRight'
                    });
                },
            });
        }

        // On form submission, validate stock availability
        $('#issuance-form').submit(function(e) {
            e.preventDefault();

            const productId = $('#product_id').val();
            const requestedQuantity = parseInt($('#quantity').val(), 10);

            if (!productId || !requestedQuantity) {
                iziToast.error({
                    message: 'Please select a product and enter a valid quantity.',
                    position: 'topRight'
                });
                return;
            }

            // Check stock before submission
            $.ajax({
                url: `{{ route('issuance.checkAvailability', '') }}/${productId}`,
                type: 'GET',
                success: function(data) {
                    if (data.available_stock >= requestedQuantity) {
                        submitForm(); // Proceed to submit the form
                    } else {
                        iziToast.error({
                            message: `Insufficient stock. Available: ${data.available_stock}`,
                            position: 'topRight',
                        });
                    }
                },
                error: function() {
                    iziToast.error({
                        message: 'Failed to check stock availability.',
                        position: 'topRight'
                    });
                },
            });
        });

        function submitForm() {
            $.ajax({
                url: '{{ route("issuance.store") }}',
                method: 'POST',
                data: $('#issuance-form').serialize(),
                success: function(response) {
                    iziToast.success({
                        message: response.success,
                        position: 'topRight'
                    });
                    $('#issuance-modal').modal('hide');
                    issuanceTable.ajax.reload();
                },
                error: function(xhr) {
                    iziToast.error({
                        message: xhr.responseJSON.error || 'An error occurred!',
                        position: 'topRight'
                    });
                },
            });
        }
        // // Submit Issuance Form
        // $('#issuance-form').submit(function(e) {
        //     e.preventDefault();

        //     $.ajax({
        //         url: '{{ route("issuance.store") }}',
        //         method: 'POST',
        //         data: $(this).serialize(),
        //         success: function(response) {
        //             iziToast.success({
        //                 message: response.success,
        //                 position: 'topRight'
        //             });
        //             $('#issuance-modal').modal('hide');
        //             issuanceTable.ajax.reload();
        //         },
        //         error: function(xhr) {
        //             iziToast.error({
        //                 message: xhr.responseJSON.error || 'An error occurred!',
        //                 position: 'topRight'
        //             });
        //         },
        //     });
        // });

        // Edit Issuance
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('{{ route("issuance.edit", "") }}/' + id, function(data) {
                $('#issuance_id').val(data.id);
                $('#department_id').val(data.department_id);
                $('#staff_id').val(data.staff_id);
                $('#product_id').val(data.product_id);
                $('#quantity').val(data.quantity);
                $('#issued_by').val(data.issued_by);
                $('#description').val(data.description);
                $('#issuance-modal').modal('show');
                $('.modal-title').text('Edit Issuance');
            });
        });

        // Delete Issuance
        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            if (confirm('Are you sure to delete this issuance?')) {
                $.ajax({
                    url: '{{ route("issuance.delete", "") }}/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        iziToast.success({
                            message: response.success,
                            position: 'topRight'
                        });
                        issuanceTable.ajax.reload();
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error || 'An error occurred!',
                            position: 'topRight'
                        });
                    },
                });
            }
        });
    });
</script>
</script>
@endsection