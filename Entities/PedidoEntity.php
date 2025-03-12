<?php
namespace PagamentosCannal\Entities;

class PedidoEntity
{

    private string $id;

    private string $valor;

    private int $parcelas;

    private string $descricaoFatura;

    private string $nomeDoItem;

    public function __construct(string $id, string $valor, int $parcelas, string $descricaoFatura, string $nomeDoItem)
    {
        $this->id = $id;
        $this->valor = $valor;
        $this->parcelas = $parcelas;
        $this->descricaoFatura = $descricaoFatura;
        $this->nomeDoItem = $nomeDoItem;
        return $this;
    }

    /**
     * Obtém o ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Define o ID do pedido
     *
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Obtém o valor.
     *
     * @return float
     */
    public function getValor(): float
    {
        return $this->valor;
    }

    /**
     * Define o valor.
     *
     * @param float $valor
     * @return self
     */
    public function setValor(float $valor): self
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * Obtém a quantidade de parcelas.
     *
     * @return int
     */
    public function getParcelas(): int
    {
        return $this->parcelas;
    }

    /**
     * Define a quantidade de parcelas.
     *
     * @param int $parcelas
     * @return self
     */
    public function setParcelas(int $parcelas): self
    {
        $this->parcelas = $parcelas;
        return $this;
    }

    /**
     * Obtém a descrição na fatura
     *
     * @return string
     */
    public function getDescricaoFatura(): string
    {
        return $this->descricaoFatura;
    }

    /**
     * Define a descricao na fatura.
     *
     * @param string $descricaoFatura
     * @return self
     */
    public function setDescricaoFatura(float $descricaoFatura): self
    {
        $this->descricaoFatura = $descricaoFatura;
        return $this;
    }

    /**
     * Obtém o nome do item
     *
     * @return string
     */
    public function getNomeDoItem(): string
    {
        return $this->nomeDoItem;
    }

    /**
     * Define o nome do item
     *
     * @param string $nomeDoItem
     * @return self
     */
    public function setNomeDoItem(string $nomeDoItem): self
    {
        $this->nomeDoItem = $nomeDoItem;
        return $this;
    }
}
