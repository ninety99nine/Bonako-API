<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Base\BaseModel;
use App\Models\SubscriptionPlan;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Subscriptions\SubscriptionCreated;
use App\Exceptions\SubscriptionPlanDurationRequiredException;

class SubscriptionRepository extends BaseRepository
{
    /**
     *  Return the TransactionRepository instance
     *
     *  @return TransactionRepository
     */
    public function transactionRepository()
    {
        return resolve(TransactionRepository::class);
    }

    /**
     *  Create a subsscription
     *
     *  @param Model $model - The resource being subscribed for
     *  @param Request $request - The HTTP request
     *  @param Subscription $latestSubscription - The latest subscription
     *  @return SubscriptionRepository
     */
    public function create($model, Request $request = null, Subscription $latestSubscription = null)
    {
        //  If the subscription exists
        if($latestSubscription) {

            //  Set the start datetime based on the last subscription end datetime
            $startAt = Carbon::parse($latestSubscription->end_at);

        }else{

            //  Set the start datetime as the current datetime
            $startAt = now();

        }

        //  Get the Subscription Plan ID
        $subscriptionPlanId = $request->input('subscription_plan_id');

        //  Get the Subscription Plan
        $subscriptionPlan = SubscriptionPlan::find($subscriptionPlanId);

        //  Calculate the subscription amount
        $amount = $this->calculateSubscriptionAmount($request, $subscriptionPlan);

        //  Calculate the subscription duration
        $duration = $this->calculateSubscriptionDuration($request, $subscriptionPlan);

        //  Calculate the end datetime
        $endAt = $this->calculateSubscriptionEndAt($startAt, $duration, $subscriptionPlan->frequency);

        //  Create the subscription
        $subscriptionRepository = parent::create([
            'end_at' => $endAt,
            'start_at' => $startAt,
            'owner_id' => $model->id,
            'user_id' => auth()->user()->id,
            'owner_type' => $model->getResourceName(),
            'subscription_plan_id' => $subscriptionPlanId
        ]);

        /**
         *  Get the subscription
         *
         *  @var Subscription $subscription
         */
        $subscription = $subscriptionRepository->model;

        /**
         *  Get the subscription owner i.e Store, Order, e.t.c
         *
         *  @var BaseModel $subscriptionFor
         */
        $subscriptionFor = $subscription->owner;

        //  Create a transaction of the given resource and the subscription plan
        $transactionRepository = $this->createTransaction($model, $subscription, $subscriptionPlan, $amount, $request);

        /**
         *  Get the transaction
         *
         *  @var Transaction $transaction
         */
        $transaction = $transactionRepository->model;

        /**
         *  Expand this logic so that we can allow users to subscribe for other users,
         *  therefore we can then send notifications to alert the parties when these
         *  subscriptions are successful. For now we only allow a user to subscribe
         *  for themselves.
         */
        $notifyUsers = auth()->user();
        $subscriptionByUser = auth()->user();
        $subscriptionForUser = auth()->user();

        //  Notify the user that they have subscribed successfully
        //  change to Notification::send() instead of Notification::sendNow() so that this is queued
        Notification::sendNow(
            $notifyUsers,
            new SubscriptionCreated($subscription, $transaction, $subscriptionFor, $subscriptionByUser, $subscriptionForUser)
        );

        //  Return the subscription repository
        return $subscriptionRepository;
    }

    /**
     *  Calculate subsscription amount
     *
     *  @param Request $request
     *  @param SubscriptionPlan $subscriptionPlan
     *  @return int
     */
    public function calculateSubscriptionAmount(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        if(strtolower($subscriptionPlan->type) == 'variable') {
            return $this->calculateSubscriptionDuration($request, $subscriptionPlan) * $subscriptionPlan->price;
        }else{
            return $subscriptionPlan->price;
        }
    }

    /**
     *  Calculate subsscription duration
     *
     *  @param Request $request
     *  @param SubscriptionPlan $subscriptionPlan
     *  @return int
     */
    public function calculateSubscriptionDuration(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        if(strtolower($subscriptionPlan->type) == 'variable') {
            if($request->filled('duration')) {
                return $request->input('duration');
            }else{
                throw new SubscriptionPlanDurationRequiredException;
            }
        }else{
            return $subscriptionPlan->duration;
        }
    }

    /**
     *  Calculate the subscription end datetime based
     *  on the start datetime and subscription plan
     *
     *  @return Carbon
     */
    public function calculateSubscriptionEndAt(Carbon $startAt, $duration, $frequency)
    {
        //  If the subscription plan frequency is measured in days
        if( $frequency == 'day' ) {

            //  End the subscription plan after the given duration of days
            return Carbon::parse($startAt)->addDays( $duration );

        //  If the subscription plan frequency is measured in weeks
        }elseif( $frequency == 'week' ) {

            //  End the subscription plan after the given duration of weeks
            return Carbon::parse($startAt)->addWeeks( $duration );

        //  If the subscription plan frequency is measured in months
        }elseif( $frequency == 'month' ) {

            //  End the subscription plan after the given duration of months
            return Carbon::parse($startAt)->addMonths( $duration );

            //  If the subscription plan frequency is measured in years
        }elseif( $frequency == 'year' ) {

            //  End the subscription plan after the given duration of years
            return Carbon::parse($startAt)->addYears( $duration );

        }else{

            //  Never end the subscription
            return null;

        }
    }

    /**
     *  Mark the subscription as paid
     *
     *  @param Model $model The resource being subscribed for
     *  @param Subscription $subscription The subscription created
     *  @param SubscriptionPlan $subscriptionPlan The subscription plan used
     *  @param Request $request The HTTP request
     *
     *  @return TransactionRepository
     */
    public function createTransaction($model, $subscription, $subscriptionPlan, $amount, $request)
    {
        $status = 'Paid';
        $percentage = 100;
        $verifiedByUserId = null;
        $currency = $subscriptionPlan->currency;
        $paymentMethodId = $request->input('payment_method_id');

        /**
         *  The same user that is paying for this subscription
         *  is the same user that requested this payment.
         *  This is why the $payerUserId and the
         *  $requestedByUserId are the same.
         */
        $payerUserId = auth()->user()->id;
        $requestedByUserId = $payerUserId;

        //  If this is a store subscription
        if($model instanceof Store) {

            /**
             *  This description reads as follows:
             *
             *  Before -> 3 day subscription for store access priced at P10.00
             *  After  -> 3 day subscription to access Heavenly Fruits priced at P10.00
             */
            $description = Str::replace('for store access', 'to access '.ucwords($model->name), $subscriptionPlan->description);

        }else{

            $description = 'Subscription payment';

        }

        //  Create a new transaction
        return $this->transactionRepository()->create([
            'status' => $status,
            'description' => $description,

            'amount' => $amount,
            'currency' => $currency,
            'percentage' => $percentage,
            'payment_method_id' =>$paymentMethodId,

            'payer_user_id' => $payerUserId,

            /**
             *  The requested_by_user_id is set to indicate that the transaction
             *  is verified by the system but the transaction being requested by
             *  the specified user. While the requested_by_user_id is set, the
             *  verified_by_user_id must be NULL. They cannot both have values
             *  at the same time.
             *
             *  If both the requested_by_user_id and the verified_by_user_id are
             *  set, then it causes confusion as to who verified this transaction
             */
            'requested_by_user_id' => $requestedByUserId,
            'verified_by_user_id' => $verifiedByUserId,

            'owner_id' => $subscription->id,
            'owner_type' => $subscription->getResourceName()
        ]);

    }

}
