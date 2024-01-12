<?php

namespace App\Repositories;

use Exception;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Models\SubscriptionPlan;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Return the repository subscription plan
     *
     *  @return SubscriptionPlan - Repository model user
     */
    public function getSubscriptionPlan()
    {
        if($this->model instanceof SubscriptionPlan) {

            return $this->model;

        }else{

            throw new Exception('This repository model is not an instance of the User model');

        }
    }

    /**
     *  Show the subscription plans
     *
     *  @return SubscriptionPlanRepository
     */
    public function showSubscriptionPlans()
    {
        //  Get the specified subscription plan type
        $type = request()->input('type');

        //  Get the specified subscription plan service
        $service = request()->input('service');

        //  Get the specified subscription plan active status
        $active = request()->input('active');

        //  Get the subscription plans by order of their positions
        $subscriptionPlans = $this->model->orderBy('position', 'asc');

        if(!empty($service)) {
            $subscriptionPlans = $subscriptionPlans->where('service', urldecode($service));
        }

        return $this->setModel($subscriptionPlans)->get();
    }

    /**
     *  Calculate subscription plan amount against duration
     *
     *  @param Request $request
     *  @return int
     */
    public function calculateSubscriptionPlanAmountAgainstSubscriptionDuration(Request $request)
    {
        if(is_null($this->getSubscriptionPlan()->metadata['duration'])) {
            return $this->getSubscriptionPlanDuration($request) * $this->getSubscriptionPlan()->price->amount;
        }else{
            return $this->getSubscriptionPlan()->price->amount;
        }
    }

    /**
     *  Get the subscription plan duration
     *
     *  @param Request $request
     *  @return int
     */
    public function getSubscriptionPlanDuration(Request $request)
    {
        /// If the subscription plan duration is not set on the subscription plan
        if(is_null($this->getSubscriptionPlan()->metadata['duration'])) {

            if($request->filled('duration')) {

                /// Get the duration specified by the user
                return $request->input('duration');

            }else{

                //  Throw an Exception
                throw ValidationException::withMessages(['duration' => 'The duration field is required']);

            }

        }else{

            /// Get the duration specified by the subscription plan
            return $this->getSubscriptionPlan()->metadata['duration'];

        }
    }

    /**
     *  Calculate subscription plan amount against sms credits
     *
     *  @param Request $request
     *  @return int
     */
    public function calculateSubscriptionPlanAmountAgainstSmsCredits(Request $request)
    {
        return $this->getSubscriptionPlan()->price->amount * $this->getSubscriptionPlanSmsCredits($request);
    }

    /**
     *  Get the subscription plan sms credits
     *
     *  @param Request $request
     *  @return int
     */
    public function getSubscriptionPlanSmsCredits(Request $request)
    {
        /// If the subscription plan sms credits are not set on the subscription plan
        if(is_null($this->getSubscriptionPlan()->metadata['sms_credits'])) {

            if($request->filled('sms_credits')) {

                /// Get the sms credits specified by the user
                return $request->input('sms_credits');

            }else{

                //  Throw an Exception
                throw ValidationException::withMessages(['sms_credits' => 'The sms credits field is required']);

            }

        }else{

            /// Get the sms credits specified by the subscription plan
            return $this->getSubscriptionPlan()->metadata['sms_credits'];

        }
    }
}
