<?php
namespace App\Libraries\Pagamentos;

use App\Libraries\Pagamentos\Entities\Cliente;
use App\Libraries\Pagamentos\Entities\Pedido;
use App\Libraries\Pagamentos\Entities\Cartao;
use App\Libraries\Pagamentos\Entities\Transacao;
use App\Libraries\Pagamentos\Entities\Recebivel;

interface PagamentosInterface
{

    public function __construct(string $key, ?string $nome = null);

    public function getNome(): ?string;

    public function creditCard(Cliente &$cli, Pedido $pedido): Transacao;

    public function pix(Cliente &$cli, Pedido $pedido): Transacao;

    public function refund(string $charge_id, float $amount): Transacao;

    public function saveCard(Cliente &$cli, string $token): string;

    public function getCards(Cliente &$cli): array;

    public function updateCustumer(Cliente &$cli): Cliente;

    public function getReceivable(int $payable_id): ?Recebivel;

    public function getReceivables(string $charge_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array;

    public function getCharge(string $charge_id): ?Transacao;

    public function cancelCharge(string $charge_id): Transacao;
}