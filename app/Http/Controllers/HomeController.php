<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\Controller;
use App\Http\Resources\UserResource;
use App\Services\MobileNumber\MobileNumberService;
use App\Services\Ussd\UssdService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function apiHome()
    {
        /**
         *  Since the api home endpoint does not require an authenticated user,
         *  i.e withoutMiddleware('auth:sanctum'), this means that the endpoint
         *  is open to everyone with or without the bearer token. This means
         *  that we never check the bearer token to block access on the
         *  condition of an invalid bearer token.
         */
        $user = auth()->user();

        /**
         *  Set the Api Home links
         */
        $links = [
            'login' => route('auth.login'),
            'register' => route('auth.register'),
            'account.exists' => route('auth.account.exists'),
            'reset.password' => route('auth.reset.password'),
            'validate.register' => route('auth.validate.register'),
            'validate.reset.password' => route('auth.validate.reset.password'),
            'verify.mobile.verification.code' => route('auth.verify.mobile.verification.code'),
            'generate.mobile.verification.code' => route('auth.generate.mobile.verification.code'),

            'show.stores' => route('stores.show'),
            'create.stores' => route('stores.create'),
            'show.brand.stores' => route('brand.stores.show'),
            'show.influencer.stores' => route('influencer.stores.show'),

            'update.assigned.stores.arrangement' => route('assigned.stores.arrangement.update'),

            'check.invitations.to.follow.stores' => route('stores.check.invitations.to.follow'),
            'accept.all.invitations.to.follow.stores' => route('stores.accept.all.invitations.to.follow'),
            'decline.all.invitations.to.follow.stores' => route('stores.decline.all.invitations.to.follow'),

            'check.invitations.to.join.team.stores' => route('stores.check.invitations.to.join.team'),
            'accept.all.invitations.to.join.team.stores' => route('stores.accept.all.invitations.to.join.team'),
            'decline.all.invitations.to.join.team.stores' => route('stores.decline.all.invitations.to.join.team'),

            'show.search.filters' => route('search.filters.show'),
            'show.search.friend.groups' => route('search.friend.groups.show'),
            'show.search.friends' => route('search.friends.show'),
            'show.search.stores' => route('search.stores.show'),

            'show.occasions' => route('occasions.show'),
            'show.ai.message.categories' => route('ai.message.categories.show'),
            'show.payment.methods' => route('payment.methods.show'),
            'show.payment.method.filters' => route('payment.method.filters.show'),
            'show.shortcode.owner' => route('shortcode.owner.show'),
            'show.subscription.plans' => route('subscription.plans.show'),
            'search.user.by.mobile.number' => route('users.search.by.mobile.number'),

            'show.terms.and.conditions' => route('terms.and.conditions.show'),  //  redirect

        ];

        return [
            'accepted_terms_and_conditions' => $user ? $user->accepted_terms_and_conditions : false,
            'mobile_verification_shortcode' => UssdService::getMobileVerificationShortcode(),
            'mobile_number_extension' => MobileNumberService::getMobileNumberExtension(),
            'reserved_shortcode_range' => UssdService::getReservedShortcodeRange(),
            'authenticated' => $user ? true : false,
            'user' => $user ? new UserResource($user) : null,
            'links' => $links,
        ];
    }
}
