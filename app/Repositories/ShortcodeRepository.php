<?php

namespace App\Repositories;

use App\Models\AiAssistant;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\InstantCart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Base\BaseModel;
use App\Models\SmsAlert;
use App\Services\Ussd\UssdService;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShortcodeRepository extends BaseRepository
{
    /**
     *  Create a shortcode that can be used to pay for a resource
     *
     *  @return BaseRepository
     */
    public function showShortcodeOwner(Request $request)
    {
        //  Get the code
        $code = $request->input('code');

        //  Get the shortcode using the provided code
        $shortcode = $this->model->where(['code' => $code])->first();

        $isPayingShortcode = strtolower($shortcode->action) == 'pay';
        $isVisitingShortcode = strtolower($shortcode->action) == 'visit';

        //  If the shortcode exists
        if($shortcode) {

            //  Get the shortcode owner e.g Store, Instant Cart, e.t.c
            $owner = $shortcode->owner;

            //  If this is a Store instance
            if($owner instanceof Store) {

                $ownerPayload = [
                    'name' => $owner->name,
                    'type' => $owner->getResourceName(),
                    'links' => [
                        'self' => route('store.show', ['store' => $owner->id]),
                    ]
                ];

                if($isPayingShortcode) {
                    $ownerPayload['links']['showPaymentMethods'] = route('payment.methods.show');
                    $ownerPayload['links']['showSubscriptionPlans'] = route('subscription.plans.show');
                    $ownerPayload['links']['createSubscriptionPlan'] = route('store.subscriptions.create', ['store' => $owner->id]);
                    $ownerPayload['links']['calculateSubscriptionAmount'] = route('store.subscriptions.calculate.amount', ['store' => $owner->id]);
                }

            //  If this is an AiAssistant instance
            }else if($owner instanceof AiAssistant) {

                $ownerPayload = [
                    'name' => 'AI Assistant',
                    'type' => $owner->getResourceName(),
                    'links' => [
                        'self' => route('user.ai.assistant.show', ['user' => $owner->user_id])
                    ]
                ];

                if($isPayingShortcode) {
                    $ownerPayload['links']['showPaymentMethods'] = route('payment.methods.show');
                    $ownerPayload['links']['showSubscriptionPlans'] = route('subscription.plans.show');
                    $ownerPayload['links']['createSubscriptionPlan'] = route('user.ai.assistant.subscriptions.create', ['user' => $owner->user_id]);
                    $ownerPayload['links']['calculateSubscriptionAmount'] = route('user.ai.assistant.subscriptions.calculate.amount', ['user' => $owner->user_id]);
                }

            //  If this is an SmsAlert instance
            }else if($owner instanceof SmsAlert) {

                $ownerPayload = [
                    'name' => 'SMS Alert',
                    'type' => $owner->getResourceName(),
                    'links' => [
                        'self' => route('user.sms.alert.show', ['user' => $owner->user_id])
                    ]
                ];

                if($isPayingShortcode) {
                    $ownerPayload['links']['showPaymentMethods'] = route('payment.methods.show');
                    $ownerPayload['links']['showSubscriptionPlans'] = route('subscription.plans.show');
                    $ownerPayload['links']['createTransaction'] = route('user.sms.alert.transactions.create', ['user' => $owner->user_id]);
                    $ownerPayload['links']['calculateTransactionAmount'] = route('user.sms.alert.transactions.calculate.amount', ['user' => $owner->user_id]);
                }

            //  If this is an InstantCart instance
            }else if($owner instanceof InstantCart) {

                $ownerPayload = [
                    'name' => $owner->name,
                    'type' => $owner->getResourceName(),
                    'links' => [
                        'self' => route('instant.cart.show', ['instant_cart' => $owner->id]),
                    ]
                ];

            }

            //  Set the action
            $ownerPayload['action'] = strtolower($shortcode->action);
            $ownerPayload['reserved_for_user_id'] = $shortcode->reserved_for_user_id;

            return $ownerPayload;

        }else{

            throw new ModelNotFoundException;

        }
    }

    /**
     *  Create a shortcode that can be used to pay for a resource
     *
     *  @param Transaction $model - The transaction to be paid
     *  @param int $reservedForUserId - The User ID permitted to dial this shortcode
     *  @return ShortcodeRepository
     */
    public function generatePaymentShortcode($model, $reservedForUserId)
    {
        return $this->requestShortcode($model, 'Pay', $reservedForUserId, null);
    }

    /**
     *  Create a shortcode that can be used to visit this resource
     *
     *  @param BaseModel $model - The resource to be visited e.g Store, Instant cart, e.t.c
     *  @param datetime $expiresAt - The expiry date and time for this resource
     *  @return ShortcodeRepository
     */
    public function requestVisitShortcode($model, $expiresAt)
    {
        return $this->requestShortcode($model, 'Visit', null, $expiresAt);
    }

    /**
     *  Create a shortcode that can be used for this resource
     *
     *  @param BaseModel $model - The resource e.g Store, Instant cart, e.t.c
     *  @param string $action - The action to be performed by this shortcode
     *  @param int $reservedForUserId - The User ID permitted to dial this shortcode
     *  @param datetime $expiresAt - The expiry date and time for this resource
     *  @return ShortcodeRepository
     */
    private function requestShortcode($model, $action, $reservedForUserId = null, $expiresAt = null)
    {
        //  Set default expiry date and time (24 hours from now) if non is provided
        $expiresAt = $expiresAt ?? Carbon::now()->addHours(24)->format('Y-m-d H:i:s');

        //  Search for a matching shortcode (Already issued before for the same Model resource)
        if( $shortcode = $this->getMatchingShortcode($model, $action) ) {

            //  Update the expiry date
            return $this->setModel($shortcode)->update(['expires_at' => $expiresAt]);

        }else{

            //  Search for any other available inactive short codes (Already issued before for the another Model resource)
            $shortcode = $this->getExistingButExpiredShortcode($action);

            //  If the shortcode exists
            if( $shortcode ) {

                //  Update the existing shortcode
                return $this->setModel($shortcode)->update([
                    'owner_id' => $model->id,
                    'expires_at' => $expiresAt,
                    'owner_type' => $model->getResourceName(),
                    'reserved_for_user_id' => $reservedForUserId,
                ]);

            //  If the shortcode does not exist
            }else{

                //  Generate a new code
                $code = UssdService::generateResourceCode($this->model, $action);

                //  Create a new shortcode
                return parent::create([
                    'code' => $code,
                    'action' => $action,
                    'owner_id' => $model->id,
                    'expires_at' => $expiresAt,
                    'owner_type' => $model->getResourceName(),
                    'reserved_for_user_id' => $reservedForUserId
                ]);

            }

        }

    }

    /**
     *  This method will search and return any short code
     *  that matches the current search criteria. This is
     *  used to search for a shortcode that has already
     *  been assigned to the specified model for the
     *  specified action. Return the matching
     *  shortcode even if it has expired.
     *
     *  @param BaseModel $model - The resource to be visited e.g Store, Instant cart, e.t.c
     *  @param string $action - The action to be performed by this shortcode
     *  @return Shortcode|null
     */
    public function getMatchingShortcode($model, $action)
    {
        //  Set the search criteria
        $search = [
            'action' => $action,
            'owner_id' => $model->id,
            'owner_type' => $model->getResourceName()
        ];

        return $this->model->where($search)->latest()->first();
    }

    /**
     *  This method will search and return any available
     *  short code that is currently not in use. The
     *  short code must have expired.
     *
     *  @param string $action
     *  @return Shortcode|null
     */
    public function getExistingButExpiredShortcode($action)
    {
        return $this->model->action($action)->expired()->oldest()->first();
    }

    /**
     *  Expire the shortcode
     *
     *  @return ShortcodeRepository
     */
    public function expireShortcode()
    {
        $this->update(['expires_at' => now()]);

        return $this;
    }
}
