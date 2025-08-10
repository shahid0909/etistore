<!-- sidebar menu area start -->
@php
    $usr = Auth::guard('admin')->user();
@endphp
<div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}">
                <!-- <h3 class="text-white">ETI Store</h3>  -->
                <img src="{{ asset('backend/assets/images/logo/logo.png') }}" alt="Logo" height="80px" width="50%"><br>
                ETI Store IMS
            </a>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">

                    @if ($usr->can('dashboard.view'))
                        <li class="active">
                            <a href="javascript:void(0)" aria-expanded="true"><i class="ti-dashboard"></i><span>Dashboard</span></a>
                            <ul class="collapse">
                                <li class="{{ Route::is('admin.dashboard') ? 'active' : '' }}"><a
                                        href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($usr->can('role.create') || $usr->can('role.view') ||  $usr->can('role.edit') ||  $usr->can('role.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-tasks"></i><span>
                        Roles And Permissions
                        </span></a>
                            <ul class="collapse {{ Route::is('admin.roles.create') || Route::is('admin.roles.index') || Route::is('admin.roles.edit') || Route::is('admin.roles.show') ? 'in' : '' }}">
                                @if ($usr->can('role.view'))
                                    <li class="{{ Route::is('admin.roles.index')  || Route::is('admin.roles.edit') ? 'active' : '' }}">
                                        <a href="{{ route('admin.roles.index') }}">Role</a></li>
                                @endif
                                @if ($usr->can('role.create'))
                                    <li class="{{ Route::is('admin.roles.create')  ? 'active' : '' }}"><a
                                            href="{{ route('admin.roles.create') }}">Create Role</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif


                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            User
                        </span></a>
                            <ul class="collapse {{ Route::is('admin.admins.create') || Route::is('admin.admins.index') || Route::is('admin.admins.edit') || Route::is('admin.admins.show') ? 'in' : '' }}">

                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('admin.admins.index')  || Route::is('admin.admins.edit') ? 'active' : '' }}">
                                        <a href="{{ route('admin.admins.index') }}">User List</a></li>
                                @endif

                                @if ($usr->can('admin.create'))
                                    <li class="{{ Route::is('admin.admins.create')  ? 'active' : '' }}"><a
                                            href="{{ route('admin.admins.create') }}">Create User</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Setup Staff / Officer
                        </span></a>
                            <ul class="collapse {{ Route::is('designation.index') || Route::is('designation.index') || Route::is('designation.index') || Route::is('designation.index') ? 'in' : '' }}">

                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('designation.index')  || Route::is('designation.index') ? 'active' : '' }}">
                                        <a href="{{ route('designation.index') }}">Designation</a></li>
                                @endif
                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('department.index')  || Route::is('department.index') ? 'active' : '' }}">
                                        <a href="{{ route('department.index') }}">Department</a></li>
                                @endif
                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('staff.index')  || Route::is('staff.index') ? 'active' : '' }}">
                                        <a href="{{ route('staff.index') }}">Staff / Officer list</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                <!-- @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Department
                        </span></a>
                        <ul class="collapse {{ Route::is('department.index') || Route::is('department.index') || Route::is('department.index') || Route::is('department.index') ? 'in' : '' }}">

                            @if ($usr->can('admin.view'))
                        <li class="{{ Route::is('department.index')  || Route::is('department.index') ? 'active' : '' }}"><a href="{{ route('department.index') }}">Department</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Employee
                        </span></a>
                        <ul class="collapse {{ Route::is('staff.index') || Route::is('staff.index') || Route::is('staff.index') || Route::is('staff.index') ? 'in' : '' }}">

                            @if ($usr->can('admin.view'))
                        <li class="{{ Route::is('staff.index')  || Route::is('staff.index') ? 'active' : '' }}"><a href="{{ route('staff.index') }}">Staff / Officer list</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif -->
                    @if ($usr->can('category.create') || $usr->can('category.view') ||  $usr->can('category.edit') ||  $usr->can('category.delete')
                            || $usr->can('category.create') || $usr->can('category.view') ||  $usr->can('category.edit') ||  $usr->can('category.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Setup
                        </span></a>
                            <ul class="collapse {{ Route::is('category.index') || Route::is('category.index') || Route::is('category.index') || Route::is('category.index') ? 'in' : '' }}">

                                @if ($usr->can('category.create') || $usr->can('category.view') ||  $usr->can('category.edit') ||  $usr->can('category.delete'))
                                    <li class="{{ Route::is('category.index')  || Route::is('category.index') ? 'active' : '' }}">
                                        <a href="{{ route('category.index') }}">Category</a></li>
                                @endif
                            </ul>
                            <ul class="collapse {{ Route::is('sub-category.index') || Route::is('sub-category.index') || Route::is('sub-category.index') || Route::is('sub-category.index') ? 'in' : '' }}">

                                @if ($usr->can('sub-category.create') || $usr->can('sub-category.view') ||  $usr->can('sub-category.edit') ||  $usr->can('sub-category.delete'))
                                    <li class="{{ Route::is('category.index')  || Route::is('category.index') ? 'active' : '' }}">
                                        <a href="{{ route('sub_category.index') }}">Sub Category</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (
                             $usr->can('admin.create') || $usr->can('admin.view') || $usr->can('admin.edit') || $usr->can('admin.delete') ||
                             $usr->can('product.create') || $usr->can('product.view') || $usr->can('product.edit') || $usr->can('product.delete') ||
                             $usr->can('issue.create') || $usr->can('issue.view') || $usr->can('issue.edit') || $usr->can('issue.delete') ||
                             $usr->can('purchase.create') || $usr->can('purchase.view') || $usr->can('purchase.edit') || $usr->can('purchase.delete')
                         )
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true">
                                <i class="fa fa-user"></i>
                                <span>Product</span>
                            </a>

                            <ul class="collapse {{ Route::is('product.index', 'refundableproduct.index', 'purchases.index', 'issuance.index') ? 'in' : '' }}">
                                @if ($usr->can('product.create') || $usr->can('product.view'))
                                    <li class="{{ Route::is('product.index') ? 'active' : '' }}">
                                        <a href="{{ route('product.index') }}">Non Returnable Product</a>
                                    </li>
                                @endif

                                @if ($usr->can('product.create') || $usr->can('product.view'))
                                    <li class="{{ Route::is('refundableproduct.index') ? 'active' : '' }}">
                                        <a href="{{ route('refundableproduct.index') }}">Returnable Product</a>
                                    </li>
                                @endif

                                @if ($usr->can('purchase.create') || $usr->can('purchase.view') || $usr->can('purchase.edit') || $usr->can('purchase.delete')   )
                                    <li class="{{ Route::is('purchases.index') ? 'active' : '' }}">
                                        <a href="{{ route('purchases.index') }}">Purchase</a>
                                    </li>
                                @endif

                                @if ($usr->can('issue.create') || $usr->can('issue.view') || $usr->can('issue.edit') || $usr->can('issue.delete')   )
                                    <li class="{{ Route::is('issuance.index') ? 'active' : '' }}">
                                        <a href="{{ route('issuance.index') }}">Issuance</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif


                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Report
                        </span></a>
                            <ul class="collapse {{ Route::is('report.index') || Route::is('purchase_report.index') ? 'in' : '' }}">

                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('report.index')  || Route::is('purchase_report.index') ? 'active' : '' }}">
                                        <a href="{{ route('report.index') }}">Issue Report</a></li>
                                @endif
                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('report.index')  || Route::is('purchase_report.index') ? 'active' : '' }}">
                                        <a href="{{ route('purchase_report.index') }}">purchase Report</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- sidebar menu area end -->
