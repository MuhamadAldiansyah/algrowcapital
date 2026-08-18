<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('login');
        }

        // Developer is the owner of the SaaS, they don't need a subscription
        if ($user->role === 'developer') {
            return $next($request);
        }

        // Allow superadmin or users without tenant if they are special, but for now we enforce tenant subscription
        if ($user->tenant_id) {
            $hasActiveSubscription = \App\Models\Subscription::where('tenant_id', $user->tenant_id)
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->exists();

            if (!$hasActiveSubscription) {
                // If user is just a Mitra (role user), they don't pay. We allow them to use the system or at least not be blocked by this middleware.
                if (in_array($user->role, ['user', 'admin', 'investor'])) {
                    // Let them proceed to dashboard without being blocked by subscription
                    return $next($request);
                }
                
                // If they are not accessing the pricing or subscription routes, redirect them
                if (!$request->is('pricing*') && !$request->is('subscription*') && !$request->is('logout')) {
                    return redirect()->route('pricing.index')->with('warning', 'Langganan Anda telah berakhir atau belum aktif. Silakan pilih paket langganan untuk melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
