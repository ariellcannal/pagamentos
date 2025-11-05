<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar chaves API.
 */
class ApiKey extends Model
{
    protected $table = 'api_keys';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'api_key',
        'alias',
        'webhook_url',
        'is_active',
        'last_used_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Gera uma nova chave API.
     *
     * @param int $userId
     * @param string $alias
     * @param string $webhookUrl
     * @return int
     */
    public function generateApiKey(int $userId, string $alias, string $webhookUrl): int
    {
        $apiKey = bin2hex(random_bytes(32));

        $data = [
            'user_id' => $userId,
            'api_key' => $apiKey,
            'alias' => $alias,
            'webhook_url' => $webhookUrl,
            'is_active' => true,
        ];

        return $this->insert($data);
    }

    /**
     * Obtém uma chave API pelo token.
     *
     * @param string $apiKey
     * @return array|null
     */
    public function getByApiKey(string $apiKey): ?array
    {
        return $this->where('api_key', $apiKey)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Obtém as chaves API de um usuário.
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Atualiza o timestamp de último uso de uma chave API.
     *
     * @param int $apiKeyId
     * @return bool
     */
    public function updateLastUsed(int $apiKeyId): bool
    {
        return $this->update($apiKeyId, ['last_used_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Desativa uma chave API.
     *
     * @param int $apiKeyId
     * @param int $userId
     * @return bool
     */
    public function deactivate(int $apiKeyId, int $userId): bool
    {
        return $this->where('id', $apiKeyId)
                    ->where('user_id', $userId)
                    ->set(['is_active' => false])
                    ->update();
    }
}

