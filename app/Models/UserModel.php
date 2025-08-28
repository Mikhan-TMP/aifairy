<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_id',
        'email',
        'password',
        'status',
        'created_at'
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


    public function createUser($verificationId){
        //using the verification id, get the user details such as email, and password.
        $verificationModel = new UserVerificationModel();
        $verification = $verificationModel->find($verificationId);
        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Invalid verification ID.'
            ];
        }

        $data = [
            'role_id' => 1, // Default to user
            'email' => $verification['email'],
            'password' => $verification['password'],
            'status' => 1, //1 for active
            'created_at' => date('Y-m-d H:i:s')
        ];

        //check if email exists before creating user
        if ($this->where('email', $verification['email'])->first()) {
            return [
                'status' => false,
                'message' => 'Email already exists.'
            ];
        }

        $createUser = $this->insert($data);
        //get the created userID
        $userID = $this->insertID();
        return [
            'status' => $createUser !== false,
            'message' => $createUser !== false ? 'User created successfully.' : 'Failed to create user.',
            'id' => $userID
        ];
    }
}
