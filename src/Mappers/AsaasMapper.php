<?php

declare(strict_types=1);

namespace CANNALPagamentos\Mappers;

class AsaasMapper extends AbstractBancoMapper
{
    public function mapearStatus(string $statusOriginal): string
    {
        return match (strtoupper($statusOriginal)) {
            'RECEIVED', 'CONFIRMED' => self::STATUS_PAGAMENTO_RECEBIDO,
            'PENDING', 'AWAITING_RISK_ANALYSIS', 'IN_ANALYSIS' => self::STATUS_EM_PROCESSAMENTO,
            'OVERDUE' => self::STATUS_PAGAMENTO_VENCIDO,
            'REFUNDED' => self::STATUS_REEMBOLSADO,
            'PARTIALLY_REFUNDED' => self::STATUS_REEMBOLSADO_PARCIALMENTE,
            'CANCELLED', 'DELETED' => self::STATUS_PAGAMENTO_CANCELADO,
            'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE', 'REFUSED' => self::STATUS_FALHA,
            default => self::STATUS_EM_PROCESSAMENTO,
        };
    }
}
