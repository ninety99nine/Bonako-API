<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\Controller;
use App\Models\Transaction;
use App\Services\DirectPayOnline\DirectPayOnlineService;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function paymentSuccess(Transaction $transaction, Request $request)
    {
        // Verify the transaction
        $updatedTransaction = DirectPayOnlineService::verifyPayment($transaction, $request);

        // Show the payment page
        return view('payment-success', ['transaction' => $updatedTransaction]);
    }

    public function perfectPayAdvertisement()
    {
        return view('perfect-pay-advertisement');
    }
}
