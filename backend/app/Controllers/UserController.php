<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Repositories\UserRepository;
use App\Entities\UserEntity;
use Firebase\JWT\JWT;

class UserController extends ResourceController
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * User login (returns JWT)
     */
    public function authenticate()
    {
        $payload = $this->request->getJSON(true);
        $user = $this->userRepository->authenticate($payload['email'] ?? '', $payload['password'] ?? '');

        if (!$user) {
            return $this->fail('Invalid credentials', 401);
        }

        $key = getenv('jwt.secret');
        $iat = time();
        $exp = $iat + (int)getenv('jwt.expiration');

        $jwtPayload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => $iat,
            'exp' => $exp,
            'data' => [
                'userId' => $user->getId()
            ]
        ];

        $token = JWT::encode($jwtPayload, $key, 'HS256');

        $userArray = $user->toArray();
        unset($userArray['password']);

        return $this->respond([
            'status' => 'success',
            'user'   => $userArray,
            'token'  => $token
        ]);
    }

    /**
     * User registration
     */
    public function register()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            return $this->failValidationErrors('Email, password, and name are required.');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = new UserEntity($data);
        $created = $this->userRepository->create($user);

        return $created
            ? $this->respondCreated($created)
            : $this->fail('Failed to register user.');
    }

    /**
     * Update logged-in user info
     */
    public function update($id = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        if (!$loggedUserId || $id <= 0) {
            return $this->failValidationErrors('User ID is required and user must be authenticated.');
        }

        // Users can only update their own account
        if ($loggedUserId !== $id) {
            return $this->failForbidden('You cannot update another user\'s account.');
        }

        $data = $this->request->getJSON(true);
        $user = new UserEntity($data);
        $user->setId($id);

        if (!empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }

        $updated = $this->userRepository->updateUser($user);

        return $updated
            ? $this->respondUpdated($user)
            : $this->fail("Failed to update user {$id}.");
    }

    /**
     * Soft delete logged-in user
     */
    public function delete($id = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        if (!$loggedUserId || $id <= 0) {
            return $this->failValidationErrors('User ID is required and user must be authenticated.');
        }

        // Users can only delete their own account
        if ($loggedUserId !== $id) {
            return $this->failForbidden('You cannot delete another user\'s account.');
        }

        $deleted = $this->userRepository->deleteUser($id);

        return $deleted
            ? $this->respondDeleted(['message' => "User {$id} deleted successfully."])
            : $this->fail("Failed to delete user {$id}.");
    }
}
