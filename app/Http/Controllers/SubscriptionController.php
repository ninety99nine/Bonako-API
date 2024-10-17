<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\SubscriptionRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Subscription\ShowSubscriptionsRequest;
use App\Http\Requests\Models\Subscription\CreateSubscriptionRequest;
use App\Http\Requests\Models\Subscription\UpdateSubscriptionRequest;
use App\Http\Requests\Models\Subscription\DeleteSubscriptionsRequest;

class SubscriptionController extends BaseController
{
    /**
     *  @var SubscriptionRepository
     */
    protected $repository;

    /**
     * SubscriptionController constructor.
     *
     * @param SubscriptionRepository $repository
     */
    public function __construct(SubscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show subscriptions.
     *
     * @param ShowSubscriptionsRequest $request
     * @return JsonResponse
     */
    public function showSubscriptions(ShowSubscriptionsRequest $request): JsonResponse
    {
        if($request->storeId) {
            $request->merge(['store_id' => $request->storeId]);
        }elseif($request->aiAssistantId) {
            $request->merge(['ai_assistant_id' => $request->aiAssistantId]);
        }

        return $this->prepareOutput($this->repository->showSubscriptions($request->all()));
    }

    /**
     * Create subscription.
     *
     * @param CreateSubscriptionRequest $request
     * @return JsonResponse
     */
    public function createSubscription(CreateSubscriptionRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createSubscription($request->all()));
    }

    /**
     * Delete subscriptions.
     *
     * @param DeleteSubscriptionsRequest $request
     * @return JsonResponse
     */
    public function deleteSubscriptions(DeleteSubscriptionsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteSubscriptions($request->all()));
    }

    /**
     * Show subscription.
     *
     * @param string $subscriptionId
     * @return JsonResponse
     */
    public function showSubscription(string $subscriptionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showSubscription($subscriptionId));
    }

    /**
     * Update subscription.
     *
     * @param UpdateSubscriptionRequest $request
     * @param string $subscriptionId
     * @return JsonResponse
     */
    public function updateSubscription(UpdateSubscriptionRequest $request, string $subscriptionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateSubscription($subscriptionId, $request->all()));
    }

    /**
     * Delete subscription.
     *
     * @param string $subscriptionId
     * @return JsonResponse
     */
    public function deleteSubscription(string $subscriptionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteSubscription($subscriptionId));
    }
}
