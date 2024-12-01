<?php

namespace App\Repositories;

use App\Enums\PaymentMethodType;
use App\Models\Store;
use App\Traits\AuthTrait;
use App\Models\PaymentMethod;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Collection;
use App\Services\Filter\FilterService;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\PaymentMethodResources;
use Illuminate\Database\Eloquent\Relations\Relation;

class PaymentMethodRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show payment methods.
     *
     * @return PaymentMethodResources|array
     */
    public function showPaymentMethods(array $data = []): PaymentMethodResources|array
    {
        if($this->getQuery() == null) {

            $storeId = isset($data['store_id']) ? $data['store_id'] : null;
            $nonAssociatedStoreId = isset($data['non_associated_store_id']) ? $data['non_associated_store_id'] : null;

            if($storeId) {
                $store = Store::find($storeId);
                if($store) {
                    $this->setQuery($store->paymentMethods()->latest());
                }else{
                    return ['message' => 'This store does not exist'];
                }
            }else if($nonAssociatedStoreId) {
                $store = Store::find($nonAssociatedStoreId);
                if($store) {
                    $existingPaymentMethodTypes = $store->paymentMethods()->pluck('type')->reject(PaymentMethodType::MANUAL_PAYMENT->value);
                    $this->setQuery(PaymentMethod::whereNull('store_id')->whereNotIn('type', $existingPaymentMethodTypes)->latest());
                }else{
                    return ['message' => 'This store does not exist'];
                }
            }else {
                if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show payment methods'];
                $this->setQuery(PaymentMethod::query()->latest());
            }
        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Create payment method.
     *
     * @param array $data
     * @return PaymentMethod|array
     */
    public function createPaymentMethod(array $data): PaymentMethod|array
    {
        if(isset($data['store_id'])) {

            $storeId = $data['store_id'];
            $store = Store::find($storeId);

            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['created' => false, 'message' => 'You do not have permission to create store payment method'];
            }else{
                return ['created' => false, 'message' => 'This store does not exist'];
            }

        }else{

            if(!$this->isAuthourized()) return ['created' => false, 'message' => 'You do not have permission to create payment methods'];

        }

        $paymentMethod = PaymentMethod::create($data);
        return $this->showCreatedResource($paymentMethod);
    }

    /**
     * Delete payment methods.
     *
     * @param array $paymentMethodIds
     * @return array
     */
    public function deletePaymentMethods(array $paymentMethodIds): array
    {
        if(!$this->isAuthourized()) return ['deleted' => false, 'message' => 'You do not have permission to delete payment methods'];

        $paymentMethods = $this->setQuery(PaymentMethod::query())->getPaymentMethodsByIds($paymentMethodIds);

        if($totalPaymentMethods = $paymentMethods->count()) {

            foreach($paymentMethods as $paymentMethod) {
                $paymentMethod->delete();
            }

            return ['deleted' => true, 'message' => $totalPaymentMethods  .($totalPaymentMethods  == 1 ? ' payment method': ' payment methods') . ' deleted'];

        }else{
            return ['deleted' => false, 'message' => 'No payment methods deleted'];
        }
    }

    /**
     * Show payment method.
     *
     * @param PaymentMethod|string|null $paymentMethodId
     * @return PaymentMethod|array|null
     */
    public function showPaymentMethod(PaymentMethod|string|null $paymentMethodId = null): PaymentMethod|array|null
    {
        if(($paymentMethod = $paymentMethodId) instanceof PaymentMethod) {
            $paymentMethod = $this->applyEagerLoadingOnModel($paymentMethod);
        }else {
            $query = $this->getQuery() ?? PaymentMethod::query();
            if($paymentMethodId) $query = $query->where('payment_methods.id', $paymentMethodId);
            $this->setQuery($query)->applyEagerLoadingOnQuery();
            $paymentMethod = $this->query->first();
        }

        return $this->showResourceExistence($paymentMethod);
    }

    /**
     * Update payment method.
     *
     * @param PaymentMethod|string $paymentMethodId
     * @param array $data
     * @return PaymentMethod|array
     */
    public function updatePaymentMethod(PaymentMethod|string $paymentMethodId, array $data): PaymentMethod|array
    {
        $paymentMethod = PaymentMethod::with(['store'])->find($paymentMethodId);

        if($paymentMethod) {
            $store = $paymentMethod->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['updated' => false, 'message' => 'You do not have permission to update payment method'];
                if(!$this->checkIfHasRelationOnRequest('store')) $paymentMethod->unsetRelation('store');
            }else{
                if(!$this->isAuthourized()) return ['updated' => false, 'message' => 'You do not have permission to update payment method'];
            }

            $paymentMethod->update($data);
            return $this->showUpdatedResource($paymentMethod);
        }else{
            return ['updated' => false, 'message' => 'This payment method does not exist'];
        }
    }

    /**
     * Delete payment method.
     *
     * @param PaymentMethod|string $paymentMethodId
     * @return array
     */
    public function deletePaymentMethod(PaymentMethod|string $paymentMethodId): array
    {
        $paymentMethod = PaymentMethod::with(['store'])->find($paymentMethodId);

        if($paymentMethod) {
            $store = $paymentMethod->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['deleted' => false, 'message' => 'You do not have permission to delete payment method'];
            }else{
                if(!$this->isAuthourized()) return ['deleted' => false, 'message' => 'You do not have permission to delete payment method'];
            }

            $deleted = $paymentMethod->delete();

            if ($deleted) {
                return ['deleted' => true, 'message' => 'Payment method deleted'];
            }else{
                return ['deleted' => false, 'message' => 'Payment method delete unsuccessful'];
            }
        }else{
            return ['deleted' => false, 'message' => 'This payment method does not exist'];
        }
    }

    /***********************************************
     *             MISCELLANEOUS METHODS           *
     **********************************************/

    /**
     * Query payment method by ID.
     *
     * @param string $paymentMethodId
     * @param array $relationships
     * @return Builder|Relation
     */
    public function queryPaymentMethodById(string $paymentMethodId, array $relationships = []): Builder|Relation
    {
        return $this->query->where('payment_methods.id', $paymentMethodId)->with($relationships);
    }

    /**
     * Get payment method by ID.
     *
     * @param string $paymentMethodId
     * @param array $relationships
     * @return PaymentMethod|null
     */
    public function getPaymentMethodById(string $paymentMethodId, array $relationships = []): PaymentMethod|null
    {
        return $this->queryPaymentMethodById($paymentMethodId, $relationships)->first();
    }

    /**
     * Query payment methods by IDs.
     *
     * @param array<string> $paymentMethodId
     * @param string $relationships
     * @return Builder|Relation
     */
    public function queryPaymentMethodsByIds($paymentMethodIds): Builder|Relation
    {
        return $this->query->whereIn('payment_methods.id', $paymentMethodIds);
    }

    /**
     * Get payment methods by IDs.
     *
     * @param array<string> $paymentMethodId
     * @param string $relationships
     * @return Collection
     */
    public function getPaymentMethodsByIds($paymentMethodIds): Collection
    {
        return $this->queryPaymentMethodsByIds($paymentMethodIds)->get();
    }
}
