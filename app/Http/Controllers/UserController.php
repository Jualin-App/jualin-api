<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Responses\ApiResponse;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return ApiResponse::success('Users fetched', $this->service->getAll());
    }

    public function show($id)
    {
        return ApiResponse::success('User fetched', $this->service->getById($id));
    }

    public function store(StoreUserRequest $request)
    {
        return ApiResponse::success('User created', $this->service->create($request->validated()), 201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        return ApiResponse::success('User updated', $this->service->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return ApiResponse::success('User deleted');
    }
}
