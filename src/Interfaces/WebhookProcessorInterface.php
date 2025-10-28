<?php

namespace CANNALPagamentos\Interfaces;

use CANNALPagamentos\Entities\Transacao;

interface WebhookProcessorInterface
{
    /**
     * Processa o payload bruto do Webhook de um Gateway.
     *
     * @param array $payload O payload JSON/Array recebido do Gateway.
     * @param array $headers Os headers HTTP para validação de segurança.
     * @return Transacao A entidade de Transacao padronizada.
     * @throws \Exception Em caso de falha na validação ou processamento.
     */
    public function process(array $payload, array $headers): Transacao;
}
