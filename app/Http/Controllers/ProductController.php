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

        Log::info('Product index request', [
            'filters' => $filters,
            'client'  => $request->ip(),
        ]);

        // Repo returns a LengthAwarePaginator
        $paginated = $this->repo->getAll($filters);

        // items as plain array (no nested pagination object)
        $items = $paginated->items();

        // build meta to match the desired JS pagination shape
        $meta = [
            'totalItems'  => $paginated->total(),
            'totalPages'  => $paginated->lastPage(),
            'currentPage' => $paginated->currentPage(),
            'limit'       => $paginated->perPage(),
            'sortBy'      => $filters['sort_by'] ?? 'created_at',
            'sortOrder'   => isset($filters['sort_dir']) ? strtolower($filters['sort_dir']) : 'desc',
        ];

        return ApiResponse::success('Products retrieved', [
            'meta' => $meta,
            'data' => $items,
        ]);
    }
}
