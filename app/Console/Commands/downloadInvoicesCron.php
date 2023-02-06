<?php

namespace App\Console\Commands;

use App\BusinessInvoice;
use App\Profile;
use Illuminate\Console\Command;

class downloadInvoicesCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download invoice from psi gate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getAccountId($profile)
    {
        return "I00000" . $profile->id;
    }

    private function authString()
    {
        return '<CID>' . \config('env.PSI_TEST_CID') . '</CID>
        <UserID>' . \config('env.PSI_TEST_USERID') . '</UserID>
        <Password>' . \config('env.PSI_TEST_PASSWORD') . '</Password>';
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

    private function getInvoicesFromPsiViaAccoundId($accountId)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?><Request>
        ' . $this->authString() . '
            <Action>INV00</Action>
            <Condition>
                <AccountID>' . $accountId . '</AccountID>
            </Condition>
        </Request>';
        $xmlResponse = $this->curlMe($xmlString);
        $endResponse = simplexml_load_string($xmlResponse);
        // dd($endResponse);
        return $endResponse;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $activeProfiles = Profile::where('is_subscription_active', 'Yes')->get();
        foreach ($activeProfiles as $profile) {
            $profileAccountId = $this->getAccountId($profile);
            if ($profileAccountId == "I000001") {
                $invoices = $this->getInvoicesFromPsiViaAccoundId($profileAccountId);
                // dd($invoices);
                foreach ($invoices as $inv) {
                    // $bi = new BusinessInvoice();
                    // $bi->transaction_log = json_encode($inv);
                }
            }
        }
    }
}
