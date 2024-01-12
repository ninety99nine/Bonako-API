<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Base\BaseModel;
use App\Models\SubscriptionPlan;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Subscriptions\SubscriptionCreated;
use Illuminate\Database\Eloquent\Model;

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
     *  Return the SubscriptionPlanRepository instance
     *
     *  @return SubscriptionPlanRepository
     */
    public function subscriptionPlanRepository()
    {
        return resolve(SubscriptionPlanRepository::class);
    }

    /**
     *  Create a subscription
     *
     *  @param Model $model - The resource being subscribed for
     *  @param Request $request - The HTTP request
     *  @param Subscription $latestSubscription - The latest subscription
     *  @return SubscriptionRepository
     */
    public function createSubscription(Model $model, Request $request = null, Subscription $latestSubscription = null)
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

        //  Get the Subscription Plan frequency
        $frequency = $subscriptionPlan->metadata['frequency'];

        //  Calculate the subscription plan amount
        $amount = $this->subscriptionPlanRepository()->setModel($subscriptionPlan)->calculateSubscriptionPlanAmountAgainstSubscriptionDuration($request);

        //  Get the subscription plan duration
        $duration = $this->subscriptionPlanRepository()->setModel($subscriptionPlan)->getSubscriptionPlanDuration($request);

        //  Calculate the end datetime
        $endAt = $this->calculateSubscriptionEndAt($startAt, $duration, $frequency);

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
        $transactionRepository = $this->transactionRepository()->createTransaction($subscription, $subscriptionPlan, $request);

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

}
