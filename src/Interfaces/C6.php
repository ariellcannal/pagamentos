<?php

declare(strict_types=1);

namespace CANNALPagamentos\Interfaces;

use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Recebivel;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\Exceptions\CANNALPagamentosException;
use CANNALPagamentos\PagamentosInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class C6 implements PagamentosInterface
{
    private object $httpClient;
    private LoggerInterface $logger;
    private string $key;
    private string $clientSecret;
    private string $nome;

    public function __construct(
        string $key,
        ?string $nome = null,
        ?LoggerInterface $logger = null,
        ?string $baseUrl = null,
        ?string $clientSecret = null,
        bool $sandbox = true
    ) {
        $this->key = $key;
        $this->clientSecret = $clientSecret ?? '';
        $this->nome = $nome ?: 'C6';
        $this->logger = $logger ?? new NullLogger();

        $baseUri = $baseUrl ?: ($sandbox
            ? 'https://sandbox.api.c6bank.com.br/v1/'
            : 'https://api.c6bank.com.br/v1/');

        if (!class_exists('GuzzleHttp\\Client')) {
            throw new CANNALPagamentosException('Dependencia guzzlehttp/guzzle nao encontrada.');
        }

        $clientClass = 'GuzzleHttp\\Client';
        $this->httpClient = new $clientClass([
            'base_uri' => $baseUri,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Client-Id' => $this->key,
                'X-Client-Secret' => $this->clientSecret,
            ],
            'verify' => false,
        ]);
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao
    {
        $payload = [
            'payment_method' => 'credit_card',
            'amount' => $pedido->getValor(),
            'external_id' => $pedido->getId(),
            'customer' => [
                'name' => $cli->getNome(),
                'document' => $cli->getCpf(),
                'email' => $cli->getEmail(),
            ],
        ];

        if ($cartao instanceof Cartao) {
            $payload['card'] = [
                'holder_name' => $cartao->getNome(),
                'number' => preg_replace('/\D+/', '', $cartao->getNumero()),
                'exp_month' => $cartao->getVencimentoMes(),
                'exp_year' => $cartao->getVencimentoAno(),
                'cvv' => $cartao->getCodigo(),
            ];
        } else {
            $payload['card_token'] = $cartao;
        }

        $response = $this->request('POST', 'charges', $payload);

        return $this->mapTransacao($response, 'cartao');
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        $payload = [
            'payment_method' => 'pix',
            'amount' => $pedido->getValor(),
            'external_id' => $pedido->getId(),
            'customer' => [
                'name' => $cli->getNome(),
                'document' => $cli->getCpf(),
                'email' => $cli->getEmail(),
            ],
        ];

        $response = $this->request('POST', 'charges', $payload);
        $transacao = $this->mapTransacao($response, 'pix');

        if (isset($response['pix_code'])) {
            $transacao->setPixQrCode((string) $response['pix_code']);
        }

        if (isset($response['pix_qrcode_url'])) {
            $transacao->setPixQrCodeUrl((string) $response['pix_qrcode_url']);
        }

        return $transacao;
    }

    public function boleto(Cliente &$cli, Pedido $pedido): Transacao
    {
        $payload = [
            'payment_method' => 'boleto',
            'amount' => $pedido->getValor(),
            'external_id' => $pedido->getId(),
            'customer' => [
                'name' => $cli->getNome(),
                'document' => $cli->getCpf(),
                'email' => $cli->getEmail(),
            ],
        ];

        $response = $this->request('POST', 'charges', $payload);
        return $this->mapTransacao($response, 'boleto');
    }

    public function refund(string $charge_id, int $amount): Transacao
    {
        $response = $this->request('POST', 'charges/' . $charge_id . '/refund', ['amount' => $amount]);

        $transacao = $this->mapTransacao($response, null);
        $transacao->setValorCancelado((float) $amount);

        return $transacao;
    }

    public function saveCard(Cliente &$cli, Cartao $cartao): Cartao
    {
        $payload = [
            'customer_external_id' => (string) $cli->getId(),
            'holder_name' => $cartao->getNome(),
            'number' => preg_replace('/\D+/', '', $cartao->getNumero()),
            'exp_month' => $cartao->getVencimentoMes(),
            'exp_year' => $cartao->getVencimentoAno(),
            'cvv' => $cartao->getCodigo(),
        ];

        $response = $this->request('POST', 'cards/tokenize', $payload);
        if (isset($response['id'])) {
            $cartao->setId((string) $response['id']);
        }

        return $cartao;
    }

    public function getCards(Cliente &$cli): array
    {
        $response = $this->request('GET', 'customers/' . $cli->getId() . '/cards');
        return $response['data'] ?? [];
    }

    public function updateCustumer(Cliente &$alu): Cliente
    {
        return $alu;
    }

    public function getReceivable(int $payable_id): ?Recebivel
    {
        throw new CANNALPagamentosException('Funcao getReceivable nao suportada no gateway C6.');
    }

    public function getReceivables(string $charge_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array
    {
        throw new CANNALPagamentosException('Funcao getReceivables nao suportada no gateway C6.');
    }

    public function getCharge(string $charge_id): ?Transacao
    {
        $response = $this->request('GET', 'charges/' . $charge_id);
        return $this->mapTransacao($response, null);
    }

    public function cancelCharge(string $charge_id)
    {
        $response = $this->request('POST', 'charges/' . $charge_id . '/cancel');
        return $this->mapTransacao($response, null);
    }

    private function request(string $method, string $uri, ?array $payload = null): array
    {
        try {
            $options = [];
            if ($payload !== null) {
                $options['json'] = $payload;
            }

            $response = $this->httpClient->request($method, $uri, $options);
            $content = (string) $response->getBody();
            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            $this->logger->error('C6 request error: ' . $e->getMessage());
            throw new CANNALPagamentosException('Falha na comunicacao com C6: ' . $e->getMessage(), 0, $e);
        }
    }

    private function mapTransacao(array $response, ?string $tipo): Transacao
    {
        $transacao = new Transacao();
        $transacao
            ->setTipo($tipo)
            ->setForma($tipo)
            ->setOperadora('C6')
            ->setOperadoraID((string) ($response['id'] ?? $response['external_id'] ?? ''))
            ->setOperadoraStatus((string) ($response['status'] ?? 'PENDING'))
            ->setOperadoraResposta(json_encode($response))
            ->setOperadoraCodigo((string) ($response['external_id'] ?? ''))
            ->setValorBruto(isset($response['amount']) ? (float) $response['amount'] : null)
            ->setDataTransacao((string) ($response['created_at'] ?? date('Y-m-d H:i:s')))
            ->setConfirmada(in_array(strtoupper((string) ($response['status'] ?? '')), ['PAID', 'APPROVED', 'RECEIVED'], true));

        return $transacao;
    }
}
