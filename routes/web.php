<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealSectionController;
use App\Http\Controllers\HeroSectionController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseInventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('/', [AuthController::class, 'loginProcess'])->name('loginProcess');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::group(['prefix' => 'admin'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboardStats', [DashboardController::class, 'getFilteredStats'])
        ->name('admin.dashboardStats');
    Route::get('/dashboard/payment-method-revenue', [DashboardController::class, 'getPaymentMethodRevenue'])
        ->name('admin.dashboard.paymentMethodRevenue');
    Route::get('/dashboard/low-stock-items', [DashboardController::class, 'getLowStockItems'])
        ->name('admin.dashboard.lowStockItems');
    Route::get('/dashboard/sales-summary', [DashboardController::class, 'getSalesSummary'])
        ->name('admin.dashboard.salesSummary');
    Route::get('/dashboard/sales-trend', [DashboardController::class, 'getSalesTrend'])
        ->name('admin.dashboard.salesTrend');
    Route::get('/dashboard/category-breakdown', [DashboardController::class, 'getCategoryBreakdown'])
        ->name('admin.dashboard.categoryBreakdown');
    Route::get('/dashboard/profit-breakdown', [DashboardController::class, 'getProfitBreakdown'])
        ->name('admin.dashboard.profitBreakdown');
    Route::get('/dashboard/inventory-by-category', [DashboardController::class, 'getInventoryByCategory'])
        ->name('admin.dashboard.inventoryByCategory');
    Route::get('/dashboard/purchases-by-vendor', [DashboardController::class, 'getPurchasesByVendor'])
        ->name('admin.dashboard.purchasesByVendor');
    // category
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('admin.category');
        Route::get('/create', [CategoryController::class, 'create'])->name('admin.category.create');
        Route::post('/store', [CategoryController::class, 'storeCategory'])->name('admin.category.store');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('admin.category.edit');
        Route::post('/edit/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
        Route::get('/delete/{id}', [CategoryController::class, 'delete'])->name('admin.category.delete');
    });
    // inventory items
    Route::group(['prefix' => 'inventoryItem'], function () {
        Route::get('/', [InventoryItemController::class, 'index'])->name('admin.inventoryItem');
        Route::get('/categories', [InventoryItemController::class, 'getCategories'])->name('admin.inventoryItem.categories');
        Route::get('/wishlist/{id}', [InventoryItemController::class, 'wishlist'])->name('admin.inventoryItem.wishlist');
        Route::get('/create', [InventoryItemController::class, 'create'])->name('admin.inventoryItem.create');
        Route::post('/store', [InventoryItemController::class, 'store'])->name('admin.inventoryItem.store');
        Route::get('/edit/{id}', [InventoryItemController::class, 'edit'])->name('admin.inventoryItem.edit');
        Route::post('/edit/{id}', [InventoryItemController::class, 'update'])->name('admin.inventoryItem.update');
        Route::get('/delete/{id}', [InventoryItemController::class, 'delete'])->name('admin.inventoryItem.delete');
    });
    // purchase inventory
    Route::group(['prefix' => 'purchaseInventory'], function () {
        Route::get('/', [PurchaseInventoryController::class, 'index'])->name('admin.purchaseInventory');
        Route::get('/create', [PurchaseInventoryController::class, 'create'])->name('admin.purchaseInventory.create');
        Route::post('/store', [PurchaseInventoryController::class, 'store'])->name('admin.purchaseInventory.store');
        Route::get('/edit/{id}', [PurchaseInventoryController::class, 'edit'])->name('admin.purchaseInventory.edit');
        Route::post('/edit/{id}', [PurchaseInventoryController::class, 'update'])->name('admin.purchaseInventory.update');
        Route::get('/delete/{id}', [PurchaseInventoryController::class, 'delete'])->name('admin.purchaseInventory.delete');
        Route::get('/view/{id}', [PurchaseInventoryController::class, 'view'])->name('admin.purchaseInventory.view');
        Route::get('/storeRecords', [PurchaseInventoryController::class, 'storeDataDetails'])->name('admin.purchaseInventory.storeRecords');
        Route::get('/viewRecord/{id}', [PurchaseInventoryController::class, 'viewRecords'])->name('admin.purchaseInventory.viewRecord');
    });
    // sales
    Route::group(['prefix' => 'sales'], function () {
        Route::get('/index', [SalesController::class, 'index'])->name('admin.sales.index');
        Route::post('/store', [SalesController::class, 'storeSales'])->name('admin.sales.store');
        Route::get('/searchCustomer', [SalesController::class, 'searchCustomers'])->name('admin.sales.searchCustomer');
    });
    // sales invoice
    Route::group(['prefix' => 'invoice'], function () {
        Route::get('/index', [InvoiceController::class, 'index'])->name('admin.invoice.index');
        Route::get('/viewInvoice/{id}', [InvoiceController::class, 'viewInvoice'])->name('admin.invoice.viewInvoice');
    });
    // report
    Route::group(['prefix' => 'reports'], function () {
        Route::get('/', [ReportController::class, 'index'])->name('admin.reports');
        Route::get('/salesReport', [ReportController::class, 'salesReports'])->name('admin.reports.salesReport');
        Route::get('/inventoryReport', [ReportController::class, 'inventoryReport'])->name('admin.reports.inventoryReport');
        Route::get('/getReport', [ReportController::class, 'getReport'])->name('admin.reports.getReport');
        Route::get('/getSalesReport', [ReportController::class, 'getSalesReport'])->name('admin.reports.getSalesReport');
        Route::get('/getInventoryReport', [ReportController::class, 'getInventoryReport'])->name('admin.reports.getInventoryReport');
    });
    // product reviews (moderation)
    Route::group(['prefix' => 'review'], function () {
        Route::get('/', [ReviewController::class, 'index'])->name('admin.review');
        Route::get('/delete/{id}', [ReviewController::class, 'delete'])->name('admin.review.delete');
    });
    // storefront orders
    Route::group(['prefix' => 'order'], function () {
        Route::get('/', [OrderController::class, 'index'])->name('admin.order');
        Route::get('/show/{id}', [OrderController::class, 'show'])->name('admin.order.show');
        Route::post('/status/{id}', [OrderController::class, 'updateStatus'])->name('admin.order.status');
    });

    // customer
    Route::group(['prefix' => 'customer'], function () {
        Route::get('/', [CustomerController::class, 'index'])->name('admin.customer');
        Route::get('/addresses/{id}', [CustomerController::class, 'addresses'])->name('admin.customer.addresses');
        Route::get('/create', [CustomerController::class, 'create'])->name('admin.customer.create');
        Route::post('/store', [CustomerController::class, 'store'])->name('admin.customer.store');
        Route::get('/edit/{id}', [CustomerController::class, 'edit'])->name('admin.customer.edit');
        Route::post('/edit/{id}', [CustomerController::class, 'update'])->name('admin.customer.update');
        Route::get('/delete/{id}', [CustomerController::class, 'delete'])->name('admin.customer.delete');
    });
    // hero section (storefront homepage)
    Route::group(['prefix' => 'heroSection'], function () {
        Route::get('/', [HeroSectionController::class, 'index'])->name('admin.heroSection');
        Route::get('/create', [HeroSectionController::class, 'create'])->name('admin.heroSection.create');
        Route::post('/store', [HeroSectionController::class, 'store'])->name('admin.heroSection.store');
        Route::get('/edit/{id}', [HeroSectionController::class, 'edit'])->name('admin.heroSection.edit');
        Route::post('/edit/{id}', [HeroSectionController::class, 'update'])->name('admin.heroSection.update');
        Route::get('/delete/{id}', [HeroSectionController::class, 'delete'])->name('admin.heroSection.delete');
    });
    // deals section (storefront homepage)
    Route::group(['prefix' => 'dealSection'], function () {
        Route::get('/', [DealSectionController::class, 'index'])->name('admin.dealSection');
        Route::get('/create', [DealSectionController::class, 'create'])->name('admin.dealSection.create');
        Route::post('/store', [DealSectionController::class, 'store'])->name('admin.dealSection.store');
        Route::get('/edit/{id}', [DealSectionController::class, 'edit'])->name('admin.dealSection.edit');
        Route::post('/edit/{id}', [DealSectionController::class, 'update'])->name('admin.dealSection.update');
        Route::get('/delete/{id}', [DealSectionController::class, 'delete'])->name('admin.dealSection.delete');
    });
    // permission
    Route::group(['prefix' => 'permission'], function () {
        Route::get('/', [PermissionController::class, 'index'])->name('admin.permission');
        Route::get('/create', [PermissionController::class, 'create'])->name('admin.permission.create');
        Route::post('/create', [PermissionController::class, 'store'])->name('admin.permission.store');
        Route::get('edit/{id}', [PermissionController::class, 'edit'])->name('admin.permission.edit');
        Route::post('edit/{id}', [PermissionController::class, 'update'])->name('admin.permission.update');
        Route::get('delete/{id}', [PermissionController::class, 'delete'])->name('admin.permission.delete');
    });
    // Role
    Route::group(['prefix' => 'role'], function () {
        Route::get('/', [RoleController::class, 'index'])->name('admin.role');
        Route::get('/create', [RoleController::class, 'create'])->name('admin.role.create');
        Route::post('/create', [RoleController::class, 'store'])->name('admin.role.store');
        Route::get('edit/{id}', [RoleController::class, 'edit'])->name('admin.role.edit');
        Route::post('edit/{id}', [RoleController::class, 'update'])->name('admin.role.update');
        Route::get('delete/{id}', [RoleController::class, 'delete'])->name('admin.role.delete');
    });
    // profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('admin.profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
    // user
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.user');
        Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
        Route::post('/create', [UserController::class, 'store'])->name('admin.user.store');
        Route::get('edit/{id}', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::post('edit/{id}', [UserController::class, 'update'])->name('admin.user.update');
        Route::get('delete/{id}', [UserController::class, 'delete'])->name('admin.user.delete');
    });
});


Route::get('/dashboard-alternate', function () {
    return view('dashboard-alternate');
});
Route::get('/dashboard-analytics', function () {
    return view('dashboard-analytics');
});
/*App*/
Route::get('/app-emailbox', function () {
    return view('app-emailbox');
});
Route::get('/app-emailread', function () {
    return view('app-emailread');
});
Route::get('/app-chat-box', function () {
    return view('app-chat-box');
});
Route::get('/app-file-manager', function () {
    return view('app-file-manager');
});
Route::get('/app-contact-list', function () {
    return view('app-contact-list');
});
Route::get('/app-to-do', function () {
    return view('app-to-do');
});
Route::get('/app-invoice', function () {
    return view('app-invoice');
});
Route::get('/app-fullcalender', function () {
    return view('app-fullcalender');
});
/*Charts*/
Route::get('/charts-apex-chart', function () {
    return view('charts-apex-chart');
});
Route::get('/charts-chartjs', function () {
    return view('charts-chartjs');
});
Route::get('/charts-highcharts', function () {
    return view('charts-highcharts');
});
/*ecommerce*/
Route::get('/ecommerce-products', function () {
    return view('ecommerce-products');
});
Route::get('/ecommerce-products-details', function () {
    return view('ecommerce-products-details');
});
Route::get('/ecommerce-add-new-products', function () {
    return view('ecommerce-add-new-products');
});
Route::get('/ecommerce-orders', function () {
    return view('ecommerce-orders');
});

/*Components*/
Route::get('/widgets', function () {
    return view('widgets');
});
Route::get('/component-alerts', function () {
    return view('component-alerts');
});
Route::get('/component-accordions', function () {
    return view('component-accordions');
});
Route::get('/component-badges', function () {
    return view('component-badges');
});
Route::get('/component-buttons', function () {
    return view('component-buttons');
});
Route::get('/component-cards', function () {
    return view('component-cards');
});
Route::get('/component-carousels', function () {
    return view('component-carousels');
});
Route::get('/component-list-groups', function () {
    return view('component-list-groups');
});
Route::get('/component-media-object', function () {
    return view('component-media-object');
});
Route::get('/component-modals', function () {
    return view('component-modals');
});
Route::get('/component-navs-tabs', function () {
    return view('component-navs-tabs');
});
Route::get('/component-navbar', function () {
    return view('component-navbar');
});
Route::get('/component-paginations', function () {
    return view('component-paginations');
});
Route::get('/component-popovers-tooltips', function () {
    return view('component-popovers-tooltips');
});
Route::get('/component-progress-bars', function () {
    return view('component-progress-bars');
});
Route::get('/component-spinners', function () {
    return view('component-spinners');
});
Route::get('/component-notifications', function () {
    return view('component-notifications');
});
Route::get('/component-avtars-chips', function () {
    return view('component-avtars-chips');
});
/*Content*/
Route::get('/content-grid-system', function () {
    return view('content-grid-system');
});
Route::get('/content-typography', function () {
    return view('content-typography');
});
Route::get('/content-text-utilities', function () {
    return view('content-text-utilities');
});
/*Icons*/
Route::get('/icons-line-icons', function () {
    return view('icons-line-icons');
});
Route::get('/icons-boxicons', function () {
    return view('icons-boxicons');
});
Route::get('/icons-feather-icons', function () {
    return view('icons-feather-icons');
});
/*Authentication*/
Route::get('/authentication-signin', function () {
    return view('authentication-signin');
});
Route::get('/authentication-signup', function () {
    return view('authentication-signup');
});
Route::get('/authentication-signin-with-header-footer', function () {
    return view('authentication-signin-with-header-footer');
});
Route::get('/authentication-signup-with-header-footer', function () {
    return view('authentication-signup-with-header-footer');
});
Route::get('/authentication-forgot-password', function () {
    return view('authentication-forgot-password');
});
Route::get('/authentication-reset-password', function () {
    return view('authentication-reset-password');
});
Route::get('/authentication-lock-screen', function () {
    return view('authentication-lock-screen');
});
/*Table*/
Route::get('/table-basic-table', function () {
    return view('table-basic-table');
})->name('table-basic-table');

Route::get('/table-datatable', function () {
    return view('table-datatable');
})->name('table-datatable');

/*Pages*/
Route::get('/user-profile', function () {
    return view('user-profile');
});
Route::get('/timeline', function () {
    return view('timeline');
});
Route::get('/pricing-table', function () {
    return view('pricing-table');
});
Route::get('/errors-404-error', function () {
    return view('errors-404-error');
});
Route::get('/errors-500-error', function () {
    return view('errors-500-error');
});
Route::get('/errors-coming-soon', function () {
    return view('errors-coming-soon');
});
Route::get('/error-blank-page', function () {
    return view('error-blank-page');
});
Route::get('/faq', function () {
    return view('faq');
});
/*Forms*/
Route::get('/form-elements', function () {
    return view('form-elements');
});

Route::get('/form-input-group', function () {
    return view('form-input-group');
});
Route::get('/form-layouts', function () {
    return view('form-layouts');
});
Route::get('/form-validations', function () {
    return view('form-validations');
});
Route::get('/form-wizard', function () {
    return view('form-wizard');
});
Route::get('/form-text-editor', function () {
    return view('form-text-editor');
});
Route::get('/form-file-upload', function () {
    return view('form-file-upload');
});
Route::get('/form-date-time-pickes', function () {
    return view('form-date-time-pickes');
});
Route::get('/form-select2', function () {
    return view('form-select2');
});
/*Maps*/
Route::get('/map-google-maps', function () {
    return view('map-google-maps');
});
Route::get('/map-vector-maps', function () {
    return view('map-vector-maps');
});
/*Un-found*/
Route::get('/test/content-grid-system', function () {
    return view('test/content-grid-system');
});
