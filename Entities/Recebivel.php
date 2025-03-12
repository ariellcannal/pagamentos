<?php
namespace PagamentosCannal\Entities;

class Recebivel extends _EntityBase
{

    protected ?int $id = null;

    protected ?float $valor = null;

    protected ?float $valorLiquido = null;

    protected ?float $estornoValor = null;

    protected ?string $estornoData = null;

    protected ?string $forma = null;

    protected ?string $dataTransacao = null;

    protected ?string $dataRecebimento = null;

    protected ?bool $recebido = false;

    protected ?int $parcela = null;

    protected ?int $transacao = null;

    protected ?string $operadora = null;

    protected ?string $operadoraResposta = null;

    protected ?string $operadoraStatus = null;

    protected ?string $operadoraID = null;

    protected ?string $operadoraData = null;

    protected ?string $criacao = null;

    /**
     * Construtor da classe Recebiveis_model.
     */
    public function __construct(?array $array = null)
    {
        if ($array) {
            $this->importArray($array);
        }
        return $this;
    }

    /**
     * Obtém o ID do pagamento.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Define o ID do pagamento.
     *
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Obtém o valor do pagamento.
     *
     * @return float|null
     */
    public function getValor(): ?float
    {
        return $this->valor;
    }

    /**
     * Define o valor do pagamento.
     *
     * @param float|null $valor
     * @return self
     */
    public function setValor(?float $valor): self
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * Obtém o valor líquido do pagamento.
     *
     * @return float|null
     */
    public function getValorLiquido(): ?float
    {
        return $this->valorLiquido;
    }

    /**
     * Define o valor líquido do pagamento.
     *
     * @param float|null $valorLiquido
     * @return self
     */
    public function setValorLiquido(?float $valorLiquido): self
    {
        $this->valorLiquido = $valorLiquido;
        return $this;
    }

    /**
     * Obtém o valor de estorno.
     *
     * @return float|null
     */
    public function getEstornoValor(): ?float
    {
        return $this->estornoValor;
    }

    /**
     * Define o valor de estorno.
     *
     * @param float|null $estornoValor
     * @return self
     */
    public function setEstornoValor(?float $estornoValor): self
    {
        $this->estornoValor = $estornoValor;
        return $this;
    }

    /**
     * Obtém a data de estorno.
     *
     * @return string|null
     */
    public function getEstornoData(): ?string
    {
        return $this->estornoData;
    }

    /**
     * Define a data de estorno.
     *
     * @param string|null $estornoData
     * @return self
     */
    public function setEstornoData(?string $estornoData): self
    {
        $this->estornoData = $estornoData;
        return $this;
    }

    /**
     * Obtém a forma de pagamento.
     *
     * @return string|null
     */
    public function getForma(): ?string
    {
        return $this->forma;
    }

    /**
     * Define a forma de pagamento.
     *
     * @param string|null $forma
     * @return self
     */
    public function setForma(?string $forma): self
    {
        $this->forma = $forma;
        return $this;
    }

    /**
     * Obtém a data da transação.
     *
     * @return string|null
     */
    public function getDataTransacao(): ?string
    {
        return $this->dataTransacao;
    }

    /**
     * Define a data da transação.
     *
     * @param string|null $dataTransacao
     * @return self
     */
    public function setDataTransacao(?string $dataTransacao): self
    {
        $this->dataTransacao = $dataTransacao;
        return $this;
    }

    /**
     * Obtém a data de recebimento.
     *
     * @return string|null
     */
    public function getDataRecebimento(): ?string
    {
        return $this->dataRecebimento;
    }

    /**
     * Define a data de recebimento.
     *
     * @param string|null $dataRecebimento
     * @return self
     */
    public function setDataRecebimento(?string $dataRecebimento): self
    {
        $this->dataRecebimento = $dataRecebimento;
        return $this;
    }

    /**
     * Verifica se o recebível foi recebido.
     *
     * @return bool|null
     */
    public function isRecebido(): ?bool
    {
        return $this->recebido;
    }

    /**
     * Define se o recebível foi recebido.
     *
     * @param bool|null $recebido
     * @return self
     */
    public function setRecebido(?bool $recebido): self
    {
        $this->recebido = $recebido;
        return $this;
    }

    /**
     * Obtém o ID da parcela.
     *
     * @return int|null
     */
    public function getParcela(): ?int
    {
        return $this->parcela;
    }

    /**
     * Define o ID da parcela.
     *
     * @param int|null $parcela
     * @return self
     */
    public function setParcela(?int $parcela): self
    {
        $this->parcela = $parcela;
        return $this;
    }

    /**
     * Obtém o id da transação
     *
     * @return int|null
     */
    public function getTransacao(): ?int
    {
        return $this->transacao;
    }

    /**
     * Define o id da transacao
     *
     * @param int|null $transacao
     * @return self
     */
    public function setTransacao(?int $transacao): self
    {
        $this->transacao = $transacao;
        return $this;
    }

    /**
     * Obtém a operadora.
     *
     * @return string|null
     */
    public function getOperadora(): ?string
    {
        return $this->operadora;
    }

    /**
     * Define a operadora.
     *
     * @param string|null $operadora
     * @return self
     */
    public function setOperadora(?string $operadora): self
    {
        $this->operadora = $operadora;
        return $this;
    }

    /**
     * Obtém a resposta da operadora.
     *
     * @return string|null
     */
    public function getOperadoraResposta(): ?string
    {
        return $this->operadoraResposta;
    }

    /**
     * Define a resposta da operadora.
     *
     * @param string|null $operadoraResposta
     * @return self
     */
    public function setOperadoraResposta(?string $operadoraResposta): self
    {
        $this->operadoraResposta = $operadoraResposta;
        return $this;
    }

    /**
     * Obtém o status da operadora.
     *
     * @return string|null
     */
    public function getOperadoraStatus(): ?string
    {
        return $this->operadoraStatus;
    }

    /**
     * Define o status da operadora.
     *
     * @param string|null $operadoraStatus
     * @return self
     */
    public function setOperadoraStatus(?string $operadoraStatus): self
    {
        $this->operadoraStatus = $operadoraStatus;
        return $this;
    }

    /**
     * Obtém o ID da operadora.
     *
     * @return string|null
     */
    public function getOperadoraID(): ?string
    {
        return $this->operadoraID;
    }

    /**
     * Define o ID da operadora.
     *
     * @param string|null $operadoraID
     * @return self
     */
    public function setOperadoraID(?string $operadoraID): self
    {
        $this->operadoraID = $operadoraID;
        return $this;
    }

    /**
     * Get the value of operadoraData
     *
     * @return string|null
     */
    public function getOperadoraData(): ?string
    {
        return $this->operadoraData;
    }

    /**
     * Set the value of operadoraData
     *
     * @param string|null $operadoraData
     * @return self
     */
    public function setOperadoraData(?string $operadoraData): self
    {
        $this->operadoraData = $operadoraData;
        return $this;
    }

    /**
     * Obtém a data de criação do registro.
     *
     * @return string|null
     */
    public function getCriacao(): ?string
    {
        return $this->criacao;
    }

    /**
     * Set the value of criacao
     *
     * @param string|null $operadoraData
     * @return self
     */
    public function setCriacao(?string $criacao): self
    {
        $this->criacao = $criacao;
        return $this;
    }
}
