<?php

namespace App\Http\Controllers;

use App\BusinessSubscription;
use App\BusinessSubscriptionCancel;
use App\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private function authString()
    {
        return '<CID>' . \config('env.PSI_TEST_CID') . '</CID>
        <UserID>' . \config('env.PSI_TEST_USERID') . '</UserID>
        <Password>' . \config('env.PSI_TEST_PASSWORD') . '</Password>';
    }

    private function getAccountId($profile)
    {
        return "I00000" . $profile->id;
    }

    private function curlMe($xmlData)
    {
        // dd(\config('env.PSI_TEST_URL'));
        $ch = curl_init('https://staging.psigate.com:8645/Messenger/AMMessenger');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('SOAPAction', 'MySoapAction'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function getAllCardInfoFromPsiViaAccountId($accountId)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?><Request>
        ' . $this->authString() . '
            <Action>AMA05</Action>
            <Condition>
                <AccountID>' . $accountId . '</AccountID>
            </Condition>
        </Request>';
        $xmlResponse = $this->curlMe($xmlString);
        $endResponse = simplexml_load_string($xmlResponse);
        return $endResponse;
    }

    public function subscriptionHistoryScreen()
    {
        $profile = auth()->user()->profile;
        $accountId = $this->getAccountId($profile);
        $cardInfoFromPsi = $this->getAllCardInfoFromPsiViaAccountId($accountId);
        $subscriptions = BusinessSubscription::where('business_profile_id', $profile->id)->orderBy('id', 'desc')->get();
        return view('subscriptionHistory', compact('cardInfoFromPsi', 'profile', 'subscriptions'));
    }

    public function subscriptionScreen()
    {
        $accountId = $this->getAccountId(auth()->user()->profile);
        $cardInfoFromPsi = $this->getAllCardInfoFromPsiViaAccountId($accountId);
        $profile = auth()->user()->profile;
        $subscriptions = Subscription::where('subscription_type', 'retail')->get();
        return view('subscriptions', compact('cardInfoFromPsi', 'profile', 'subscriptions'));
    }

    public function cancelCurrentSubscription(Request $request)
    {
        $profile = auth()->user()->profile;
        $b = new BusinessSubscriptionCancel();
        $b->business_profile_id = $profile->id;
        $b->business_subscription_id = $profile->current_business_subscription_id;
        $b->save();
        return redirect()->back();
    }

    public function paymentScreen($slug)
    {
        $profile = auth()->user()->profile;
        $accountId = $this->getAccountId($profile);
        $cardInfoFromPsi = $this->getAllCardInfoFromPsiViaAccountId($accountId);
        $subscription = Subscription::where('slug', $slug)->first();
        return view('payment', compact('slug', 'cardInfoFromPsi', 'profile', 'subscription'));
    }

    private function createAChargeOnPsi($request, $slug, $profile)
    {
        // dd($request->all());
        $sub = Subscription::where('slug', $slug)->first();
        $billingCycle = $sub->billing_cycle; // monthly or half-yearly
        $amt = 0;
        $tax = 0;
        $startTime = now()->format('Y-m-d');
        if ($billingCycle == "half-yearly") {
            $amt = number_format($sub->amount / 6, 2);
            $endTime = now()->addMonths(6)->format('Y-m-d');
        } else {
            $amt = number_format($sub->amount, 2);
            $endTime = now()->addMonths(1)->format('Y-m-d');
        }
        $tax = number_format(0.13 * $amt, 2);
        $triggerDate = now()->format('d');
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?><Request>
            ' . $this->authString() . '
            <Action>RBC99</Action>
            <Charge>
                <RBName>Subscription Purchase ' . $slug . '</RBName>
                <StoreID>teststore</StoreID>
                <AccountID>' . $this->getAccountId($profile) . '</AccountID>
                <SerialNo>' . (int)$request->selectedSerialNo . '</SerialNo>
                <Interval>M</Interval>
                <RBTrigger>' . $triggerDate . '</RBTrigger>
                <StartTime>' . $startTime . '</StartTime>
                <EndTime>' . $endTime . '</EndTime>
                <ItemInfo>
                    <ProductID>1</ProductID>
                    <Description>Subscription Purchase</Description>
                    <Quantity>1</Quantity>
                    <Price>' . $amt . '</Price>
                    <Tax1>' . $tax . '</Tax1>
                </ItemInfo>
            </Charge>
            </Request>';
        $xmlResponse = $this->curlMe($xmlString);
        $endResponse = simplexml_load_string($xmlResponse);
        return [$endResponse, $startTime, $endTime];
    }

    private function createABusinessSubRowAndUpdateCurrentSubscription(
        $profile,
        $slug,
        $result
    ) {
        $sub = Subscription::where('slug', $slug)->first();
        $chargeResponseFromPsi = $result[0];
        $startTime = $result[1];
        $endTime = $result[2];

        $lastSub = $profile->lastSub();
        $lastSub->status = "completed";
        $lastSub->update();
        if ($lastSub->subscription->slug !== "basic-retail-monthly") {
            $this->cancelChargeOnPsiViaBusinessSubscription($lastSub);
        }

        $b = new BusinessSubscription();
        $b->business_profile_id = $profile->id;
        $b->status = "ongoing";
        $b->subscription_id = $sub->id;
        $b->activate_datetime = $startTime;
        $b->expire_datetime = $endTime;
        $b->transaction_log = json_encode($chargeResponseFromPsi);
        $b->transaction_message = $chargeResponseFromPsi->ReturnMessage;
        $b->save();

        $profile->current_business_subscription_id = $b->id;
        $profile->is_subscription_active = "Yes";
        $profile->update();
    }

    // cancel old charge on PSi

    private function cancelChargeOnPsiViaBusinessSubscription($lastSub)
    {
        $charge = json_decode($lastSub->transaction_log)->Charge;
        $RBCID = $charge->RBCID;

        $xmlString = '<?xml version="1.0" encoding="UTF-8"?><Request>
            ' . $this->authString() . '
            <Action>RBC09</Action>
            <Condition>
                <RBCID>' . $RBCID . '</RBCID>
            </Condition> 
            </Request>';
        $xmlResponse = $this->curlMe($xmlString);
        $endResponse = simplexml_load_string($xmlResponse);
        $c = new BusinessSubscriptionCancel();
        $c->business_profile_id = $lastSub->business_profile_id;
        $c->business_subscription_id = $lastSub->id;
        $c->transaction_log = json_encode($endResponse);
        $c->save();
    }

    public function doPayment(Request $request)
    {
        $slug = $request->slug;
        $profile = auth()->user()->profile;
        //create A Charge On Psi
        $result = $this->createAChargeOnPsi($request, $slug, $profile);
        $firstResult = $result[0];
        if ($firstResult) {
            if ($firstResult->ReturnMessage == "Register Recurring Charges completed successfully.") {
                $this->createABusinessSubRowAndUpdateCurrentSubscription($profile, $slug, $result);
            } else {
                return redirect()->back();
            }
        }
        return redirect('/thankYouPage/' . $slug . '');
    }
}
