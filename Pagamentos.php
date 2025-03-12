<?php
namespace PagamentosCannal;

use PagamentosCannal\Entities\CartaoEntity;
use PagamentosCannal\Entities\PedidoEntity;

use OficinasCannal\Entities\OperadorasTransacoesEntity;
use OficinasCannal\Entities\AlunosEntity;
use OficinasCannal\Entities\OperadorasEntity;
use OficinasCannal\Entities\RecebiveisEntity;

interface Pagamentos
{

    public function __construct(OperadorasEntity $opr, bool $force_production = false);

    public function setKey(OperadorasEntity $opr, bool $force_production): self;

    public function creditCard(AlunosEntity &$alu, PedidoEntity $pedido, CartaoEntity|string $cartao): OperadorasTransacoesEntity;

    public function pix(AlunosEntity &$alu, PedidoEntity $pedido): OperadorasTransacoesEntity;

    public function refund(string $charge_id, int $amount): OperadorasTransacoesEntity;

    public function saveCard(AlunosEntity &$alu, CartaoEntity $cartao): CartaoEntity;

    public function getCards(AlunosEntity &$alu): array;

    public function updateCustumer(AlunosEntity &$alu): AlunosEntity;

    public function getReceivable(int $payable_id): ?RecebiveisEntity;

    public function getReceivables(string $charge_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array;

    public function getCharge(string  $charge_id): ?OperadorasTransacoesEntity;
    
    public function cancelCharge(string $charge_id);
}