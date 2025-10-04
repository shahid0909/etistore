@extends('backend.layouts.master')

@section('title', 'Create Requisition')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
@endsection

@section('admin-content')
<div class="container mt-5">
    <button id="add-requisition" class="btn btn-primary mb-3">Add Requisition</button>

    <!-- Modal -->
    <div id="requisition-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="requisition-form">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Requisition</h5>
                        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="requisition_id" name="id">

                        {{-- Staff Row --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="department_id">Department</label>
                                <select id="department_id" name="department_id" class="form-control" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="staff_id">Staff</label>
                                <select id="staff_id" name="staff_id" class="form-control" required>
                                    <option value="">Select Staff</option>

                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="designation">Designation</label>
                                <input type="text" id="designation" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label>Rationale / Purpose</label>
                            <textarea name="rationale" id="rationale" class="form-control" required></textarea>
                        </div>

                        {{-- Products Row --}}
                        <div id="products-wrapper">
                            <div class="product-row row mb-2">
                                <div class="col-md-5">
                                    <label>Product</label>
                                    <select name="products[]" class="form-control product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <small class="text-muted stock-info">Stock: -</small>
                                </div>
                                <div class="col-md-3">
                                    <label>Quantity</label>
                                    <input type="number" name="quantities[]" class="form-control qty-input" min="1" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-product">X</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-product-row" class="btn btn-secondary mb-2">Add Another Product</button>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit Requisition</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
<script>
$(document).ready(function() {

    $('#add-requisition').click(function() {
        $('#requisition-form')[0].reset();
        $('#requisition-modal').modal('show');
    });

    // Fetch staff when department is selected
    $('#department_id').change(function() {
        let deptId = $(this).val();
        $('#staff_id').empty().append('<option value="">Select Staff</option>');
        $('#designation').val('');

        if (deptId) {
            $.get("{{ url('requisitions/staff') }}/" + deptId, function(data) {
                $.each(data, function(index, staff) {
                    $('#staff_id').append(`<option value="${staff.id}">${staff.name}</option>`);
                });
            });
        }
    });

    // Fetch designation when staff is selected
    $('#staff_id').change(function() {
        let staffId = $(this).val();
        $('#designation').val('');
        if (staffId) {
            $.get("{{ url('requisitions/staff/details') }}/" + staffId, function(data) {
                $('#designation').val(data.designation);
            });
        }
    });
    // Product → Show Stock
    $(document).on('change', '.product-select', function() {
        let productId = $(this).val();
        let stockInfo = $(this).closest('.product-row').find('.stock-info');
        if (productId) {
            $.get("{{ url('requisitions/products/stock') }}/" + productId, function(data) {
                stockInfo.text('Stock: ' + data.current_stock);
                if (data.current_stock <= 0) {
                    stockInfo.removeClass('text-muted').addClass('text-danger');
                } else {
                    stockInfo.removeClass('text-danger').addClass('text-success');
                }
            });
        } else {
            stockInfo.text('Stock: -').removeClass('text-success text-danger').addClass('text-muted');
        }
    });
    // Add product row
    $('#add-product-row').click(function() {
        let newRow = $('.product-row:first').clone();
        newRow.find('select').val('');
        newRow.find('input').val('');
        $('#products-wrapper').append(newRow);
    });

    // Remove product row
    $(document).on('click', '.remove-product', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('.product-row').remove();
        } else {
            iziToast.error({ message: 'At least one product is required', position: 'topRight' });
        }
    });

    // Submit form
    $('#requisition-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("requisitions.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                iziToast.success({ message: response.success, position: 'topRight' });
                $('#requisition-modal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                iziToast.error({ message: xhr.responseJSON.error || 'An error occurred!', position: 'topRight' });
            }
        });
    });

});
</script>
@endsection
