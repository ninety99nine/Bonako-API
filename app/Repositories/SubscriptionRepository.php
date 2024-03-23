<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Base\BaseModel;
use App\Traits\Base\BaseTrait;
use App\Models\SubscriptionPlan;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Subscriptions\SubscriptionCreated;

class SubscriptionRepository extends BaseRepository
{
    use BaseTrait;
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
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return OrderRepository
     */
    public function eagerLoadSubscriptionRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        //  Check if we want to eager load the user on this review
        if( request()->input('with_user') ) {

            //  Additionally we can eager load the user on this review
            array_push($relationships, 'user');

        }

        //  Check if we want to eager load the owner
        if( request()->input('with_owner') ) {

            //  Additionally we can eager load the owner
            array_push($relationships, 'owner');

        }

        //  Check if we want to eager load the transaction
        if( request()->input('with_transaction') ) {

            //  Additionally we can eager load the transaction
            array_push($relationships, 'transaction');

        }

        //  Check if we want to eager load the subscription plan
        if( request()->input('with_subscription_plan') ) {

            //  Additionally we can eager load the subscription plan
            array_push($relationships, 'subscriptionPlan');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Subscription)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the subscriptions
     *
     *  @return SubscriptionRepository
     */
    public function showSubscriptions()
    {
        $endAt = request()->input('end_at');
        $startAt = request()->input('start_at');
        $status = $this->model->separateWordsThenLowercase(request()->input('status'));
        $service = $this->model->separateWordsThenLowercase(request()->input('service'));

        //  Query the latest subscriptions first
        $subscriptions = $this->model->latest();

        if(!empty($endAt)) {
            [$operator, $date] = $this->extractOperatorAndDate($endAt);
            $subscriptions = $subscriptions->whereDate('end_at', $operator, $date);
        }

        if(!empty($startAt)) {
            [$operator, $date] = $this->extractOperatorAndDate($startAt);
            $subscriptions = $subscriptions->whereDate('start_at', $operator, $date);
        }

        if(!empty($status)) {

            if($status == 'active') {

                $subscriptions = $subscriptions->notExpired();

            }else if($status == 'inactive') {

                $subscriptions = $subscriptions->expired();

            }

        }

        if(!empty($service)) {
            $subscriptions = $subscriptions->where('owner_type', $service);
        }

        //  Eager load the subscription relationships based on request inputs
        return $this->eagerLoadSubscriptionRelationships($subscriptions)->get();
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

            if(Carbon::parse($latestSubscription->end_at)->isFuture()) {

                //  Set the start datetime based on the last subscription end datetime
                $startAt = Carbon::parse($latestSubscription->end_at);

            }else{

                //  Set the start datetime as the current datetime
                $startAt = now();

            }

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
            'user_id' => request()->auth_user->id,
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
        $notifyUsers = request()->auth_user;
        $subscriptionByUser = request()->auth_user;
        $subscriptionForUser = request()->auth_user;

        //  Notify the user that they have subscribed successfully
        Notification::send(
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
