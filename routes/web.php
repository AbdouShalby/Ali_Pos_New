<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CryptoGatewayController;
use App\Http\Controllers\CryptoTransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ExpenseReportController;
use App\Http\Controllers\ExternalPurchaseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerSalesController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

// Language Switch Route
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        Session::put('locale', $locale);
        App::setLocale($locale);
        Config::set('app.locale', $locale);
        session(['text_direction' => $locale == 'ar' ? 'rtl' : 'ltr']);
    }
    return redirect()->back();
})->name('language.switch');

Auth::routes();

Route::group(['middleware' => ['auth', 'role:Admin']], function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    // POS Routes
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/warehouse-products', [POSController::class, 'getWarehouseProducts'])->name('pos.warehouse-products');
    Route::get('/pos/search-barcode', [POSController::class, 'searchByBarcode'])->name('pos.search-barcode');
    Route::get('/pos/search-products', [POSController::class, 'searchProducts'])->name('pos.search-products');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{id}', [POSController::class, 'printReceipt'])->name('pos.receipt');
    Route::get('/pos/fullscreen', [POSController::class, 'fullscreen'])->name('pos.fullscreen');

    // **Accounting Routes**
    Route::get('/accounting/payments', [PaymentController::class, 'index'])->name('accounting.payments');
    Route::get('/accounting/payments/create', [PaymentController::class, 'create'])->name('accounting.payments.create');
    Route::post('/accounting/payments/store', [PaymentController::class, 'store'])->name('accounting.payments.store');
    Route::delete('/accounting/payments/{id}', [PaymentController::class, 'destroy'])->name('accounting.payments.destroy');
    Route::get('/accounting/revenues', [RevenueController::class, 'index'])->name('accounting.revenues');

    // **Brand Routes**
    Route::post('/brands/store', [BrandController::class, 'store'])->name('brands.store');
    Route::resource('/brands', BrandController::class)->middleware(['auth', 'permission:manage brands']);

    // **Cash Register Routes**
    Route::get('/cash-register', [CashRegisterController::class, 'index'])->name('cash-register.index');
    Route::get('/cash-register/charts', [CashRegisterController::class, 'charts'])->name('cash-register.charts');
    Route::get('/cash-register/daily/{date}', [CashRegisterController::class, 'dailyDetails'])->name('cash-register.daily');
    Route::get('/cash-register/log', [CashRegisterController::class, 'log'])->name('cash-register.log');
    Route::get('/cash-register/reports', [CashRegisterController::class, 'reports'])->name('cash-register.reports');
    Route::get('/cash-register/transaction/{id}', [CashRegisterController::class, 'transactionDetails'])->name('cash-register.transaction');

    // **Category Routes**
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::resource('/categories', CategoryController::class)->middleware(['auth', 'permission:manage categories']);

    // **Crypto Routes**
    Route::resource('/crypto_gateways', CryptoGatewayController::class)->middleware(['auth', 'permission:manage crypto_gateways']);
    Route::get('/crypto_transactions', [CryptoTransactionController::class, 'index'])->name('crypto_transactions.index');
    Route::get('/crypto_transactions/create/{gatewayId}', [CryptoTransactionController::class, 'create'])->name('crypto_transactions.create');
    Route::post('/crypto_transactions/store/{gatewayId}', [CryptoTransactionController::class, 'store'])->name('crypto_transactions.store');
    Route::get('/crypto_transactions/history', [CryptoTransactionController::class, 'history'])->name('crypto_transactions.history');
    Route::get('/crypto_transactions/export', [CryptoTransactionController::class, 'export'])->name('crypto_transactions.export');

    // **Customer Routes**
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::resource('/customers', CustomerController::class)->middleware(['auth', 'permission:manage customers']);

    // **Debt Routes**
    Route::get('/debt/{debt}/payments-history', [SupplierController::class, 'paymentHistory'])->name('debt.paymentHistory');

    // **Device Routes**
    Route::resource('/devices', DeviceController::class)->middleware(['auth', 'permission:manage devices']);

    // **Expense Reports**
    Route::get('/reports/expenses', [ExpenseReportController::class, 'index'])->name('reports.expenses');

    // **Maintenance Routes**
    Route::get('/maintenances/{id}/print', [MaintenanceController::class, 'print'])->name('maintenances.print');
    Route::resource('/maintenances', MaintenanceController::class)->middleware(['auth', 'permission:manage maintenances']);

    // **Mobile Routes**
    Route::resource('/mobiles', MobileController::class)->middleware(['auth', 'permission:manage mobiles']);

    // **Product Routes**
    Route::get('/products-by-category/{categoryId}', [PurchaseController::class, 'getProductsByCategory']);
    Route::get('/products/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.generateBarcode');
    Route::get('/products/check-barcode/{barcode}', [ProductController::class, 'checkBarcode'])->name('products.checkBarcode');
    Route::post('/products/{product}/delete-image', [ProductController::class, 'deleteImage'])->name('products.deleteImage');
    Route::delete('/products/{product}/remove-warehouse/{warehouse}', [ProductController::class, 'removeWarehouse']);
    Route::resource('/products', ProductController::class)->middleware(['auth', 'permission:manage products']);
    Route::get('/products/search', [ProductController::class, 'searchProducts'])->name('products.search');
    Route::get('/products/{product}/duplicate', [ProductController::class, 'duplicateProduct'])->name('products.duplicate');

    // **Purchase Routes**
    Route::get('/purchases/history', [PurchaseController::class, 'history'])->name('purchases.history');
    Route::resource('/purchases', PurchaseController::class)->middleware(['auth', 'permission:manage purchases']);
    Route::resource('/external_purchases', ExternalPurchaseController::class)->middleware(['auth', 'permission:manage external_purchases']);
    Route::get('purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');

    // **Reports Routes**
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/detailed-sales', [ReportController::class, 'detailedSales'])->name('reports.detailed_sales');
        Route::get('/sales/export/pdf', [ReportController::class, 'exportSalesPDF'])->name('reports.sales.export.pdf');
        Route::get('/sales/export/excel', [ReportController::class, 'exportSalesExcel'])->name('reports.sales.export.excel');

        Route::get('/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('/purchases/export/pdf', [ReportController::class, 'exportPurchasesPDF'])->name('reports.purchases.export.pdf');
        Route::get('/purchases/export/excel', [ReportController::class, 'exportPurchasesExcel'])->name('reports.purchases.export.excel');

        Route::get('/debts', [ReportController::class, 'debts'])->name('reports.debts');
        Route::get('/debts/export/pdf', [ReportController::class, 'exportDebtsPDF'])->name('reports.debts.export.pdf');
        Route::get('/debts/export/excel', [ReportController::class, 'exportDebtsExcel'])->name('reports.debts.export.excel');
    });

    // **Sale Routes**
    Route::get('/sales/history', [SaleController::class, 'history'])->name('sales.history');
    Route::get('/sales/{id}/products', [SaleController::class, 'getProducts'])->name('sales.products');
    Route::get('/sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::resource('/sales', SaleController::class)->middleware(['auth', 'permission:manage sales']);

    // **Settings Routes**
    Route::resource('/settings', SettingsController::class)->except(['show'])->middleware(['auth', 'permission:manage settings']);

    // **Supplier Routes**
    Route::get('/suppliers/{supplier}/debts', [SupplierController::class, 'debts'])->name('suppliers.debts');
    Route::get('/debts/{debt}/payments', [SupplierController::class, 'showPaymentsForm'])->name('suppliers.payments');
    Route::post('/debts/{debt}/payments', [SupplierController::class, 'recordPayment'])->name('debt.record_payment');
    Route::get('/debts/{debt}/payment-history', [SupplierController::class, 'paymentHistory'])->name('suppliers.payment_history');
    Route::post('/suppliers/{supplier}/record-bulk-payment', [SupplierController::class, 'recordBulkPayment'])->name('suppliers.record_bulk_payment');
    Route::resource('/suppliers', SupplierController::class)->middleware(['auth', 'permission:manage suppliers']);

    // **Transaction Routes**
    Route::resource('/transactions', TransactionController::class)->middleware(['auth', 'permission:manage accounts']);

    // **User Routes**
    Route::resource('/users', UserController::class)->middleware(['auth', 'permission:manage users']);

    // **Warehouse Routes**
    Route::get('/warehouses/{warehouse}/products', [WarehouseController::class, 'getProducts']);
    Route::resource('/warehouses', WarehouseController::class)->middleware('permission:manage warehouses');

    // Customer Sales Routes
    Route::get('/customers/{customer}/sales', [CustomerSalesController::class, 'index'])->name('customers.sales');
    Route::get('/customers/{customer}/sales/export/pdf', [CustomerSalesController::class, 'exportPdf'])->name('customers.sales.export.pdf');
    Route::get('/customers/{customer}/sales/export/excel', [CustomerSalesController::class, 'exportExcel'])->name('customers.sales.export.excel');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('sales')->group(function () {
        Route::get('export/pdf/{sale}', [SaleController::class, 'exportPdf'])->name('sales.export.pdf');
        Route::get('export/excel/{sale}', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::get('/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');
        Route::get('/recent', [NotificationController::class, 'getRecentNotifications'])->name('notifications.recent');
        Route::post('/test', [NotificationController::class, 'createTestNotifications'])->name('notifications.test');
    });
});
