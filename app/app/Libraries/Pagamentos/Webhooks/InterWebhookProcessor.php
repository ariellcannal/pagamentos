<?php

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\WebhookProcessorInterface;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;

class InterWebhookProcessor implements WebhookProcessorInterface
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
        $this->logger->info("Inter: Processando webhook para cobrança {$payload["codigoSolicitacao"]}");

        // Mapeamento do payload do Inter para a entidade Transacao
        $transacao = new Transacao();
        $transacao->setOperadoraID($payload["codigoSolicitacao"]);
        $transacao->setOperadoraStatus($payload["situacao"]);
        $transacao->setValorBruto($payload["valorNominal"]);
        $transacao->setOperadoraResposta($payload);
        $transacao->setOperadoraCodigo($payload["seuNumero"]);
        
        return $transacao;
    }

    public function validate(array $payload, string $signature): bool
    {
        // O Inter usa um processo de validação com certificado digital
        // A implementação real dependerá da documentação oficial
        // Aqui, simulamos uma validação simples
        $calculatedSignature = hash_hmac("sha256", json_encode($payload), $this->webhookKey);
        
        if (hash_equals($calculatedSignature, $signature)) {
            $this->logger->info("Inter: Assinatura do webhook validada com sucesso.");
            return true;
        }
        
        $this->logger->error("Inter: Falha na validação da assinatura do webhook.");
        return false;
    }
}

