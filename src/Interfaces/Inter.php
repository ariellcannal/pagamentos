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

class Inter implements PagamentosInterface
{
    private object $httpClient;
    private LoggerInterface $logger;
    private string $key;
    private string $clientSecret;
    private ?string $certificatePath;
    private ?string $certificatePassword;
    private string $nome;

    public function __construct(
        string $key,
        ?string $nome = null,
        ?LoggerInterface $logger = null,
        ?string $clientSecret = null,
        ?string $certificatePath = null,
        ?string $certificatePassword = null,
        bool $sandbox = true
    ) {
        $this->key = $key;
        $this->clientSecret = $clientSecret ?? '';
        $this->certificatePath = $certificatePath;
        $this->certificatePassword = $certificatePassword;
        $this->nome = $nome ?: 'Inter';
        $this->logger = $logger ?? new NullLogger();

        $baseUri = $sandbox
            ? 'https://cdpj.partners.bancointer.com.br/'
            : 'https://cdpj.bancointer.com.br/';

        $options = [
            'base_uri' => $baseUri,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Client-Id' => $this->key,
                'X-Client-Secret' => $this->clientSecret,
            ],
            'verify' => false,
        ];

        if ($this->certificatePath) {
            $options['cert'] = [$this->certificatePath, (string) $this->certificatePassword];
        }

        if (!class_exists('GuzzleHttp\\Client')) {
            throw new CANNALPagamentosException('Dependencia guzzlehttp/guzzle nao encontrada.');
        }

        $clientClass = 'GuzzleHttp\\Client';
        $this->httpClient = new $clientClass($options);
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao
    {
        throw new CANNALPagamentosException('Banco Inter nao suporta cartao de credito neste gateway.');
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        $payload = [
            'seuNumero' => $pedido->getId(),
            'valorNominal' => $pedido->getValor(),
            'dataVencimento' => date('Y-m-d', strtotime('+1 day')),
            'pagador' => [
                'cpfCnpj' => preg_replace('/\D+/', '', (string) $cli->getCpf()),
                'nome' => $cli->getNome(),
                'email' => $cli->getEmail(),
            ],
        ];

        $response = $this->request('POST', 'cobranca/v3/cobrancas', $payload);

        $transacao = $this->mapTransacao($response, 'pix');
        $transacao->setPixQrCode($response['pix']['emv'] ?? null);
        $transacao->setPixQrCodeUrl($response['pix']['imagemQrcode'] ?? null);

        return $transacao;
    }

    public function boleto(Cliente &$cli, Pedido $pedido): Transacao
    {
        $payload = [
            'seuNumero' => $pedido->getId(),
            'valorNominal' => $pedido->getValor(),
            'dataVencimento' => date('Y-m-d', strtotime('+7 day')),
            'pagador' => [
                'cpfCnpj' => preg_replace('/\D+/', '', (string) $cli->getCpf()),
                'nome' => $cli->getNome(),
                'email' => $cli->getEmail(),
            ],
        ];

        $response = $this->request('POST', 'cobranca/v3/cobrancas', $payload);
        return $this->mapTransacao($response, 'boleto');
    }

    public function refund(string $charge_id, int $amount): Transacao
    {
        $response = $this->request('POST', 'cobranca/v3/cobrancas/' . $charge_id . '/cancelar', ['valor' => $amount]);
        $transacao = $this->mapTransacao($response, null);
        $transacao->setValorCancelado((float) $amount);

        return $transacao;
    }

    public function saveCard(Cliente &$cli, Cartao $cartao): Cartao
    {
        throw new CANNALPagamentosException('Banco Inter nao suporta saveCard neste gateway.');
    }

    public function getCards(Cliente &$cli): array
    {
        return [];
    }

    public function updateCustumer(Cliente &$alu): Cliente
    {
        return $alu;
    }

    public function getReceivable(int $payable_id): ?Recebivel
    {
        throw new CANNALPagamentosException('Funcao getReceivable nao suportada no gateway Inter.');
    }

    public function getReceivables(string $charge_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array
    {
        throw new CANNALPagamentosException('Funcao getReceivables nao suportada no gateway Inter.');
    }

    public function getCharge(string $charge_id): ?Transacao
    {
        $response = $this->request('GET', 'cobranca/v3/cobrancas/' . $charge_id);
        return $this->mapTransacao($response, null);
    }

    public function cancelCharge(string $charge_id)
    {
        $response = $this->request('POST', 'cobranca/v3/cobrancas/' . $charge_id . '/cancelar');
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
            $this->logger->error('Inter request error: ' . $e->getMessage());
            throw new CANNALPagamentosException('Falha na comunicacao com Inter: ' . $e->getMessage(), 0, $e);
        }
    }

    private function mapTransacao(array $response, ?string $tipo): Transacao
    {
        $status = (string) ($response['situacao'] ?? $response['status'] ?? 'EM_ABERTO');

        $transacao = new Transacao();
        $transacao
            ->setTipo($tipo)
            ->setForma($tipo)
            ->setOperadora('Inter')
            ->setOperadoraID((string) ($response['codigoSolicitacao'] ?? $response['txid'] ?? ''))
            ->setOperadoraCodigo((string) ($response['seuNumero'] ?? ''))
            ->setOperadoraStatus($status)
            ->setOperadoraResposta(json_encode($response))
            ->setDataTransacao((string) ($response['dataHoraSituacao'] ?? $response['dataCriacao'] ?? date('Y-m-d H:i:s')))
            ->setValorBruto(isset($response['valorNominal']) ? (float) $response['valorNominal'] : null)
            ->setConfirmada(in_array(strtoupper($status), ['RECEBIDO', 'PAGO', 'LIQUIDADO'], true));

        return $transacao;
    }
}
