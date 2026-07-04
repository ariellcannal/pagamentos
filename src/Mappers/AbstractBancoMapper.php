<?php

declare(strict_types=1);

namespace CANNALPagamentos\Mappers;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\DTO\PagamentoResponse;

abstract class AbstractBancoMapper implements BancoMapperInterface
{
    public const STATUS_EM_PROCESSAMENTO = 'EM_PROCESSAMENTO';
    public const STATUS_PAGAMENTO_RECEBIDO = 'PAGAMENTO_RECEBIDO';
    public const STATUS_PAGAMENTO_VENCIDO = 'PAGAMENTO_VENCIDO';
    public const STATUS_PAGAMENTO_CANCELADO = 'PAGAMENTO_CANCELADO';
    public const STATUS_FALHA = 'FALHA';
    public const STATUS_REEMBOLSADO = 'REEMBOLSADO';
    public const STATUS_REEMBOLSADO_PARCIALMENTE = 'REEMBOLSADO_PARCIALMENTE';

    public function mapearTransacao(
        Transacao $transacao,
        ?Pedido $pedido = null,
        ?Cliente $cliente = null,
        ?string $banco = null
    ): PagamentoResponse {
        $statusPadronizado = $this->mapearStatus((string) $transacao->getOperadoraStatus());

        $response = (new PagamentoResponse())
            ->setId((string) ($transacao->getId() ?? ''))
            ->setIdOperadora($transacao->getOperadoraID())
            ->setStatus($statusPadronizado)
            ->setStatusDescricao($this->descricaoStatus($statusPadronizado))
            ->setForma($transacao->getForma() ?: $transacao->getTipo())
            ->setValor($transacao->getValorBruto())
            ->setValorLiquido($transacao->getValorLiquido())
            ->setDataCriacao($transacao->getDataTransacao())
            ->setDataConfirmacao($transacao->getConfirmada() ? $transacao->getDataTransacao() : null)
            ->setPixQrCode($transacao->getPixQrCode())
            ->setPixQrCodeUrl($transacao->getPixQrCodeUrl())
            ->setBanco($banco)
            ->setBancoStatusOriginal($transacao->getOperadoraStatus())
            ->setPedido($pedido)
            ->setCliente($cliente)
            ->setTransacao($transacao);

        $dadosOperadora = [];
        $raw = $transacao->getOperadoraResposta();
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $dadosOperadora = $decoded;
            }
        }

        return $response->setDadosOperadora($dadosOperadora);
    }

    public function descricaoStatus(string $statusPadronizado): string
    {
        return match ($statusPadronizado) {
            self::STATUS_EM_PROCESSAMENTO => 'Em processamento',
            self::STATUS_PAGAMENTO_RECEBIDO => 'Pagamento recebido',
            self::STATUS_PAGAMENTO_VENCIDO => 'Pagamento vencido',
            self::STATUS_PAGAMENTO_CANCELADO => 'Pagamento cancelado',
            self::STATUS_FALHA => 'Falha',
            self::STATUS_REEMBOLSADO => 'Reembolsado',
            self::STATUS_REEMBOLSADO_PARCIALMENTE => 'Reembolsado parcialmente',
            default => 'Status desconhecido',
        };
    }
}
