<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\CartRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Cart\ShowCartsRequest;

class CartController extends BaseController
{
    /**
     *  @var CartRepository
     */
    protected $repository;

    /**
     * CartController constructor.
     *
     * @param CartRepository $repository
     */
    public function __construct(CartRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show carts.
     *
     * @param ShowCartsRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showCarts(ShowCartsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCarts());
    }

    /**
     * Show cart.
     *
     * @param string $cartId
     * @return JsonResponse
     */
    public function showCart(string $cartId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCart($cartId));
    }
}
