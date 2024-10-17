<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\ProductLineRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\ProductLine\ShowProductLinesRequest;

class ProductLineController extends BaseController
{
    /**
     *  @var ProductLineRepository
     */
    protected $repository;

    /**
     * ProductLineController constructor.
     *
     * @param ProductLineRepository $repository
     */
    public function __construct(ProductLineRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show product lines.
     *
     * @param ShowProductLinesRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showProductLines(ShowProductLinesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showProductLines());
    }

    /**
     * Show product line.
     *
     * @param string $productLineId
     * @return JsonResponse
     */
    public function showProductLine(string $productLineId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showProductLine($productLineId));
    }
}
