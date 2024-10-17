<?php

namespace App\Observers;

use App\Models\Cart;
use App\Repositories\CartRepository;

class CartObserver
{

    /**
     *  Return the CartRepository instance
     *
     *  @return CartRepository
     */
    public function cartRepository()
    {
        return resolve(CartRepository::class);
    }

    public function created(Cart $cart)
    {
        //
    }

    public function updated(Cart $cart)
    {
        //
    }

    public function deleted(Cart $cart)
    {
        //
    }

    public function restored(Cart $cart)
    {
        //
    }

    public function forceDeleted(Cart $cart)
    {
    }
}
