<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return service('response')
                ->setJSON(['message' => 'Token required'])
                ->setStatusCode(401);
        }

        $token = $matches[1];
        $key = getenv('JWT_SECRET') ?: '';

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            // Optionally, set user info in request
        } catch (\Exception $e) {
            return service('response')
                ->setJSON(['message' => 'Invalid or expired token'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
    }
}