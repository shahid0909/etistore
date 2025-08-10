@extends('backend.layouts.master')

@section('title', 'Purchase Reports')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endsection

@section('admin-content')
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Purchase Report</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>Purchase Report</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row mb-3 mt-5">
        <div class="col-md-6">
            <label for="product_id">Product</label>
            <select id="product_id" class="form-control">
                <option value="">All Products</option>
            </select>
        </div>
        <div class="col-md-6">
            <label>Date Range</label>
            <div class="d-flex">
                <input type="date" id="start_date" class="form-control">
                <input type="date" id="end_date" class="form-control ml-1">
            </div>
        </div>
    </div>

    <table id="purchase-report-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
    $(document).ready(function() {
        const table = $('#purchase-report-table').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [ [10, 50, 100, 200], [10, 50, 100, 200] ],
            ajax: {
                url: '{{ route("purchase_report.data") }}',
                data: function(d) {
                    d.product_id = $('#product_id').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'product_name' },
                { data: 'quantity' },
                { data: 'date' }
            ],
            dom: '<"row mb-3"<"col-md-6 d-flex align-items-center"B><"col-md-6"f>>rt<"row"<"col-md-6"l><"col-md-6"p>>',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Download CSV',
                    className: 'btn btn-success btn-sm'
                }
            ]
        });

        function loadDropdowns() {
            $.ajax({
                url: '{{ route("purchase_report.dropdown") }}',
                success: function(data) {
                    populateDropdown('#product_id', data.products, 'Select Product');
                }
            });
        }

        function populateDropdown(selector, items, placeholder) {
            const dropdown = $(selector);
            dropdown.empty();
            dropdown.append(`<option value="">${placeholder}</option>`);
            items.forEach(item => {
                dropdown.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $('.form-control').on('change', function() {
            table.ajax.reload();
        });

        loadDropdowns();
    });
</script>
@endsection
