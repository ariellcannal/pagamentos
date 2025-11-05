<?php

namespace App\Controllers;

use App\Models\ApiKey;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller para gerenciar chaves API.
 */
class ApiKeys extends Controller
{
    protected $apiKeyModel;
    protected $session;

    public function __construct()
    {
        $this->apiKeyModel = new ApiKey();
        $this->session = session();
    }

    /**
     * Lista as chaves API do usuário.
     *
     * @return string
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        $apiKeys = $this->apiKeyModel->getByUserId($userId);

        $data = [
            'title' => 'Chaves API',
            'api_keys' => $apiKeys,
        ];

        return view('api_keys/index', $data);
    }

    /**
     * Processa a criação de uma nova chave API.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $userId = $this->session->get('user_id');

        // Validação dos dados
        $rules = [
            'alias' => 'required|string|max_length[255]',
            'webhook_url' => 'required|valid_url',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alias = $this->request->getPost('alias');
        $webhookUrl = $this->request->getPost('webhook_url');

        // Gerar a chave API
        $apiKeyId = $this->apiKeyModel->generateApiKey($userId, $alias, $webhookUrl);

        if ($apiKeyId) {
            $apiKey = $this->apiKeyModel->find($apiKeyId);
            return redirect()->to('/api-keys')->with('success', 'Chave API criada com sucesso! Chave: ' . $apiKey['api_key']);
        }

        return redirect()->back()->with('error', 'Erro ao criar chave API.');
    }

    /**
     * Exibe os detalhes de uma chave API.
     *
     * @param int $apiKeyId
     * @return string|ResponseInterface
     */
    public function view(int $apiKeyId)
    {
        $userId = $this->session->get('user_id');
        $apiKey = $this->apiKeyModel->find($apiKeyId);

        if (!$apiKey || $apiKey['user_id'] !== $userId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Chave API não encontrada.');
        }

        $data = [
            'title' => 'Detalhes da Chave API',
            'api_key' => $apiKey,
        ];

        return view('api_keys/view', $data);
    }

    /**
     * Desativa uma chave API.
     *
     * @param int $apiKeyId
     * @return ResponseInterface
     */
    public function delete(int $apiKeyId)
    {
        $userId = $this->session->get('user_id');
        $apiKey = $this->apiKeyModel->find($apiKeyId);

        if (!$apiKey || $apiKey['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'Chave API não encontrada.');
        }

        if ($this->apiKeyModel->deactivate($apiKeyId, $userId)) {
            return redirect()->to('/api-keys')->with('success', 'Chave API desativada com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao desativar chave API.');
    }

    /**
     * Regenera uma chave API.
     *
     * @param int $apiKeyId
     * @return ResponseInterface
     */
    public function regenerate(int $apiKeyId)
    {
        $userId = $this->session->get('user_id');
        $apiKey = $this->apiKeyModel->find($apiKeyId);

        if (!$apiKey || $apiKey['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'Chave API não encontrada.');
        }

        // Desativar a chave antiga
        $this->apiKeyModel->deactivate($apiKeyId, $userId);

        // Gerar uma nova chave
        $newApiKeyId = $this->apiKeyModel->generateApiKey(
            $userId,
            $apiKey['alias'],
            $apiKey['webhook_url']
        );

        if ($newApiKeyId) {
            $newApiKey = $this->apiKeyModel->find($newApiKeyId);
            return redirect()->to('/api-keys')->with('success', 'Chave API regenerada com sucesso! Nova chave: ' . $newApiKey['api_key']);
        }

        return redirect()->back()->with('error', 'Erro ao regenerar chave API.');
    }

    /**
     * Retorna a documentação da API em JSON.
     *
     * @return ResponseInterface
     */
    public function documentation()
    {
        $userId = $this->session->get('user_id');
        $username = $this->session->get('username');

        $documentation = [
            'title' => 'Documentação da API de Webhooks',
            'base_url' => base_url(),
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/hook/api/{username}/{api_key}',
                    'description' => 'Receber webhooks customizados',
                    'example_url' => base_url() . '/hook/api/' . $username . '/{sua_chave_api}',
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body_example' => [
                        'charge_id' => 123,
                        'status' => 'paid',
                        'amount' => 100.50,
                        'timestamp' => date('Y-m-d H:i:s'),
                    ],
                    'response_success' => [
                        'success' => true,
                    ],
                    'response_error' => [
                        'error' => 'Descrição do erro',
                    ],
                ],
            ],
            'webhook_events' => [
                'charge.paid' => 'Cobrança foi paga',
                'charge.pending' => 'Cobrança está pendente',
                'charge.overdue' => 'Cobrança venceu',
                'charge.cancelled' => 'Cobrança foi cancelada',
            ],
        ];

        return $this->response->setJSON($documentation);
    }
}

