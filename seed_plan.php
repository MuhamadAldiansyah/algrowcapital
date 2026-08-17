<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plan = App\Models\SubscriptionPlan::firstOrCreate(
    ['name' => 'Paket Pro (6 Bulan)'],
    ['price' => 150000, 'duration_months' => 6]
);

// Give the current tenant (Algrow Capital) an active subscription so they can use the app
$tenant = App\Models\Tenant::where('name', 'Algrow Capital')->first();
if ($tenant) {
    App\Models\Subscription::firstOrCreate(
        ['tenant_id' => $tenant->id],
        [
            'subscription_plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'active'
        ]
    );
    echo "Successfully seeded Subscription Plan and attached to Algrow Capital.\n";
} else {
    echo "Tenant not found.\n";
}
