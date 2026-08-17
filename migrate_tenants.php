<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$firstUser = App\Models\User::first();
if ($firstUser) {
    $tenant = App\Models\Tenant::firstOrCreate(
        ['name' => 'Algrow Capital'],
        ['owner_id' => $firstUser->id]
    );
    App\Models\User::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
    App\Models\MitraAccount::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
    App\Models\Investor::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
    App\Models\Ipo::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
    App\Models\MitraGroup::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
    echo "Successfully updated all records to Algrow Capital tenant.\n";
} else {
    echo "No users found.\n";
}
