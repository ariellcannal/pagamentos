<?php

declare(strict_types=1);

namespace CANNALPagamentos\Contracts;

use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\DTO\PagamentoResponse;

interface PagamentosInterfaceV2
{
    public function obterNome(): string;

    public function creditCard(Cliente &$cliente, Pedido $pedido, Cartao|string $cartao): PagamentoResponse;

    public function pix(Cliente &$cliente, Pedido $pedido): PagamentoResponse;

    public function boleto(Cliente &$cliente, Pedido $pedido): PagamentoResponse;

    public function criarCliente(Cliente $cliente): Cliente;

    public function atualizarCliente(Cliente $cliente): Cliente;

    public function obterCliente(string $id): Cliente;

    public function salvarCartao(Cliente &$cliente, Cartao $cartao): Cartao;

    public function listarCartoes(Cliente &$cliente): array;

    public function deletarCartao(string $id): bool;

    public function obterTransacao(string $id): PagamentoResponse;

    public function listarTransacoes(array $filtros = []): array;

    public function estornar(string $idTransacao, float $valor): PagamentoResponse;

    public function cancelar(string $idTransacao): PagamentoResponse;

    public function reprocessarTransacaoRecusada(
        Cliente &$cliente,
        Pedido $pedido,
        Cartao|string $cartao,
        ?array $opcoes = null
    ): PagamentoResponse;
}
