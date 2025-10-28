<?php

namespace CANNALPagamentos\Interfaces;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Recebivel;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\PagamentosInterface;
use PagarmeApiSDKLib\PagarmeApiSDKClient;
use PagarmeApiSDKLib\PagarmeApiSDKClientBuilder;
use PagarmeApiSDKLib\Authentication\BasicAuthCredentialsBuilder;
use PagarmeApiSDKLib\Models\CreateAddressRequest;
use PagarmeApiSDKLib\Models\CreateCustomerRequest;
use PagarmeApiSDKLib\Models\CreatePixPaymentRequest;
use apimatic\jsonmapper\JsonMapperException;
use PagarmeApiSDKLib\Models\CreateCancelChargeRequest;
use PagarmeApiSDKLib\Models\CreateCreditCardPaymentRequest;
use PagarmeApiSDKLib\Models\CreateCardRequest;
use PagarmeApiSDKLib\Models\CreateCardOptionsRequest;
use PagarmeApiSDKLib\Models\GetChargeResponse;
use PagarmeApiSDKLib\Models\Builders\CreateOrderRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreateOrderItemRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreatePaymentRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreateCustomerRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\UpdateCustomerRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreatePhonesRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreatePhoneRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreateCardRequestBuilder;
use PagarmeApiSDKLib\Models\Builders\CreateCardOptionsRequestBuilder;
use PagarmeApiSDKLib\Models\CreatePhonesRequest;
use RuntimeException;
use Throwable;

class Pagarme implements PagamentosInterface
{
    private ?CreateCustomerRequest $custumer = null;
    private ?CreateAddressRequest $custumer_address = null;
    private ?PagarmeApiSDKClient $client = null;
    private ?string $key = null;
    private ?string $nome = null;
    private ?LoggerInterface $logger = null;

    public function __construct(string $key, ?string $nome = null, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->key = $key;
        $this->nome = $nome ?? 'Pagarme';
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    private function handleException(Throwable $exception): void
    {
        // ... (código de handleException)
    }

    private function normalizeException(Throwable $exception, ?string $rawBody): array
    {
        // ... (código de normalizeException)
    }

    private function extractApiErrorMessage(mixed $payload): ?string
    {
        // ... (código de extractApiErrorMessage)
    }

    private function configureHttpClient(PagarmeApiSDKClient $client): void
    {
        // ... (código de configureHttpClient)
    }

    private function getClient(): PagarmeApiSDKClient
    {
        // ... (código de getClient)
    }

    public function getCustumerAddress(Cliente &$cli): bool|CreateAddressRequest
    {
        // ... (código de getCustumerAddress)
    }

    public function updateCustumer(Cliente &$cli): Cliente
    {
        // ... (código de updateCustumer)
    }

    public function getCustumer(Cliente &$cli): CreateCustomerRequest
    {
        // ... (código de getCustumer)
    }

    public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao
    {
        // ... (código de creditCard)
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        // ... (código de pix)
    }

    public function boleto(Cliente &$cli, Pedido $pedido): Transacao
    {
        if (! $this->client) {
            $this->getClient();
        }

        try {
            $ordersController = $this->client->getOrdersController();
            $this->getCustumer($cli);

            $boleto = \PagarmeApiSDKLib\Models\Builders\CreateBoletoPaymentRequestBuilder::init()
                ->dueAt(new \DateTime(date('Y-m-d', strtotime('+7 days'))))
                ->instructions('Não receber após o vencimento.')
                ->build();

            $body = CreateOrderRequestBuilder::init([
                CreateOrderItemRequestBuilder::init($pedido->getValor(), $pedido->getNomeDoItem(), 1, 'oficinas')->code($pedido->getId())
                    ->build()
            ], $this->custumer, [
                CreatePaymentRequestBuilder::init('boleto')->boleto($boleto)->build()
            ], $pedido->getId(), true, null, false, $_SERVER['REMOTE_ADDR'])->build();

            $this->logger->debug('BOLETO REQUEST:' . PHP_EOL . json_encode($body->jsonSerialize()));
            $order = $ordersController->createOrder($body);
            $this->logger->debug('BOLETO RESPONSE:' . PHP_EOL . json_encode($order->jsonSerialize()));

            $transacao = new Transacao();
            $charge = $order->getCharges()[0];
            $payment = $charge->getLastTransaction();

            $transacao->setOperadoraID($charge->getId());
            $transacao->setOperadoraStatus($charge->getStatus());
            $transacao->setValorBruto($charge->getAmount() / 100);
            $transacao->setDataExpiracao($payment->getDueAt()->format('Y-m-d H:i:s'));
            $transacao->setPixQrCode($payment->getQrCode());
            $transacao->setOperadoraCodigo($order->getCode());
            $transacao->setOperadora('Pagarme');
            
            return $transacao;
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function refund(string $charge_id, int $amount): Transacao
    {
        // ... (código de refund)
    }

    public function saveCard(Cliente &$cli, Cartao $cartao): Cartao
    {
        // ... (código de saveCard)
    }

    public function getCards(Cliente &$cli): array
    {
        // ... (código de getCards)
    }

    public function getReceivable(int $payable_id): ?Recebivel
    {
        // ... (código de getReceivable)
    }

    public function getReceivables(string $charge_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array
    {
        // ... (código de getReceivables)
    }

    public function getCharge(string $charge_id): ?Transacao
    {
        // ... (código de getCharge)
    }

    public function cancelCharge(string $charge_id)
    {
        // ... (código de cancelCharge)
    }
}
