<?php

namespace App\Http\Resources;

use App\Repositories\UserRepository;
use App\Http\Resources\BaseResource;
use App\Repositories\ShortcodeRepository;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\PaymentMethodRepository;

class TransactionResource extends BaseResource
{
    protected $resourceRelationships = [
        'activePaymentShortcode' => ShortcodeRepository::class,
        'paymentMethod' => PaymentMethodRepository::class,
        'requestedByUser' => UserRepository::class,
        'verifiedByUser' => UserRepository::class,
        'payedByUser' => UserRepository::class,
    ];

    public function setLinks()
    {
        $routeNamePrefix = 'transaction.';
        $transactionrId = $this->resource->id;
        $params = ['transaction' => $transactionrId];

        $this->resourceLinks = [
            new ResourceLink('self', route($routeNamePrefix.'show', $params), 'The transaction'),
            new ResourceLink('delete.transaction', route($routeNamePrefix.'delete', $params), 'Delete transaction'),
            new ResourceLink('show.proof.of.payment.photo', route($routeNamePrefix.'proof.of.payment.photo.show', $params), 'Show transaction proof of payment photo'),
            new ResourceLink('update.proof.of.payment.photo', route($routeNamePrefix.'proof.of.payment.photo.update', $params), 'Update transaction proof of payment photo'),
            new ResourceLink('delete.proof.of.payment.photo', route($routeNamePrefix.'proof.of.payment.photo.delete', $params), 'Delete transaction proof of payment photo'),
        ];
    }
}
