<?php

namespace App\Http\Controllers;

use App\BusinessSubscription;
use App\BusinessSubscriptionCancel;
use App\PaymentMethod;
use App\Profile;
use App\Subscription;
use App\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    private function getAccountId($profile)
    {
        return "I00000" . $profile->id;
    }

    private function curlMe($xmlData)
    {
        // dd(\config('env.PSI_TEST_URL'));
        $ch = curl_init('https://accountsstaging.psigate.com/xml');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('SOAPAction', 'MySoapAction'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        // return simplexml_load_string($response);

    }

    public function thankYouPage($slug)
    {
        return view('thankYouPage', compact('slug'));
    }

    private function authString()
    {

        $k = '<CID>' . \config('env.PSI_TEST_CID') . '</CID>
        <UserID>' . \config('env.PSI_TEST_USERID') . '</UserID>
        <Password>' . \config('env.PSI_TEST_PASSWORD') . '</Password> ';
        return $k;
    }


    private function addAccountOnPsi($profile, $user)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?><Request>
        ' . $this->authString() . '
            <Action>AMA01</Action>
            <Account>
                <AccountID>' . $profile->account_id . '</AccountID>
                <Name>' . $user->name . '</Name>
                <Company>' . $user->name . '</Company>
                <Address1>145 King St.</Address1>
                <Address2>2300</Address2>
                <City>Toronto</City>
                <Province>Ontario</Province>
                <Postalcode>M5H 1J8</Postalcode>
                <Country>Canada</Country>
                <Phone>1-905-123-4567</Phone>
                <Fax>1-905-123-4568</Fax>
                <Email>' . $user->email . '</Email>
                <Comments>No Comment Today</Comments>
                <CardInfo>
                    <CardHolder>' . $user->name . '</CardHolder> 
                    <CardNumber>4111111111111111</CardNumber>
                    <CardExpMonth>08</CardExpMonth>
                    <CardExpYear>24</CardExpYear>
                </CardInfo>
            </Account>
        </Request>';
        $k = $this->curlMe($xmlString);
    }

    public function createATestUser()
    {
        // $this->truncate();
        $name = 'flint10';
        $user = new User();
        $user->name = $name;
        $user->email = $name . '@yopmail.com';
        $user->password = bcrypt(12345678);
        $user->save();
        $slug = "basic-retail-monthly";
        $sub = Subscription::where('slug', $slug)->first();

        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->is_subscription_active = "No";
        $profile->save();

        $businessSubscription = new BusinessSubscription();
        $businessSubscription->business_profile_id = $profile->id;
        $businessSubscription->status = "ongoing";
        $businessSubscription->subscription_id = $sub->id;
        $businessSubscription->activate_datetime = now()->format('M-d-Y');
        $businessSubscription->expire_datetime = null;
        $businessSubscription->transaction_log = "Subscribed to Basic";
        $businessSubscription->transaction_message = "Subscribed to Basic";
        $businessSubscription->save();

        $p = Profile::where('id', $profile->id)->first();
        $p->account_id = $this->getAccountId($profile);
        $p->current_business_subscription_id = $businessSubscription->id;
        $p->update();
        $this->addAccountOnPsi($p, $user);

        $pm = new PaymentMethod();
        $pm->account_id = $this->getAccountId($profile);
        $pm->profile_id = $profile->id;
        $pm->serial_no = 1;
        $pm->is_default = 1;
        $pm->is_backup_payment = 0;
        $pm->transaction_log = 'CARD added custom from homecontroller';
        $pm->save();

        return "true";
    }

    public function truncate()
    {
        User::truncate();
        BusinessSubscriptionCancel::truncate();
        BusinessSubscription::truncate();
        PaymentMethod::truncate();
        Profile::truncate();
    }
}
