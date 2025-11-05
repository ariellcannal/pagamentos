<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar cobranças.
 */
class Charge extends Model
{
    protected $table = 'charges';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'bank_type',
        'charge_type',
        'origin',
        'external_id',
        'bank_charge_id',
        'amount',
        'description',
        'customer_name',
        'customer_email',
        'customer_document',
        'due_date',
        'status',
        'pix_qr_code',
        'pix_qr_code_url',
        'boleto_barcode',
        'boleto_url',
        'payment_link_url',
        'bank_response',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'bank_type' => 'required|in_list[pagarme,inter,c6]',
        'charge_type' => 'required|in_list[boleto,pix,credit_card]',
        'amount' => 'required|numeric|greater_than[0]',
        'status' => 'required|in_list[pending,paid,overdue,canceled]',
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
     * Obtém as cobranças de um usuário com paginação.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByUserId(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Obtém uma cobrança pelo ID e ID do usuário.
     *
     * @param int $chargeId
     * @param int $userId
     * @return array|null
     */
    public function getChargeById(int $chargeId, int $userId): ?array
    {
        return $this->where('id', $chargeId)
                    ->where('user_id', $userId)
                    ->first();
    }

    /**
     * Obtém uma cobrança pelo ID do banco.
     *
     * @param string $bankChargeId
     * @param int $userId
     * @return array|null
     */
    public function getChargeByBankId(string $bankChargeId, int $userId): ?array
    {
        return $this->where('bank_charge_id', $bankChargeId)
                    ->where('user_id', $userId)
                    ->first();
    }

    /**
     * Atualiza o status de uma cobrança.
     *
     * @param int $chargeId
     * @param string $status
     * @param array $additionalData
     * @return bool
     */
    public function updateStatus(int $chargeId, string $status, array $additionalData = []): bool
    {
        $data = array_merge(['status' => $status], $additionalData);
        return $this->update($chargeId, $data);
    }

    /**
     * Conta as cobranças de um usuário por status.
     *
     * @param int $userId
     * @param string $status
     * @return int
     */
    public function countByStatus(int $userId, string $status): int
    {
        return $this->where('user_id', $userId)
                    ->where('status', $status)
                    ->countAllResults();
    }
}

