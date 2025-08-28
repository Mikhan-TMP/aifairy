<?php

namespace App\Controllers\Api\V1\Authentication;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserLogin extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
    }

    /******************************************************************
     * Handles user login.
     *
     * Checks if the given email and password matches a valid user.
     * If valid, generates a JWT token.
     * Returns a JSON response with the message and the JWT token.
     *
     * @return ResponseInterface
     ******************************************************************/
    public function handleUserLogin(){
        $data = $this->request->getJSON();
        $email = $data->email ?? null;
        $password = $data->password ?? null;

        if (!$email || !$password) {
            return $this->response->setJSON([
                'message' => 'Email and password are required.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //check if email exists
        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            return $this->response->setJSON([
                'message' => 'User not found.'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //verify password
        if (!password_verify($password, $user['password'])) {
            return $this->response->setJSON([
                'message' => 'Invalid password.'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        //generate JWT token
        $token = $this->generateJWT($user['user_id']);
        if (!$token) {
            return $this->response->setJSON([
                'message' => 'Failed to generate token.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Login successful.',
            'token' => $token
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }


    
    /** *********************************************************
     * Generate a JWT token from the given user ID.
     * @param int $userId ID of the user to generate token for.
     * @return string|null JWT token, or null if failed.
    ************************************************************/
    private function generateJWT($userId) {
        $key = getenv('JWT_SECRET');
        $payload = [
            'iss' => 'aifairy', // Issuer
            'aud' => 'aifairy', // Audience
            'iat' => time(),    // Issued at
            'exp' => time() + 3600, // Expires in 1 hour
            'sub' => $userId
        ];
        return JWT::encode($payload, $key, 'HS256');
    }
}
