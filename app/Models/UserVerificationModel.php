<?php

namespace App\Models;

use CodeIgniter\Model;

class UserVerificationModel extends Model
{
    protected $table            = 'user_verification';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'email',
        'password',
        'verification_code',
        'verification_expiration'
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

    public function createVerification(string $email, string $password, string $code, string $expiration): array
    {
        //hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $existing = $this->where('email', $email)->first();
        if ($existing) {
            // Check if the existing code is still active (not expired)
            if (isset($existing['verification_expiration']) && strtotime($existing['verification_expiration']) > time()) {
                // Code is still active, do not send or update
                return [
                    'status' => false,
                    'message' => 'Verification is still pending.'
                ];
            }
            // Update by primary key (id)
            $this->update($existing['id'], [
                'password' => $hashedPassword,
                'verification_code' => $code,
                'verification_expiration' => $expiration
            ]);
            return [
                'status' => true,
                'message' => 'Verification code updated successfully.'
            ];
        }
        // Else create a new verification record
        $data = [
            'email' => $email,
            'password' => $hashedPassword,
            'verification_code' => $code,
            'verification_expiration' => $expiration
        ];
        $afterInsert = $this->insert($data);
        return [
            'status' => $afterInsert !== false,
            'message' => $afterInsert !== false ? 'Verification created successfully.' : 'Failed to create verification.'
        ];
    }

    public function handleVerifyEmail(string $email, string $code)
    {
        $user = $this->where('email', $email)->first();
        if (!$user) {
            return [
                'status' => false,
                'message' => 'Email does not exist. Please register first.'
            ];
        }

        $verification = $this->where('email', $email)
            ->where('verification_code', $code)
            ->first();
        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Incorrect Verification Code.'
            ];
        }

        // Check if the code is expired
        if (strtotime($verification['verification_expiration']) < time()) {
            return [
                'status' => false,
                'message' => 'Verification code has expired.'
            ];
        }

        //empty the verification code and expiration
        $this->update($verification['id'], [
            'verification_code' => null,
            'verification_expiration' => null
        ]);

        return [
            'status' => true,
            'message' => 'Verification code is valid.',
            'id' => $verification['id']
        ];
    }
}
