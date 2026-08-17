<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = \App\Models\Tenant::with(['owner', 'subscriptions' => function($q) {
            $q->where('status', 'active')->where('end_date', '>', now())->with('plan');
        }])->get();
        
        $plans = \App\Models\SubscriptionPlan::all();

        return view('tenants.index', compact('tenants', 'plans'));
    }

    public function activateSubscription(Request $request, \App\Models\Tenant $tenant)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'custom_end_date' => 'nullable|date'
        ]);

        $plan = \App\Models\SubscriptionPlan::findOrFail($request->plan_id);
        $oldSub = $tenant->subscriptions()->where('status', 'active')->first();
        $isSamePlan = $oldSub && $oldSub->subscription_plan_id == $plan->id;

        $endDate = now()->addMonths($plan->duration_months);

        if ($request->custom_end_date) {
            $parsedCustomDate = \Carbon\Carbon::parse($request->custom_end_date);
            
            // Jika tanggal diubah manual, gunakan tanggal baru tersebut.
            if ($oldSub && $parsedCustomDate->format('Y-m-d') !== $oldSub->end_date->format('Y-m-d')) {
                $endDate = $parsedCustomDate->endOfDay();
            } 
            // Jika tanggal tidak diubah DAN paket tidak diubah, pertahankan tanggal kadaluarsa yang lama.
            elseif ($isSamePlan && $oldSub) {
                $endDate = $oldSub->end_date;
            }
            // Jika paket diubah (tapi tanggal tetap sama), ia akan otomatis menggunakan $endDate bawaan (now + durasi).
        }

        // Deactivate old active subscriptions
        $tenant->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        // Create new active subscription
        $tenant->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'start_date' => $oldSub ? $oldSub->start_date : now(),
            'end_date' => $endDate,
        ]);

        return back()->with('success', 'Langganan berhasil diperbarui.');
    }

    public function deactivateSubscription(Request $request, \App\Models\Tenant $tenant)
    {
        $tenant->subscriptions()->where('status', 'active')->update(['status' => 'expired']);
        return back()->with('success', 'Langganan perusahaan berhasil dinonaktifkan.');
    }
}
