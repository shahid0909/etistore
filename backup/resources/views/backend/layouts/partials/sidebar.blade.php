 <!-- sidebar menu area start -->
 @php
     $usr = Auth::guard('admin')->user();
 @endphp
 <div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}">
                <h2 class="text-white">Admin</h2> 
            </a>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">

                    @if ($usr->can('dashboard.view'))
                    <li class="active">
                        <a href="javascript:void(0)" aria-expanded="true"><i class="ti-dashboard"></i><span>dashboard</span></a>
                        <ul class="collapse">
                            <li class="{{ Route::is('admin.dashboard') ? 'active' : '' }}"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        </ul>
                    </li>
                    @endif

                    @if ($usr->can('role.create') || $usr->can('role.view') ||  $usr->can('role.edit') ||  $usr->can('role.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-tasks"></i><span>
                            Roles & Permissions
                        </span></a>
                        <ul class="collapse {{ Route::is('admin.roles.create') || Route::is('admin.roles.index') || Route::is('admin.roles.edit') || Route::is('admin.roles.show') ? 'in' : '' }}">
                            @if ($usr->can('role.view'))
                                <li class="{{ Route::is('admin.roles.index')  || Route::is('admin.roles.edit') ? 'active' : '' }}"><a href="{{ route('admin.roles.index') }}">All Roles</a></li>
                            @endif
                            @if ($usr->can('role.create'))
                                <li class="{{ Route::is('admin.roles.create')  ? 'active' : '' }}"><a href="{{ route('admin.roles.create') }}">Create Role</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    
                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Admins
                        </span></a>
                        <ul class="collapse {{ Route::is('admin.admins.create') || Route::is('admin.admins.index') || Route::is('admin.admins.edit') || Route::is('admin.admins.show') ? 'in' : '' }}">
                            
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('admin.admins.index')  || Route::is('admin.admins.edit') ? 'active' : '' }}"><a href="{{ route('admin.admins.index') }}">All Admins</a></li>
                            @endif

                            @if ($usr->can('admin.create'))
                                <li class="{{ Route::is('admin.admins.create')  ? 'active' : '' }}"><a href="{{ route('admin.admins.create') }}">Create Admin</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Designation
                        </span></a>
                        <ul class="collapse {{ Route::is('designation.index') || Route::is('designation.index') || Route::is('designation.index') || Route::is('designation.index') ? 'in' : '' }}">
                            
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('designation.index')  || Route::is('designation.index') ? 'active' : '' }}"><a href="{{ route('designation.index') }}">All Designation</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Department
                        </span></a>
                        <ul class="collapse {{ Route::is('department.index') || Route::is('department.index') || Route::is('department.index') || Route::is('department.index') ? 'in' : '' }}">
                            
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('department.index')  || Route::is('department.index') ? 'active' : '' }}"><a href="{{ route('department.index') }}">All Department</a></li>
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
                                <li class="{{ Route::is('staff.index')  || Route::is('staff.index') ? 'active' : '' }}"><a href="{{ route('staff.index') }}">All Employee</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Category
                        </span></a>
                        <ul class="collapse {{ Route::is('category.index') || Route::is('category.index') || Route::is('category.index') || Route::is('category.index') ? 'in' : '' }}">
                            
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('category.index')  || Route::is('category.index') ? 'active' : '' }}"><a href="{{ route('category.index') }}">All Category</a></li>
                            @endif
                        </ul>
                        <ul class="collapse {{ Route::is('category.index') || Route::is('category.index') || Route::is('category.index') || Route::is('category.index') ? 'in' : '' }}">
                            
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('category.index')  || Route::is('category.index') ? 'active' : '' }}"><a href="{{ route('sub_category.index') }}">All Sub Category</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                    <li>
                        <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            Product
                        </span></a>
                        <ul class="collapse {{ Route::is('product.index') || Route::is('purchase.index') || Route::is('issuance.index') || Route::is('product.index') ? 'in' : '' }}">
                            
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('product.index')  || Route::is('product.index') ? 'active' : '' }}"><a href="{{ route('product.index') }}">All Product</a></li>
                            @endif
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('purchase.index')  || Route::is('purchase.index') ? 'active' : '' }}"><a href="{{ route('purchase.index') }}">All Purchase</a></li>
                            @endif
                            @if ($usr->can('admin.view'))
                                <li class="{{ Route::is('issuance.index')  || Route::is('issuance.index') ? 'active' : '' }}"><a href="{{ route('issuance.index') }}">All Issuance</a></li>
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