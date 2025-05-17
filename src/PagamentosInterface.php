<?php
namespace CANNALPagamentos;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\Entities\Recebivel;

interface PagamentosInterface
{

    private ?string $key = null;

    private ?string $nome = null;

    private ?string $log = null;

    public function __construct(string $key, ?string $nome = null);

    public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao;

    public function pix(Cliente &$cli, Pedido $pedido): Transacao;

    public function refund(string $charge_id, int $amount): Transacao;

    public function saveCard(Cliente &$cli, Cartao $cartao): Cartao;

    public function getCards(Cliente &$cli): array;

    public function updateCustumer(Cliente &$alu): Cliente;

    public function getReceivable(int $payable_id): ?Recebivel;

    public function getReceivables(string $charge_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array;

    public function getCharge(string $charge_id): ?Transacao;

    public function cancelCharge(string $charge_id);
}