<?php

declare(strict_types=1);

namespace CANNALPagamentos\Adapters;

use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Exceptions\CANNALPagamentosException;
use CANNALPagamentos\PagamentosInterface;
use CANNALPagamentos\Contracts\PagamentosInterfaceV2;
use CANNALPagamentos\DTO\PagamentoResponse;
use CANNALPagamentos\Mappers\BancoMapperInterface;

class LegacyGatewayAdapter implements PagamentosInterfaceV2
{
    public function __construct(
        private readonly PagamentosInterface $gateway,
        private readonly BancoMapperInterface $mapper,
        private readonly string $banco
    ) {
    }

    public function obterNome(): string
    {
        return $this->gateway->getNome() ?: strtoupper($this->banco);
    }

    public function creditCard(Cliente &$cliente, Pedido $pedido, Cartao|string $cartao): PagamentoResponse
    {
        $transacao = $this->gateway->creditCard($cliente, $pedido, $cartao);
        return $this->mapper->mapearTransacao($transacao, $pedido, $cliente, $this->banco);
    }

    public function pix(Cliente &$cliente, Pedido $pedido): PagamentoResponse
    {
        $transacao = $this->gateway->pix($cliente, $pedido);
        return $this->mapper->mapearTransacao($transacao, $pedido, $cliente, $this->banco);
    }

    public function boleto(Cliente &$cliente, Pedido $pedido): PagamentoResponse
    {
        $transacao = $this->gateway->boleto($cliente, $pedido);
        return $this->mapper->mapearTransacao($transacao, $pedido, $cliente, $this->banco);
    }

    public function criarCliente(Cliente $cliente): Cliente
    {
        $copy = clone $cliente;
        return $this->gateway->updateCustumer($copy);
    }

    public function atualizarCliente(Cliente $cliente): Cliente
    {
        $copy = clone $cliente;
        return $this->gateway->updateCustumer($copy);
    }

    public function obterCliente(string $id): Cliente
    {
        throw new CANNALPagamentosException('Operacao obterCliente nao suportada por ' . $this->banco);
    }

    public function salvarCartao(Cliente &$cliente, Cartao $cartao): Cartao
    {
        return $this->gateway->saveCard($cliente, $cartao);
    }

    public function listarCartoes(Cliente &$cliente): array
    {
        return $this->gateway->getCards($cliente);
    }

    public function deletarCartao(string $id): bool
    {
        throw new CANNALPagamentosException('Operacao deletarCartao nao suportada por ' . $this->banco);
    }

    public function obterTransacao(string $id): PagamentoResponse
    {
        $transacao = $this->gateway->getCharge($id);
        if ($transacao === null) {
            return PagamentoResponse::erro('TRANSACAO_NAO_ENCONTRADA', 'Transacao nao encontrada.');
        }

        return $this->mapper->mapearTransacao($transacao, null, null, $this->banco);
    }

    public function listarTransacoes(array $filtros = []): array
    {
        throw new CANNALPagamentosException('Operacao listarTransacoes nao suportada por ' . $this->banco);
    }

    public function estornar(string $idTransacao, float $valor): PagamentoResponse
    {
        $transacao = $this->gateway->refund($idTransacao, (int) round($valor));
        return $this->mapper->mapearTransacao($transacao, null, null, $this->banco);
    }

    public function cancelar(string $idTransacao): PagamentoResponse
    {
        $cancel = $this->gateway->cancelCharge($idTransacao);
        if (is_object($cancel) && method_exists($cancel, 'getOperadoraStatus')) {
            return $this->mapper->mapearTransacao($cancel, null, null, $this->banco);
        }

        $consulta = $this->gateway->getCharge($idTransacao);
        if ($consulta !== null) {
            return $this->mapper->mapearTransacao($consulta, null, null, $this->banco);
        }

        return PagamentoResponse::erro('CANCELAMENTO_NAO_CONFIRMADO', 'Cancelamento solicitado, mas sem retorno de transacao.');
    }

    public function reprocessarTransacaoRecusada(
        Cliente &$cliente,
        Pedido $pedido,
        Cartao|string $cartao,
        ?array $opcoes = null
    ): PagamentoResponse {
        // Reprocessamento de fallback: reenviar tentativa com o mesmo fluxo de cartao.
        return $this->creditCard($cliente, $pedido, $cartao);
    }
}
