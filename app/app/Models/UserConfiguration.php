<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar configurações de usuários.
 */
class UserConfiguration extends Model
{
    protected $table = 'user_configurations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'bank_type',
        'pagarme_api_key',
        'inter_client_id',
        'inter_client_secret',
        'inter_certificate_path',
        'inter_certificate_password',
        'c6_api_key',
        'bling_api_key',
        'bling_webhook_url',
        'boleto_instructions',
        'email_webhook_url',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Obtém as configurações de um usuário.
     *
     * @param int $userId
     * @return array|null
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Atualiza as configurações de um usuário.
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateByUserId(int $userId, array $data): bool
    {
        $data['user_id'] = $userId;

        $existing = $this->getByUserId($userId);

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }
}

