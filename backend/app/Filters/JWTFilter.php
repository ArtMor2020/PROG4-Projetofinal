<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return service('response')
                ->setJSON(['error' => 'Token ausente ou inválido'])
                ->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $key = getenv('jwt.secret');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Make user info available in request
            $request->user = $decoded->data;
        } catch (Exception $e) {
            return service('response')
                ->setJSON(['error' => 'Token inválido: ' . $e->getMessage()])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed here
    }
}
