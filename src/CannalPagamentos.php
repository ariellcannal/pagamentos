<?php

declare(strict_types=1);

namespace CANNALPagamentos;

use CANNALPagamentos\Exceptions\CANNALPagamentosException;
use CANNALPagamentos\Contracts\PagamentosInterfaceV2;
use CANNALPagamentos\DTO\CredenciaisAutenticacao;
use CANNALPagamentos\DTO\PagamentoResponse;
use CANNALPagamentos\Factory\BancoFactory;
use CANNALPagamentos\Mappers\AsaasMapper;
use CANNALPagamentos\Mappers\C6Mapper;
use CANNALPagamentos\Mappers\InterMapper;
use CANNALPagamentos\Mappers\PagarmeMapper;
use CANNALPagamentos\Webhooks\WebhookFactory;
use CANNALPagamentos\Webhooks\WebhookSecurityConfig;
use CANNALPagamentos\Webhooks\WebhookSecurityValidator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CannalPagamentos
{
    /** @var array<string, PagamentosInterfaceV2> */
    private array $gateways = [];

    private WebhookFactory $webhookFactory;

    public function __construct(
        ?WebhookSecurityConfig $webhookSecurityConfig = null,
        ?LoggerInterface $logger = null
    ) {
        $logger = $logger ?? new NullLogger();

        $validator = new WebhookSecurityValidator($webhookSecurityConfig ?? new WebhookSecurityConfig());
        $this->webhookFactory = new WebhookFactory($validator);

        $this->webhookFactory
            ->registrar('pagarme', new \CANNALPagamentos\Webhooks\PagarmeWebhookProcessor($logger))
            ->registrar('asaas', new \CANNALPagamentos\Webhooks\AsaasWebhookProcessor($logger))
            ->registrar('c6', new \CANNALPagamentos\Webhooks\C6WebhookProcessor($logger))
            ->registrar('inter', new \CANNALPagamentos\Webhooks\InterWebhookProcessor($logger));
    }

    public function registrarBanco(CredenciaisAutenticacao $credenciais, ?LoggerInterface $logger = null): self
    {
        $gateway = BancoFactory::criar($credenciais, $logger);
        $this->gateways[$credenciais->getTipo()] = $gateway;
        return $this;
    }

    public function usar(string $banco): PagamentosInterfaceV2
    {
        $key = strtolower($banco);
        if (!isset($this->gateways[$key])) {
            throw new CANNALPagamentosException('Banco nao registrado: ' . $key);
        }

        return $this->gateways[$key];
    }

    /**
     * Metodo unico para processamento de webhook.
     *
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function webhook(array $payload, array $headers = [], string $rawPayload = ''): PagamentoResponse
    {
        $resultado = $this->webhookFactory->processar($payload, $headers, $rawPayload);
        $banco = $resultado['banco'];
        $transacao = $resultado['transacao'];

        $mapper = match ($banco) {
            'pagarme' => new PagarmeMapper(),
            'asaas' => new AsaasMapper(),
            'c6' => new C6Mapper(),
            'inter' => new InterMapper(),
            default => throw new CANNALPagamentosException('Mapper de webhook nao encontrado para ' . $banco),
        };

        return $mapper->mapearTransacao($transacao, null, null, $banco);
    }
}
