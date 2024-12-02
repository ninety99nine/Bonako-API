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
            new ResourceLink('create.store', route('create.store')),
            new ResourceLink('search.store.by.alias', route('search.store.by.alias')),
            new ResourceLink('search.store.by.ussd.mobile.number', route('search.store.by.ussd.mobile.number')),
            new ResourceLink('show.last.visited.store', route('show.last.visited.store')),
            new ResourceLink('show.store.deposit.options', route('show.store.deposit.options')),
            new ResourceLink('show.store.installment.options', route('show.store.installment.options')),

            new ResourceLink('show.friends', route('show.friends')),
            new ResourceLink('show.reviews', route('show.reviews')),

            new ResourceLink('show.orders', route('show.orders')),
            new ResourceLink('create.order', route('create.order')),
            new ResourceLink('delete.orders', route('delete.orders')),
            new ResourceLink('show.order.status.counts', route('show.order.status.counts')),

            new ResourceLink('show.products', route('show.products')),
            new ResourceLink('create.product', route('create.product')),
            new ResourceLink('delete.products', route('delete.products')),
            new ResourceLink('update.product.visibility', route('update.product.visibility')),
            new ResourceLink('update.product.arrangement', route('update.product.arrangement')),

            new ResourceLink('show.coupons', route('show.coupons')),
            new ResourceLink('create.coupon', route('create.coupon')),
            new ResourceLink('delete.coupons', route('delete.coupons')),

            new ResourceLink('show.customers', route('show.customers')),
            new ResourceLink('create.customer', route('create.customer')),
            new ResourceLink('delete.customers', route('delete.customers')),

            new ResourceLink('show.store.rolling.numbers', route('show.store.rolling.numbers')),
            new ResourceLink('create.store.rolling.number', route('create.store.rolling.number')),
            new ResourceLink('delete.store.rolling.numbers', route('delete.store.rolling.numbers')),

            new ResourceLink('show.addresses', route('show.addresses')),
            new ResourceLink('add.address', route('add.address')),
            new ResourceLink('remove.addresses', route('remove.addresses')),

            new ResourceLink('show.occasions', route('show.occasions')),

            new ResourceLink('create.review', route('create.review')),




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
            new ResourceLink('create.payment.method', route('create.payment.method')),
            new ResourceLink('delete.payment.methods', route('delete.payment.methods')),
            new ResourceLink('update.payment.method.arrangement', route('update.payment.method.arrangement')),

            new ResourceLink('show.review.rating.options', route('show.review.rating.options')),
            new ResourceLink('show.ai.message.categories', route('show.ai.message.categories')),

            new ResourceLink('launch.ussd', route('launch.ussd')),
            new ResourceLink('show.languages', route('show.languages')),
            new ResourceLink('show.countries', route('show.countries')),
            new ResourceLink('show.currencies', route('show.currencies')),
        ];
    }
}
