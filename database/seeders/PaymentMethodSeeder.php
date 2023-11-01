<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class PaymentMethodSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Foreach payment method
        foreach($this->getPaymentMethods() as $paymentMethod) {

            //  Create payment method
            PaymentMethod::create($paymentMethod);

        }
    }

    /**
     *  Return the payment methods
     *
     *  @return array
     */
    public function getPaymentMethods() {
        return [
            [
                'name' => 'Airtime',
                'method' => 'Airtime',
                'category' => 'Airtime',
                'description' => 'Payment using airtime',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 0,
                'available_on_ussd' => 1,
                'position' => 1,
                'active' => 1,
            ],
            [
                'name' => 'Cash',
                'method' => 'Cash',
                'category' => 'Cash',
                'description' => 'Payment using cash',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 1,
                'available_on_ussd' => 0,
                'position' => 2,
                'active' => 1,
            ],
            [
                'name' => 'Ewallet',
                'method' => 'Ewallet',
                'category' => 'Mobile Wallet',
                'description' => 'Payment using Ewallet',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 1,
                'available_on_ussd' => 0,
                'position' => 3,
                'active' => 1,
            ],
            [
                'name' => 'Pay2Cell',
                'method' => 'Pay2Cell',
                'category' => 'Mobile Wallet',
                'description' => 'Payment using Pay2Cell',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 1,
                'available_on_ussd' => 0,
                'position' => 4,
                'active' => 1,
            ],
            [
                'name' => 'Orange Money',
                'method' => 'Orange Money',
                'category' => 'Mobile Wallet',
                'description' => 'Payment using Orange Money',
                'available_on_perfect_pay' => 1,
                'available_on_stores' => 1,
                'available_on_ussd' => 1,
                'position' => 5,
                'active' => 1,
            ],
            [
                'name' => 'MyZaka',
                'method' => 'MyZaka',
                'category' => 'Mobile Wallet',
                'description' => 'Payment using MyZaka',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 1,
                'available_on_ussd' => 1,
                'position' => 6,
                'active' => 1,
            ],
            [
                'name' => 'BTC Smega',
                'method' => 'BTC Smega',
                'category' => 'Mobile Wallet',
                'description' => 'Payment using Smega',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 1,
                'available_on_ussd' => 1,
                'position' => 7,
                'active' => 1,
            ],
            [
                'name' => 'Unayo',
                'method' => 'Unayo',
                'category' => 'Mobile Wallet',
                'description' => 'Payment using Unayo',
                'available_on_perfect_pay' => 0,
                'available_on_stores' => 1,
                'available_on_ussd' => 1,
                'position' => 8,
                'active' => 1,
            ],
            [
                'name' => 'Credit/Debit Card',
                'method' => 'DPO Card',
                'category' => 'Card',
                'description' => 'Payment using credit or debit card',
                'available_on_perfect_pay' => 1,
                'available_on_stores' => 1,
                'available_on_ussd' => 0,
                'position' => 9,
                'active' => 1,
            ],
        ];
    }
}
