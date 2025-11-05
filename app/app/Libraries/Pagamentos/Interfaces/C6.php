<?php

namespace App\Libraries\Pagamentos\Interfaces;

use App\Libraries\Pagamentos\PagamentosInterface;
use App\Libraries\Pagamentos\Entities\Cliente;
use App\Libraries\Pagamentos\Entities\Pedido;
use App\Libraries\Pagamentos\Entities\Transacao;
use App\Libraries\Pagamentos\Entities\Recebivel;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class C6 implements PagamentosInterface
{
    private Client $httpClient;
    private LoggerInterface $logger;
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl = 'https://api.c6bank.com.br/v1'; // Exemplo de URL base

    public function __construct(string $apiKey, string $apiSecret, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        // Inicialização do cliente HTTP (Guzzle)
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken(), // Simulação de autenticação
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ],
            'verify' => false, // Desabilitar verificação SSL para testes (NÃO USAR EM PRODUÇÃO)
        ]);
    }

    private function getAccessToken(): string
    {
        // Lógica real de obtenção de token OAuth2 do C6 Bank
        // Esta é uma simulação, pois a implementação real requer endpoints e credenciais
        return base64_encode("{$this->apiKey}:{$this->apiSecret}");
    }

    private function sendRequest(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $uri, $options);
            $body = json_decode($response->getBody()->getContents(), true);
            return $body;
        } catch (RequestException $e) {
            $this->logger->error("C6: Erro na requisição para {$uri}: " . $e->getMessage());
            $response = $e->getResponse();
            $body = json_decode($response->getBody()->getContents(), true);
            throw new \Exception("C6 API Error: " . ($body['message'] ?? $e->getMessage()), $response->getStatusCode());
        }
    }

    public function creditCard(Cliente &$cli, Pedido $pedido): Transacao
    {
        $this->logger->info("C6: Criando cobrança (Cartão de Crédito) para o cliente {$cli->getNome()}");

        $data = [
            'amount' => $pedido->getValor(),
            'currency' => 'BRL',
            'external_id' => $pedido->getId(),
            'payment_method' => 'credit_card',
            'customer' => [
                'name' => $cli->getNome(),
                'email' => $cli->getEmail(),
                'document' => $cli->getCPF(),
            ],
            // O token do cartão viria aqui, mas a API do C6 requer tokenização prévia
            'card_token' => 'card_token_simulado', 
        ];

        $response = $this->sendRequest('POST', '/charges', ['json' => $data]);

        $transacao = new Transacao();
        $transacao->setOperadoraID($response['id']);
        $transacao->setOperadoraStatus($response['status']);
        $transacao->setValorBruto($response['amount']);
        $transacao->setOperadoraResposta($response);
        $transacao->setOperadoraCodigo($response['external_id']);
        
        return $transacao;
    }

    public function boleto(Cliente &$cli, Pedido $pedido): Transacao
    {
        $this->logger->info("C6: Criando cobrança (Boleto) para o cliente {$cli->getNome()}");

        $data = [
            'amount' => $pedido->getValor(),
            'currency' => 'BRL',
            'external_id' => $pedido->getId(),
            'payment_method' => 'boleto',
            'due_date' => $pedido->getDataVencimento(),
            'customer' => [
                'name' => $cli->getNome(),
                'email' => $cli->getEmail(),
                'document' => $cli->getCPF(),
            ],
        ];

        $response = $this->sendRequest('POST', '/charges', ['json' => $data]);

        $transacao = new Transacao();
        $transacao->setOperadoraID($response['id']);
        $transacao->setOperadoraStatus($response['status']);
        $transacao->setValorBruto($response['amount']);
        $transacao->setOperadoraResposta($response);
        $transacao->setOperadoraCodigo($response['external_id']);
        
        return $transacao;
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        $this->logger->info("C6: Criando cobrança (Pix) para o cliente {$cli->getNome()}");

        $data = [
            'amount' => $pedido->getValor(),
            'currency' => 'BRL',
            'external_id' => $pedido->getId(),
            'payment_method' => 'pix',
            'customer' => [
                'name' => $cli->getNome(),
                'email' => $cli->getEmail(),
                'document' => $cli->getCPF(),
            ],
        ];

        $response = $this->sendRequest('POST', '/charges', ['json' => $data]);

        $transacao = new Transacao();
        $transacao->setOperadoraID($response['id']);
        $transacao->setOperadoraStatus($response['status']);
        $transacao->setValorBruto($response['amount']);
        $transacao->setOperadoraResposta($response);
        $transacao->setOperadoraCodigo($response['external_id']);
        
        return $transacao;
    }

    public function refund(string $chargeId, float $amount): Transacao
    {
        $this->logger->info("C6: Solicitando estorno para cobrança {$chargeId}");

        $data = [
            'amount' => $amount,
        ];

        $response = $this->sendRequest('POST', "/charges/{$chargeId}/refunds", ['json' => $data]);

        $transacao = new Transacao();
        $transacao->setOperadoraID($response['id']);
        $transacao->setOperadoraStatus($response['status']);
        $transacao->setValorBruto($response['amount']);
        $transacao->setOperadoraResposta($response);
        
        return $transacao;
    }

    public function saveCard(Cliente &$cli, string $token): string
    {
        // A API do C6 requer um processo de tokenização específico
        throw new \Exception("Método 'saveCard' requer implementação do fluxo de tokenização do C6 Bank.");
    }

    public function getCards(Cliente $cli): array
    {
        // A API do C6 requer um endpoint para listar cartões tokenizados
        return [];
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        // A API do C6 requer um endpoint para atualizar clientes
        return $cli;
    }

    public function getReceivable(string $receivableId): Recebivel
    {
        // A API do C6 requer um endpoint para buscar recebíveis
        throw new \Exception("Método 'getReceivable' requer implementação do endpoint de recebíveis do C6 Bank.");
    }

    public function getReceivables(array $filters): array
    {
        // A API do C6 requer um endpoint para listar recebíveis
        return [];
    }

    public function getCharge(string $chargeId): Transacao
    {
        $this->logger->info("C6: Buscando cobrança {$chargeId}");

        $response = $this->sendRequest('GET', "/charges/{$chargeId}");

        $transacao = new Transacao();
        $transacao->setOperadoraID($response['id']);
        $transacao->setOperadoraStatus($response['status']);
        $transacao->setValorBruto($response['amount']);
        $transacao->setOperadoraResposta($response);
        $transacao->setOperadoraCodigo($response['external_id']);
        
        return $transacao;
    }

    public function cancelCharge(string $chargeId): Transacao
    {
        $this->logger->info("C6: Cancelando cobrança {$chargeId}");

        $response = $this->sendRequest('POST', "/charges/{$chargeId}/cancel");

        $transacao = new Transacao();
        $transacao->setOperadoraID($response['id']);
        $transacao->setOperadoraStatus($response['status']);
        $transacao->setOperadoraResposta($response);
        
        return $transacao;
    }
}

