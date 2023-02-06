<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessSubscription extends Model
{
    //

    public function subscription()
    {
        return $this->belongsTo('App\Subscription', 'subscription_id', 'id');
    }

    public function subscriptionCancel()
    {
        return $this->belongsTo('App\BusinessSubscriptionCancel', 'id', 'business_subscription_id');
    }
}
