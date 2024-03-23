<?php

namespace App\Http\Resources;

use App\Enums\PaymentMethodAvailability;
use App\Enums\PaymentMethodFilter;
use App\Traits\Base\BaseTrait;
use App\Models\SubscriptionPlan;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class HomeResource extends BaseResource
{
    use BaseTrait;

    public function setLinks()
    {
        $this->resourceLinks = [
            new ResourceLink('login', route('auth.login')),
            new ResourceLink('register', route('auth.register')),
            new ResourceLink('account.exists', route('auth.account.exists')),
            new ResourceLink('reset.password', route('auth.reset.password')),
            new ResourceLink('validate.register', route('auth.validate.register')),
            new ResourceLink('validate.reset.password', route('auth.validate.reset.password')),
            new ResourceLink('verify.mobile.verification.code', route('auth.verify.mobile.verification.code')),
            new ResourceLink('generate.mobile.verification.code', route('auth.generate.mobile.verification.code')),

            new ResourceLink('show.stores', route('stores.show')),
            new ResourceLink('create.stores', route('stores.create')),
            new ResourceLink('show.brand.stores', route('brand.stores.show')),
            new ResourceLink('show.influencer.stores', route('influencer.stores.show')),

            new ResourceLink('update.assigned.stores.arrangement', route('assigned.stores.arrangement.update')),

            new ResourceLink('check.invitations.to.follow.stores', route('stores.check.invitations.to.follow')),
            new ResourceLink('accept.all.invitations.to.follow.stores', route('stores.accept.all.invitations.to.follow')),
            new ResourceLink('decline.all.invitations.to.follow.stores', route('stores.decline.all.invitations.to.follow')),

            new ResourceLink('check.invitations.to.join.team.stores', route('stores.check.invitations.to.join.team')),
            new ResourceLink('accept.all.invitations.to.join.team.stores', route('stores.accept.all.invitations.to.join.team')),
            new ResourceLink('decline.all.invitations.to.join.team.stores', route('stores.decline.all.invitations.to.join.team')),

            new ResourceLink('show.search.filters', route('search.filters.show')),
            new ResourceLink('show.search.stores', route('search.stores.show')),
            new ResourceLink('show.search.friends', route('search.friends.show')),
            new ResourceLink('show.search.friend.groups', route('search.friend.groups.show')),

            new ResourceLink('show.occasions', route('occasions.show')),
            new ResourceLink('show.shortcode.owner', route('shortcode.owner.show')),
            new ResourceLink('show.ai.message.categories', route('ai.message.categories.show')),

            new ResourceLink('show.payment.methods', route('payment.methods.show')),
            new ResourceLink('show.payment.method.filters', route('payment.method.filters.show')),
            new ResourceLink('show.ussd.payment.methods', route('payment.methods.show', ['usage' => PaymentMethodAvailability::AvailableOnUssd->value, 'filter' => PaymentMethodFilter::Active->value])),

            new ResourceLink('show.subscriptions', route('subscriptions.show')),

            new ResourceLink('show.transactions', route('transactions.show')),

            new ResourceLink('show.subscription.plans', route('subscription.plans.show')),
            new ResourceLink('show.store.subscription.plans', route('subscription.plans.show', ['service' => SubscriptionPlan::STORE_SERVICE_NAME, 'active' => '1'])),
            new ResourceLink('show.sms.alert.subscription.plans', route('subscription.plans.show', ['service' => SubscriptionPlan::SMS_ALERT_SERVICE_NAME, 'active' => '1'])),
            new ResourceLink('show.ai.assistant.subscription.plans', route('subscription.plans.show', ['service' => SubscriptionPlan::AI_ASSISTANT_SERVICE_NAME, 'active' => '1'])),

            new ResourceLink('search.user.by.mobile.number', route('users.search.by.mobile.number')),
            new ResourceLink('show.terms.and.conditions', route('terms.and.conditions.show')),
        ];
    }
}
