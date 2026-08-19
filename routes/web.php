<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\MitraAccountController;
use App\Http\Controllers\IpoController;
use App\Http\Controllers\IpoAllocationController;
use App\Http\Controllers\IpoSaleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Temporary route for Vercel database migration
Route::get('/migrate-db', function () {
    try {
        // Force Session Pooler (port 5432) for migrations to prevent Transaction Pooler DDL issues
        $url = env('DATABASE_URL');
        if ($url) {
            $url = str_replace(':6543', ':5432', $url);
            config(['database.connections.pgsql.url' => $url]);
            \Illuminate\Support\Facades\DB::purge('pgsql');
        }

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return "Database migration and seeding completed successfully! You can now close this page.";
    } catch (\Exception $e) {
        return "Migration Error: " . $e->getMessage();
    }
});

Route::get('/import-old-data', function () {
    try {
        // Force Session Pooler (port 5432) to ensure session_replication_role persists
        $url = env('DATABASE_URL');
        if ($url) {
            $url = str_replace(':6543', ':5432', $url);
            config(['database.connections.pgsql.url' => $url]);
            \Illuminate\Support\Facades\DB::purge('pgsql');
        }

        \Illuminate\Support\Facades\DB::statement('SET session_replication_role = replica;');

        \Illuminate\Support\Facades\DB::table('users')->delete();
        \Illuminate\Support\Facades\DB::table('users')->insert([
            ['id' => 1, 'username' => 'developer', 'name' => 'Developer', 'email' => 'nunungnunungnurhaeni@gmail.com', 'password' => \Illuminate\Support\Facades\Hash::make('#Kipasangin123'), 'role' => 'developer', 'status' => 'active', 'tenant_id' => 1],
            ['id' => 2, 'username' => 'Muhamad Aldiansyah', 'name' => 'Muhamad Aldiansyah', 'email' => 'Muhamad Aldiansyah@algrow.local', 'password' => \Illuminate\Support\Facades\Hash::make('#Kipasangin123'), 'role' => 'investor', 'status' => 'active', 'tenant_id' => null],
        ]);

        \Illuminate\Support\Facades\DB::table('tenants')->delete();
        \Illuminate\Support\Facades\DB::table('tenants')->insert([
            ['id' => 1, 'name' => 'Algrow Capital', 'owner_id' => 1]
        ]);

        \Illuminate\Support\Facades\DB::table('subscription_plans')->delete();
        \Illuminate\Support\Facades\DB::table('subscription_plans')->insert([
            ['id' => 1, 'name' => 'Paket Pro (6 Bulan)', 'price' => 150000, 'duration_months' => 6],
            ['id' => 2, 'name' => 'Paket Lifetime', 'price' => 5000000, 'duration_months' => 120],
            ['id' => 3, 'name' => 'Paket Basic (3 Bulan)', 'price' => 75000, 'duration_months' => 3],
        ]);

        \Illuminate\Support\Facades\DB::table('subscriptions')->delete();
        \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
            ['id' => 1, 'tenant_id' => 1, 'subscription_plan_id' => 1, 'status' => 'expired']
        ]);

        \Illuminate\Support\Facades\DB::statement("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('tenants_id_seq', (SELECT MAX(id) FROM tenants));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('subscription_plans_id_seq', (SELECT MAX(id) FROM subscription_plans));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('subscriptions_id_seq', (SELECT MAX(id) FROM subscriptions));");

        \Illuminate\Support\Facades\DB::statement('SET session_replication_role = DEFAULT;');

        return "Data SaaS Core (Users, Tenants, Subscriptions) berhasil di-import menggunakan Session Pooler! Anda sudah bisa Login.";
    } catch (\Exception $e) {
        return "Import Error: " . $e->getMessage();
    }
});

Route::get('/debug-db', function () {
    $users = \Illuminate\Support\Facades\DB::table('users')->get();
    return response()->json([
        'users' => $users,
    ]);
});

// Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::view('forgot-password', 'auth.forgot-password')->name('password.request');
Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::get('register/search-mitra', [AuthController::class, 'searchMitra'])->name('register.search-mitra');
Route::get('register/companies', [AuthController::class, 'getCompanies'])->name('register.companies');
Route::get('register/mitras/{tenant}', [AuthController::class, 'getTenantMitras'])->name('register.tenant-mitras');
Route::get('register/search-tenant-mitras/{tenant}', [AuthController::class, 'searchTenantMitras'])->name('register.search-tenant-mitras');
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// OTP Verification Routes
Route::get('verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verification.notice');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verification.verify');
Route::post('resend-otp', [AuthController::class, 'resendOtp'])->name('verification.resend');

// Pricing & Subscription
Route::middleware(['auth'])->group(function () {
    Route::get('/pricing', [\App\Http\Controllers\PricingController::class, 'index'])->name('pricing.index');
    Route::post('/pricing/purchase/{id}', [\App\Http\Controllers\PricingController::class, 'purchase'])->name('pricing.purchase');
});

// Protected Routes (Require Active Subscription)
Route::middleware(['auth', 'verified', 'subscribed'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Administrative Routes (Only for Admin & Developer role)
    Route::middleware('admin')->group(function () {
        Route::resource('investors', InvestorController::class)->except(['show']);
        Route::get('investors/{investor}/fund', [InvestorController::class, 'fund'])->name('investors.fund');
        Route::post('investors/{investor}/fund', [InvestorController::class, 'storeFund'])->name('investors.store-fund');
        Route::post('investors/{investor}/withdraw', [InvestorController::class, 'withdraw'])->name('investors.withdraw');
        Route::post('investors/{investor}/deposit', [InvestorController::class, 'deposit'])->name('investors.deposit');

        Route::post('mitra-accounts/import', [MitraAccountController::class, 'import'])->name('mitra-accounts.import');
        Route::get('mitra-catalog', [MitraAccountController::class, 'grid'])->name('mitra-accounts.grid');
        Route::get('ipos/{ipo}/allotment-bulk', [IpoAllocationController::class, 'bulkCreate'])->name('ipos.allotment-bulk');
        Route::post('ipos/{ipo}/allotment-bulk', [IpoAllocationController::class, 'bulkStore'])->name('ipos.allotment-bulk.store');
        Route::post('mitra-groups/{mitra_group}/assign', [\App\Http\Controllers\MitraGroupController::class, 'assignAccounts'])->name('mitra-groups.assign');
        Route::post('mitra-groups/{mitra_group}/remove', [\App\Http\Controllers\MitraGroupController::class, 'removeAccounts'])->name('mitra-groups.remove');
        Route::resource('mitra-groups', \App\Http\Controllers\MitraGroupController::class);
        Route::patch('mitra-accounts/{mitraAccount}/update-field', [MitraAccountController::class, 'updateField'])->name('mitra-accounts.update-field');
        Route::get('mitra-accounts/export', [MitraAccountController::class, 'export'])->name('mitra-accounts.export');
        Route::get('mitra-accounts/template', [MitraAccountController::class, 'template'])->name('mitra-accounts.template');
        Route::resource('mitra-accounts', MitraAccountController::class)->except(['index']);
        Route::get('ipos/report', [IpoController::class, 'report'])->name('ipos.report');
        Route::get('ipos/report/export', [IpoController::class, 'exportReport'])->name('ipos.export-report');
        Route::resource('ipos', IpoController::class);

        Route::post('ipos/{ipo}/select-mitras', [IpoController::class, 'selectMitras'])->name('ipos.select-mitras');
        Route::post('ipos/{ipo}/placement', [IpoController::class, 'storePlacement'])->name('ipos.store-placement');
        Route::post('ipos/{ipo}/placement-row', [IpoController::class, 'storeRowPlacement'])->name('ipos.store-row-placement');
        Route::post('ipos/{ipo}/bulk-placement', [IpoController::class, 'storeBulkPlacement'])->name('ipos.bulk-placement');
        Route::delete('ipos/{ipo}/placement-row/{account}', [IpoController::class, 'destroyRowPlacement'])->name('ipos.destroy-row-placement');
        Route::delete('ipos/{ipo}/reset-placements', [IpoController::class, 'resetAllPlacements'])->name('ipos.reset-placements');
        Route::delete('ipos/{ipo}/reset-allotments', [IpoController::class, 'resetAllAllotments'])->name('ipos.reset-allotments');
        Route::delete('ipos/{ipo}/reset-sales', [IpoController::class, 'resetAllSales'])->name('ipos.reset-sales');
        Route::delete('ipos/{ipo}/reset-all', [IpoController::class, 'resetAllData'])->name('ipos.reset-all');
        Route::get('ipos/{ipo}/export-placements', [IpoController::class, 'exportPlacements'])->name('ipos.export-placements');

        Route::get('placements/{placement}/allocation', [IpoAllocationController::class, 'create'])->name('ipo-allocations.create');
        Route::post('placements/{placement}/allocation', [IpoAllocationController::class, 'store'])->name('ipo-allocations.store');

        Route::get('ipos/{ipo}/sale', [IpoSaleController::class, 'create'])->name('ipo-sales.create');
        Route::post('ipos/{ipo}/sale', [IpoSaleController::class, 'store'])->name('ipo-sales.store');
        
        // User Management & Activity Monitor
        Route::post('users/broadcast', function (Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Cache::put('global_greeting', [
                'id' => time(),
                'message' => $request->message
            ], now()->addMinutes(10));
            return back()->with('success', 'Sapaan Sang Pencipta berhasil dikirim ke seluruh layar umat secara real-time!');
        })->name('broadcast.send');
        
        Route::get('api/check-broadcast', function () {
            if (\Illuminate\Support\Facades\Cache::has('global_greeting')) {
                return response()->json(\Illuminate\Support\Facades\Cache::get('global_greeting'));
            }
            return response()->json(['message' => null]);
        })->name('broadcast.check');
        
        Route::resource('users', UserController::class);
        Route::get('tenants', [\App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
        Route::put('tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'update'])->name('tenants.update');
        Route::delete('tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'destroy'])->name('tenants.destroy');
        Route::post('tenants/{tenant}/activate-subscription', [\App\Http\Controllers\TenantController::class, 'activateSubscription'])->name('tenants.activate-subscription');
        Route::post('tenants/{tenant}/deactivate-subscription', [\App\Http\Controllers\TenantController::class, 'deactivateSubscription'])->name('tenants.deactivate-subscription');
        
        // Profit Distribution
        Route::get('/profit-distribution', [App\Http\Controllers\ProfitDistributionController::class, 'index'])->name('profit-distribution.index');
        Route::post('/profit-distribution/{ipo}/distribute', [App\Http\Controllers\ProfitDistributionController::class, 'distribute'])->name('profit-distribution.distribute');
    });

    // Routes accessible by Investors (and Admins/Developers)
    Route::get('investors/{investor}', [InvestorController::class, 'show'])->name('investors.show');
    Route::get('investors/{investor}/transactions', [InvestorController::class, 'transactions'])->name('investors.transactions');
    Route::post('investors/{investor}/update-account', [InvestorController::class, 'updateAccount'])->name('investors.update-account');
    Route::get('investors/{investor}/portfolio', [InvestorController::class, 'portfolio'])->name('investors.portfolio');
    Route::get('/investor/report', [InvestorController::class, 'report'])->name('investor.report');

    Route::get('investors/{investor}/export', [InvestorController::class, 'export'])->name('investors.export');
    Route::get('mitra-accounts', [MitraAccountController::class, 'index'])->name('mitra-accounts.index');

    // User IPO Tasks (Mitra/Joki)
    Route::get('my-tasks/profit', [\App\Http\Controllers\UserIpoTaskController::class, 'myProfit'])->name('user-tasks.profit');
    Route::get('my-tasks', [\App\Http\Controllers\UserIpoTaskController::class, 'index'])->name('user-tasks.index');
    Route::get('my-tasks/{ipo}/allotment', [\App\Http\Controllers\UserIpoTaskController::class, 'editAllotment'])->name('user-tasks.allotment');
    Route::post('my-tasks/{ipo}/allotment', [\App\Http\Controllers\UserIpoTaskController::class, 'storeAllotment'])->name('user-tasks.store-allotment');
    Route::get('my-tasks/{ipo}/sale', [\App\Http\Controllers\UserIpoTaskController::class, 'editSale'])->name('user-tasks.sale');
    Route::post('my-tasks/{ipo}/sale', [\App\Http\Controllers\UserIpoTaskController::class, 'storeSale'])->name('user-tasks.store-sale');

    // Live Ticker API
    Route::get('ticker-live/{ticker}', [IpoController::class, 'tickerLive'])->name('ticker.live');

    // User Profile
    Route::get('my-profile', [\App\Http\Controllers\UserProfileController::class, 'edit'])->name('my-profile.edit');
    Route::put('my-profile', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('my-profile.update');
});
