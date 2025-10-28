<?php

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\Interfaces\WebhookProcessorInterface;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;
use Exception;

class PagarmeWebhookProcessor implements WebhookProcessorInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(array $payload, array $headers): Transacao
    {
        // 1. Validação de Segurança (Simulada)
        if (!isset($headers['X-Hub-Signature'])) {
            throw new Exception("Webhook do Pagar.me sem assinatura de segurança.");
        }
        // Lógica de validação de assinatura real seria aqui...

        // 2. Extração e Tradução
        $data = $payload['data'];
        $charge = $data['charges'][0] ?? null;

        if (!$charge) {
            throw new Exception("Payload do Pagar.me inválido: charge não encontrado.");
        }

        // 3. Mapeamento para Transacao
        $transacao = new Transacao();
        $transacao->setOperadoraID($charge['id']);
        $transacao->setOperadoraStatus($charge['status']);
        $transacao->setValorBruto($charge['amount'] / 100); // Pagar.me usa centavos
        $transacao->setDataTransacao($charge['paid_at'] ?? null);
        $transacao->setOperadoraCodigo($data['code'] ?? null); // Código do pedido/ordem
        $transacao->setOperadora('Pagarme');
        $transacao->setOperadoraResposta(json_encode($payload));

        // Lógica de status
        $transacao->setConfirmada($charge['status'] === 'paid');

        return $transacao;
    }
}
