<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Repositories\ProductRepository;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * GET /v1/products
     * Query params:
     * - category
     * - location (city or region)
     * - name (partial match)
     * - price_min
     * - price_max
     * - sort_by (price, name, created_at)
     * - sort_dir (asc|desc)
     */
    public function index(ProductFilterRequest $request)
    {
        $filters = $request->validated();

        // Temporary debug log to compare requests from Insomnia vs browser
        try {
            Log::info('Product index request', [
                'filters' => $filters,
                'headers' => $request->header(),
                'ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        $query = $this->repo->filter($filters);

        // Return a simple collection (no pagination metadata) to match ApiResponse data shape
        $products = $query->get();

        return ApiResponse::success('Products retrieved', $products);
    }
}
