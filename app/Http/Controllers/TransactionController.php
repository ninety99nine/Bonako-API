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

    public function index(Request $request)
    {
        return response($this->repository->get()->transform(), Response::HTTP_OK);
    }

    public function show(Store $store, Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->show()->transform(), Response::HTTP_OK);
    }

    public function confirmDelete(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->generateDeleteConfirmationCode(), Response::HTTP_OK);
    }

    public function delete(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->deleteTransaction(), Response::HTTP_OK);
    }

    public function cancel(CancelTransactionRequest $request, Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->cancel($request)->transform(), Response::HTTP_OK);
    }

    public function uncancel(UncancelRequest $request, Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->uncancel($request)->transform(), Response::HTTP_OK);
    }

    public function showCancellationReasons(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->showCancellationReasons(), Response::HTTP_OK);
    }

    public function generatePaymentShortcode(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->generatePaymentShortcode()->transform(), Response::HTTP_OK);
    }

    public function expirePaymentShortcode(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->expirePaymentShortcode()->transform(), Response::HTTP_OK);
    }

    public function showProofOfPaymentPhoto(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->showProofOfPaymentPhoto(), Response::HTTP_OK);
    }

    public function updateProofOfPaymentPhoto(UpdateProofOfPaymentPhotoRequest $request, Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->updateProofOfPaymentPhoto($request), Response::HTTP_CREATED);
    }

    public function deleteProofOfPaymentPhoto(Transaction $transaction)
    {
        return response($this->repository->setModel($transaction)->removeExistingProofOfPaymentPhoto(), Response::HTTP_OK);
    }
}
