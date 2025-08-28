<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\UserModel;

class UserProfileModel extends Model
{
    protected $table            = 'user_profile';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'first_name',
        'last_name',
        'profile_image',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**********************************************************************************************************************************************
     * Creates a user profile.
     * 
     * Verifies if the user exists, then creates a user profile with an empty first name, last name, and profile image.
     * 
     * @param int $userID The ID of the user whose profile is to be created.
     * 
     * @return array Returns an array with status and message. If the user profile is created successfully, status is true, otherwise it is false.
     ***********************************************************************************************************************************************/
    public function createProfile($userID){
        //verify if the user exist
        $this->userModel = new UserModel();
        $user = $this->userModel->find($userID);
        if (!$user) {
            return [
                'status' => false,
                'message' => 'User does not exist.'
            ];
        }
        $data = [
            'user_id' => $userID,
            'first_name' => '',
            'last_name' => '',
            'profile_image' => '',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $createUserProfile = $this->insert($data);
        return [
            'status' => $createUserProfile !== false,
            'message' => $createUserProfile !== false ? 'User profile created successfully.' : 'Failed to create user profile.'
        ];
    }
    
}
