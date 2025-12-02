<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function getAll($perPage = 10)
    {
        return $this->users->getAll(['per_page' => $perPage]);
    }

    public function getById($id)
    {
        return $this->users->find($id);
    }

    public function create(array $data)
    {
        $data['password'] = bcrypt($data['password']);
        return $this->users->create($data);
    }

    public function update($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        return $this->users->update($id, $data);
    }

    public function delete($id)
    {
        return $this->users->delete($id);
    }
}
