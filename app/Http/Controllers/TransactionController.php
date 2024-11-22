<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\TransactionRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Transaction\ShowTransactionsRequest;
use App\Http\Requests\Models\Transaction\DeleteTransactionsRequest;

class TransactionController extends BaseController
{
    /**
     *  @var TransactionRepository
     */
    protected $repository;

    /**
     * TransactionController constructor.
     *
     * @param TransactionRepository $repository
     */
    public function __construct(TransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show transactions.
     *
     * @param ShowTransactionsRequest $request
     * @return JsonResponse
     */
    public function showTransactions(ShowTransactionsRequest $request): JsonResponse
    {
        if($request->storeId) {
            $request->merge(['store_id' => $request->storeId]);
        }else if($request->orderId) {
            $request->merge(['order_id' => $request->orderId]);
        }else if($request->pricingPlanId) {
            $request->merge(['pricing_plan_id' => $request->pricingPlanId]);
        }

        return $this->prepareOutput($this->repository->showTransactions($request->all()));
    }

    /**
     * Delete transactions.
     *
     * @param DeleteTransactionsRequest $request
     * @return JsonResponse
     */
    public function deleteTransactions(DeleteTransactionsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteTransactions($request->all()));
    }

    /**
     * Show transaction.
     *
     * @param string $transactionId
     * @return JsonResponse
     */
    public function showTransaction(string $transactionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showTransaction($transactionId));
    }

    /**
     * Delete transaction.
     *
     * @param string $transactionId
     * @return JsonResponse
     */
    public function deleteTransaction(string $transactionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteTransaction($transactionId));
    }

    /**
     * Renew transaction payment link.
     *
     * @param string $transactionId
     * @return JsonResponse
     */
    public function renewPaymentLink(string $transactionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->renewPaymentLink($transactionId));
    }

    /**
     * Show transaction proof of payment photo.
     *
     * @param string $transactionId
     * @return JsonResponse
     */
    public function showTransactionProofOfPaymentPhoto(string $transactionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showTransactionProofOfPaymentPhoto($transactionId));
    }

    /**
     * Upload transaction proof of payment photo.
     *
     * @param string $transactionId
     * @return JsonResponse
     */
    public function uploadTransactionProofOfPaymentPhoto(string $transactionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->uploadTransactionProofOfPaymentPhoto($transactionId));
    }

    /**
     * Delete transaction proof of payment photo.
     *
     * @param string $transactionId
     * @return JsonResponse
     */
    public function deleteTransactionProofOfPaymentPhoto(string $transactionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteTransactionProofOfPaymentPhoto($transactionId));
    }
}
