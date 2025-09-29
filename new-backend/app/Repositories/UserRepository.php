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

    /**
     * Authenticates user
     */
    public function authenticate(string $email, string $password): ?UserEntity
    {
        $user = $this->userModel->where('email', $email)->where('is_deleted', false)->first();
        if (!$user instanceof UserEntity || !password_verify($password, $user->getPassword())) {
            return null;
        }
        $user->setPassword('');
        return $user;
    }

    /**
     * Create user
     */
    public function create(UserEntity $user): ?UserEntity
    {
        try {
            // check if email already exists
            $existingUser = $this->userModel->where('email', $user->getEmail())
                                            ->where('is_deleted', false)
                                            ->first();
            if ($existingUser) {
                return null;
            }

            // create new user
            $this->userModel->insert($user);
            $user->setId($this->userModel->getInsertID());
            $user->setPassword('');
            return $user;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Updates user
     */
    public function updateUser(UserEntity $user): bool
    {
        try {
            return (bool) $this->userModel->update($user->getId(), $user);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Deletes user
     */
    public function deleteUser(int $id): bool
    {
        try {
            return (bool) $this->userModel->update($id, ['is_deleted' => true]);
        } catch (Throwable) {
            return false;
        }
    }
}