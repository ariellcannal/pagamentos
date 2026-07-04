<?php

declare(strict_types=1);

namespace CANNALPagamentos\Mappers;

class InterMapper extends AbstractBancoMapper
{
    public function mapearStatus(string $statusOriginal): string
    {
        return match (strtoupper($statusOriginal)) {
            'RECEBIDO', 'PAGO', 'LIQUIDADO' => self::STATUS_PAGAMENTO_RECEBIDO,
            'EM_PROCESSAMENTO', 'EM_ABERTO', 'CRIADO', 'PENDENTE' => self::STATUS_EM_PROCESSAMENTO,
            'VENCIDO' => self::STATUS_PAGAMENTO_VENCIDO,
            'CANCELADO' => self::STATUS_PAGAMENTO_CANCELADO,
            'ESTORNADO_PARCIAL' => self::STATUS_REEMBOLSADO_PARCIALMENTE,
            'ESTORNADO', 'REEMBOLSADO' => self::STATUS_REEMBOLSADO,
            'NEGADO', 'FALHA' => self::STATUS_FALHA,
            default => self::STATUS_EM_PROCESSAMENTO,
        };
    }
}
