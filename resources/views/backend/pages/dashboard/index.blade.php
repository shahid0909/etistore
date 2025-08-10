
@extends('backend.layouts.master')

@section('title')
Dashboard Page - Admin Panel
@endsection


@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Dashboard</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="index.html">Home</a></li>
                    <li><span>Dashboard</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <marquee style="margin-top: 5px ; color: red ; font-weight: bold ">This is a demo version. Access is available on 25 June 2025.</marquee>


    <div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-4 mt-md-5 mb-3">
                <div class="card">
                    <div class="seo-fact sbg1">
                        <a href="{{ route('admin.roles.index') }}">
                            <div class="p-4 d-flex justify-content-between align-items-center">
                                <div class="seofct-icon"><i class="fa fa-users"></i> Roles</div>
                                <h2>{{ $total_roles }}</h2>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-md-5 mb-3">
                <div class="card">
                    <div class="seo-fact sbg2">
                        <a href="{{ route('admin.admins.index') }}">
                            <div class="p-4 d-flex justify-content-between align-items-center">
                                <div class="seofct-icon"><i class="fa fa-user"></i> Admins</div>
                                <h2>{{ $total_admins }}</h2>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-md-5 mb-3">
                <div class="card">
                    <div class="seo-fact sbg3">
                        <div class="p-4 d-flex justify-content-between align-items-center">
                            <div class="seofct-icon"> <i class="fa fa-universal-access" aria-hidden="true"></i> Permissions</div>
                            <h2>{{ $total_permissions }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
  <div class="container">
    <h2 class="my-4">Low Stock Products</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Current Stock</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->subcategory->name ?? 'N/A' }}</td>
                    <td>{{ $product->inventory->current_stock ?? 0 }}</td>
                    <td>{{ $product->inventory->reorder_level ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No low-stock products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection
