<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Sms\SmsService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test-sms', function () {

    try {

        /*
        $friends = [(new User)->where('id', 1)->first(), (new User)->where('id', 1)->first(), (new User)->where('id', 1)->first()];
        $friend = (new User)->where('id', 2)->first();
        $customer = (new User)->where('id', 1)->first();
        $order = (new Order)->where('order_for', 'Me And Friends')->first();
        $store = (new Store)->first();
        $transaction = (new Transaction)->whereNotNull('dpo_payment_url')->first();

        $content = $order->craftNewOrderSmsMessageForFriend($store, $customer, $friend, $friends);
        $recipientMobileNumber = $customer->mobile_number->withExtension;
        */

        return SmsService::sendOrangeSms('This is a test sms', '26772882239', null, null, null);

        return 'Sent!';

    } catch (\Throwable $th) {

        report($th);

    }

});

//  Remove this when running on production
Route::get('/php-info', function () {
    return phpinfo();
});

//  Redirect to terms and conditions
Route::redirect('/terms', 'https://forms.fillout.com/t/hNffdJnchyus', 301)->name('terms.and.conditions.show');

Route::controller(WebController::class)->group(function(){
    Route::get('/{transaction}/payment-success', 'paymentSuccess')->name('payment.success.page');
    Route::get('/perfect-pay-advertisement', 'perfectPayAdvertisement')->name('perfect.pay.advertisement.page');
});

/*
Route::get('/create', [ExampleController::class, 'form'])->name('form-show');
Route::post('/create', [ExampleController::class, 'store'])->name('form-create');
*/
