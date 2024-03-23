<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\TransactionRepository;
use App\Http\Requests\Models\UncancelRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Requests\Models\Transaction\CancelTransactionRequest;
use App\Http\Requests\Models\Transaction\UpdateProofOfPaymentPhotoRequest;
use App\Models\Store;

class TransactionController extends BaseController
{
    /**
     *  @var TransactionRepository
     */
    protected $repository;

    public function showTransactions()
    {
        return $this->prepareOutput($this->repository->showTransactions());
    }

    public function show(Store $store, Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->show());
    }

    public function confirmDelete(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->generateDeleteConfirmationCode());
    }

    public function delete(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->deleteTransaction());
    }

    public function cancel(CancelTransactionRequest $request, Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->cancel($request));
    }

    public function uncancel(UncancelRequest $request, Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->uncancel($request));
    }

    public function showCancellationReasons(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->showCancellationReasons());
    }

    public function generatePaymentShortcode(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->generatePaymentShortcode());
    }

    public function expirePaymentShortcode(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->expirePaymentShortcode());
    }

    public function showProofOfPaymentPhoto(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->showProofOfPaymentPhoto());
    }

    public function updateProofOfPaymentPhoto(UpdateProofOfPaymentPhotoRequest $request, Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->updateProofOfPaymentPhoto($request), Response::HTTP_CREATED);
    }

    public function deleteProofOfPaymentPhoto(Transaction $transaction)
    {
        return $this->prepareOutput($this->setModel($transaction)->removeExistingProofOfPaymentPhoto());
    }
}
