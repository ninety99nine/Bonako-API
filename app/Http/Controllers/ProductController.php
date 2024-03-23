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
        return $this->prepareOutput($this->repository->get());
    }

    public function show(Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->showProduct());
    }

    public function update(UpdateProductRequest $request, Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->updateProduct($request));
    }

    public function confirmDelete(Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->generateDeleteConfirmationCode());
    }

    public function delete(Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->delete());
    }

    public function showPhoto(Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->showPhoto());
    }

    public function updatePhoto(UpdatePhotoRequest $request, Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->updatePhoto($request), Response::HTTP_CREATED);
    }

    public function deletePhoto(Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->removeExistingPhoto());
    }

    public function showVariations(ShowVariationsRequest $request, Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->showVariations($request));
    }

    public function createVariations(CreateVariationsRequest $request, Store $store, Product $product)
    {
        return $this->prepareOutput($this->setModel($product)->createVariations($request), Response::HTTP_CREATED);
    }
}
