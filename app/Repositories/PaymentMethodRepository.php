<?php

namespace App\Repositories;

use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use App\Repositories\BaseRepository;

class PaymentMethodRepository extends BaseRepository
{
    /**
     *  Show the payment method filters
     *
     *  @return array
     */
    public function showPaymentMethodFilters()
    {
        $filters = collect(PaymentMethod::FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Available On Perfect Pay',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Available On Ussd',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) {

            //  Query the payment methods by the filter
            $total = $this->queryPaymentMethodsByFilter($filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->model->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the transaction
     *
     *  @return TransactionRepository
     */
    public function showPaymentMethods()
    {
        $filter = $this->model->separateWordsThenLowercase(request()->input('filter'));
        $paymentMethods = $this->queryPaymentMethodsByFilter($filter)->orderBy('position', 'asc');
        return $this->setModel($paymentMethods)->get();
    }

    /**
     *  Query the payment methods by the specified filter
     *
     *  @param string $filter - The filter to query the payment methods
     *  @return App\Models\PaymentMethod
     */
    public function queryPaymentMethodsByFilter($filter)
    {
        //  Normalize the filter
        $filter = $this->model->separateWordsThenLowercase($filter);

        //  Get the payment method eloquent instance
        $paymentMethods = $this->model;

        if($filter == 'available on perfect pay') {

            $paymentMethods = $paymentMethods->availableOnPerfectPay();

        }else if($filter == 'available on stores') {

            $paymentMethods = $paymentMethods->availableOnStores();

        }else if($filter == 'available on ussd') {

            $paymentMethods = $paymentMethods->availableOnUssd();

        }else if($filter == 'active') {

            $paymentMethods = $paymentMethods->active();

        }else if($filter == 'inactive') {

            $paymentMethods = $paymentMethods->inactive();

        }

        //  Return the payment methods query
        return $paymentMethods;
    }
}
