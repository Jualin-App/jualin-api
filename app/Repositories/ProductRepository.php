<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Get all products with optional filters and pagination.
     *
     * Supported filters:
     * - category
     * - location (matches seller.city or seller.region)
     * - name (partial match)
     * - price_min
     * - price_max
     * - sort_by (allowed: price, name, created_at)
     * - sort_dir (asc|desc)
     * - per_page (int)
     * - seller_id (filter by seller/owner id)
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $q = Product::query();

        if (!empty($filters['seller_id'])) {
            $q->where('seller_id', $filters['seller_id']);
        }

        if (!empty($filters['category'])) {
            $q->where('category', $filters['category']);
        }

        if (!empty($filters['location'])) {
            $location = $filters['location'];
            $q->whereHas('seller', function ($sq) use ($location) {
                $sq->where('city', $location)->orWhere('region', $location);
            });
        }

        if (!empty($filters['name'])) {
            $q->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $q->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $q->where('price', '<=', $filters['price_max']);
        }

        $allowedSort = ['price', 'name', 'created_at'];
        if (!empty($filters['sort_by']) && in_array($filters['sort_by'], $allowedSort, true)) {
            $direction = 'asc';
            if (!empty($filters['sort_dir']) && in_array(strtolower($filters['sort_dir']), ['asc', 'desc'], true)) {
                $direction = strtolower($filters['sort_dir']);
            }
            $q->orderBy($filters['sort_by'], $direction);
        } else {
            $q->orderByDesc('created_at');
        }

        $perPage = isset($filters['per_page']) && (int) $filters['per_page'] > 0
            ? (int) $filters['per_page']
            : 10;

        return $q->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->find($id);
        if (!$product) {
            return null;
        }

        $product->fill($data);
        $product->save();

        return $product;
    }

    public function delete(int $id): bool
    {
        $product = $this->find($id);
        if (!$product) {
            return false;
        }

        return (bool) $product->delete();
    }
}
