<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $plans = \App\Models\SubscriptionPlan::where('name', '!=', 'Paket Lifetime')->get();
        $user = auth()->user();
        $hasActiveSubscription = false;

        if ($user && $user->tenant_id) {
            $hasActiveSubscription = \App\Models\Subscription::where('tenant_id', $user->tenant_id)
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->exists();

            if ($hasActiveSubscription) {
                return redirect()->route('dashboard')->with('success', 'Langganan Anda telah aktif!');
            }
        }

        // Developer doesn't need to see pricing if they just want to use the app, but we can let them see it if they want.
        // Actually, if the user explicitly typed /pricing, let's just redirect them if they have a bypass role.
        if ($user && $user->role === 'developer') {
            return redirect()->route('dashboard');
        }
        
        // Users (Mitra/Joki) do not pay for subscriptions, so redirect them away.
        if ($user && $user->role === 'user') {
            return redirect()->route('dashboard');
        }

        return view('pricing.index', compact('plans', 'hasActiveSubscription'));
    }

    public function purchase(Request $request, $id)
    {
        $plan = \App\Models\SubscriptionPlan::findOrFail($id);
        $user = auth()->user();

        if (!$user->tenant_id) {
            return back()->with('error', 'Anda tidak memiliki Tenant/Grup yang valid.');
        }

        // Dummy logic for Manual Payment (WhatsApp redirect)
        $message = "Halo Algrow Capital, saya " . $user->name . " dari Grup " . $user->tenant->name . " ingin berlangganan " . $plan->name . " seharga Rp " . number_format($plan->price, 0, ',', '.') . ". Mohon info nomor rekeningnya.";
        
        $whatsappUrl = "https://wa.me/6287822421207?text=" . urlencode($message);

        return redirect()->away($whatsappUrl);
    }
}
