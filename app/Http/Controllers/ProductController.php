<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Responses\ProductResponse;
use App\Http\Responses\ApiResponse;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(): JsonResponse
    {
        $products = $this->productRepository->getAll();
        return ApiResponse::success('Products retrieved successfully', ProductResponse::collection($products));
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());
        return ApiResponse::success('Product created successfully', new ProductResponse($product), 201);
    }

    public function show($id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return ApiResponse::error('Product not found', null, 404);
        }
        return ApiResponse::success('Product retrieved successfully', new ProductResponse($product));
    }

    public function update(ProductUpdateRequest $request, $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return ApiResponse::error('Product not found', null, 404);
        }
        $updatedProduct = $this->productRepository->update($id, $request->validated());
        return ApiResponse::success('Product updated successfully', new ProductResponse($updatedProduct));
    }

    public function destroy($id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return ApiResponse::error('Product not found', null, 404);
        }
        $this->productRepository->delete($id);
        return ApiResponse::success('Product deleted successfully', null);
    }
}