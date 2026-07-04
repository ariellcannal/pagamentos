<?php

declare(strict_types=1);

namespace CANNALPagamentos\Mappers;

class PagarmeMapper extends AbstractBancoMapper
{
    public function mapearStatus(string $statusOriginal): string
    {
        return match (strtolower($statusOriginal)) {
            'paid', 'overpaid' => self::STATUS_PAGAMENTO_RECEBIDO,
            'pending', 'processing', 'waiting_payment' => self::STATUS_EM_PROCESSAMENTO,
            'canceled', 'cancelled' => self::STATUS_PAGAMENTO_CANCELADO,
            'failed', 'refused', 'chargedback' => self::STATUS_FALHA,
            'partial_refunded' => self::STATUS_REEMBOLSADO_PARCIALMENTE,
            'refunded' => self::STATUS_REEMBOLSADO,
            default => self::STATUS_EM_PROCESSAMENTO,
        };
    }
}
