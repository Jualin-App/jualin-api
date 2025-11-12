<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function query()
    {
        return Product::query();
    }

    public function filter(array $filters)
    {
        $q = $this->query();

        if (!empty($filters['category'])) {
            $q->where('category', $filters['category']);
        }

        if (!empty($filters['location'])) {
            // assuming seller's city or region is stored on users table and relation exists
            $q->whereHas('seller', function ($sq) use ($filters) {
                $sq->where('city', $filters['location'])->orWhere('region', $filters['location']);
            });
        }

        if (!empty($filters['name'])) {
            $q->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['price_min'])) {
            $q->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $q->where('price', '<=', $filters['price_max']);
        }

        // Allow ordering only by a safe whitelist to avoid injection and errors
        $allowedSort = ['price', 'name', 'created_at'];
        if (!empty($filters['sort_by']) && in_array($filters['sort_by'], $allowedSort, true)) {
            $direction = in_array(strtolower($filters['sort_dir'] ?? 'asc'), ['asc', 'desc'], true)
                ? strtolower($filters['sort_dir'])
                : 'asc';
            $q->orderBy($filters['sort_by'], $direction);
        }

        return $q;
    }
}
