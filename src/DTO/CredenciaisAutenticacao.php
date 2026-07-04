<?php

declare(strict_types=1);

namespace CANNALPagamentos\DTO;

use CANNALPagamentos\Exceptions\CANNALPagamentosException;

class CredenciaisAutenticacao
{
    public const BANCO_PAGARME = 'pagarme';
    public const BANCO_ASAAS = 'asaas';
    public const BANCO_C6 = 'c6';
    public const BANCO_INTER = 'inter';

    private string $tipo;
    private string $chavePublica;
    private string $chavePrivada;
    private ?string $certificado;
    private ?string $senhaCertificado;
    private ?string $nomeOperadora;
    private ?string $baseUrl;
    private bool $sandbox;

    public function __construct(
        string $tipo,
        string $chavePublica,
        string $chavePrivada,
        bool $sandbox = true,
        ?string $nomeOperadora = null,
        ?string $certificado = null,
        ?string $senhaCertificado = null,
        ?string $baseUrl = null
    ) {
        $this->tipo = strtolower(trim($tipo));
        $this->chavePublica = trim($chavePublica);
        $this->chavePrivada = trim($chavePrivada);
        $this->sandbox = $sandbox;
        $this->nomeOperadora = $nomeOperadora;
        $this->certificado = $certificado;
        $this->senhaCertificado = $senhaCertificado;
        $this->baseUrl = $baseUrl;

        $this->validate();
    }

    public function validate(): void
    {
        $tiposValidos = [
            self::BANCO_PAGARME,
            self::BANCO_ASAAS,
            self::BANCO_C6,
            self::BANCO_INTER,
        ];

        if (!in_array($this->tipo, $tiposValidos, true)) {
            throw new CANNALPagamentosException('Tipo de banco invalido: ' . $this->tipo);
        }

        if ($this->chavePublica === '' || $this->chavePrivada === '') {
            throw new CANNALPagamentosException('chavePublica e chavePrivada sao obrigatorias.');
        }

        if ($this->tipo === self::BANCO_INTER && ($this->certificado === null || $this->certificado === '')) {
            throw new CANNALPagamentosException('Banco Inter exige certificado.');
        }
    }

    public static function fromEnv(string $tipo, ?string $prefixo = null): self
    {
        $tipoNormalizado = strtolower(trim($tipo));
        $prefixoFinal = $prefixo ?: strtoupper($tipoNormalizado) . '_';

        $chavePublica = (string) getenv($prefixoFinal . 'CHAVE_PUBLICA');
        $chavePrivada = (string) getenv($prefixoFinal . 'CHAVE_PRIVADA');
        $sandboxRaw = getenv($prefixoFinal . 'SANDBOX');
        $sandbox = $sandboxRaw === false ? true : filter_var($sandboxRaw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($sandbox === null) {
            $sandbox = true;
        }

        return new self(
            tipo: $tipoNormalizado,
            chavePublica: $chavePublica,
            chavePrivada: $chavePrivada,
            sandbox: $sandbox,
            nomeOperadora: getenv($prefixoFinal . 'NOME_OPERADORA') ?: null,
            certificado: getenv($prefixoFinal . 'CERTIFICADO') ?: null,
            senhaCertificado: getenv($prefixoFinal . 'SENHA_CERTIFICADO') ?: null,
            baseUrl: getenv($prefixoFinal . 'BASE_URL') ?: null,
        );
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getChavePublica(): string
    {
        return $this->chavePublica;
    }

    public function getChavePrivada(): string
    {
        return $this->chavePrivada;
    }

    public function getCertificado(): ?string
    {
        return $this->certificado;
    }

    public function getSenhaCertificado(): ?string
    {
        return $this->senhaCertificado;
    }

    public function getNomeOperadora(): ?string
    {
        return $this->nomeOperadora;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
