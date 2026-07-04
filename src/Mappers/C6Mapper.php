<?php

declare(strict_types=1);

namespace CANNALPagamentos\Mappers;

class C6Mapper extends AbstractBancoMapper
{
    public function mapearStatus(string $statusOriginal): string
    {
        return match (strtoupper($statusOriginal)) {
            'PAID', 'APPROVED', 'RECEIVED' => self::STATUS_PAGAMENTO_RECEBIDO,
            'CREATED', 'PROCESSING', 'PENDING' => self::STATUS_EM_PROCESSAMENTO,
            'EXPIRED' => self::STATUS_PAGAMENTO_VENCIDO,
            'CANCELED', 'CANCELLED' => self::STATUS_PAGAMENTO_CANCELADO,
            'PARTIALLY_REFUNDED' => self::STATUS_REEMBOLSADO_PARCIALMENTE,
            'REFUNDED' => self::STATUS_REEMBOLSADO,
            'FAILED', 'DENIED', 'REFUSED' => self::STATUS_FALHA,
            default => self::STATUS_EM_PROCESSAMENTO,
        };
    }
}
