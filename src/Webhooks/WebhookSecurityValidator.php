<?php

declare(strict_types=1);

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\Exceptions\CANNALPagamentosException;

class WebhookSecurityValidator
{
    public function __construct(private readonly WebhookSecurityConfig $config)
    {
    }

    /**
     * @param array<string, string> $headers
     */
    public function validar(string $banco, array $headers, string $rawPayload = ''): void
    {
        $this->validarToken($banco, $headers);
        $this->validarTimestamp($headers);
        $this->validarSignature($banco, $headers, $rawPayload);
        $this->validarIp($banco, $headers);
    }

    /** @param array<string, string> $headers */
    private function validarToken(string $banco, array $headers): void
    {
        $tokenEsperado = $this->config->getToken($banco);
        if ($tokenEsperado === null || $tokenEsperado === '') {
            return;
        }

        $tokenRecebido = $headers['X-Webhook-Token'] ?? $headers['x-webhook-token'] ?? null;
        if ($tokenRecebido !== $tokenEsperado) {
            throw new CANNALPagamentosException('Token de webhook invalido para ' . $banco);
        }
    }

    /** @param array<string, string> $headers */
    private function validarTimestamp(array $headers): void
    {
        $timestamp = $headers['X-Webhook-Timestamp'] ?? $headers['x-webhook-timestamp'] ?? null;
        if ($timestamp === null || $timestamp === '') {
            return;
        }

        $timestampInt = (int) $timestamp;
        $agora = time();

        if (abs($agora - $timestampInt) > $this->config->getMaxTimestampSkew()) {
            throw new CANNALPagamentosException('Timestamp de webhook fora da janela permitida.');
        }
    }

    /** @param array<string, string> $headers */
    private function validarSignature(string $banco, array $headers, string $rawPayload): void
    {
        $signatureKey = $this->config->getSignatureKey($banco);
        if ($signatureKey === null || $signatureKey === '' || $rawPayload === '') {
            return;
        }

        $assinatura = $headers['X-Webhook-Signature'] ?? $headers['x-webhook-signature'] ?? null;
        if ($assinatura === null || $assinatura === '') {
            throw new CANNALPagamentosException('Assinatura de webhook ausente para ' . $banco);
        }

        $esperada = hash_hmac('sha256', $rawPayload, $signatureKey);
        $normalizada = str_starts_with($assinatura, 'sha256=') ? substr($assinatura, 7) : $assinatura;

        if (!hash_equals($esperada, $normalizada)) {
            throw new CANNALPagamentosException('Assinatura de webhook invalida para ' . $banco);
        }
    }

    /** @param array<string, string> $headers */
    private function validarIp(string $banco, array $headers): void
    {
        $ipsPermitidos = $this->config->getAllowIps($banco);
        if ($ipsPermitidos === []) {
            return;
        }

        $ipOrigem = $headers['X-Forwarded-For'] ?? $headers['x-forwarded-for'] ?? $headers['Remote-Addr'] ?? $headers['remote-addr'] ?? null;
        if ($ipOrigem === null || $ipOrigem === '') {
            throw new CANNALPagamentosException('IP de origem nao informado para validacao de webhook.');
        }

        $ipOrigem = trim(explode(',', $ipOrigem)[0]);

        foreach ($ipsPermitidos as $ipPermitido) {
            if ($ipOrigem === $ipPermitido) {
                return;
            }
        }

        throw new CANNALPagamentosException('IP nao autorizado para webhook do banco ' . $banco);
    }
}
