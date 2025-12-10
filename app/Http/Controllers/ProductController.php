<?php

namespace App\Http\Controllers;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Repositories\ProductRepository;
use App\Http\Responses\ApiResponse;
use App\Http\Responses\ProductResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(ProductFilterRequest $request)
    {
        $filters = $request->validated();
        $paginated = $this->repo->getAll($filters);

        return ApiResponse::success('Products retrieved', $paginated);
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = $this->repo->create($request->validated());
        return ApiResponse::success('Product created successfully', new ProductResponse($product), 201);
    }

    public function show($id): JsonResponse
    {
        $product = $this->repo->find($id);
        if (!$product) {
            return ApiResponse::error('Product not found', null, 404);
        }
        return ApiResponse::success('Product retrieved successfully', new ProductResponse($product));
    }

    public function update(ProductUpdateRequest $request, $id): JsonResponse
    {
        $product = $this->repo->find($id);
        if (!$product) {
            return ApiResponse::error('Product not found', null, 404);
        }
        $updatedProduct = $this->repo->update($id, $request->validated());
        return ApiResponse::success('Product updated successfully', new ProductResponse($updatedProduct));
    }

    public function destroy($id): JsonResponse
    {
        $product = $this->repo->find($id);
        if (!$product) {
            return ApiResponse::error('Product not found', null, 404);
        }
        $this->repo->delete($id);
        return ApiResponse::success('Product deleted successfully', null);
    }
}
