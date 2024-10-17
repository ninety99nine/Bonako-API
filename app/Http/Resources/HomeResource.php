<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class HomeResource extends BaseResource
{
    use BaseTrait;

    public function setLinks()
    {
        $this->resourceLinks = [
            new ResourceLink('login', route('login')),
            new ResourceLink('register', route('register')),
            new ResourceLink('account.exists', route('account.exists')),
            new ResourceLink('reset.password', route('reset.password')),
            new ResourceLink('validate.register', route('validate.register')),
            new ResourceLink('validate.reset.password', route('validate.reset.password')),
            new ResourceLink('show.terms.and.conditions', route('show.terms.and.conditions')),
            new ResourceLink('show.terms.and.conditions.takeaways', route('show.terms.and.conditions.takeaways')),
            new ResourceLink('verify.mobile.verification.code', route('verify.mobile.verification.code')),
            new ResourceLink('generate.mobile.verification.code', route('generate.mobile.verification.code')),

            new ResourceLink('show.users', route('show.users')),
            new ResourceLink('show.stores', route('show.stores')),
            new ResourceLink('show.orders', route('show.orders')),
            new ResourceLink('show.coupons', route('show.coupons')),
            new ResourceLink('show.friends', route('show.friends')),
            new ResourceLink('show.reviews', route('show.reviews')),
            new ResourceLink('show.products', route('show.products')),
            new ResourceLink('show.customers', route('show.customers')),
            new ResourceLink('show.addresses', route('show.addresses')),
            new ResourceLink('show.occasions', route('show.occasions')),

            new ResourceLink('show.ai.lessons', route('show.ai.lessons')),

            new ResourceLink('show.ai.messages', route('show.ai.messages')),
            new ResourceLink('create.ai.message', route('create.ai.message')),
            new ResourceLink('delete.ai.messages', route('delete.ai.messages')),

            new ResourceLink('show.transactions', route('show.transactions')),
            new ResourceLink('show.ai.assistants', route('show.ai.assistants')),
            new ResourceLink('show.friend.groups', route('show.friend.groups')),
            new ResourceLink('show.pricing.plans', route('show.pricing.plans')),
            new ResourceLink('show.notifications', route('show.notifications')),
            new ResourceLink('show.subscriptions', route('show.subscriptions')),
            new ResourceLink('show.payment.methods', route('show.payment.methods')),
            new ResourceLink('show.review.rating.options', route('show.review.rating.options')),
            new ResourceLink('show.ai.message.categories', route('show.ai.message.categories')),

            new ResourceLink('launch.ussd', route('launch.ussd')),
        ];
    }
}
