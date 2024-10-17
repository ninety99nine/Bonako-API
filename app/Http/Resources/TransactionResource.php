<?php

namespace App\Http\Resources;

use App\Repositories\UserRepository;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\PaymentMethodRepository;

class TransactionResource extends BaseResource
{
    protected $resourceRelationships = [
        'paymentMethod' => PaymentMethodRepository::class,
        'manuallyVerifiedByUser' => UserRepository::class,
        'requestedByUser' => UserRepository::class,
    ];

    public function setLinks()
    {
        $transaction = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.transaction', route('show.transaction', ['transactionId' => $transaction->id])),
            new ResourceLink('update.transaction', route('update.transaction', ['transactionId' => $transaction->id])),
            new ResourceLink('delete.transaction', route('delete.transaction', ['transactionId' => $transaction->id])),
            new ResourceLink('show.transaction.proof.of.payment.photo', route('show.transaction.proof.of.payment.photo', ['transactionId' => $transaction->id])),
            new ResourceLink('upload.transaction.proof.of.payment.photo', route('upload.transaction.proof.of.payment.photo', ['transactionId' => $transaction->id])),
            new ResourceLink('delete.transaction.proof.of.payment.photo', route('delete.transaction.proof.of.payment.photo', ['transactionId' => $transaction->id])),
        ];
    }
}
