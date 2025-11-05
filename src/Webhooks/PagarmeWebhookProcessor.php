<?php

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\WebhookProcessorInterface;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;

class PagarmeWebhookProcessor implements WebhookProcessorInterface
{
    private LoggerInterface $logger;
    private string $webhookKey;

    public function __construct(string $webhookKey, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->webhookKey = $webhookKey;
    }

    public function process(array $payload): Transacao
    {
        $this->logger->info("Pagarme: Processando webhook para evento {$payload["event"]}");

        // Mapeamento do payload do Pagar.me para a entidade Transacao
        $charge = $payload["data"]["charge"] ?? $payload["data"];
        
        $transacao = new Transacao();
        $transacao->setOperadoraID($charge["id"]);
        $transacao->setOperadoraStatus($charge["status"]);
        $transacao->setValorBruto($charge["amount"] / 100); // Pagar.me usa centavos
        $transacao->setOperadoraResposta($payload);
        $transacao->setOperadoraCodigo($charge["code"] ?? null); // Código da ordem
        
        return $transacao;
    }

    public function validate(array $payload, string $signature): bool
    {
        // O Pagar.me envia a assinatura no header "X-Hub-Signature"
        $calculatedSignature = hash_hmac("sha256", json_encode($payload), $this->webhookKey);
        
        if (hash_equals($calculatedSignature, $signature)) {
            $this->logger->info("Pagarme: Assinatura do webhook validada com sucesso.");
            return true;
        }
        
        $this->logger->error("Pagarme: Falha na validação da assinatura do webhook. Recebido: {$signature}, Calculado: {$calculatedSignature}");
        return false;
    }
}

