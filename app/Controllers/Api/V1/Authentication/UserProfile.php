<?php

namespace App\Controllers\Api\V1\Authentication;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UserProfile extends BaseController
{
    public function index()
    {
        //
    }

    /**************************************************************************
     * Handles user profile access.
     * THIS IS JUST FOR TESTING THE JWT -> Early Development of the API
     * This endpoint is protected by JWT authentication.
     * Returns a JSON response indicating successful access to the user profile.
     *
     * @return ResponseInterface
     ***************************************************************************/
    public function handleUserProfile(){
        return $this->response->setJSON([
            'message' => 'User profile accessed successfully.'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
