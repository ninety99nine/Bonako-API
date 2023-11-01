<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\ProductRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Product\UpdatePhotoRequest;
use App\Http\Requests\Models\Product\UpdateProductRequest;
use App\Http\Requests\Models\Product\CreateVariationsRequest;
use App\Http\Requests\Models\Product\ShowVariationsRequest;

class ProductController extends BaseController
{
    /**
     *  @var ProductRepository
     */
    protected $repository;

    public function index(Request $request)
    {
        return response($this->repository->get()->transform(), Response::HTTP_OK);
    }

    public function show(Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->showProduct()->transform(), Response::HTTP_OK);
    }

    public function update(UpdateProductRequest $request, Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->updateProduct($request)->transform(), Response::HTTP_OK);
    }

    public function confirmDelete(Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->generateDeleteConfirmationCode(), Response::HTTP_OK);
    }

    public function delete(Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->delete(), Response::HTTP_OK);
    }

    public function showPhoto(Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->showPhoto(), Response::HTTP_OK);
    }

    public function updatePhoto(UpdatePhotoRequest $request, Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->updatePhoto($request), Response::HTTP_CREATED);
    }

    public function deletePhoto(Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->removeExistingPhoto(), Response::HTTP_OK);
    }

    public function showVariations(ShowVariationsRequest $request, Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->showVariations($request)->transform(), Response::HTTP_OK);
    }

    public function createVariations(CreateVariationsRequest $request, Store $store, Product $product)
    {
        return response($this->repository->setModel($product)->createVariations($request)->transform(), Response::HTTP_CREATED);
    }
}
