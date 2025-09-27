<?php

namespace App\Repositories;

use App\Models\UserModel;
use App\Entities\UserEntity;
use Throwable;

class UserRepository
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function authenticate(string $email, string $password): ?UserEntity
    {
        $user = $this->userModel->where('email', $email)->where('is_deleted', false)->first();
        if (!$user instanceof UserEntity || !password_verify($password, $user->getPassword())) {
            return null;
        }
        $user->setPassword('');
        return $user;
    }

    public function create(UserEntity $user): ?UserEntity
    {
        try {
            $this->userModel->insert($user);
            $user->setId($this->userModel->getInsertID());
            $user->setPassword('');
            return $user;
        } catch (Throwable) {
            return null;
        }
    }

    public function updateUser(UserEntity $user): bool
    {
        try {
            return (bool) $this->userModel->update($user->getId(), $user);
        } catch (Throwable) {
            return false;
        }
    }

    public function deleteUser(int $id): bool
    {
        try {
            return (bool) $this->userModel->update($id, ['is_deleted' => true]);
        } catch (Throwable) {
            return false;
        }
    }
}