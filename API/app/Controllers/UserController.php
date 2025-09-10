<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Repositories\UserRepository;
use App\Entities\UserEntity;
use Firebase\JWT\JWT;

class UserController extends ResourceController
{
    protected $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function authenticate()
    {
        $payload = $this->request->getJSON(true);
        $user = $this->userRepository->authenticate($payload['email'] ?? '', $payload['password'] ?? '');

        if (!$user) {
            return $this->fail('Credenciais inválidas', 401);
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
}