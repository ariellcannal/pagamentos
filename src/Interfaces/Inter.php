<?php

namespace CANNALPagamentos\Interfaces;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\Mocks\InterSDKMock;
use Psr\Log\LoggerInterface;
use Exception;

class Inter implements PagamentosInterface
{
    private InterSDKMock $sdk;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->sdk = new InterSDKMock(); // Uso do Mock para demonstração
        $this->logger = $logger;
    }

    // Métodos de PagamentosInterface (Implementação simulada)

    public function creditCard(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        throw new Exception("Banco Inter não suporta diretamente transações de Cartão de Crédito via API de Cobrança.");
    }

    public function pix(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // Lógica de Adapter: Traduzir Entidades para o formato do Inter
        $requestData = [
            'seuNumero' => $pedido->getId(), // Usando ID do pedido como 'seuNumero'
            'valorNominal' => $pedido->getValorTotal(),
            'dataVencimento' => date('Y-m-d', strtotime('+7 days')),
            'numDiasAgenda' => 60,
            'pagador' => [
                'cpfCnpj' => $cli->getCpfCnpj(),
                'nome' => $cli->getNome(),
                // ... outros campos de endereço do cliente ...
            ],
        ];

        try {
            $response = $this->sdk->emitirCobranca($requestData);
            
            // Lógica de Adapter: Traduzir resposta do Inter para Entidade Transacao
            $transacao = new Transacao();
            $transacao->setOperadoraID($response['codigoSolicitacao']);
            $transacao->setOperadoraStatus($response['status']);
            $transacao->setValorBruto($response['valorNominal']);
            $transacao->setPixQrCode($response['qrCode']);
            $transacao->setOperadoraCodigo($response['seuNumero']); // Novo campo
            $transacao->setOperadora('Inter');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao emitir Pix (Inter): " . $e->getMessage());
            throw $e;
        }
    }

    public function refund(string $charge_id, float $amount): Transacao
    {
        $this->logger->info("Simulando refund de {$amount} para charge ID {$charge_id} no Inter.");
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
        $token = 'tok_' . substr(md5($cartao . time()), 0, 16);
        $this->logger->info("Simulando saveCard para cliente {$cli->getId()} no Inter. Token: {$token}");
        return $token;
    }

    public function getCards(Cliente $cli): array
    {
        $this->logger->info("Simulando consulta de cartões salvos para cliente {$cli->getId()} no Inter.");
        // Retorna uma lista de tokens de cartão simulados
        return ['tok_inter_1234', 'tok_inter_5678'];
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        return $cli;
    }
    
    // Métodos de consulta (simplificados)
    public function getReceivable(string $id): Transacao
    {
        $this->logger->info("Simulando consulta de recebível ID {$id} no Inter.");
        $transacao = new Transacao();
        $transacao->setOperadoraID($id);
        $transacao->setValorLiquido(100.00);
        $transacao->setOperadoraStatus('SETTLED');
        return $transacao;
    }
    public function getReceivables(array $params): array
    {
        $this->logger->info("Simulando consulta de recebíveis no Inter com filtros: " . json_encode($params));
        return [
            (new Transacao())->setOperadoraID('rec_1')->setValorLiquido(50.00),
            (new Transacao())->setOperadoraID('rec_2')->setValorLiquido(75.00),
        ];
    }
    public function getCharge(string $id): Transacao
    {
        $this->logger->info("Simulando consulta de charge ID {$id} no Inter.");
        $transacao = new Transacao();
        $transacao->setOperadoraID($id);
        $transacao->setValorBruto(150.00);
        $transacao->setOperadoraStatus('PAID');
        return $transacao;
    }
    public function cancelCharge(string $charge_id): Transacao
    {
        $this->logger->info("Simulando cancelamento de charge ID {$charge_id} no Inter.");
        $transacao = new Transacao();
        $transacao->setOperadoraID($charge_id);
        $transacao->setOperadoraStatus('CANCELLED');
        $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
        return $transacao;
    }
}
