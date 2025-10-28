<?php

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\Interfaces\WebhookProcessorInterface;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;
use Exception;

class InterWebhookProcessor implements WebhookProcessorInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(array $payload, array $headers): Transacao
    {
        // 1. Validação de Segurança (Simulada)
        // O Inter usa certificados e autenticação mútua.
        // A validação real seria mais complexa e envolveria certificados.

        // 2. Extração e Tradução
        $cobranca = $payload['cobranca'] ?? null;

        if (!$cobranca) {
            throw new Exception("Payload do Inter inválido: objeto cobranca não encontrado.");
        }

        // 3. Mapeamento para Transacao
        $transacao = new Transacao();
        $transacao->setOperadoraID($cobranca['codigoSolicitacao']);
        $transacao->setOperadoraStatus($cobranca['situacao']);
        $transacao->setValorBruto($cobranca['valorTotalRecebido'] ?? $cobranca['valorNominal']);
        $transacao->setDataTransacao($cobranca['dataSituacao'] ?? null);
        $transacao->setOperadoraCodigo($cobranca['seuNumero'] ?? null); // Identificador do pedido
        $transacao->setOperadora('Inter');
        $transacao->setOperadoraResposta(json_encode($payload));

        // Lógica de status
        $transacao->setConfirmada($cobranca['situacao'] === 'RECEBIDO');

        return $transacao;
    }
}
