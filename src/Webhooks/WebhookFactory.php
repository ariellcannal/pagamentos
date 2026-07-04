<?php

declare(strict_types=1);

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\Exceptions\CANNALPagamentosException;
use CANNALPagamentos\Interfaces\WebhookProcessorInterface;

class WebhookFactory
{
    /** @var array<string, WebhookProcessorInterface> */
    private array $processors = [];

    public function __construct(private readonly WebhookSecurityValidator $validator)
    {
    }

    public function registrar(string $banco, WebhookProcessorInterface $processor): self
    {
        $this->processors[strtolower($banco)] = $processor;
        return $this;
    }

    /**
     * @param array<string, string> $headers
     */
    public function processar(array $payload, array $headers = [], string $rawPayload = ''): array
    {
        $banco = $this->detectarBanco($payload, $headers);

        if (!isset($this->processors[$banco])) {
            throw new CANNALPagamentosException('Nenhum processador de webhook registrado para ' . $banco);
        }

        $this->validator->validar($banco, $headers, $rawPayload);

        $transacao = $this->processors[$banco]->process($payload, $headers);
        if (!$transacao instanceof Transacao) {
            throw new CANNALPagamentosException('Processador de webhook retornou tipo invalido para ' . $banco);
        }

        return [
            'banco' => $banco,
            'transacao' => $transacao,
        ];
    }

    /**
     * @param array<string, string> $headers
     */
    public function detectarBanco(array $payload, array $headers): string
    {
        $headersLower = [];
        foreach ($headers as $key => $value) {
            $headersLower[strtolower($key)] = $value;
        }

        if (isset($headersLower['x-hub-signature']) || isset($payload['id']) && str_starts_with((string) $payload['id'], 'evt_')) {
            return 'pagarme';
        }

        if (isset($headersLower['asaas-access-token']) || isset($payload['event']) && str_starts_with((string) $payload['event'], 'PAYMENT_')) {
            return 'asaas';
        }

        if (isset($headersLower['x-c6-signature']) || isset($payload['information']) || (($payload['operadora'] ?? null) === 'C6')) {
            return 'c6';
        }

        if (isset($headersLower['x-inter-signature']) || isset($payload['cobranca']) || (($payload['operadora'] ?? null) === 'Inter')) {
            return 'inter';
        }

        throw new CANNALPagamentosException('Nao foi possivel detectar o banco para o webhook.');
    }
}
