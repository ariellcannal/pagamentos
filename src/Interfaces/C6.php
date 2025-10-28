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
        $this->logger->info("Simulando refund de {$amount} para charge ID {$charge_id} no C6.");
        // Simulação: Retorna uma Transacao com o valor cancelado
        $transacao = new Transacao();
        $transacao->setOperadoraID($charge_id);
        $transacao->setValorCancelado($amount);
        $transacao->setOperadoraStatus('REFUNDED');
        $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
        return $transacao;
    }

    public function saveCard(Cliente &$cli, string $cartao): string
    {
        $token = 'tok_c6_' . substr(md5($cartao . time()), 0, 16);
        $this->logger->info("Simulando saveCard para cliente {$cli->getId()} no C6. Token: {$token}");
        return $token;
    }

    public function getCards(Cliente $cli): array
    {
        $this->logger->info("Simulando consulta de cartões salvos para cliente {$cli->getId()} no C6.");
        return ['tok_c6_1234', 'tok_c6_5678'];
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        return $cli;
    }
    
    // Métodos de consulta (simplificados)
    public function getReceivable(string $id): Transacao
    {
        $this->logger->info("Simulando consulta de recebível ID {$id} no C6.");
        $transacao = new Transacao();
        $transacao->setOperadoraID($id);
        $transacao->setValorLiquido(95.00);
        $transacao->setOperadoraStatus('SETTLED');
        return $transacao;
    }
    public function getReceivables(array $params): array
    {
        $this->logger->info("Simulando consulta de recebíveis no C6 com filtros: " . json_encode($params));
        return [
            (new Transacao())->setOperadoraID('rec_c6_1')->setValorLiquido(60.00),
            (new Transacao())->setOperadoraID('rec_c6_2')->setValorLiquido(80.00),
        ];
    }
    public function getCharge(string $id): Transacao
    {
        $this->logger->info("Simulando consulta de charge ID {$id} no C6.");
        $transacao = new Transacao();
        $transacao->setOperadoraID($id);
        $transacao->setValorBruto(120.00);
        $transacao->setOperadoraStatus('PAID');
        return $transacao;
    }
    public function cancelCharge(string $charge_id): Transacao
    {
        $this->logger->info("Simulando cancelamento de charge ID {$charge_id} no C6.");
        $transacao = new Transacao();
        $transacao->setOperadoraID($charge_id);
        $transacao->setOperadoraStatus('CANCELLED');
        $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
        return $transacao;
    }
}
