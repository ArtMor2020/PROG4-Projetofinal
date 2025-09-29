<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setJSON(['error' => 'Missing or invalid Authorization header'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = substr($authHeader, 7);
        $key = getenv('jwt.secret');

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            if (isset($decoded->data->userId)) {
                $decoded->data->userId = (int) $decoded->data->userId;
            }

            $request->user = $decoded->data;
        } catch (Exception $e) {
            return service('response')
                ->setJSON(['error' => 'Invalid or expired token', 'message' => $e->getMessage()])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
