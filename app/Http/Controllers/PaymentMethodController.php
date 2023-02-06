<?php

namespace App\Http\Controllers;

use App\PaymentMethod;
use Faker\Provider\ar_SA\Payment;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    private function authString()
    {
        return '<CID>' . \config('env.PSI_TEST_CID') . '</CID>
        <UserID>' . \config('env.PSI_TEST_USERID') . '</UserID>
        <Password>' . \config('env.PSI_TEST_PASSWORD') . '</Password>';
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
        return $output;
        // dd($output);
    }

    private function savePmOnDb($request, $profile, $psiResponse)
    {
        $pm = new PaymentMethod();
        $pm->account_id = $this->getAccountId($profile);
        $pm->profile_id = $profile->id;
        $pm->serial_no = $psiResponse->Account->CardInfo->SerialNo;
        $pm->is_default = 1;
        if ($request->is_backup_payment) {
            $pm->is_backup_payment = 1;
        } else {
            $pm->is_backup_payment = 0;
        }
        $pm->transaction_log = json_encode($psiResponse);
        $pm->save();
    }

    public function paymentMethodScreen()
    {
        $authUser = auth()->user();
        $profile = $authUser->profile;
        $cardInfoFromPsi = $this->getAllCardInfoFromPsiViaAccountId($this->getAccountId($profile));
        return view('paymentMethod', compact('cardInfoFromPsi', 'profile'));
    }

    public function addCard(Request $request)
    {
        $authUser = auth()->user();
        $profile = $authUser->profile;
        // <StoreID>' . \config('env.PSI_TEST_STOREID') . '</StoreID>
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?><Request>
        ' . $this->authString() . '
        <Action>AMA11</Action>
        <Account>
            <AccountID>' . $this->getAccountId($profile) . '</AccountID>
            <CardInfo>
                <CardHolder>' . $authUser->name . '</CardHolder>
                <CardNumber>' . $request->cardNo . '</CardNumber>
                <CardExpMonth>' . $request->cardExpMonth . '</CardExpMonth>
                <CardExpYear>' . $request->cardExpYear . '</CardExpYear> 
            </CardInfo>
        </Account>
        </Request>';
        $xmlResponse = $this->curlMe($xmlString);
        $endResponse = simplexml_load_string($xmlResponse);
        $this->savePmOnDb($request, $profile, $endResponse);

        return redirect()->back();
    }

    public function makeCardAsDefault(Request $request)
    {
        $pm = PaymentMethod::where('profile_id', auth()->user()->profile->id)->where('serial_no', $request->serial_no)->first();
        // dd($pm);
        if ($pm == null) {
            return false;
        }
        $pm->default = 1;
        $pm->update();
        $pmu = PaymentMethod::where('profile_id', auth()->user()->profile->id)->whereNotIn('id', [$pm->id])->get();

        foreach ($pmu as $p) {
            if ($pm == null) {
                return redirect()->back();
            }
            $p->default = 0;
            $p->update();
        }
        return redirect()->back();
    }

    public function makeCardAsBackUpPayment(Request $request)
    {
        $pm = PaymentMethod::where('profile_id', auth()->user()->profile->id)->where('serial_no', $request->serial_no)->first();
        if ($pm == null) {
            return redirect()->back();
        }
        $pm->is_backup_payment = 1;
        $pm->update();
        return redirect()->back();
    }
}
