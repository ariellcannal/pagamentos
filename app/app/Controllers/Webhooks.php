<?php

namespace App\Controllers;

use App\Models\Charge;
use App\Models\User;
use App\Models\WebhookLog;
use App\Models\ApiKey;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller para receber e processar webhooks dos bancos.
 */
class Webhooks extends Controller
{
    protected $chargeModel;
    protected $userModel;
    protected $webhookLogModel;
    protected $apiKeyModel;

    public function __construct()
    {
        $this->chargeModel = new Charge();
        $this->userModel = new User();
        $this->webhookLogModel = new WebhookLog();
        $this->apiKeyModel = new ApiKey();
    }

    /**
     * Recebe webhooks do Pagar.me.
     *
     * @param string $username
     * @return ResponseInterface
     */
    public function pagarme(string $username)
    {
        $user = $this->userModel->where('username', $username)->first();

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Usuário não encontrado']);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        // Registrar o webhook
        $this->webhookLogModel->logWebhook(
            $user['id'],
            'pagarme',
            '/hook/pagarme/' . $username,
            $payload
        );

        try {
            // Processar o webhook
            $this->processPagarmeWebhook($user['id'], $payload);

            return $this->response->setStatusCode(200)->setJSON(['success' => true]);
        } catch (\Exception $e) {
            $this->webhookLogModel->logWebhook(
                $user['id'],
                'pagarme',
                '/hook/pagarme/' . $username,
                $payload,
                ['error' => $e->getMessage()],
                500,
                $e->getMessage()
            );

            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Recebe webhooks do Banco Inter.
     *
     * @param string $username
     * @return ResponseInterface
     */
    public function inter(string $username)
    {
        $user = $this->userModel->where('username', $username)->first();

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Usuário não encontrado']);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        // Registrar o webhook
        $this->webhookLogModel->logWebhook(
            $user['id'],
            'inter',
            '/hook/inter/' . $username,
            $payload
        );

        try {
            // Processar o webhook
            $this->processInterWebhook($user['id'], $payload);

            return $this->response->setStatusCode(200)->setJSON(['success' => true]);
        } catch (\Exception $e) {
            $this->webhookLogModel->logWebhook(
                $user['id'],
                'inter',
                '/hook/inter/' . $username,
                $payload,
                ['error' => $e->getMessage()],
                500,
                $e->getMessage()
            );

            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Recebe webhooks do Bling.
     *
     * @param string $username
     * @return ResponseInterface
     */
    public function bling(string $username)
    {
        $user = $this->userModel->where('username', $username)->first();

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Usuário não encontrado']);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        // Registrar o webhook
        $this->webhookLogModel->logWebhook(
            $user['id'],
            'bling',
            '/hook/bling/' . $username,
            $payload
        );

        try {
            // Processar o webhook
            $this->processBlingWebhook($user['id'], $payload);

            return $this->response->setStatusCode(200)->setJSON(['success' => true]);
        } catch (\Exception $e) {
            $this->webhookLogModel->logWebhook(
                $user['id'],
                'bling',
                '/hook/bling/' . $username,
                $payload,
                ['error' => $e->getMessage()],
                500,
                $e->getMessage()
            );

            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Recebe webhooks de API customizadas.
     *
     * @param string $username
     * @param string $apiAlias
     * @return ResponseInterface
     */
    public function api(string $username, string $apiAlias)
    {
        $user = $this->userModel->where('username', $username)->first();

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Usuário não encontrado']);
        }

        $apiKey = $this->apiKeyModel->getByApiKey($apiAlias);

        if (!$apiKey || $apiKey['user_id'] !== $user['id']) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Chave API inválida']);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        // Atualizar o timestamp de último uso
        $this->apiKeyModel->updateLastUsed($apiKey['id']);

        // Registrar o webhook
        $this->webhookLogModel->logWebhook(
            $user['id'],
            'api',
            '/hook/api/' . $username . '/' . $apiAlias,
            $payload
        );

        try {
            // Processar o webhook customizado
            $this->processApiWebhook($user['id'], $payload, $apiKey);

            return $this->response->setStatusCode(200)->setJSON(['success' => true]);
        } catch (\Exception $e) {
            $this->webhookLogModel->logWebhook(
                $user['id'],
                'api',
                '/hook/api/' . $username . '/' . $apiAlias,
                $payload,
                ['error' => $e->getMessage()],
                500,
                $e->getMessage()
            );

            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Processa webhooks do Pagar.me.
     *
     * @param int $userId
     * @param array $payload
     * @return void
     */
    private function processPagarmeWebhook(int $userId, array $payload): void
    {
        // Implementar a lógica de processamento do webhook do Pagar.me
        // Exemplo: atualizar o status da cobrança baseado no evento recebido
    }

    /**
     * Processa webhooks do Banco Inter.
     *
     * @param int $userId
     * @param array $payload
     * @return void
     */
    private function processInterWebhook(int $userId, array $payload): void
    {
        // Implementar a lógica de processamento do webhook do Inter
    }

    /**
     * Processa webhooks do Bling.
     *
     * @param int $userId
     * @param array $payload
     * @return void
     */
    private function processBlingWebhook(int $userId, array $payload): void
    {
        // Implementar a lógica de processamento do webhook do Bling
    }

    /**
     * Processa webhooks customizados de API.
     *
     * @param int $userId
     * @param array $payload
     * @param array $apiKey
     * @return void
     */
    private function processApiWebhook(int $userId, array $payload, array $apiKey): void
    {
        // Implementar a lógica de processamento do webhook customizado
        // Enviar os dados para a URL configurada no webhook
    }
}

