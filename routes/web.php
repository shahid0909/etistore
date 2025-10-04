<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RequisitionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\IssuancesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\RefundableProduct;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubCategoryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    // Artisan::call('session:clear');

    return "Cache cleared!";
});

Route::get('/test-write', function () {
    $file = storage_path('app/test.txt');
    // dd($file);  // This will print out the full file path

    // Try writing to the file after confirming the correct path
    file_put_contents($file, 'Test content');
    return 'File written successfully!';
});


Auth::routes();

Route::get('/', 'HomeController@redirectAdmin')->name('index');
Route::get('/home', 'HomeController@index')->name('home');

/**
 * Admin routes
 */
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', 'Backend\DashboardController@index')->name('admin.dashboard');
    Route::resource('roles', 'Backend\RolesController', ['names' => 'admin.roles']);
    Route::resource('users', 'Backend\UsersController', ['names' => 'admin.users']);
    Route::resource('admins', 'Backend\AdminsController', ['names' => 'admin.admins']);
    // Login Routes
    Route::get('/login', 'Backend\Auth\LoginController@showLoginForm')->name('admin.login');
    Route::post('/login/submit', 'Backend\Auth\LoginController@login')->name('admin.login.submit');

    // Logout Routes
    Route::post('/logout/submit', 'Backend\Auth\LoginController@logout')->name('admin.logout.submit');

    // Forget Password Routes
    Route::get('/password/reset', 'Backend\Auth\ForgetPasswordController@showLinkRequestForm')->name('admin.password.request');
    Route::post('/password/reset/submit', 'Backend\Auth\ForgetPasswordController@reset')->name('admin.password.update');
});

// Designation Routes
Route::group(['prefix' => 'designation', 'as' => 'designation.'], function () {
    Route::get('/', [DesignationController::class, 'index'])->name('index'); // /designation
    Route::post('/store', [DesignationController::class, 'store'])->name('store'); // /designation/store
    Route::get('/list', [DesignationController::class, 'datatable'])->name('datatable'); // /designation/list
    Route::get('/edit/{id}', [DesignationController::class, 'edit'])->name('edit'); // /designation/edit/{id}
    Route::delete('delete/{id}', [DesignationController::class, 'destroy'])->name('delete');// /designation/delete/{id}
});

// Designation Routes
Route::group(['prefix' => 'department', 'as' => 'department.'], function () {
    Route::get('/', [DepartmentController::class, 'index'])->name('index'); // /department
    Route::post('/store', [DepartmentController::class, 'store'])->name('store'); // /department/store
    Route::get('/list', [DepartmentController::class, 'datatable'])->name('datatable'); // /department/list
    Route::get('/edit/{id}', [DepartmentController::class, 'edit'])->name('edit'); // /department/edit/{id}
    Route::delete('delete/{id}', [DepartmentController::class, 'destroy'])->name('delete');// /department/delete/{id}
});

// employee Routes
Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
    Route::get('/', [StaffController::class, 'index'])->name('index'); // /staff
    Route::post('/store', [StaffController::class, 'store'])->name('store'); // /staff/store
    Route::get('/list', [StaffController::class, 'datatable'])->name('datatable'); // /staff/list
    Route::get('/edit/{id}', [StaffController::class, 'edit'])->name('edit'); // /staff/edit/{id}
    Route::delete('delete/{id}', [StaffController::class, 'destroy'])->name('delete');// /staff/delete/{id}
});

// Category Routes
Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index'); // /staff
    Route::post('/store', [CategoryController::class, 'store'])->name('store'); // /staff/store
    Route::get('/list', [CategoryController::class, 'datatable'])->name('datatable'); // /staff/list
    Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('edit'); // /staff/edit/{id}
    Route::delete('delete/{id}', [CategoryController::class, 'destroy'])->name('delete');// /staff/delete/{id}
});

// Sub Category Routes
Route::group(['prefix' => 'sub_category', 'as' => 'sub_category.'], function () {
    Route::get('/', [SubCategoryController::class, 'index'])->name('index'); // /sub_category
    Route::post('/store', [SubCategoryController::class, 'store'])->name('store'); // /sub_category/store
    Route::get('/list', [SubCategoryController::class, 'datatable'])->name('datatable'); // /sub_category/list
    Route::get('/edit/{id}', [SubCategoryController::class, 'edit'])->name('edit'); // /sub_category/edit/{id}
    Route::delete('delete/{id}', [SubCategoryController::class, 'destroy'])->name('delete');// /sub_category/delete/{id}
});


// Products Routes
Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('/', [ProductController::class, 'index'])->name('index'); // /sub_category
    Route::get('/get-subcategories', [ProductController::class, 'getSubcategories'])->name('subcategories');
    Route::post('/store', [ProductController::class, 'store'])->name('store'); // /sub_category/store
    Route::get('/list', [ProductController::class, 'datatable'])->name('datatable'); // /sub_category/list
    Route::get('/edit/{id}', [ProductController::class, 'edit'])->name('edit'); // /sub_category/edit/{id}
    Route::delete('delete/{id}', [ProductController::class, 'destroy'])->name('delete');// /sub_category/delete/{id}
});

Route::group(['prefix' => 'refundableproduct', 'as' => 'refundableproduct.'], function () {
    Route::get('/', [RefundableProduct::class, 'index'])->name('index'); // /sub_category
    Route::get('/get-subcategories', [RefundableProduct::class, 'getSubcategories'])->name('subcategories');
    Route::post('/store', [RefundableProduct::class, 'store'])->name('store'); // /sub_category/store
    Route::get('/list', [RefundableProduct::class, 'datatable'])->name('datatable'); // /sub_category/list
    Route::get('/edit/{id}', [RefundableProduct::class, 'edit'])->name('edit'); // /sub_category/edit/{id}
    Route::delete('delete/{id}', [RefundableProduct::class, 'destroy'])->name('delete');// /sub_category/delete/{id}
});


Route::prefix('purchases')->group(function () {
    Route::get('/', [PurchasesController::class, 'index'])->name('purchases.index');
    Route::get('/datatable', [PurchasesController::class, 'datatable'])->name('purchases.datatable');
    Route::get('/categories', [PurchasesController::class, 'getCategories'])->name('purchases.categories');
    Route::get('/subcategories/{categoryId}', [PurchasesController::class, 'getSubCategories'])->name('purchases.subcategories');
    Route::get('/products/{subCategoryId}', [PurchasesController::class, 'getProducts'])->name('purchases.products');
    Route::post('/store', [PurchasesController::class, 'store'])->name('purchases.store');
    Route::get('/edit/{id}', [PurchasesController::class, 'edit'])->name('purchases.edit');
    Route::post('/update/{id}', [PurchasesController::class, 'update'])->name('purchases.update');
    Route::delete('/destroy/{id}', [PurchasesController::class, 'destroy'])->name('purchases.destroy');
});

Route::prefix('issuances')->group(function () {
    Route::get('/', [IssuancesController::class, 'index'])->name('issuance.index'); // Issuance list view
    Route::get('/issuances/products', [IssuancesController::class, 'getDropdownData'])->name('issuance.products'); // Fetch products for dropdown
    Route::get('/issuances/availability/{productId}', [IssuancesController::class, 'checkProductAvailability'])->name('issuance.checkAvailability');
    Route::get('/datatable', [IssuancesController::class, 'datatable'])->name('issuance.datatable'); // DataTable API
    Route::post('/store', [IssuancesController::class, 'store'])->name('issuance.store'); // Store a new issuance
    Route::get('/edit/{id}', [IssuancesController::class, 'edit'])->name('issuance.edit'); // Edit issuance data
    Route::put('/update/{id}', [IssuancesController::class, 'update'])->name('issuance.update'); // Update issuance
    Route::delete('/delete/{id}', [IssuancesController::class, 'destroy'])->name('issuance.delete'); // Delete issuance
});

Route::get('reports', [ReportsController::class, 'index'])->name('report.index');
Route::get('reports/data', [ReportsController::class, 'fetchReportData'])->name('report.data');
Route::get('reports/dropdown', [ReportsController::class, 'getDropdownData'])->name('report.dropdown');

// Purchase Report
Route::get('purchase/reports', [PurchaseReportController::class, 'index'])->name('purchase_report.index');
Route::get('purchase/reports/data', [PurchaseReportController::class, 'fetchReportData'])->name('purchase_report.data');
Route::get('purchase/reports/dropdown', [PurchaseReportController::class, 'getDropdownData'])->name('purchase_report.dropdown');

Route::prefix('requisitions')->name('requisitions.')->group(function() {
    Route::get('/', [RequisitionController::class, 'index'])->name('index');
    Route::get('/create', [RequisitionController::class, 'create'])->name('create');
    Route::get('/staff/{departmentId}', [RequisitionController::class, 'getStaffByDepartment'])->name('requisitions.getStaffByDepartment');
    Route::get('/staff/details/{staffId}', [RequisitionController::class, 'getStaffDetails'])->name('requisitions.getStaffDetails');
    Route::get('/products/stock/{id}', [RequisitionController::class, 'getStock'])->name('products.stock');
    Route::post('/store', [RequisitionController::class, 'store'])->name('store');
});
