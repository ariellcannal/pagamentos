<?php
namespace PagamentosCannal\Entities;

class CartaoEntity
{

    private string $numero;

    private string $codigo;

    private int $vencimento_mes;

    private int $vencimento_ano;

    private string $nome;

    private ?bool $salvar = true;

    private ?string $id;

    private ?string $ultimosQuatro;

    private ?string $bandeira;

    public function __construct()
    {
        return $this;
    }

    /**
     * Obtém número do cartão
     *
     * @return string
     */
    public function getNumero(): string
    {
        return $this->numero;
    }

    /**
     * Define o número do cartão
     *
     * @param string $numero
     * @return self
     */
    public function setNumero(string $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    /**
     * Obtém o código.
     *
     * @return string
     */
    public function getCodigo(): string
    {
        return $this->codigo;
    }

    /**
     * Define o código.
     *
     * @param string $codigo
     * @return self
     */
    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * Obtém o mês de vencimento
     *
     * @return int
     */
    public function getVencimentoMes(): int
    {
        return $this->vencimento_mes;
    }

    /**
     * Define o mês de vencimento
     *
     * @param int $vencimento_mes
     * @return self
     */
    public function setVencimentoMes(int $vencimento_mes): self
    {
        $this->vencimento_mes = $vencimento_mes;
        return $this;
    }

    /**
     * Obtém o ano de vencimento
     *
     * @return int
     */
    public function getVencimentoAno(): int
    {
        return $this->vencimento_ano;
    }

    /**
     * Define o ano de vencimento.
     *
     * @param int $vencimento_ano
     * @return self
     */
    public function setVencimentoAno(int $vencimento_ano): self
    {
        $this->vencimento_ano = $vencimento_ano;
        return $this;
    }

    /**
     * Obtém o nome
     *
     * @return string
     */
    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * Define o nome
     *
     * @param string $nome
     * @return self
     */
    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * Obtém salvar
     *
     * @return bool
     */
    public function getSalvar(): bool
    {
        return $this->salvar;
    }

    /**
     * Define o salvar
     *
     * @param bool $salvar
     * @return self
     */
    public function setSalvar(bool $salvar): self
    {
        $this->salvar = $salvar;
        return $this;
    }

    /**
     * Obtém o id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Define o id
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
     * Obtém os últimos quatro dígitos
     *
     * @return string
     */
    public function getUltimosQuatro(): string
    {
        return $this->ultimosQuatro;
    }

    /**
     * Define os últimos quatro dígitos
     *
     * @param string $ultimosQuatro
     * @return self
     */
    public function setUltimosQuatro(string $ultimosQuatro): self
    {
        $this->ultimosQuatro = $ultimosQuatro;
        return $this;
    }

    /**
     * Obtém a bandeira
     *
     * @return string
     */
    public function getBandeira(): string
    {
        return $this->bandeira;
    }

    /**
     * Define a bandeira
     *
     * @param string $bandeira
     * @return self
     */
    public function setBandeira(string $bandeira): self
    {
        $this->bandeira = $bandeira;
        return $this;
    }
}
