<?php

namespace App\Repositories;

use App\Exceptions\TransactionCannotBeUnCancelledException;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use App\Traits\Base\BaseTrait;

class TransactionRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
    }

    /**
     *  Return the ShortcodeRepository instance
     *
     *  @return ShortcodeRepository
     */
    public function shortcodeRepository()
    {
        return resolve(ShortcodeRepository::class);
    }

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return TransactionRepository
     */
    public function eagerLoadTransactionRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        //  Check if we want to eager load the paying user on this transaction
        if( request()->input('with_paying_user') ) {

            //  Additionally we can eager load the paying user on this transaction
            array_push($relationships, 'payingUser');

        }

        //  Check if we want to eager load the requesting user on this transaction
        if( request()->input('with_requesting_user') ) {

            //  Additionally we can eager load the requesting user on this transaction
            array_push($relationships, 'requestingUser');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Transaction)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        return $this->setModel($model);
    }

    /**
     *  Show the order transaction filters
     *
     *  @param Order $order
     *  @return array
     */
    public function showOrderTransactionFilters(Order $order)
    {
        $filters = collect(Transaction::FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Active',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Inactive',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($order) {

            //  Query the transactions by the filter
            $total = $this->queryOrderTransactionsByFilter($order, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the transaction
     *
     *  @param Order $order
     *  @return TransactionRepository
     */
    public function showOrderTransactions(Order $order)
    {
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));
        $transactions = $this->queryOrderTransactionsByFilter($order, $filter)->orderBy('updated_at', 'desc');
        $transactions = $this->eagerLoadTransactionRelationships($transactions);
        return $this->setModel($transactions->model);
    }

    /**
     *  Query the order transactions by the specified filter
     *
     *  @param Order $order
     *  @param string $filter - The filter to query the order transactions e.g Paid, Pending Payment, e.t.c
     *  @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function queryOrderTransactionsByFilter(Order $order, $filter)
    {
        //  Get the order transaction
        $transactions = $order->transactions();

        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        //  Check if this filter is a type of transaction payment status
        if(collect(array_map('strtolower', Transaction::STATUSES))->contains($filter)) {

            //  Filter by transaction payment status
            $transactions = $transactions->where('payment_status', $filter);

        }

        //  Return the transactions query
        return $transactions;
    }

    /**
     *  Cancel the transaction
     *
     *  @return TransactionRepository
     */
    public function cancel(Request $request)
    {
        /**
         *  @var Transaction $transaction
         */
        $transaction = $this->model;

        /**
         *  Determine if this is an order transaction
         *
         *  @var Order $order
         */
        $isAnOrderTransaction = ($order = $transaction->owner) instanceof Order;

        //  If this transaction belongs to an order
        if( $isAnOrderTransaction ) {

            //  Set the order as the repository model
            $orderRepository = $this->orderRepository()->setModel($order);

            //  Avoid transaction modifications on a cancelled order
            $orderRepository->avoidInitiatingTransactionsOnCancelledOrder('Transaction changes are restricted while the order is cancelled');

        }

        //  Cancel the transaction
        parent::cancel($request);

        //  Check if this is an order transaction
        if( $isAnOrderTransaction ) {

            //  Update the order amount balance after cancelling this transaction
            $orderRepository->updateOrderAmountBalance();

        }

        //  Return this transaction repository
        return $this;
    }

    /**
     *  Uncancel the transaction
     *
     *  @return TransactionRepository
     */
    public function uncancel()
    {
        /**
         *  @var Transaction $transaction
         */
        $transaction = $this->model;

        /**
         *  Determine if this is an order transaction
         *
         *  @var Order $order
         */
        $isAnOrderTransaction = ($order = $transaction->owner) instanceof Order;

        //  If this transaction belongs to an order
        if( $isAnOrderTransaction ) {

            //  Set the order as the repository model
            $orderRepository = $this->orderRepository()->setModel($order);

            //  Avoid transaction modifications on a cancelled order
            $orderRepository->avoidInitiatingTransactionsOnCancelledOrder('Transaction changes are restricted while the order is cancelled');

            //  If the transaction amount is more than the amount outstanding
            if( $transaction->amount->amount > ($outstandingAmountRemaining = $order->amount_outstanding->amount - $order->amount_pending->amount)  ) {

                //  Convert to money format
                $outstandingAmountRemaining = $transaction->convertToMoneyFormat($outstandingAmountRemaining, $order->currency);

                //  Throw an Exception - Amount exceeded
                throw new TransactionCannotBeUnCancelledException(
                    'The transaction cannot be uncancelled because the transaction amount '.$transaction->amount->amountWithCurrency.' is more than the remaining payable amount '.$outstandingAmountRemaining->amountWithCurrency.' for this order'
                );

            }

            //  If this transaction is pending payment
            if( $transaction->isPendingPayment() ) {

                //  Get the transaction payer's User ID
                $payerUserId = $transaction->payer_user_id;

                //  Avoid requesting multiple pending payment for the same payer
                $orderRepository->avoidRequestingMultiplePendingPaymentsPerUser($payerUserId);

            }

        }

        //  Uncancel the transaction
        parent::uncancel();

        //  If this transaction belongs to an order
        if( $isAnOrderTransaction ) {

            //  Update the order amount balance after uncancelling this transaction
            $orderRepository->updateOrderAmountBalance();

        }

        //  Return this transaction repository
        return $this;
    }

    /**
     *  Create a payment shortcode for this transaction
     *
     *  This will allow the user to dial the shortcode pay via USSD
     *
     *  @return TransactionRepository
     */
    public function generatePaymentShortcode()
    {
        /**
         *  @var Transaction $transaction
         */
        $transaction = $this->model;

        //  Get the User ID that this shortcode is reserved for
        $reservedForUserId = $transaction->payer_user_id;

        //  Request a payment shortcode for this pending transaction
        $this->shortcodeRepository()->generatePaymentShortcode($transaction, $reservedForUserId);

        //  Set the transaction as the repository model with the active payment shortcode
        $this->setModel(

            //  Load the active payment shortcode on this transaction
            $transaction->load('activePaymentShortcode')

        );

        return $this;
    }

    /**
     *  Remove a payment shortcode from this transaction
     *
     *  @return TransactionRepository
     */
    public function expirePaymentShortcode()
    {
        /**
         *  @var Transaction $transaction
         */
        $transaction = $this->model;

        //  Get the transaction active payment shortcode
        $activePaymentShortcode = $transaction->activePaymentShortcode;

        //  If the transaction has an active payment shortcode
        if( $activePaymentShortcode ) {

            /**
             *  Expire the transaction active payment shortcode.
             *  This will detach the shortcode since we
             *  only query non-expired shortcodes as
             *  payment shortcodes.
             */
            $this->shortcodeRepository()->setModel($activePaymentShortcode)->expireShortcode();

        }

        return $this;
    }

}
