<?php

namespace App\Repositories\Contracts\Auth;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function create(array $data): User;

    public function findByEmail(string $email): ?User;

    public function findByEmailAndRole(string $email, string $role): ?User;
}
