<?php

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\Interfaces\WebhookProcessorInterface;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;
use Exception;

class C6WebhookProcessor implements WebhookProcessorInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(array $payload, array $headers): Transacao
    {
        // 1. Validação de Segurança (Simulada)
        // O C6 usa autenticação mútua e/ou validação de assinatura.
        // A validação real seria aqui.

        // 2. Extração e Tradução
        $information = $payload['information'] ?? []; // Objeto com os detalhes do Boleto/Charge
        
        if (empty($information)) {
            throw new Exception("Payload do C6 inválido: information object não encontrado.");
        }

        // 3. Mapeamento para Transacao
        $transacao = new Transacao();
        $transacao->setOperadoraID($payload['external_id']);
        $transacao->setOperadoraStatus($payload['status']);
        $transacao->setOperadoraCodigo($payload['partner_id'] ?? null); // Identificador do pedido
        $transacao->setOperadora('C6');
        $transacao->setOperadoraResposta(json_encode($payload));

        // Extraindo valor e data do objeto 'information' (ex: Boleto)
        $transacao->setValorBruto($information['amount'] ?? 0.0);
        $transacao->setDataTransacao($information['paid_at'] ?? $payload['date_time']);
        
        // Lógica de status
        $transacao->setConfirmada($payload['status'] === 'PAID');

        return $transacao;
    }
}
