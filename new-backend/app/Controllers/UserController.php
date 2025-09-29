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
        $user = $this->userRepository->authenticate(
            $payload['email'] ?? '', 
            $payload['password'] ?? ''
        );

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
                'userId' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ];

        $token = JWT::encode($jwtPayload, $key, 'HS256');

        /*
        setcookie(
        'token',
        $token,
        [
            'expires' => $exp,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
            ]
        );*/

        $userArray = $user->toArray();
        unset($userArray['password']);

        return $this->respond([
            'status' => 'success',
            'user'   => $userArray,
            'token'  => $token         // debug
        ]);
    }

    /**
     * User registration
    */
    public function register()
    {
        $data = $this->request->getJSON(true);

        log_message('debug', 'Registering user with data: ' . json_encode($data));

        if (empty($data['email']) || empty($data['password'])) {
            return $this->failValidationErrors('Email and password are required.');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = new UserEntity($data);
        $created = $this->userRepository->create($user);

        if (!$created) {
            return $this->fail('Failed to register user.');
        }

        // Respond with JSON
        return $this->respondCreated([
            'status' => 'success',
            'user' => $user->toArray()
        ]);
    }

    /**
     * Session info
     */
    public function session()
    {
        $user = $this->request->user ?? null;

        if (!$user) {
            return $this->failUnauthorized('No user in request (invalid token)');
        }

        return $this->respond([
            'status' => 'success',
            'user'   => $user,
        ]);
    }

    /**
     * Update logged-in user info
     */
    public function update($id = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        
        if (!$loggedUserId) {
            return $this->failValidationErrors('User ID is required and user must be authenticated.');
        }

        $data = $this->request->getJSON(true);
        $user = new UserEntity($data);
        $user->setId($loggedUserId);

        if (!empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }

        $updated = $this->userRepository->updateUser($user);

        return $updated
            ? $this->respondUpdated($user->toPublicArray())
            : $this->fail("Failed to update user {$loggedUserId}.");
    }

    /**
     * Soft delete logged-in user
     */
    public function delete($id = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;

        if (!$loggedUserId) {
            return $this->failValidationErrors('User ID is required and user must be authenticated.');
        }

        $deleted = $this->userRepository->deleteUser($loggedUserId);

        return $deleted
            ? $this->respondDeleted(['message' => "User {$loggedUserId} deleted successfully."])
            : $this->fail("Failed to delete user {$loggedUserId}.");
    }
}
