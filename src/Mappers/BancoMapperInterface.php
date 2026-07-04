<?php

declare(strict_types=1);

namespace CANNALPagamentos\Mappers;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\DTO\PagamentoResponse;

interface BancoMapperInterface
{
    public function mapearTransacao(
        Transacao $transacao,
        ?Pedido $pedido = null,
        ?Cliente $cliente = null,
        ?string $banco = null
    ): PagamentoResponse;

    public function mapearStatus(string $statusOriginal): string;

    public function descricaoStatus(string $statusPadronizado): string;
}
