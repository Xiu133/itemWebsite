<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function create(array $data): User
    {
        return $this->model->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'customer',
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByEmailAndRole(string $email, string $role): ?User
    {
        return $this->model
            ->where('email', $email)
            ->where('role', $role)
            ->first();
    }
}
