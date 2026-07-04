<?php

declare(strict_types=1);

namespace CANNALPagamentos\DTO;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;

class PagamentoResponse
{
    private ?string $id = null;
    private ?string $idOperadora = null;
    private ?string $status = null;
    private ?string $statusDescricao = null;
    private ?string $forma = null;
    private ?float $valor = null;
    private ?float $valorLiquido = null;
    private ?float $valorTaxa = null;
    private ?string $dataCriacao = null;
    private ?string $dataConfirmacao = null;
    private string $moeda = 'BRL';
    private ?string $pixQrCode = null;
    private ?string $pixQrCodeUrl = null;
    private ?string $boletoUrl = null;
    private ?string $boletoNumero = null;
    private ?string $erroMensagem = null;
    private ?string $erroCodigo = null;
    private ?string $banco = null;
    private ?string $bancoStatusOriginal = null;
    private array $dadosOperadora = [];

    private ?Pedido $pedido = null;
    private ?Cliente $cliente = null;
    private ?Transacao $transacao = null;

    public static function erro(string $codigo, string $mensagem): self
    {
        return (new self())
            ->setErroCodigo($codigo)
            ->setErroMensagem($mensagem)
            ->setStatus('FALHA')
            ->setStatusDescricao('Falha');
    }

    public function getId(): ?string { return $this->id; }
    public function setId(?string $id): self { $this->id = $id; return $this; }

    public function getIdOperadora(): ?string { return $this->idOperadora; }
    public function setIdOperadora(?string $idOperadora): self { $this->idOperadora = $idOperadora; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): self { $this->status = $status; return $this; }

    public function getStatusDescricao(): ?string { return $this->statusDescricao; }
    public function setStatusDescricao(?string $statusDescricao): self { $this->statusDescricao = $statusDescricao; return $this; }

    public function getForma(): ?string { return $this->forma; }
    public function setForma(?string $forma): self { $this->forma = $forma; return $this; }

    public function getValor(): ?float { return $this->valor; }
    public function setValor(?float $valor): self { $this->valor = $valor; return $this; }

    public function getValorLiquido(): ?float { return $this->valorLiquido; }
    public function setValorLiquido(?float $valorLiquido): self { $this->valorLiquido = $valorLiquido; return $this; }

    public function getValorTaxa(): ?float { return $this->valorTaxa; }
    public function setValorTaxa(?float $valorTaxa): self { $this->valorTaxa = $valorTaxa; return $this; }

    public function getDataCriacao(): ?string { return $this->dataCriacao; }
    public function setDataCriacao(?string $dataCriacao): self { $this->dataCriacao = $dataCriacao; return $this; }

    public function getDataConfirmacao(): ?string { return $this->dataConfirmacao; }
    public function setDataConfirmacao(?string $dataConfirmacao): self { $this->dataConfirmacao = $dataConfirmacao; return $this; }

    public function getMoeda(): string { return $this->moeda; }
    public function setMoeda(string $moeda): self { $this->moeda = $moeda; return $this; }

    public function getPixQrCode(): ?string { return $this->pixQrCode; }
    public function setPixQrCode(?string $pixQrCode): self { $this->pixQrCode = $pixQrCode; return $this; }

    public function getPixQrCodeUrl(): ?string { return $this->pixQrCodeUrl; }
    public function setPixQrCodeUrl(?string $pixQrCodeUrl): self { $this->pixQrCodeUrl = $pixQrCodeUrl; return $this; }

    public function getBoletoUrl(): ?string { return $this->boletoUrl; }
    public function setBoletoUrl(?string $boletoUrl): self { $this->boletoUrl = $boletoUrl; return $this; }

    public function getBoletoNumero(): ?string { return $this->boletoNumero; }
    public function setBoletoNumero(?string $boletoNumero): self { $this->boletoNumero = $boletoNumero; return $this; }

    public function getErroMensagem(): ?string { return $this->erroMensagem; }
    public function setErroMensagem(?string $erroMensagem): self { $this->erroMensagem = $erroMensagem; return $this; }

    public function getErroCodigo(): ?string { return $this->erroCodigo; }
    public function setErroCodigo(?string $erroCodigo): self { $this->erroCodigo = $erroCodigo; return $this; }

    public function getBanco(): ?string { return $this->banco; }
    public function setBanco(?string $banco): self { $this->banco = $banco; return $this; }

    public function getBancoStatusOriginal(): ?string { return $this->bancoStatusOriginal; }
    public function setBancoStatusOriginal(?string $bancoStatusOriginal): self { $this->bancoStatusOriginal = $bancoStatusOriginal; return $this; }

    public function getDadosOperadora(): array { return $this->dadosOperadora; }
    public function setDadosOperadora(array $dadosOperadora): self { $this->dadosOperadora = $dadosOperadora; return $this; }

    public function getPedido(): ?Pedido { return $this->pedido; }
    public function setPedido(?Pedido $pedido): self { $this->pedido = $pedido; return $this; }

    public function getCliente(): ?Cliente { return $this->cliente; }
    public function setCliente(?Cliente $cliente): self { $this->cliente = $cliente; return $this; }

    public function getTransacao(): ?Transacao { return $this->transacao; }
    public function setTransacao(?Transacao $transacao): self { $this->transacao = $transacao; return $this; }
}
