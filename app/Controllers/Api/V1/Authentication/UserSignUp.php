<?php

namespace App\Controllers\Api\V1\Authentication;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\UserVerificationModel;
use App\Models\UserProfileModel;

class UserSignUp extends BaseController
{
    protected $userModel;
    protected $userVerificationModel;
    protected $userProfileModel;

    
    /*******************************
     * Constructor.
     *
     * @return void
     *******************************/
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userVerificationModel = new UserVerificationModel();
        $this->userProfileModel = new UserProfileModel();
    }


    /***************************************************************************************************
     * Handles user registration.
     * Returns a JSON response with a message, indicating if the registration is successful or not.
     * If the email already exists, it will return a 400 bad request response.
     * If the request is missing email or password, it will return a 400 bad request response.
     * If the request is successful, it will return a 200 ok response.
     * If the request fails, it will return a 500 internal server error response.
     *
     * @return ResponseInterface
     ***************************************************************************************************/
    public function handleUsersignup(){
        $data = $this->request->getJSON();
        $email = $data->email ?? null;
        $password = $data->password ?? null;

        if (!$email || !$password) {
            return $this->response->setJSON([
                'message' => 'Email and password are required.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //check if email exists
        if ($this->userModel->where('email', $email)->first()) {
            return $this->response->setJSON([
                'message' => 'Email already exists.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //generate verification code
        $verificationData = $this->generateVerificationCode();
        if(!$verificationData) {
            return $this->response->setJSON([
                'message' => 'Failed to generate verification code.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        //start db transaction
        $db = db_connect();
        $db->transStart();

        //add the verification record
        $verificationModel = new UserVerificationModel();
        $verifyUser = $verificationModel->createVerification($email, $password,  $verificationData['code'], $verificationData['expiration']);
        if($verifyUser['status'] === false) {
            $db->transRollback();
            return $this->response
                ->setJSON([
                    'message' => $verifyUser['message']
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //use SMTP server to send to user's email the code.
        $sendVerification = $this->sendEmailVerification($email, $verificationData['code']);
        if(!$sendVerification) {
            $db->transRollback();
            return $this->response->setJSON([
                'message' => 'Failed to send verification email.'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $db->transCommit();
        return $this->response->setJSON([
            'message' => 'User registered successfully. Verification email sent.'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }


    
    /********************************************************************************************
     * Generates a verification code that expires in 5 minutes.
     * 
     * The code is a random 3 byte hex string.
     * The expiration is a string in the format 'Y-m-d H:i:s' that is 5 minutes in the future.
     * 
     * @return array
     *******************************************************************************************/
    private function generateVerificationCode(){
        $code = bin2hex(random_bytes(3));
        $expiration = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        return [
            'code' => $code,
            'expiration' => $expiration
        ];
    }



    /******************************************************************************
     * Sends a verification email to the given email address with the given code.
     * 
     * @param string $email The email address to send the verification to.
     * @param string $code The verification code to send.
     * @return bool True if the email was sent successfully, false otherwise.
     ******************************************************************************/
    private function sendEmailVerification($email, $code){
        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Email Verification');
        $emailService->setMessage('
            <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            padding: 20px;
                            color: #333;
                        }
                        .container {
                            background-color: #fff;
                            padding: 30px;
                            border-radius: 8px;
                            max-width: 600px;
                            margin: auto;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                        }
                        h1 {
                            color: #007BFF;
                        }
                        p {
                            font-size: 16px;
                        }
                        .code {
                            font-size: 28px;
                            font-weight: bold;
                            color: #28a745;
                            background-color: #e9f8ee;
                            padding: 15px 25px;
                            display: inline-block;
                            border-radius: 6px;
                            letter-spacing: 4px;
                            margin: 20px 0;
                        }
                        .footer {
                            font-size: 12px;
                            color: #777;
                            margin-top: 30px;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>AI Fairy - Verification Code</h1>
                        <p>We received a request to verify your email address for your AI Fairy account. Please use the verification code below to complete your registration:</p>
                        <div class="code">' . $code . '</div>
                        <p>If you did not request this verification, please ignore this email.</p>
                        <p class="footer">This verification code will expire in 5 minutes.</p>
                    </div>
                </body>
            </html>
        ');
        if (!$emailService->send()) {
            return false;
        } else {
            return true;
        }
    }

    

    /**************************************************************************************************
     * Handles user email verification.
     * 
     * Given an email and code, will verify the email address and create a user account and profile.
     * 
     * @return ResponseInterface
     **************************************************************************************************/
    public function handleUserVerify(){
        //get the email and code from the request
        $data = $this->request->getJSON();
        $email = $data->email ?? null;
        $code = $data->code ?? null;

        //start db transaction
        $db = db_connect();
        $db->transStart();

        $verification = $this->userVerificationModel->handleVerifyEmail($email, $code);
        if ($verification['status'] === false) {
            $db->transRollback();
            return $this->response->setJSON([
                'message' => $verification['message']
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //create the user account
        $createUser = $this->userModel->createUser($verification['id']);
        if ($createUser['status'] === false) {
            $db->transRollback();
            return $this->response->setJSON([
                'message' => $createUser['message']
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }


        //create the user profile
        $userID = $createUser['id'];
        $createProfile = $this->userProfileModel->createProfile($userID);
        if ($createProfile['status'] === false) {
            $db->transRollback();
            return $this->response->setJSON([
                'message' => $createProfile['message']
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        $db->transCommit(); 

        return $this->response->setJSON([
            'message' => 'Email verified successfully.'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
    
}
