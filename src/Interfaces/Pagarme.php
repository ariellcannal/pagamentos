<?php
namespace CANNALPagamentos\Interfaces;

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
use PagarmeApiSDKLib\Exceptions\ErrorException;
use PagarmeApiSDKLib\Exceptions\ApiException;
use CANNALLogs\Log;

class Pagarme implements PagamentosInterface
{

    private ?CreateCustomerRequest $custumer = null;

    private ?CreateAddressRequest $custumer_address = null;

    private ?PagarmeApiSDKClient $client = null;

    private ?string $key = null;

    private ?string $nome = null;

    private ?string $log = null;

    public function __construct(string $key, ?string $nome = null)
    {
        $this->key = $key;

        if ($nome) {
            $this->nome = $nome;
        } else {
            $this->nome = 'Pagarme';
        }

        $this->log = new Log();
    }

    private function exception($ex)
    {
        $this->log->write('ERROR', 'PAGARME ERROR:' . PHP_EOL . $ex->getHttpResponse()
            ->getRawBody() . PHP_EOL . $ex->getTraceAsString());
        $text = json_decode($ex->getHttpResponse()->getRawBody())->message;
        set_status_header($ex->getHttpResponse()->getStatusCode(), $text);
        echo $text;
        exit(1); // EXIT_ERROR
    }

    private function getClient()
    {
        try {
            $this->client = PagarmeApiSDKClientBuilder::init()->basicAuthCredentials(BasicAuthCredentialsBuilder::init($this->key, 'BasicAuthPassword'))->build();
            return $this->client;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function getCustumerAddress(Cliente &$cli): CreateAddressRequest
    {
        if (! empty($this->custumer_address) && $this->custumer_address instanceof CreateAddressRequest) {
            return $this->custumer_address;
        } else {
            try {
                if (empty($cli->getEnderecoCidade()) || empty($cli->getEnderecoEstado())) {
                    return false;
                }
                if (! $this->client) {
                    $this->getClient();
                }

                if (ENVIRONMENT == 'production' && $cli->getPagarmeId() != "") {
                    $customerController = $this->client->getCustomersController();
                    $addresses = $customerController->getAddresses($cli->getPagarmeId());
                    foreach ($addresses->getData() as $addr) {
                        $customerController->deleteAddress($cli->getPagarmeId(), $addr->getId());
                    }
                }

                $cli->setEnderecoCep(str_pad(preg_replace('/[^0-9]/', '', $cli->getEnderecoCep()), 8, '0', STR_PAD_LEFT));

                $this->log->write('DEBUG', 'CREATE ADDRESS CEP: ' . $cli->getEnderecoCep());

                if (empty($cli->getEnderecoComplemento())) {
                    $cli->setEnderecoComplemento('');
                }

                $this->custumer_address = new CreateAddressRequest($cli->getEndereco(), $cli->getEnderecoNumero(), $cli->getEnderecoCep(), $cli->getEnderecoBairro(), $cli->getEnderecoCidade(), $cli->getEnderecoEstado(), 'BR', $cli->getEnderecoComplemento(), $cli->getEndereco() . ', ' . $cli->getEnderecoNumero() . ($cli->getEnderecoComplemento() != "" ? ', ' . $cli->getEnderecoComplemento() : ''), $cli->getEnderecoBairro() . ', ' . $cli->getEnderecoCidade() . '/' . $cli->getEnderecoEstado() . ' - ' . $cli->getEnderecoCep());

                $this->log->write('DEBUG', 'CREATE ADDRESS REQUEST:' . PHP_EOL . json_encode($this->custumer_address));

                return $this->custumer_address;
            } catch (ErrorException $e) {
                $this->exception($e);
            } catch (ApiException $e) {
                $this->exception($e);
            }
        }
    }

    public function updateCustumer(Cliente &$cli): Cliente
    {
        if (! empty($this->custumer) && $this->custumer_address instanceof CreateCustomerRequest) {
            return $this->custumer;
        } else {
            if (! $this->client) {
                $this->getClient();
            }
            try {
                $cli->setCelular(preg_replace('/[^0-9]/', '', $cli->getCelular()));
                $cli->setCpf(preg_replace('/[^0-9]/', '', $cli->getCpf()));

                $customerController = $this->client->getCustomersController();

                $phone = CreatePhonesRequestBuilder::init()->mobilePhone(CreatePhoneRequestBuilder::init()->areaCode(substr($cli->getCelular(), 0, 2))
                    ->countryCode("55")
                    ->number(substr($cli->getCelular(), 2, strlen($cli->getCelular()) - 2))
                    ->build())
                    ->build();

                $address = $this->getCustumerAddress($cli);

                $this->custumer = CreateCustomerRequestBuilder::init($cli->getNome(), $cli->getEmail(), $cli->getCpf(), 'individual', $address, [
                    'alu_id' => $cli->getId()
                ], $phone, $cli->getId())->build();
                if (ENVIRONMENT == 'production' && $cli->getPagarmeId()) {
                    $result = $customerController->updateCustomer($cli->getPagarmeId(), UpdateCustomerRequestBuilder::init()->name($cli->getNome())
                        ->email($cli->getEmail())
                        ->document($cli->getCpf())
                        ->type('individual')
                        ->address($address)
                        ->metadata([
                        'alu_id' => $cli->getId()
                    ])
                        ->phones($phone)
                        ->code($cli->getId())
                        ->build());
                    $this->log->write('DEBUG', 'UPDATE CUSTUMER REQUEST:' . PHP_EOL . json_encode($result->jsonSerialize()));
                } else {
                    $result = $customerController->createCustomer($this->custumer);
                    $cli->setPagarmeId($result->getId());
                    $this->log->write('DEBUG', 'CREATE CUSTUMER REQUEST:' . PHP_EOL . json_encode($result->jsonSerialize()));
                }
                return $cli;
            } catch (ErrorException $e) {
                $this->exception($e);
            } catch (ApiException $e) {
                $this->exception($e);
            }
        }
    }

    public function saveCard(Cliente &$cli, Cartao $cartao): Cartao
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }

            $customerController = $this->client->getCustomersController();

            $card = CreateCardRequestBuilder::init()->type('credit')
                ->number(preg_replace('/[^0-9]/', '', $cartao->getNumero()))
                ->holderName($cartao->getNome())
                ->expMonth($cartao->getVencimentoMes())
                ->expYear($cartao->getVencimentoAno())
                ->cvv($cartao->getCodigo())
                ->billingAddress($this->getCustumerAddress($cli))
                ->metadata([
                'alu_id' => $cli->getId()
            ])
                ->privateLabel(false)
                ->options(CreateCardOptionsRequestBuilder::init(true))
                ->build();

            $result = $customersController->createCard($this->updateCustumer($cli)
                ->getPagarmeId(), $card);
            $this->log->write('DEBUG', 'SAVE CARD REQUEST:' . PHP_EOL . json_encode($result->jsonSerialize()));

            $cartao->setId($result->getId());
            return $cartao;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function getCards(Cliente &$cli): array
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }

            $customerController = $this->client->getCustomersController();
            $result = $customerController->getCards($cli->getPagarmeId());
            $retorno = [];
            foreach ($result->getData() as $card) {
                $cartao = new Cartao();
                $retorno[] = $cartao->setId($card->getId())
                    ->setVencimentoMes($card->getExpMonth())
                    ->setVencimentoAno($card->getExpYear())
                    ->setUltimosQuatro($card->getLastFourDigits())
                    ->setBandeira($card->getBrand());
            }
            return $retorno;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }

            $ordersController = $this->client->getOrdersController();

            $this->updateCustumer($cli);

            $pedido->setValor(number_format($pedido->getValor(), 2, '', ''));

            $creditCard = new CreateCreditCardPaymentRequest();
            if ($cartao instanceof Cartao) {
                $card = new CreateCardRequest();
                $card->setNumber(preg_replace('/[^0-9]/', '', $cartao->getNumero()));
                $card->setHolderName($cartao->getNome());
                $card->setExpMonth($cartao->getVencimentoMes());
                $card->setExpYear($cartao->getVencimentoAno());
                $card->setCvv($cartao->getCodigo());
                $card->setBillingAddress($this->getCustumerAddress($cli));
                $card->setMetadata([
                    'alu_id' => $cli->getId()
                ]);
                $card->setOptions(new CreateCardOptionsRequest(true));
                $card->setPrivateLabel(false);
                $card->setType('credit');

                $creditCard->setCard($card);

                if (ENVIRONMENT == 'production' && $cartao->getSalvar() && $cli->getPagarmeId()) {
                    $customerController = $this->client->getCustomersController();
                    $customerController->createCard($cli->getPagarmeId(), $card);
                }
            } else {
                $customerController = $this->client->getCustomersController();
                try {
                    if (ENVIRONMENT == 'production') {
                        // $card = $customerController->getCard($alu->getPagarmeId(), $cartao);
                        // $customerController->updateCard($alu->getPagarmeId(), $cartao, UpdateCardRequestBuilder::init($card->getHolderName(), $card->getExpMonth(), $card->getExpYear(), $this->get_custumer_address($alu), $card->getMetadata(), (is_null($card->getLabel()) ? "" : $card->getLabel()))->build());
                    }
                    $creditCard->setCardId($cartao);
                } catch (ErrorException $e) {
                    return false;
                } catch (ApiException $e) {
                    return false;
                }
            }

            $creditCard->setCapture(true);
            $creditCard->setStatementDescriptor($pedido->getDescricaoFatura());
            $creditCard->setInstallments($pedido->getParcelas());

            $body = CreateOrderRequestBuilder::init([
                CreateOrderItemRequestBuilder::init($pedido->getValor(), $pedido->getNomeDoItem(), 1, 'oficinas')->code($pedido->getId())
                    ->build()
            ], $this->custumer, [
                CreatePaymentRequestBuilder::init('credit_card')->creditCard($creditCard)->build()
            ], $pedido->getId(), true, null, false, $_SERVER['REMOTE_ADDR'])->build();

            $this->log->write('DEBUG', 'CARTAO REQUEST:' . PHP_EOL . json_encode($body->jsonSerialize()));
            $order = $ordersController->createOrder($body);
            $this->log->write('DEBUG', 'CARTAO RESPONSE:' . PHP_EOL . json_encode($order->jsonSerialize()));

            $charge = $order->getCharges()[0];
            $transacao = new Transacao();
            if ($charge->getLastTransaction()->getCard()) {
                $transacao->setCartao($charge->getLastTransaction()
                    ->getCard()
                    ->getBrand() . ' final ' . $charge->getLastTransaction()
                    ->getCard()
                    ->getLastFourDigits());
            }
            if ($charge->getLastTransaction()->getInstallments()) {
                $transacao->setParcelas($charge->getLastTransaction()
                    ->getInstallments());
            }
            $transacao->setTipo('cartao')->setOperadoraID($order->getCharges()[0]->getId());

            return $this->fillTransacao($charge, $transacao);

            $erros = [];
            if ($charge->getAntifraudResponse()->getStatus() == 'reproved') {
                if ($charge->getAntifraudResponse()->getReturnMessage()) {
                    $erros[] = 'Antifraude: ' . $charge->getAntifraudResponse()->getReturnMessage();
                } else {
                    $erros[] = 'O Antifraude da operadora não aprovou a transação. Verifique todos os dados de inscrição e tente novamente. Em caso de dúvidas contate oficinas@cannal.com.br';
                }
            }
            $errors = $charge->getGatewayResponse()->getErrors();
            if ($errors) {
                foreach ($charge->getGatewayResponse()->getErrors() as $error) {
                    $error = $error->getMessage();
                    if ($error) {
                        $erros[] = $error;
                    }
                }
            }
            if (count($erros)) {
                $erros = implode(' ' . PHP_EOL, $erros);
                $transacao->setOperadoraErros($erros);
            } else if ($charge->getStatus() === 'paid') {
                $transacao->setConfirmada(true);
            }

            if ($charge->getCard()) {
                $transacao->setCartao($charge->getCard()
                    ->getBrand() . ' final ' . $charge->getCard()
                    ->getLastFourDigits());
            }
            if ($charge->getInstallments()) {
                $transacao->setParcelas($charge->getInstallments());
            }
            return $transacao->setTipo('cartao')
                ->setValorBruto($charge->getAmount() / 100)
                ->setDataTransacao($charge->getCreatedAt()
                ->format('Y-m-d H:i:s'))
                ->setOperadora($this->opr->getNome())
                ->setOperadoraData(date('Y-m-d H:i:s'))
                ->setOperadoraResposta(json_encode($charge))
                ->setOperadoraStatus($charge->getStatus())
                ->setOperadoraID($order->getCharges()[0]->getId());
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }
            $ordersController = $this->client->getOrdersController();

            $this->updateCustumer($cli);

            $pix = new CreatePixPaymentRequest();
            $pix->setExpiresAt(new \DateTime('+1 day'));

            $body = CreateOrderRequestBuilder::init([
                CreateOrderItemRequestBuilder::init($pedido->getValor() * 100, $pedido->getNomeDoItem(), 1, 'oficinas')->code($pedido->getId())
                    ->build()
            ], $this->custumer, [
                CreatePaymentRequestBuilder::init('pix')->pix($pix)->build()
            ], $pedido->getId(), true, null, false, $_SERVER['REMOTE_ADDR'])->build();

            $this->log->write('DEBUG', 'PIX REQUEST:' . PHP_EOL . json_encode($body->jsonSerialize()));
            $order = $ordersController->createOrder($body);
            $this->log->write('DEBUG', 'PIX RESPONSE:' . PHP_EOL . json_encode($order->jsonSerialize()));

            $charge = $order->getCharges()[0];

            $transacao = new Transacao();
            $transacao->setTipo('pix')
                ->setOperadoraID($order->getCharges()[0]->getId())
                ->setPixQrCode($charge->getLastTransaction()
                ->getQrCode())
                ->setPixQrCodeUrl($charge->getLastTransaction()
                ->getQrCodeUrl())
                ->setDataExpiracao($charge->getLastTransaction()
                ->getExpiresAt()
                ->format('Y-m-d H:i:s'));

            return $this->fillTransacao($charge, $transacao);

            $errors = $charge->getGatewayResponse()->getErrors();
            if ($errors) {
                foreach ($errors as $error) {
                    $error = $error->getMessage();
                    if ($error) {
                        $erros[] = $error;
                    }
                    $erros = implode(' ' . PHP_EOL, $erros);
                    $transacao->setOperadoraErros($erros);
                }
            }

            return $transacao->setTipo('pix')
                ->setDataExpiracao($charge->getExpiresAt()
                ->format('Y-m-d H:i:s'))
                ->setValorBruto($charge->getAmount() / 100)
                ->setDataTransacao($charge->getCreatedAt()
                ->format('Y-m-d H:i:s'))
                ->setOperadora($this->opr->getNome())
                ->setOperadoraData(date('Y-m-d H:i:s'))
                ->setOperadoraResposta(json_encode($charge))
                ->setOperadoraStatus($charge->getStatus())
                ->setOperadoraID($order->getCharges()[0]->getId());
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function refund(string $charge_id, int $amount): Transacao
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }

            if (ENVIRONMENT == 'development') {
                $charge_id = 'ch_j3NPOrJCkC3OREWm';
                $amount = 1000;
            }

            $chargeController = $this->client->getChargesController();

            $request = new CreateCancelChargeRequest("");
            $request->setAmount($amount * 100);

            $this->log->write('DEBUG', 'ESTORNO REQUEST:' . PHP_EOL . json_encode($request->jsonSerialize()));
            $charge = $chargeController->cancelCharge($charge_id, $request);
            $this->log->write('DEBUG', 'ESTORNO RESPONSE:' . PHP_EOL . json_encode($charge->jsonSerialize()));

            return $this->fillTransacao($charge);

            $transacao = new Transacao();

            $transacao->setDataCancelamento($charge->getCanceledAt()
                ->format('Y-m-d H:i:s'))
                ->setValorCancelado($charge->getCanceledAmount() / 100)
                ->setOperadora($this->opr->getNome())
                ->setOperadoraData(date('Y-m-d H:i:s'))
                ->setOperadoraResposta(json_encode($charge))
                ->setOperadoraStatus($charge->getStatus())
                ->setOperadoraID($charge->getId());
            return $transacao;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    private function fillTransacao(GetChargeResponse $charge, ?Transacao $transacao = null): Transacao
    {
        if (! $transacao) {
            $transacao = new Transacao();
        }

        $erros = [];
        if (method_exists($charge, 'getAntifraudResponse') && $charge->getAntifraudResponse()->getStatus() == 'reproved') {
            if ($charge->getAntifraudResponse()->getReturnMessage()) {
                $erros[] = 'Antifraude: ' . $charge->getAntifraudResponse()->getReturnMessage();
            } else {
                $erros[] = 'O Antifraude da operadora não aprovou a transação. Verifique todos os dados de inscrição e tente novamente. Em caso de dúvidas contate oficinas@cannal.com.br';
            }
        }

        if (method_exists($charge, 'getGatewayResponse')) {
            foreach ($charge->getGatewayResponse()->getErrors() as $error) {
                $error = $error->getMessage();
                if ($error) {
                    $erros[] = $error;
                }
            }
        }
        if (count($erros)) {
            $erros = implode(' ' . PHP_EOL, $erros);
            $transacao->setOperadoraErros($erros);
        } else if ($charge->getStatus() === 'paid') {
            $transacao->setConfirmada(true);
        }

        if ($charge->getCanceledAt()) {
            $transacao->setDataCancelamento($charge->getCanceledAt()
                ->format('Y-m-d H:i:s'));
        }
        return $transacao->setDataTransacao($charge->getCreatedAt()
            ->format('Y-m-d H:i:s'))
            ->setValorCancelado($charge->getCanceledAmount() / 100)
            ->setValorBruto($charge->getAmount() / 100)
            ->setOperadora($this->opr->getNome())
            ->setOperadoraData(date('Y-m-d H:i:s'))
            ->setOperadoraResposta(json_encode($charge))
            ->setOperadoraStatus($charge->getStatus());
    }

    public function getCharge(string $charge_id): ?Transacao
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }

            $chargeController = $this->client->getChargesController();

            $charge = $chargeController->getCharge($charge_id);

            $transacao = new Transacao();
            if (in_array($charge->getStatus(), [
                'paid',
                'overpaid'
            ])) {
                $transacao->setConfirmada(true);
                $transacao->setDataTransacao($charge->getPaidAt()
                    ->format('Y-m-d H:i:s'));
                $transacao->setValorBruto($charge->getPaidAmount() / 100);
            } else {
                $transacao->setValorBruto($charge->getAmount() / 100);
            }
            if ($charge->getDueAt()) {
                $transacao->setDataExpiracao($charge->getDueAt()
                    ->format('Y-m-d H:i:s'));
            }
            if ($charge->getCanceledAt()) {
                $transacao->setDataCancelamento($charge->getCanceledAt()
                    ->format('Y-m-d H:i:s'))
                    ->setConfirmada(false);
            }
            return $transacao->setValorCancelado($charge->getCanceledAmount() / 100)
                ->setValorLiquido(($charge->getAmount() - $charge->getCanceledAmount()) / 100)
                ->setOperadoraData(date('Y-m-d H:i:s'))
                ->setOperadoraID($charge->getId())
                ->setOperadoraResposta(json_encode($charge))
                ->setOperadoraStatus($charge->getStatus());
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function cancelCharge(string $charge_id)
    {
        try {
            if (! $this->client) {
                $this->getClient();
            }

            $chargeController = $this->client->getChargesController();

            $result = $chargeController->cancelCharge($charge->getOperadoraId());

            return $result;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function getReceivable(int $payable_id): ?Recebivel
    {
        try {
            if ($payable_id === 0) {
                return null;
            }
            if (! $this->client) {
                $this->getClient();
            }
            $payablesController = $this->client->getPayablesController();

            $payable = $payablesController->getPayableById($payable_id);

            $rec = new Recebivel();
            $rec->setParcela($payable->getInstallment())
                ->setOperadoraResposta(json_encode($payable))
                ->setOperadoraId($payable->getId())
                ->setOperadoraStatus($payable->getStatus())
                ->setOperadoraData(date('Y-m-d H:i:s'))
                ->setDataRecebimento($payable->getPaymentDate()
                ->format('Y-m-d'))
                ->setValor($rec->getValor() + ($payable->getAmount() / 100))
                ->setValorLiquido($rec->getValorLiquido() + ($payable->getAmount() - $payable->getFee()) / 100);
            if ($payable->getType() == 'refund') {
                $rec->setEstornoData($payable->getCreatedAt()
                    ->format('Y-m-d'))
                    ->setEstornoValor(($payable->getAmount() / 100) * - 1);
            } else {
                $rec->setDataTransacao($payable->getCreatedAt()
                    ->format('Y-m-d'));
            }
            return $rec;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }

    public function getReceivables(string $operadora_id = null, int $parcela_id = null, string $status = null, int $days = null): ?array
    {
        try {
            $chargeId = null;
            $gatewayId = null;
            $retorno = [];

            if (! $this->client) {
                $this->getClient();
            }
            if (! is_null($days)) {
                $paymentDateSince = new \DateTime();
                $paymentDateSince->modify('-' . (int) $days . ' day');
            } else {
                $paymentDateSince = null;
            }
            $payablesController = $this->client->getPayablesController();

            switch (substr($operadora_id, 0, 3)) {
                case 'ch_':
                    $chargeId = $operadora_id;
                    $gatewayId = null;
                    break;
                default:
                    $chargeId = null;
                    $gatewayId = (int) $operadora_id;
                    break;
            }
            $result = $payablesController->getPayables(null, null, null, $parcela_id, $status, null, null, $chargeId, null, $paymentDateSince, null, null, null, null, null, null, 1000, $gatewayId);
            if (! count($result->getData()) && $operadora_id) {
                if ($operadora_id && $result = $this->getReceivable((int) $operadora_id)) {
                    return [
                        $result
                    ];
                }
                return null;
            }

            foreach ($result->getData() as $payable) {
                $charges[$payable->getChargeId()][$payable->getInstallment()][] = $payable;
            }
            foreach ($charges as $charge_id => $installments) {
                foreach ($installments as $installment => $payables) {
                    $key = $charge_id . '_' . $installment;
                    if (isset($retorno[$key])) {
                        $rec = $retorno[$key];
                    } else {
                        $rec = new Recebivel();
                    }
                    foreach ($payables as $payable) {
                        if ($payable->getType() == 'credit') {
                            $rec->setValor($rec->getValor() + ($payable->getAmount() / 100))
                                ->setDataTransacao($payable->getCreatedAt()
                                ->format('Y-m-d'));
                        } else if ($payable->getType() == 'refund') {
                            $rec->setEstornoValor(($payable->getAmount() / 100) * - 1)
                                ->setEstornoData($payable->getCreatedAt()
                                ->format('Y-m-d'));
                        } else {
                            continue;
                        }
                        if ($payable->getStatus() === 'paid') {
                            $rec->setRecebido(true);
                        }

                        $rec->setValorLiquido($rec->getValorLiquido() + (($payable->getAmount() - $payable->getFee()) / 100));
                        $rec = $rec->setParcela($payable->getInstallment())
                            ->setOperadoraResposta(json_encode($payable))
                            ->setOperadoraId($payable->getId())
                            ->setOperadoraStatus($payable->getStatus())
                            ->setOperadoraData(date('Y-m-d H:i:s'))
                            ->setDataRecebimento($payable->getPaymentDate()
                            ->format('Y-m-d'));
                        $retorno[$key] = $rec;
                    }
                }
            }
            return $retorno;
        } catch (ErrorException $e) {
            $this->exception($e);
        } catch (ApiException $e) {
            $this->exception($e);
        }
    }
}