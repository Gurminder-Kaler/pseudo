<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{

    public function checkIfCardIsDefault($serialNo)
    {
        // dd($serialNo, $this->id);
        // dd($this->id, $serialNo);
        $pm = PaymentMethod::where('profile_id', $this->id)->where('serial_no', $serialNo)->first();
        // dd($pm);
        if ($pm == null) {
            return false;
        }
        if ($pm->is_default == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function checkIfCardIsBackUpPayment($serialNo)
    {
        $pm = PaymentMethod::where('profile_id', $this->id)->where('serial_no', $serialNo)->first();
        if ($pm == null) {
            return false;
        }
        if ($pm->is_backup_payment == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function currentSubscription()
    {
        return $this->belongsTo('App\Subscription', 'current_business_subscription_id', 'id');
    }

    public function getAllBusinessSubscriptions()
    {
        return $this->belongsTo('App\BusinessSubscription', 'business_profile_id', 'id');
    }

    public function lastSub()
    {
        $profile = auth()->user()->profile;
        return BusinessSubscription::where('business_profile_id', $profile->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function currentSubDetail()
    {
        $bs =  BusinessSubscription::where('id', $this->current_business_subscription_id)->first();
        return Subscription::where('id', $bs->subscription_id)->first();
    }

    public function checkIfCancellationIsSubmitted($subId)
    {
        // $profile = auth()->user()->profile;
        $cancel = BusinessSubscriptionCancel::where('business_subscription_id', $subId)->first();

        if ($cancel) {
            return true;
        } else {
            return false;
        }
    }

    public function checkIfThisSubscriptionIsCurrentSubscription($subId)
    {
        $sub =  BusinessSubscription::where('id', $this->current_business_subscription_id)->first();
        if ($subId == $sub->subscription_id) {
            return true;
        } else {
            return false;
        }
    }

    public function calculateLeftOutCreditFromLastSub()
    {
        $lastSub = $this->lastSub();
        if ($lastSub->subscription_id == 1) {
            return 0;
        } else {
            $decoded = json_decode($lastSub->transaction_log);
            $lastSubActivateDate = \Carbon\Carbon::parse($lastSub->activate_datetime); // 1day
            $lastSubExpireDate = \Carbon\Carbon::parse($lastSub->activate_datetime)->addMonth(1); //30 days
            $lastSubscriptionTotal = $decoded->Charge->ItemInfo->SubTotal; //$100
            $now = \Carbon\Carbon::now();
            $differenceInTodayAndEndOfSub = \Carbon\Carbon::parse($now)->diffInDays($lastSubExpireDate); //21 days
            $differenceInSubDays = \Carbon\Carbon::parse($lastSubActivateDate)->diffInDays($lastSubExpireDate); //9 days
            $payForEachDay = $lastSubscriptionTotal / $differenceInSubDays;
            $payForThesedays = $differenceInSubDays - $differenceInTodayAndEndOfSub;
            $k = $payForEachDay * $payForThesedays;
            $toBeGivenBack = $lastSubscriptionTotal - $k;
            return number_format($toBeGivenBack, 2);
        }
    }
}
