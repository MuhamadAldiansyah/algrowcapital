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
        \Illuminate\Support\Facades\DB::statement('SET session_replication_role = replica;');

        \Illuminate\Support\Facades\DB::table('users')->truncate();
        \Illuminate\Support\Facades\DB::table('users')->insert([
            ['id' => 1, 'username' => 'developer', 'name' => 'Developer', 'email' => 'nunungnunungnurhaeni@gmail.com', 'password' => '$2y$12$QU2Mlr/kwz53Ggujx1KjDOINoo5b5wuM2cyUcVDZlgssbUw1V66KS', 'role' => 'developer', 'status' => 'active', 'tenant_id' => 1],
            ['id' => 2, 'username' => 'Muhamad Aldiansyah', 'name' => 'Muhamad Aldiansyah', 'email' => 'Muhamad Aldiansyah@algrow.local', 'password' => '$2y$12$4Zg02VwypBkASgAvfGumPuki6USRbtFispyIy.s6CIRlUWK4x.7Si', 'role' => 'investor', 'status' => 'active', 'tenant_id' => null],
        ]);

        \Illuminate\Support\Facades\DB::table('tenants')->truncate();
        \Illuminate\Support\Facades\DB::table('tenants')->insert([
            ['id' => 1, 'name' => 'Algrow Capital', 'owner_id' => 1]
        ]);

        \Illuminate\Support\Facades\DB::table('subscription_plans')->truncate();
        \Illuminate\Support\Facades\DB::table('subscription_plans')->insert([
            ['id' => 1, 'name' => 'Paket Pro (6 Bulan)', 'price' => 150000, 'duration_months' => 6],
            ['id' => 2, 'name' => 'Paket Lifetime', 'price' => 5000000, 'duration_months' => 120],
            ['id' => 3, 'name' => 'Paket Basic (3 Bulan)', 'price' => 75000, 'duration_months' => 3],
        ]);

        \Illuminate\Support\Facades\DB::table('subscriptions')->truncate();
        \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
            ['id' => 1, 'tenant_id' => 1, 'subscription_plan_id' => 1, 'status' => 'expired']
        ]);

        \Illuminate\Support\Facades\DB::table('mitra_accounts')->truncate();
        \Illuminate\Support\Facades\DB::table('mitra_accounts')->insert([
            ['id' => 1, 'owner_name' => 'Abdul Ghani Alhariri', 'platform' => 'STOCKBIT', 'username' => 'Ahariri', 'password' => 'eyJpdiI6Ik0xZ2IwakNJZm5zd1U0N200SXFiRXc9PSIsInZhbHVlIjoiSFNwb1h1SElTSE4yL0pBdWhZdzZ0Zz09IiwibWFjIjoiZTEyZGYzYzJkNGExMzExNTZiYmIxZjg1MWY3MWIzNDRjZjFlNWU5MjgxNDYxMWM5OGFlNzhhNTRlMDYwN2JhZCIsInRhZyI6IiJ9', 'pin' => 'eyJpdiI6IkRVdUs5UDc2WEVhcHNydkJMU3RiQ2c9PSIsInZhbHVlIjoiQUFsNVdwNEFwcTZvTXMzSGRwN2VDQT09IiwibWFjIjoiNTk4ODg1ZGY1YzNhNTI5OTY2MTE3OTMxMjRjMzJmNTAwMDU5YzAwYTJmZjFmNmU3MzJlMmUyNTNjZGYyY2YzMSIsInRhZyI6IiJ9', 'bank_rdn' => 'BANK JAGO', 'status' => 'aktif', 'device' => 'A10', 'tenant_id' => 1],
            ['id' => 2, 'owner_name' => 'Muhammad Syahbudi', 'platform' => 'AJAIB', 'username' => 'muhammadsyahbudi43@gmail.com', 'password' => 'eyJpdiI6InRiSVowZTFVU2ZZMnRndzZyNTd0TGc9PSIsInZhbHVlIjoiWHRXclRrVGxzL21MWjI1U2FDOXhCdz09IiwibWFjIjoiNzZlYWM5YWVkOWQ0MjI2ODlmZTg1NTE4MjFhYTdiMmVhYjA5MDdmZWE1NDAyOWZjMmE1MjVkNGY2MzU0Y2I3MSIsInRhZyI6IiJ9', 'pin' => 'eyJpdiI6IjZQZTF3RU45NmszcEFNSFFTSkVWSEE9PSIsInZhbHVlIjoiOWxGMWdKM0ZISE5WOUhFNzRzY0xtQT09IiwibWFjIjoiYjAzODM0NzM3YWE0YzE3ODI5N2RjM2VhNjdmYjU1NjQ3Y2Q2YzZjMzFmZGVhYWM5M2QzMGRlMjc0YzQyNjlmNyIsInRhZyI6IiJ9', 'bank_rdn' => 'BANK PERMATA', 'rdn_account' => '9949804728', 'status' => 'aktif', 'device' => 'Ajaib HP', 'tenant_id' => 1],
            ['id' => 3, 'owner_name' => 'Ilham Setiawan', 'platform' => 'AJAIB', 'username' => 'ilhambongo123@gmail.com', 'password' => 'eyJpdiI6InZINGRFN1M1OHhraVlJMnVaYXJQWlE9PSIsInZhbHVlIjoiL2dCS1ZkaFZHdW5QbWZHZk5tdFRKdz09IiwibWFjIjoiMDkyNjgyZTFmZDJkYjA1ODhkOGNkNzM2YThlZDZlNWQ4MGM3MTMwNGNiM2U5YjRhYWU2ZmRjYmRmZGNmNjNmZSIsInRhZyI6IiJ9', 'pin' => 'eyJpdiI6IkJVekwrWG5LUVpGQmNsTFNRRVdManc9PSIsInZhbHVlIjoiUVZuWUxSd3EwOFNmS1ZUdUJCTzZnQT09IiwibWFjIjoiMzNhMjU4Mjk4N2Y2Y2FmMTMyNjZhNWZiZjRlZWQxYWNiZWI3N2Y0YzkxYTI2YmE5Nzk2MDU0OTY5Zjk4ZWI4NCIsInRhZyI6IiJ9', 'bank_rdn' => 'BANK PERMATA', 'rdn_account' => '9943222883', 'status' => 'aktif', 'device' => 'Ajaib HP', 'tenant_id' => 1],
            ['id' => 4, 'owner_name' => 'Jeri Renaldi', 'platform' => 'AJAIB', 'username' => 'renaldi jerry212@gmail.com', 'password' => 'eyJpdiI6IngyMUR3aDlNelJRUDRZYnF0ZGFwcGc9PSIsInZhbHVlIjoibWdVWU02bHhvb01QWithdG5nMnVRdz09IiwibWFjIjoiZTNhYWRhYzBmMjY5MDkwMDIyZTdiNGQxYzZjY2QzYzhmNDFlNGQwNjQ4NjQxZmE4Mzk1MTFkN2FhYWE3ZDcyZCIsInRhZyI6IiJ9', 'pin' => 'eyJpdiI6IkNCcjZiWmdzZFNPUzNnVW9BYW5Oa3c9PSIsInZhbHVlIjoiNkNaZFlwaThlWXF0SWJ0NUJrZU0vdz09IiwibWFjIjoiMzU3NjA0MzYwZWVkMWEzMTQ5OGI2YmE5M2ExNzg4YWZkYTIxZGZiODAxIsInRhZyI6IiJ9', 'bank_rdn' => 'BANK PERMATA', 'rdn_account' => '9952249867', 'status' => 'aktif', 'device' => 'Ajaib HP', 'tenant_id' => 1],
            ['id' => 5, 'owner_name' => 'Eryanto Ikmi', 'platform' => 'AJAIB', 'username' => 'kangato838@gmail.com', 'password' => 'eyJpdiI6Im5nUnRaay9zVHRaekE5Zkx1NWZmZFE9PSIsInZhbHVlIjoiZUg3WUV6SGlSTXpERGxJRVE5ZDRsQT09IiwibWFjIjoiY2FmMzNmMjUwM2Y1ZGEwZjdhODk4NDU1YThmZDRhMzkyNzhjMGU0N2YxYjc3YzQ5ZmQzYWFkMTkwN2U3Njg2ZiIsInRhZyI6IiJ9', 'pin' => 'eyJpdiI6IlZSbmdBZkc1UmJkV2xtR1V3UkFyaUE9PSIsInZhbHVlIjoidUVqSTlyemRDbklmbnpCeWRHT0M0QT09IiwibWFjIjoiNjQ4ODY1MGEwNzRiZDk4ZWYxMzQwM2EwYWFiYWVmZDVhMjFlZWQ4NzQ3ZmVkZGU5ZWJmODk5ZmVmZjY3YTc4NSIsInRhZyI6IiJ9', 'bank_rdn' => 'BANK PERMATA', 'rdn_account' => '9945396315', 'status' => 'aktif', 'device' => 'Ajaib HP', 'tenant_id' => 1],
        ]);

        \Illuminate\Support\Facades\DB::table('investors')->truncate();
        \Illuminate\Support\Facades\DB::table('investors')->insert([
            ['id' => 1, 'name' => 'Muhamad Aldiansyah', 'user_id' => 2, 'tenant_id' => 1]
        ]);

        \Illuminate\Support\Facades\DB::table('investor_transactions')->truncate();
        \Illuminate\Support\Facades\DB::table('investor_transactions')->insert([
            ['id' => 1, 'investor_id' => 1, 'amount' => 10000000.00, 'type' => 'DEPOSIT', 'description' => 'Setoran modal awal'],
            ['id' => 2, 'investor_id' => 1, 'amount' => 180000.00, 'type' => 'REFUND', 'description' => 'Refund Sisa Modal IPO SUPA (Abdul Ghani Alhariri)'],
            ['id' => 3, 'investor_id' => 1, 'amount' => 180000.00, 'type' => 'REFUND', 'description' => 'Refund Sisa Modal IPO SUPA (Muhammad Syahbudi)'],
            ['id' => 4, 'investor_id' => 1, 'amount' => 180000.00, 'type' => 'REFUND', 'description' => 'Refund Sisa Modal IPO SUPA (Ilham Setiawan)'],
            ['id' => 5, 'investor_id' => 1, 'amount' => 180000.00, 'type' => 'REFUND', 'description' => 'Refund Sisa Modal IPO SUPA (Jeri Renaldi)'],
            ['id' => 6, 'investor_id' => 1, 'amount' => 180000.00, 'type' => 'REFUND', 'description' => 'Refund Sisa Modal IPO SUPA (Eryanto Ikmi)'],
            ['id' => 12, 'investor_id' => 1, 'amount' => 626062.50, 'type' => 'PROFIT', 'description' => 'Profit Saham SUPA - Bagi Hasil (70% pool)'],
            ['id' => 13, 'investor_id' => 1, 'amount' => 10626062.00, 'type' => 'WITHDRAW', 'description' => 'Penarikan Saldo'],
        ]);

        \Illuminate\Support\Facades\DB::table('ipos')->truncate();
        \Illuminate\Support\Facades\DB::table('ipos')->insert([
            ['id' => 1, 'name' => 'PT SUPERBANK', 'image_path' => 'ipos/4IHXUeAkJUXegb0Ma3nPkrczgWd7grxA5PE16xGP.png', 'code' => 'SUPA', 'price' => 900.00, 'ipo_date' => '2026-08-11', 'mitra_fee_pct' => 30.00, 'platform_fee_pct' => 70.00, 'tenant_id' => 1]
        ]);

        \Illuminate\Support\Facades\DB::table('ipo_account_placements')->truncate();
        \Illuminate\Support\Facades\DB::table('ipo_account_placements')->insert([
            ['id' => 1, 'ipo_id' => 1, 'mitra_account_id' => 1, 'capital_allocated' => 450000, 'mitra_share_pct' => 50],
            ['id' => 2, 'ipo_id' => 1, 'mitra_account_id' => 2, 'capital_allocated' => 450000, 'mitra_share_pct' => 50],
            ['id' => 3, 'ipo_id' => 1, 'mitra_account_id' => 3, 'capital_allocated' => 450000, 'mitra_share_pct' => 50],
            ['id' => 4, 'ipo_id' => 1, 'mitra_account_id' => 4, 'capital_allocated' => 450000, 'mitra_share_pct' => 50],
            ['id' => 5, 'ipo_id' => 1, 'mitra_account_id' => 5, 'capital_allocated' => 450000, 'mitra_share_pct' => 50],
        ]);

        \Illuminate\Support\Facades\DB::table('investor_fundings')->truncate();
        \Illuminate\Support\Facades\DB::table('investor_fundings')->insert([
            ['id' => 1, 'investor_id' => 1, 'ipo_account_placement_id' => 1, 'amount_funded' => 450000, 'share_pct' => 50],
            ['id' => 2, 'investor_id' => 1, 'ipo_account_placement_id' => 2, 'amount_funded' => 450000, 'share_pct' => 50],
            ['id' => 3, 'investor_id' => 1, 'ipo_account_placement_id' => 3, 'amount_funded' => 450000, 'share_pct' => 50],
            ['id' => 4, 'investor_id' => 1, 'ipo_account_placement_id' => 4, 'amount_funded' => 450000, 'share_pct' => 50],
            ['id' => 5, 'investor_id' => 1, 'ipo_account_placement_id' => 5, 'amount_funded' => 450000, 'share_pct' => 50],
        ]);

        \Illuminate\Support\Facades\DB::table('ipo_allocations')->truncate();
        \Illuminate\Support\Facades\DB::table('ipo_allocations')->insert([
            ['id' => 1, 'ipo_account_placement_id' => 1, 'lot_allocated' => 3, 'final_price_ipo' => 900, 'total_used' => 270000, 'remaining_capital' => 180000],
            ['id' => 2, 'ipo_account_placement_id' => 2, 'lot_allocated' => 3, 'final_price_ipo' => 900, 'total_used' => 270000, 'remaining_capital' => 180000],
            ['id' => 3, 'ipo_account_placement_id' => 3, 'lot_allocated' => 3, 'final_price_ipo' => 900, 'total_used' => 270000, 'remaining_capital' => 180000],
            ['id' => 4, 'ipo_account_placement_id' => 4, 'lot_allocated' => 3, 'final_price_ipo' => 900, 'total_used' => 270000, 'remaining_capital' => 180000],
            ['id' => 5, 'ipo_account_placement_id' => 5, 'lot_allocated' => 3, 'final_price_ipo' => 900, 'total_used' => 270000, 'remaining_capital' => 180000],
        ]);

        \Illuminate\Support\Facades\DB::table('ipo_sales')->truncate();
        \Illuminate\Support\Facades\DB::table('ipo_sales')->insert([
            ['id' => 1, 'ipo_account_placement_id' => 1, 'ipo_id' => 1, 'sell_price' => 1500, 'total_return' => 450000, 'net_profit' => 178875],
            ['id' => 2, 'ipo_account_placement_id' => 2, 'ipo_id' => 1, 'sell_price' => 1500, 'total_return' => 450000, 'net_profit' => 178875],
            ['id' => 3, 'ipo_account_placement_id' => 3, 'ipo_id' => 1, 'sell_price' => 1500, 'total_return' => 450000, 'net_profit' => 178875],
            ['id' => 4, 'ipo_account_placement_id' => 4, 'ipo_id' => 1, 'sell_price' => 1500, 'total_return' => 450000, 'net_profit' => 178875],
            ['id' => 5, 'ipo_account_placement_id' => 5, 'ipo_id' => 1, 'sell_price' => 1500, 'total_return' => 450000, 'net_profit' => 178875],
        ]);

        \Illuminate\Support\Facades\DB::statement("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('tenants_id_seq', (SELECT MAX(id) FROM tenants));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('subscription_plans_id_seq', (SELECT MAX(id) FROM subscription_plans));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('subscriptions_id_seq', (SELECT MAX(id) FROM subscriptions));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('mitra_accounts_id_seq', (SELECT MAX(id) FROM mitra_accounts));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('investors_id_seq', (SELECT MAX(id) FROM investors));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('investor_transactions_id_seq', (SELECT MAX(id) FROM investor_transactions));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('ipos_id_seq', (SELECT MAX(id) FROM ipos));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('ipo_account_placements_id_seq', (SELECT MAX(id) FROM ipo_account_placements));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('investor_fundings_id_seq', (SELECT MAX(id) FROM investor_fundings));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('ipo_allocations_id_seq', (SELECT MAX(id) FROM ipo_allocations));");
        \Illuminate\Support\Facades\DB::statement("SELECT setval('ipo_sales_id_seq', (SELECT MAX(id) FROM ipo_sales));");

        \Illuminate\Support\Facades\DB::statement('SET session_replication_role = DEFAULT;');

        return "Data MySQL lama berhasil di-import ke PostgreSQL Supabase! Anda sudah bisa Login dengan data lama Anda.";
    } catch (\Exception $e) {
        return "Import Error: " . $e->getMessage();
    }
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
        Route::resource('users', UserController::class);
        Route::get('tenants', [\App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
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
});
