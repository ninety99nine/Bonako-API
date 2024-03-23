<?php

namespace App\Repositories;

use App\Enums\CacheName;
use App\Enums\PaymentMethodAvailability;
use App\Enums\PaymentMethodFilter;
use App\Helpers\CacheManager;
use App\Models\PaymentMethod;
use App\Repositories\BaseRepository;
use App\Traits\Base\BaseTrait;

class PaymentMethodRepository extends BaseRepository
{
    use BaseTrait;

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
     *  @return PaymentMethodRepository
     */
    public function showPaymentMethods()
    {
        $page = $this->getCurrentPage();
        $usage = $this->model->separateWordsThenLowercase(request()->input('usage'));
        $filter = $this->model->separateWordsThenLowercase(request()->input('filter'));
        $perPage = $this->model->separateWordsThenLowercase(request()->input('per_page'));
        $cacheManager = (new CacheManager(CacheName::PAYMENT_METHODS))->append($usage, true)->append($filter, true)->append($perPage)->append($page);

        return $cacheManager->remember(now()->addWeek(), function() use ($filter) {

            $paymentMethods = $this->queryPaymentMethodsByFilter($filter)->orderBy('position', 'asc');
            return $this->setModel($paymentMethods)->get();

        });
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

        //  Normalize the usage
        $usage = $this->model->separateWordsThenLowercase(request()->input('usage'));

        //  Get the payment method eloquent instance
        $paymentMethods = $this->model;

        if($usage == strtolower(PaymentMethodAvailability::AvailableOnPerfectPay->value)) {

            $paymentMethods = $paymentMethods->availableOnPerfectPay();

        }else if($usage == strtolower(PaymentMethodAvailability::AvailableInStores->value)) {

            $paymentMethods = $paymentMethods->availableInStores();

        }else if($usage == strtolower(PaymentMethodAvailability::AvailableOnUssd->value)) {

            $paymentMethods = $paymentMethods->availableOnUssd();

        }

        if($filter == strtolower(PaymentMethodFilter::Active->value)) {

            $paymentMethods = $paymentMethods->active();

        }else if($filter == strtolower(PaymentMethodFilter::Inactive->value)) {

            $paymentMethods = $paymentMethods->inactive();

        }

        //  Return the payment methods query
        return $paymentMethods;
    }
}
