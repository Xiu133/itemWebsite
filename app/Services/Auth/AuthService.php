<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $repository;

    public function __construct(AuthRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function registerCustomer(array $data): User
    {
        $data['role'] = 'customer';
        return $this->repository->create($data);
    }

    public function registerMerchant(array $data): User
    {
        $data['role'] = 'merchant';
        return $this->repository->create($data);
    }

    public function attemptLogin(array $credentials, string $role): bool
    {
        $user = $this->repository->findByEmailAndRole($credentials['email'], $role);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        Auth::login($user, $credentials['remember'] ?? false);
        return true;
    }

    public function getLoginRedirectPath(User $user): string
    {
        return match ($user->role) {
            'merchant' => '/',
            'admin' => '/dashboard',
            default => '/',
        };
    }

    protected function validateRegistration(array $data): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
