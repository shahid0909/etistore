@extends('backend.layouts.master')

@section('title', 'Purchase Management')

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
                    <li><span>All Purchase</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="mb-4">Purchase Management</h2>
    <button class="btn btn-primary mb-3" id="add-purchase">Add New Purchase</button>
    <table class="table table-bordered table-striped" id="purchase-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Chalan No</th>
                <th>Quantity</th>
                <th>Purchase Date</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>

</div>

<div class="modal fade" id="purchase-modal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="purchase-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Add Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="purchase-id" name="id">
                    <div class="form-group mb-3">
                        <label for="product_id">Product</label>
                        <select class="form-control" id="product_id" name="product_id">
                            <option value="">Select Product</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity">
                    </div>
                    <div class="form-group mb-3">
                        <label for="quantity">Chalan No</label>
                        <input type="text" class="form-control" id="chalan_no" name="chalan_no">
                    </div>
                    <div class="form-group mb-3">
                        <label for="purchase_date">Purchase Date</label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date">
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
        let purchaseTable = $('#purchase-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("purchase.datatable") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'product_name' // This matches the 'product_name' alias from the controller
                },
                {
                    data: 'chalan_no' // Use 'chalan_no' as it was aliased in the controller
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'purchase_date'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
        });

        // Open Add Purchase Modal
        $('#add-purchase').click(function() {
            $('#purchase-form')[0].reset(); // Reset the form
            $('#purchase_id').val(''); // Clear any existing ID
            loadProducts(); // Load products dynamically
            $('#purchase-modal').modal('show'); // Show the modal
            $('.modal-title').text('Add Purchase'); // Update modal title
        });

        function loadProducts() {
            $.ajax({
                url: '{{ route("purchase.products") }}',
                type: 'GET',
                success: function(data) {
                    let productSelect = $('#product_id');
                    productSelect.empty();
                    productSelect.append('<option value="">Select Product</option>');
                    $.each(data, function(key, product) {
                        productSelect.append(`<option value="${product.id}">${product.name}</option>`);
                    });
                },
                error: function() {
                    iziToast.error({
                        message: 'Failed to load products.',
                        position: 'topRight',
                    });
                },
            });
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });
        // Submit Purchase Form (Create or Update)
        $('#purchase-form').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route("purchase.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    iziToast.success({
                        message: response.success,
                        position: 'topRight',
                    });
                    $('#purchase-modal').modal('hide');
                    purchaseTable.ajax.reload();
                },
                error: function(xhr) {
                    iziToast.error({
                        message: xhr.responseJSON.error || 'An error occurred!',
                        position: 'topRight',
                    });
                },
            });
        });

        // Edit Purchase
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get('{{ route("purchase.edit", "") }}/' + id, function(data) {
                $('#purchase_id').val(data.id);
                $('#product_id').val(data.product_id);
                $('#chalan_no').val(data.chalan - no);
                $('#quantity').val(data.quantity);
                $('#purchase_date').val(data.purchase_date);
                loadProducts();
                $('#purchase-modal').modal('show');
                $('.modal-title').text('Edit Purchase'); // Update modal title
            });
        });

        // Delete Purchase
        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            if (confirm('Are you sure to delete this purchase?')) {
                $.ajax({
                    url: '{{ route("purchase.delete", "") }}/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        iziToast.success({
                            message: response.success,
                            position: 'topRight',
                        });
                        purchaseTable.ajax.reload();
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON.error || 'An error occurred!',
                            position: 'topRight',
                        });
                    },
                });
            }
        });
    });
</script>
@endsection