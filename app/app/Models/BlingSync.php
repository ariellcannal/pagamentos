<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar a sincronização com Bling API.
 */
class BlingSync extends Model
{
    protected $table = 'bling_sync';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'charge_id',
        'bling_nfe_id',
        'bling_order_id',
        'sync_type',
        'sync_status',
        'sync_data',
        'error_message',
        'last_sync_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Registra uma sincronização.
     *
     * @param int $userId
     * @param int $chargeId
     * @param string $syncType
     * @param array $syncData
     * @param string $status
     * @param string|null $errorMessage
     * @return int
     */
    public function recordSync(
        int $userId,
        int $chargeId,
        string $syncType,
        array $syncData,
        string $status = 'pending',
        ?string $errorMessage = null
    ): int {
        $data = [
            'user_id' => $userId,
            'charge_id' => $chargeId,
            'sync_type' => $syncType,
            'sync_data' => json_encode($syncData),
            'sync_status' => $status,
            'error_message' => $errorMessage,
            'last_sync_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }

    /**
     * Obtém o histórico de sincronização de um usuário.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getSyncHistory(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * Obtém sincronizações pendentes.
     *
     * @param int $userId
     * @return array
     */
    public function getPendingSyncs(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('sync_status', 'pending')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Atualiza o status de uma sincronização.
     *
     * @param int $syncId
     * @param string $status
     * @param array $additionalData
     * @return bool
     */
    public function updateSyncStatus(int $syncId, string $status, array $additionalData = []): bool
    {
        $data = array_merge(
            ['sync_status' => $status, 'last_sync_at' => date('Y-m-d H:i:s')],
            $additionalData
        );

        return $this->update($syncId, $data);
    }

    /**
     * Obtém a sincronização de uma cobrança.
     *
     * @param int $chargeId
     * @param int $userId
     * @return array|null
     */
    public function getSyncByChargeId(int $chargeId, int $userId): ?array
    {
        return $this->where('charge_id', $chargeId)
                    ->where('user_id', $userId)
                    ->first();
    }

    /**
     * Conta sincronizações por status.
     *
     * @param int $userId
     * @param string $status
     * @return int
     */
    public function countByStatus(int $userId, string $status): int
    {
        return $this->where('user_id', $userId)
                    ->where('sync_status', $status)
                    ->countAllResults();
    }
}

