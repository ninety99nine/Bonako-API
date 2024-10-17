<?php

namespace App\Http\Controllers;

use App\Enums\Association;
use Illuminate\Http\JsonResponse;
use App\Repositories\ReviewRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Review\ShowReviewsRequest;
use App\Http\Requests\Models\Review\CreateReviewRequest;
use App\Http\Requests\Models\Review\UpdateReviewRequest;
use App\Http\Requests\Models\Review\DeleteReviewsRequest;

class ReviewController extends BaseController
{
    /**
     *  @var ReviewRepository
     */
    protected $repository;

    /**
     * ReviewController constructor.
     *
     * @param ReviewRepository $repository
     */
    public function __construct(ReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show reviews.
     *
     * @param ShowReviewsRequest $request
     * @return JsonResponse
     */
    public function showReviews(ShowReviewsRequest $request): JsonResponse
    {
        if($request->storeId) {
            $request->merge(['store_id' => $request->storeId]);
        }

        return $this->prepareOutput($this->repository->showReviews($request->all()));
    }

    /**
     * Create review.
     *
     * @param CreateReviewRequest $request
     * @return JsonResponse
     */
    public function createReview(CreateReviewRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createReview($request->all()));
    }

    /**
     * Delete reviews.
     *
     * @param DeleteReviewsRequest $request
     * @return JsonResponse
     */
    public function deleteReviews(DeleteReviewsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteReviews($request->all()));
    }

    /**
     * Show review rating options.
     *
     * @return JsonResponse
     */
    public function showReviewRatingOptions(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showReviewRatingOptions());
    }

    /**
     * Show review.
     *
     * @param string $reviewId
     * @return JsonResponse
     */
    public function showReview(string $reviewId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showReview($reviewId));
    }

    /**
     * Update review.
     *
     * @param UpdateReviewRequest $request
     * @param string $reviewId
     * @return JsonResponse
     */
    public function updateReview(UpdateReviewRequest $request, string $reviewId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateReview($reviewId, $request->all()));
    }

    /**
     * Delete review.
     *
     * @param string $reviewId
     * @return JsonResponse
     */
    public function deleteReview(string $reviewId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteReview($reviewId));
    }
}
