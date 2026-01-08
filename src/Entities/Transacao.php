<?php
namespace CANNALPagamentos\Entities;

class Transacao extends AbstractEntity
{

    protected ?int $id = null;

    protected ?int $inscricao = null;

    protected ?string $forma = null;

    protected ?string $tipo = null;

    protected ?string $cartao = null;

    protected ?int $parcelas = null;

    protected ?float $valorBruto = null;

    protected ?float $valorLiquido = null;

    protected ?float $valorCancelado = null;

    protected ?string $dataCancelamento = null;

    protected ?string $dataTransacao = null;

    protected ?string $dataExpiracao = null;

    protected ?string $pixQrCode = null;

    protected ?string $pixQrCodeUrl = null;

    protected ?bool $confirmada = null;

    protected ?string $descricaoFatura = null;

    protected ?string $operadora = null;

    protected ?string $operadoraResposta = null;

    protected ?string $operadoraErros = null;

    protected ?string $operadoraStatus = null;

    protected ?string $operadoraID = null;

    protected ?string $operadoraData = null;

    protected ?string $operadoraCodigo = null;

    protected ?string $criacao = null;

    public function __construct(?array $array = null, ?string $prefix = null)
    {
        if ($array) {
            $this->importArray($array, $prefix);
        }
        return $this;
    }

    // Getters and Setters
    /**
     * Get the value of id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the value of id
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
     * Get the value of inscricao
     *
     * @return int
     */
    public function getInscricao(): int
    {
        return $this->inscricao;
    }

    /**
     * Set the value of inscricao
     *
     * @param int $id
     * @return self
     */
    public function setInscricao(int $inscricao): self
    {
        $this->inscricao = $inscricao;
        return $this;
    }

    /**
     * Get the value of forma
     *
     * @return string|null
     */
    public function getForma(): ?string
    {
        return $this->forma;
    }

    /**
     * Set the value of forma
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
     * Get the value of tipo
     *
     * @return string|null
     */
    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    /**
     * Set the value of tipo
     *
     * @param string|null $tipo
     * @return self
     */
    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    /**
     * Get the value of cartao
     *
     * @return string|null
     */
    public function getCartao(): ?string
    {
        return $this->cartao;
    }

    /**
     * Set the value of cartao
     *
     * @param string|null $cartao
     * @return self
     */
    public function setCartao(?string $cartao): self
    {
        $this->cartao = $cartao;
        return $this;
    }

    /**
     * Get the value of parcelas
     *
     * @return int|null
     */
    public function getParcelas(): ?int
    {
        return $this->parcelas;
    }

    /**
     * Set the value of parcelas
     *
     * @param ?int $parcelas
     * @return self
     */
    public function setParcelas(?int $parcelas): self
    {
        $this->parcelas = $parcelas;
        return $this;
    }

    /**
     * Get the value of valorBruto
     *
     * @return float|null
     */
    public function getValorBruto(): ?float
    {
        return $this->valorBruto;
    }

    /**
     * Set the value of valorBruto
     *
     * @param float|null $valorBruto
     * @return self
     */
    public function setValorBruto(?float $valorBruto): self
    {
        $this->valorBruto = $valorBruto;
        return $this;
    }

    /**
     * Get the value of valorLiquido
     *
     * @return float|null
     */
    public function getValorLiquido(): ?float
    {
        return $this->valorLiquido;
    }

    /**
     * Set the value of valorLiquido
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
     * Get the value of valorCancelado
     *
     * @return float
     */
    public function getValorCancelado(): ?float
    {
        return $this->valorCancelado;
    }

    /**
     * Set the value of valorCancelado
     *
     * @param float|null $valorCancelado
     * @return self
     */
    public function setValorCancelado(?float $valorCancelado): self
    {
        $this->valorCancelado = $valorCancelado;
        return $this;
    }

    /**
     * Get the value of dataCancelamento
     *
     * @return string|null
     */
    public function getDataCancelamento(): ?string
    {
        return $this->dataCancelamento;
    }

    /**
     * Set the value of dataCancelamento
     *
     * @param string|null $dataCancelamento
     * @return self
     */
    public function setDataCancelamento(?string $dataCancelamento): self
    {
        $this->dataCancelamento = $dataCancelamento;
        return $this;
    }

    /**
     * Get the value of dataTransacao
     *
     * @return string|null
     */
    public function getDataTransacao(): ?string
    {
        return $this->dataTransacao;
    }

    /**
     * Set the value of dataTransacao
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
     * Get the value of dataExpiracao
     *
     * @return string|null
     */
    public function getDataExpiracao(): ?string
    {
        return $this->dataExpiracao;
    }

    /**
     * Set the value of dataExpiracao
     *
     * @param string|null $dataExpiracao
     * @return self
     */
    public function setDataExpiracao(?string $dataExpiracao): self
    {
        $this->dataExpiracao = $dataExpiracao;
        return $this;
    }

    /**
     * Get the value of pixQrCode
     *
     * @return string|null
     */
    public function getPixQrCode(): ?string
    {
        return $this->pixQrCode;
    }

    /**
     * Set the value of pixQrCode
     *
     * @param string|null $pixQrCode
     * @return self
     */
    public function setPixQrCode(?string $pixQrCode): self
    {
        $this->pixQrCode = $pixQrCode;
        return $this;
    }

    /**
     * Get the value of pixQrCodeUrl
     *
     * @return string|null
     */
    public function getPixQrCodeUrl(): ?string
    {
        return $this->pixQrCodeUrl;
    }

    /**
     * Set the value of pixQrCodeUrl
     *
     * @param string|null $pixQrCodeUrl
     * @return self
     */
    public function setPixQrCodeUrl(?string $pixQrCodeUrl): self
    {
        $this->pixQrCodeUrl = $pixQrCodeUrl;
        return $this;
    }

    /**
     * Get the value of confirmada
     *
     * @return bool|null
     */
    public function getConfirmada(): ?bool
    {
        return $this->confirmada;
    }

    /**
     * Set the value of confirmada
     *
     * @param bool|null $confirmada
     * @return self
     */
    public function setConfirmada(?bool $confirmada): self
    {
        $this->confirmada = $confirmada;
        return $this;
    }

    /**
     * Get the value of descricaoFatura
     *
     * @return string|null
     */
    public function getDescricaoFatura(): ?string
    {
        return $this->descricaoFatura;
    }

    /**
     * Set the value of descricaoFatura
     *
     * @param string|null $descricaoFatura
     * @return self
     */
    public function setDescricaoFatura(?string $descricaoFatura): self
    {
        $this->descricaoFatura = $descricaoFatura;
        return $this;
    }

    /**
     * Get the value of operadora
     *
     * @return string|null
     */
    public function getOperadora(): ?string
    {
        return $this->operadora;
    }

    /**
     * Set the value of operadora
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
     * Get the value of operadoraResposta
     *
     * @return string|null
     */
    public function getOperadoraResposta(): ?string
    {
        return $this->operadoraResposta;
    }

    /**
     * Set the value of operadoraResposta
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
     * Get the value of operadoraErros
     *
     * @return string|null
     */
    public function getOperadoraErros(): ?string
    {
        return $this->operadoraErros;
    }

    /**
     * Set the value of operadoraErros
     *
     * @param string|null $operadoraErros
     * @return self
     */
    public function setOperadoraErros(?string $operadoraErros): self
    {
        $this->operadoraErros = $operadoraErros;
        return $this;
    }

    /**
     * Get the value of operadoraStatus
     *
     * @return string|null
     */
    public function getOperadoraStatus(): ?string
    {
        return $this->operadoraStatus;
    }

    /**
     * Set the value of operadoraStatus
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
     * Get the value of operadoraID
     *
     * @return string|null
     */
    public function getOperadoraID(): ?string
    {
        return $this->operadoraID;
    }

    /**
     * Set the value of operadoraID
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
     * Get the value of criacao
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
     * @param string|null $criacao
     * @return self
     */
    public function setCriacao(?string $criacao): self
    {
        $this->criacao = $criacao;
        return $this;
    }

    /**
     * Get the value of operadoraCodigo
     *
     * @return string|null
     */
    public function getOperadoraCodigo(): ?string
    {
        return $this->operadoraCodigo;
    }

    /**
     * Set the value of operadoraCodigo
     *
     * @param string|null $operadoraCodigo
     * @return self
     */
    public function setOperadoraCodigo(?string $operadoraCodigo): self
    {
        $this->operadoraCodigo = $operadoraCodigo;
        return $this;
    }
}
