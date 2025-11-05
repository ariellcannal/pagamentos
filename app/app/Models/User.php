<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gerenciar usuários (clientes) da aplicação.
 */
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['username', 'email', 'password', 'company_name', 'company_document', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Valida os dados do usuário.
     *
     * @var array
     */
    protected $validationRules = [
        'username' => 'required|alpha_numeric|is_unique[users.username]|min_length[3]|max_length[100]',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
        'company_name' => 'permit_empty|string|max_length[255]',
        'company_document' => 'permit_empty|string|max_length[20]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Autentica um usuário com base em username e senha.
     *
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->where('username', $username)
                     ->orWhere('email', $username)
                     ->first();

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        if (!$user['is_active']) {
            return null;
        }

        return $user;
    }

    /**
     * Registra um novo usuário.
     *
     * @param array $data
     * @return int|false
     */
    public function register(array $data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['is_active'] = true;

        return $this->insert($data);
    }

    /**
     * Obtém um usuário pelo ID.
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserById(int $userId): ?array
    {
        return $this->find($userId);
    }
}

