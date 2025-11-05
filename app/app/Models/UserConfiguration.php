<?php

namespace App\Models;

use CodeIgniter\Model;

class UserConfiguration extends Model
{
    protected $table = 'user_configurations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'pagarme_api_key',
        'inter_client_id',
        'inter_client_secret',
        'inter_certificate_path',
        'inter_certificate_password',
        'c6_api_key',
        'c6_api_secret',
        'bling_api_key',
        'bling_webhook_url',
        'boleto_instructions',
        'email_webhook_url',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];

    /**
     * Obtém a configuração de um usuário.
     *
     * @param int $userId
     * @return array|null
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }
}

