<?php

namespace App\Repositories;

use App\Models\Store;
use App\Traits\AuthTrait;
use App\Models\DeliveryMethod;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\DeliveryMethodResources;
use Illuminate\Database\Eloquent\Relations\Relation;

class DeliveryMethodRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show delivery methods.
     *
     * @param Store|string|null $storeId
     * @return DeliveryMethodResources|array
     */
    public function showDeliveryMethods(Store|string|null $storeId = null): DeliveryMethodResources|array
    {
        if($this->getQuery() == null) {
            if(is_null($storeId)) {
                if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show delivery methods'];
                $this->setQuery(DeliveryMethod::orderBy('position'));
            }else{
                $store = $storeId instanceof Store ? $storeId : Store::find($storeId);
                if($store) {
                    $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                    if(!$isAuthourized) return ['message' => 'You do not have permission to show delivery methods'];
                    $this->setQuery($store->deliveryMethods()->orderBy('position'));
                }else{
                    return ['message' => 'This store does not exist'];
                }
            }
        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Create delivery method.
     *
     * @param array $data
     * @return DeliveryMethod|array
     */
    public function createDeliveryMethod(array $data): DeliveryMethod|array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        if($store) {
            $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
            if(!$isAuthourized) return ['created' => false, 'message' => 'You do not have permission to create delivery methods'];
        }else{
            return ['created' => false, 'message' => 'This store does not exist'];
        }

        $data = array_merge($data, [
            'currency' => $store->currency,
            'store_id' => $storeId
        ]);

        $deliveryMethod = DeliveryMethod::create($data);

        if(isset($data['address'])) {
            $deliveryMethod->address()->create($data['address']);
        }

        $this->updateDeliveryMethodArrangement([
            'store_id' => $storeId,
            'delivery_method_ids' => [
                $deliveryMethod->id
            ]
        ]);

        return $this->showCreatedResource($deliveryMethod);
    }

    /**
     * Delete delivery methods.
     *
     * @param array $data
     * @return array
     */
    public function deleteDeliveryMethods(array $data): array
    {
        $storeId = $data['store_id'];

        if(is_null($storeId)) {
            if(!$this->isAuthourized()) return ['deleted' => false, 'message' => 'You do not have permission to delete delivery methods'];
            $this->setQuery(DeliveryMethod::query());
        }else{

            $store = Store::find($storeId);

            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['deleted' => false, 'message' => 'You do not have permission to delete delivery methods'];
                $this->setQuery($store->deliveryMethods());
            }else{
                return ['deleted' => false, 'message' => 'This store does not exist'];
            }

        }

        $deliveryMethodIds = $data['delivery_method_ids'];
        $deliveryMethods = $this->getDeliveryMethodsByIds($deliveryMethodIds);

        if($totalDeliveryMethods = $deliveryMethods->count()) {

            foreach($deliveryMethods as $deliveryMethod) {
                $deliveryMethod->delete();
            }

            return ['deleted' => true, 'message' => $totalDeliveryMethods . ($totalDeliveryMethods == 1 ? ' delivery method': ' delivery methods') . ' deleted'];

        }else{
            return ['deleted' => false, 'message' => 'No delivery methods deleted'];
        }
    }

    /**
     * Update delivery method arrangement
     *
     * @param array $data
     * @return array
     */
    public function updateDeliveryMethodArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        if($store) {
            $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
            if(!$isAuthourized) return ['message' => 'You do not have permission to update delivery method arrangement'];
            $this->setQuery($store->deliveryMethods()->orderBy('position', 'asc'));
        }else{
            return ['message' => 'This store does not exist'];
        }

        $deliveryMethodIds = $data['delivery_method_ids'];

        $deliveryMethods = $this->query->get();
        $originalDeliveryMethodPositions = $deliveryMethods->pluck('position', 'id');

        $arrangement = collect($deliveryMethodIds)->filter(function ($DeliveryMethodId) use ($originalDeliveryMethodPositions) {
            return collect($originalDeliveryMethodPositions)->keys()->contains($DeliveryMethodId);
        })->toArray();

        $movedDeliveryMethodPositions = collect($arrangement)->mapWithKeys(function ($DeliveryMethodId, $newPosition) use ($originalDeliveryMethodPositions) {
            return [$DeliveryMethodId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalDeliveryMethodPositions = $originalDeliveryMethodPositions->except(collect($movedDeliveryMethodPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedDeliveryMethodPositions) {
            return [$id => count($movedDeliveryMethodPositions) + $index + 1];
        })->toArray();

        $deliveryMethodPositions = $movedDeliveryMethodPositions + $adjustedOriginalDeliveryMethodPositions;

        if(count($deliveryMethodPositions)) {

            DB::table('delivery_methods')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($deliveryMethodPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($deliveryMethodPositions), $deliveryMethodPositions)) . 'END')]);

            return ['updated' => true, 'message' => 'Delivery method arrangement has been updated'];

        }

        return ['updated' => false, 'message' => 'No matching delivery methods to update'];
    }

    /**
     * Show delivery method.
     *
     * @param string $deliveryMethodId
     * @return DeliveryMethod|array|null
     */
    public function showDeliveryMethod(string $deliveryMethodId): DeliveryMethod|array|null
    {
        $deliveryMethod = $this->setQuery(DeliveryMethod::with(['store'])->whereId($deliveryMethodId))->applyEagerLoadingOnQuery()->getQuery()->first();

        if($deliveryMethod) {
            $store = $deliveryMethod->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['message' => 'You do not have permission to show delivery method'];
                if(!$this->checkIfHasRelationOnRequest('store')) $deliveryMethod->unsetRelation('store');
            }else{
                return ['message' => 'This store does not exist'];
            }
        }

        return $this->showResourceExistence($deliveryMethod);
    }

    /**
     * Update delivery method.
     *
     * @param string $deliveryMethodId
     * @param array $data
     * @return DeliveryMethod|array
     */
    public function updateDeliveryMethod(string $deliveryMethodId, array $data): DeliveryMethod|array
    {
        $deliveryMethod = DeliveryMethod::with(['store'])->find($deliveryMethodId);

        if($deliveryMethod) {
            $store = $deliveryMethod->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['updated' => false, 'message' => 'You do not have permission to update delivery method'];
                if(!$this->checkIfHasRelationOnRequest('store')) $deliveryMethod->unsetRelation('store');
            }else{
                return ['updated' => false, 'message' => 'This store does not exist'];
            }

            $deliveryMethod->update($data);
            return $this->showUpdatedResource($deliveryMethod);

        }else{
            return ['updated' => false, 'message' => 'This delivery method does not exist'];
        }
    }

    /**
     * Delete delivery method.
     *
     * @param string $deliveryMethodId
     * @return array
     */
    public function deleteDeliveryMethod(string $deliveryMethodId): array
    {
        $deliveryMethod = DeliveryMethod::with(['store'])->find($deliveryMethodId);

        if($deliveryMethod) {
            $store = $deliveryMethod->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['deleted' => false, 'message' => 'You do not have permission to delete delivery method'];
            }else{
                return ['deleted' => false, 'message' => 'This store does not exist'];
            }

            $deleted = $deliveryMethod->delete();

            if ($deleted) {
                return ['deleted' => true, 'message' => 'Delivery method deleted'];
            }else{
                return ['deleted' => false, 'message' => 'Delivery method delete unsuccessful'];
            }
        }else{
            return ['deleted' => false, 'message' => 'This delivery method does not exist'];
        }
    }

    /***********************************************
     *             MISCELLANEOUS METHODS           *
     **********************************************/

    /**
     * Query delivery method by ID.
     *
     * @param DeliveryMethod|string $deliveryMethodId
     * @param array $relationships
     * @return Builder|Relation
     */
    public function queryDeliveryMethodById(DeliveryMethod|string $deliveryMethodId, array $relationships = []): Builder|Relation
    {
        return $this->query->where('delivery_methods.id', $deliveryMethodId)->with($relationships);
    }

    /**
     * Get delivery method by ID.
     *
     * @param DeliveryMethod|string $deliveryMethodId
     * @param array $relationships
     * @return DeliveryMethod|null
     */
    public function getDeliveryMethodById(DeliveryMethod|string $deliveryMethodId, array $relationships = []): DeliveryMethod|null
    {
        return $this->queryDeliveryMethodById($deliveryMethodId, $relationships)->first();
    }

    /**
     * Query delivery methods by IDs.
     *
     * @param array<string> $deliveryMethodId
     * @param string $relationships
     * @return Builder|Relation
     */
    public function queryDeliveryMethodsByIds($deliveryMethodIds): Builder|Relation
    {
        return $this->query->whereIn('delivery_methods.id', $deliveryMethodIds);
    }

    /**
     * Get delivery methods by IDs.
     *
     * @param array<string> $deliveryMethodId
     * @param string $relationships
     * @return Collection
     */
    public function getDeliveryMethodsByIds($deliveryMethodIds): Collection
    {
        return $this->queryDeliveryMethodsByIds($deliveryMethodIds)->get();
    }
}
