<?php

namespace CANNALPagamentos;

use CANNALPagamentos\Entities\Transacao;

interface WebhookProcessorInterface
{
    /**
     * Processa o payload do webhook e retorna uma entidade Transacao padronizada.
     *
     * @param array $payload O payload bruto recebido do gateway.
     * @return Transacao A entidade Transacao mapeada.
     * @throws \Exception Se o processamento falhar ou o payload for inválido.
     */
    public function process(array $payload): Transacao;

    /**
     * Valida a assinatura do webhook para garantir que a origem é confiável.
     *
     * @param array $payload O payload bruto recebido do gateway.
     * @param string $signature A assinatura de segurança enviada no header.
     * @return bool True se a assinatura for válida.
     */
    public function validate(array $payload, string $signature): bool;
}

