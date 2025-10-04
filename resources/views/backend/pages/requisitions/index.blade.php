@extends('backend.layouts.master')

@section('title', 'Requisitions Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
@endsection

@section('admin-content')
<div class="container mt-5">
    <h3>All Requisitions</h3>
    <table id="requisition-table" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Products</th>
                <th>Quantities</th>
                <th>Rationale</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisitions as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>{{ $req->staff->name }}</td>
                    <td>{{ $req->department->name }}</td>
                    <td>{{ $req->designation->name ?? '' }}</td>
                    <td>
                        @foreach($req->items as $item)
                            {{ $item->product->name }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($req->items as $item)
                            {{ $item->requested_qty }}<br>
                        @endforeach
                    </td>
                    <td>{{ $req->rationale }}</td>
                    <td>{{ ucfirst($req->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#requisition-table').DataTable();
});
</script>
@endsection
