<?php

namespace CANNALPagamentos\Interfaces;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\Mocks\C6SDKMock;
use Psr\Log\LoggerInterface;
use Exception;

class C6 implements PagamentosInterface
{
    private C6SDKMock $sdk;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->sdk = new C6SDKMock(); // Uso do Mock para demonstração
        $this->logger = $logger;
    }

    // Métodos de PagamentosInterface (Implementação simulada)

    public function creditCard(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // Lógica de Adapter: Traduzir Entidades para o formato do C6
        $requestData = [
            'amount' => $pedido->getValorTotal(),
            'payment_method' => 'credit_card',
            'partner_id' => $pedido->getId(), // Usando ID do pedido como partner_id
            'customer' => [
                'document' => $cli->getCpfCnpj(),
                // ...
            ],
            // ... outros dados de cartão ...
        ];

        try {
            $response = $this->sdk->createCharge($requestData);
            
            // Lógica de Adapter: Traduzir resposta do C6 para Entidade Transacao
            $transacao = new Transacao();
            $transacao->setOperadoraID($response['external_id']);
            $transacao->setOperadoraStatus($response['status']);
            $transacao->setValorBruto($response['amount']);
            $transacao->setOperadoraCodigo($response['partner_id']); // Novo campo
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
            $response = $this->sdk->createCharge($requestData);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($response['external_id']);
            $transacao->setOperadoraStatus($response['status']);
            $transacao->setValorBruto($response['amount']);
            $transacao->setPixQrCode($response['pix_code']);
            $transacao->setOperadoraCodigo($response['partner_id']);
            $transacao->setOperadora('C6');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao emitir Pix (C6): " . $e->getMessage());
            throw $e;
        }
    }

    public function refund(string $charge_id, float $amount): Transacao
    {
        throw new Exception("Implementação de refund para C6 pendente.");
    }

    public function saveCard(Cliente &$cli, string $cartao): string
    {
        throw new Exception("Implementação de saveCard para C6 pendente.");
    }

    public function getCards(Cliente $cli): array
    {
        return [];
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        return $cli;
    }
    
    // Métodos de consulta (simplificados)
    public function getReceivable(string $id): Transacao { throw new Exception("Pendente"); }
    public function getReceivables(array $params): array { return []; }
    public function getCharge(string $id): Transacao { throw new Exception("Pendente"); }
    public function cancelCharge(string $charge_id): Transacao { throw new Exception("Pendente"); }
}
