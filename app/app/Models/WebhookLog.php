<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar logs de webhooks.
 */
class WebhookLog extends Model
{
    protected $table = 'webhook_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'bank_type',
        'endpoint',
        'payload',
        'response',
        'status_code',
        'error_message',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';

    /**
     * Registra um webhook recebido.
     *
     * @param int $userId
     * @param string $bankType
     * @param string $endpoint
     * @param array $payload
     * @param array $response
     * @param int $statusCode
     * @param string|null $errorMessage
     * @return int
     */
    public function logWebhook(
        int $userId,
        string $bankType,
        string $endpoint,
        array $payload,
        array $response = [],
        int $statusCode = 200,
        ?string $errorMessage = null
    ): int {
        $data = [
            'user_id' => $userId,
            'bank_type' => $bankType,
            'endpoint' => $endpoint,
            'payload' => json_encode($payload),
            'response' => json_encode($response),
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
        ];

        return $this->insert($data);
    }

    /**
     * Obtém os logs de webhook de um usuário.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByUserId(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * Obtém um log de webhook pelo ID.
     *
     * @param int $logId
     * @param int $userId
     * @return array|null
     */
    public function getLogById(int $logId, int $userId): ?array
    {
        return $this->where('id', $logId)
                    ->where('user_id', $userId)
                    ->first();
    }
}

