<?php

namespace App\Http\Middleware;

use App\Subscription;
use Closure;

class PreventFromGoingToCurrentPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // $authProfile = auth()->user()->profile;
        // $sub = Subscription::where('slug', $request->slug)->first();
        // if ($authProfile->checkIfThisSubscriptionIsCurrentSubscription($sub->id) == false) {
            return $next($request);
        // } else {
        //     return redirect()->back();
        // }
    }
}
