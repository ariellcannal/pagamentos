<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar templates de e-mail.
 */
class EmailTemplate extends Model
{
    protected $table = 'email_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'template_type',
        'subject',
        'html_content',
        'is_enabled',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Obtém um template de e-mail por tipo.
     *
     * @param int $userId
     * @param string $templateType
     * @return array|null
     */
    public function getByType(int $userId, string $templateType): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('template_type', $templateType)
                    ->where('is_enabled', true)
                    ->first();
    }

    /**
     * Obtém todos os templates de um usuário.
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('template_type', 'ASC')
                    ->findAll();
    }

    /**
     * Cria ou atualiza um template de e-mail.
     *
     * @param int $userId
     * @param string $templateType
     * @param string $subject
     * @param string $htmlContent
     * @param bool $isEnabled
     * @return bool
     */
    public function saveTemplate(
        int $userId,
        string $templateType,
        string $subject,
        string $htmlContent,
        bool $isEnabled = true
    ): bool {
        $existing = $this->where('user_id', $userId)
                         ->where('template_type', $templateType)
                         ->first();

        $data = [
            'user_id' => $userId,
            'template_type' => $templateType,
            'subject' => $subject,
            'html_content' => $htmlContent,
            'is_enabled' => $isEnabled,
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }

    /**
     * Ativa ou desativa um template.
     *
     * @param int $templateId
     * @param int $userId
     * @param bool $isEnabled
     * @return bool
     */
    public function toggleTemplate(int $templateId, int $userId, bool $isEnabled): bool
    {
        return $this->where('id', $templateId)
                    ->where('user_id', $userId)
                    ->set(['is_enabled' => $isEnabled])
                    ->update();
    }
}

