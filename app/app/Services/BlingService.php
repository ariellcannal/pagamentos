<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Serviço para integração com Bling API.
 */
class BlingService
{
    protected $apiKey;
    protected $client;
    protected const BASE_URL = 'https://api.bling.com.br/B8/api/v3';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Cria uma nota fiscal (NFe) no Bling.
     *
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function createNFe(array $data): array
    {
        try {
            $response = $this->client->post('/nfe', [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao criar NFe no Bling: ' . $e->getMessage());
        }
    }

    /**
     * Cria um pedido no Bling.
     *
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function createOrder(array $data): array
    {
        try {
            $response = $this->client->post('/pedidos', [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao criar pedido no Bling: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza um pedido no Bling.
     *
     * @param int $orderId
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function updateOrder(int $orderId, array $data): array
    {
        try {
            $response = $this->client->put('/pedidos/' . $orderId, [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao atualizar pedido no Bling: ' . $e->getMessage());
        }
    }

    /**
     * Obtém um pedido do Bling.
     *
     * @param int $orderId
     * @return array
     * @throws GuzzleException
     */
    public function getOrder(int $orderId): array
    {
        try {
            $response = $this->client->get('/pedidos/' . $orderId);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao obter pedido do Bling: ' . $e->getMessage());
        }
    }

    /**
     * Lista pedidos do Bling.
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function listOrders(array $filters = []): array
    {
        try {
            $response = $this->client->get('/pedidos', [
                'query' => $filters,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao listar pedidos do Bling: ' . $e->getMessage());
        }
    }

    /**
     * Cria uma conta a receber (Receivable) no Bling.
     *
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function createReceivable(array $data): array
    {
        try {
            $response = $this->client->post('/contas-receber', [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao criar conta a receber no Bling: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza uma conta a receber no Bling.
     *
     * @param int $receivableId
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function updateReceivable(int $receivableId, array $data): array
    {
        try {
            $response = $this->client->put('/contas-receber/' . $receivableId, [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao atualizar conta a receber no Bling: ' . $e->getMessage());
        }
    }

    /**
     * Obtém uma conta a receber do Bling.
     *
     * @param int $receivableId
     * @return array
     * @throws GuzzleException
     */
    public function getReceivable(int $receivableId): array
    {
        try {
            $response = $this->client->get('/contas-receber/' . $receivableId);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao obter conta a receber do Bling: ' . $e->getMessage());
        }
    }

    /**
     * Lista contas a receber do Bling.
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function listReceivables(array $filters = []): array
    {
        try {
            $response = $this->client->get('/contas-receber', [
                'query' => $filters,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao listar contas a receber do Bling: ' . $e->getMessage());
        }
    }

    /**
     * Cria um cliente no Bling.
     *
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function createContact(array $data): array
    {
        try {
            $response = $this->client->post('/contatos', [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao criar contato no Bling: ' . $e->getMessage());
        }
    }

    /**
     * Obtém um cliente do Bling.
     *
     * @param int $contactId
     * @return array
     * @throws GuzzleException
     */
    public function getContact(int $contactId): array
    {
        try {
            $response = $this->client->get('/contatos/' . $contactId);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao obter contato do Bling: ' . $e->getMessage());
        }
    }

    /**
     * Lista clientes do Bling.
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function listContacts(array $filters = []): array
    {
        try {
            $response = $this->client->get('/contatos', [
                'query' => $filters,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Erro ao listar contatos do Bling: ' . $e->getMessage());
        }
    }

    /**
     * Testa a conexão com a API do Bling.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->listContacts(['limit' => 1]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Mapeia uma cobrança para um formato compatível com Bling.
     *
     * @param array $charge
     * @param array $customer
     * @return array
     */
    public function mapChargeToReceivable(array $charge, array $customer): array
    {
        return [
            'numero' => (string) $charge['id'],
            'descricao' => $charge['description'],
            'valor' => (float) $charge['amount'],
            'dataVencimento' => $charge['due_date'],
            'contato' => [
                'nome' => $customer['name'],
                'email' => $customer['email'],
                'documento' => $customer['document'],
            ],
            'categoria' => 'Cobrança',
            'observacoes' => 'Cobrança criada via ' . $charge['bank_type'],
        ];
    }

    /**
     * Mapeia um status de cobrança para status do Bling.
     *
     * @param string $chargeStatus
     * @return string
     */
    public function mapChargeStatusToBling(string $chargeStatus): string
    {
        $statusMap = [
            'pending' => 'aberto',
            'paid' => 'recebido',
            'overdue' => 'atrasado',
            'cancelled' => 'cancelado',
        ];

        return $statusMap[$chargeStatus] ?? 'aberto';
    }

    /**
     * Mapeia um status do Bling para status de cobrança.
     *
     * @param string $blingStatus
     * @return string
     */
    public function mapBlingStatusToCharge(string $blingStatus): string
    {
        $statusMap = [
            'aberto' => 'pending',
            'recebido' => 'paid',
            'atrasado' => 'overdue',
            'cancelado' => 'cancelled',
        ];

        return $statusMap[$blingStatus] ?? 'pending';
    }
}

