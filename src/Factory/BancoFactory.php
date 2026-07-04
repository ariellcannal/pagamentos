<?php

declare(strict_types=1);

namespace CANNALPagamentos\Factory;

use CANNALPagamentos\Exceptions\CANNALPagamentosException;
use CANNALPagamentos\Interfaces\Asaas;
use CANNALPagamentos\Interfaces\C6;
use CANNALPagamentos\Interfaces\Inter;
use CANNALPagamentos\Interfaces\Pagarme;
use CANNALPagamentos\Adapters\LegacyGatewayAdapter;
use CANNALPagamentos\Contracts\PagamentosInterfaceV2;
use CANNALPagamentos\DTO\CredenciaisAutenticacao;
use CANNALPagamentos\Mappers\AsaasMapper;
use CANNALPagamentos\Mappers\C6Mapper;
use CANNALPagamentos\Mappers\InterMapper;
use CANNALPagamentos\Mappers\PagarmeMapper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class BancoFactory
{
    public static function criar(
        CredenciaisAutenticacao $credenciais,
        ?LoggerInterface $logger = null
    ): PagamentosInterfaceV2 {
        $logger = $logger ?? new NullLogger();

        return match ($credenciais->getTipo()) {
            CredenciaisAutenticacao::BANCO_PAGARME => new LegacyGatewayAdapter(
                new Pagarme(
                    key: $credenciais->getChavePrivada(),
                    nome: $credenciais->getNomeOperadora(),
                    logger: $logger
                ),
                new PagarmeMapper(),
                CredenciaisAutenticacao::BANCO_PAGARME,
            ),
            CredenciaisAutenticacao::BANCO_ASAAS => new LegacyGatewayAdapter(
                new Asaas(
                    key: $credenciais->getChavePrivada(),
                    nome: $credenciais->getNomeOperadora(),
                    logger: $logger
                ),
                new AsaasMapper(),
                CredenciaisAutenticacao::BANCO_ASAAS,
            ),
            CredenciaisAutenticacao::BANCO_C6 => new LegacyGatewayAdapter(
                new C6(
                    key: $credenciais->getChavePublica(),
                    nome: $credenciais->getNomeOperadora(),
                    logger: $logger,
                    baseUrl: $credenciais->getBaseUrl(),
                    clientSecret: $credenciais->getChavePrivada(),
                    sandbox: $credenciais->isSandbox()
                ),
                new C6Mapper(),
                CredenciaisAutenticacao::BANCO_C6,
            ),
            CredenciaisAutenticacao::BANCO_INTER => new LegacyGatewayAdapter(
                new Inter(
                    key: $credenciais->getChavePublica(),
                    nome: $credenciais->getNomeOperadora(),
                    logger: $logger,
                    clientSecret: $credenciais->getChavePrivada(),
                    certificatePath: $credenciais->getCertificado(),
                    certificatePassword: $credenciais->getSenhaCertificado(),
                    sandbox: $credenciais->isSandbox()
                ),
                new InterMapper(),
                CredenciaisAutenticacao::BANCO_INTER,
            ),
            default => throw new CANNALPagamentosException('Banco nao suportado: ' . $credenciais->getTipo()),
        };
    }
}
