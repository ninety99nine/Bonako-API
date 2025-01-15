<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Services\ShoppingCart\ShoppingCartService;
use App\Http\Requests\Models\ShoppingCart\InspectShoppingCartRequest;

class ShoppingCartController extends BaseController
{
    /**
     * Show shopping carts.
     *
     * @param InspectShoppingCartRequest $request
     * @return JsonResponse
     */
    public function inspectShoppingCart(InspectShoppingCartRequest $request): JsonResponse
    {
        //dd($request->validated());
        //return $this->prepareOutput('Inspect the shoppgin cart');

        $storeId = request()->input('store_id');
        $store = Store::find($storeId);

        return $this->prepareOutput((new ShoppingCartService)->startInspection($store));
    }
}
