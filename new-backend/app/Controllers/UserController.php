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
        // gets login credentials
        $payload = $this->request->getJSON(true);
        $user = $this->userRepository->authenticate(
            $payload['email'] ?? '', 
            $payload['password'] ?? ''
        );

        if (!$user) {
            return $this->fail('Invalid credentials', 401);
        }

        // forms jwt structure
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

        // makes token for user
        $token = JWT::encode($jwtPayload, $key, 'HS256');

        // sets secure cookie
        // can use this or json response
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

        // clears password
        $userArray = $user->toArray();
        unset($userArray['password']);

        // returns token
        return $this->respond([
            'status' => 'success',
            'user'   => $userArray,
            'token'  => $token         // this or cookie
        ]);
    }

    /**
     * User registration
    */
    public function register()
    {
        // gets credentials
        $data = $this->request->getJSON(true);

        // validates credentials
        if (empty($data['email']) || empty($data['password'])) {
            return $this->failValidationErrors('Email and password are required.');
        }

        // hashes passord
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // creates userentity and registers user
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
     * Used with the cookie token
     */
    public function session()
    {
        // gets user in session
        $user = $this->request->user ?? null;

        // checks session
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
        // gets user in session
        $loggedUserId = $this->request->user->userId ?? null;
        
        // checks session
        if (!$loggedUserId) {
            return $this->failValidationErrors('User ID is required and user must be authenticated.');
        }

        // gets data and makes entity
        $data = $this->request->getJSON(true);
        $user = new UserEntity($data);
        $user->setId($loggedUserId);

        // if password update, hashes it
        if (!empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }

        // updates user data
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
        // gets session
        $loggedUserId = $this->request->user->userId ?? null;

        // checks session
        if (!$loggedUserId) {
            return $this->failValidationErrors('User ID is required and user must be authenticated.');
        }

        // deletes user
        $deleted = $this->userRepository->deleteUser($loggedUserId);

        return $deleted
            ? $this->respondDeleted(['message' => "User {$loggedUserId} deleted successfully."])
            : $this->fail("Failed to delete user {$loggedUserId}.");
    }
}
