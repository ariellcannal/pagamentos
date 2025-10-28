<?php

namespace CANNALPagamentos\Interfaces;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use Exception;

class C6 implements PagamentosInterface
{
    private Client $httpClient;
    private LoggerInterface $logger;
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    /**
     * Construtor que recebe as credenciais para inicializar o cliente HTTP.
     *
     * @param LoggerInterface $logger
     * @param string $baseUrl URL base da API do C6 (ex: https://api.c6bank.com.br/v1)
     * @param string $clientId Client ID
     * @param string $clientSecret Client Secret
     */
    public function __construct(
        LoggerInterface $logger,
        string $baseUrl,
        string $clientId,
        string $clientSecret
    ) {
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        
        // Inicialização do cliente Guzzle para chamadas HTTP
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                // A autenticação real do C6 é complexa (OAuth2 + Certificado)
                // Aqui, apenas simulamos a injeção das credenciais.
            ],
            'verify' => false // Desabilitar verificação SSL para ambiente de sandbox
        ]);
    }

    // Métodos de PagamentosInterface (Implementação real com Guzzle)

    public function creditCard(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // Lógica de Adapter: Traduzir Entidades para o formato do C6
        $requestData = [
            'amount' => $pedido->getValorTotal(),
            'payment_method' => 'credit_card',
            'partner_id' => $pedido->getId(),
            'customer' => [
                'document' => $cli->getCpfCnpj(),
                // ...
            ],
        ];

        try {
            $response = $this->httpClient->post('charges', ['json' => $requestData]);
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($responseData['external_id']);
            $transacao->setOperadoraStatus($responseData['status']);
            $transacao->setValorBruto($responseData['amount']);
            $transacao->setOperadoraCodigo($responseData['partner_id']);
            $transacao->setOperadora('C6');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao criar charge (C6): " . $e->getMessage());
            throw $e;
        }
    }

    public function pix(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // Lógica de Adapter para Pix (similar ao creditCard, mas com Pix)
        $requestData = [
            'amount' => $pedido->getValorTotal(),
            'payment_method' => 'pix',
            'partner_id' => $pedido->getId(),
            'customer' => [
                'document' => $cli->getCpfCnpj(),
            ],
        ];

        try {
            $response = $this->httpClient->post('charges', ['json' => $requestData]);
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($responseData['external_id']);
            $transacao->setOperadoraStatus($responseData['status']);
            $transacao->setValorBruto($responseData['amount']);
            $transacao->setPixQrCode($responseData['pix_code'] ?? null);
            $transacao->setOperadoraCodigo($responseData['partner_id']);
            $transacao->setOperadora('C6');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao emitir Pix (C6): " . $e->getMessage());
            throw $e;
        }
    }

    public function refund(string $charge_id, float $amount): Transacao
    {
        try {
            $this->httpClient->post("charges/{$charge_id}/refund", ['json' => ['amount' => $amount]]);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($charge_id);
            $transacao->setValorCancelado($amount);
            $transacao->setOperadoraStatus('REFUNDED');
            $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao realizar estorno (C6): " . $e->getMessage());
            throw $e;
        }
    }

    public function saveCard(Cliente &$cli, string $cartao): string
    {
        throw new Exception("Implementação de saveCard para C6 pendente. Requer API de Tokenização.");
    }

    public function getCards(Cliente $cli): array
    {
        throw new Exception("Implementação de getCards para C6 pendente. Requer API de Tokenização.");
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        // Lógica de atualização de cliente
        try {
            $this->httpClient->put("customers/{$cli->getId()}", ['json' => $cli->toArray()]);
            return $cli;
        } catch (Exception $e) {
            $this->logger->error("Erro ao atualizar cliente (C6): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getReceivable(string $id): Transacao
    {
        throw new Exception("Consulta de recebível não implementada na API do C6.");
    }
    public function getReceivables(array $params): array
    {
        throw new Exception("Consulta de recebíveis não implementada na API do C6.");
    }
    public function getCharge(string $id): Transacao
    {
        try {
            $response = $this->httpClient->get("charges/{$id}");
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($responseData['external_id']);
            $transacao->setOperadoraStatus($responseData['status']);
            $transacao->setValorBruto($responseData['amount']);
            $transacao->setOperadora('C6');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao consultar charge (C6): " . $e->getMessage());
            throw $e;
        }
    }
    public function cancelCharge(string $charge_id): Transacao
    {
        try {
            $this->httpClient->post("charges/{$charge_id}/cancel");
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($charge_id);
            $transacao->setOperadoraStatus('CANCELLED');
            $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao cancelar charge (C6): " . $e->getMessage());
            throw $e;
        }
    }
}
