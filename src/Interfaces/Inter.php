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
        throw new Exception("Implementação de refund para Inter pendente.");
    }

    public function saveCard(Cliente &$cli, string $cartao): string
    {
        throw new Exception("Implementação de saveCard para Inter pendente.");
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
