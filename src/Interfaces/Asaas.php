<?php
namespace CANNALPagamentos\Interfaces;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Recebivel;
use CANNALPagamentos\Entities\Transacao;
use CANNALPagamentos\PagamentosInterface;
use RuntimeException;
use Throwable;

/**
 * Classe responsável por integrar pagamentos via Asaas.
 */
class Asaas implements PagamentosInterface
{

    private ?string $apiKey = null;

    private ?string $nome = null;

    private ?string $baseUrl = null;

    private ?LoggerInterface $logger = null;

    /**
     * Construtor da classe Asaas.
     *
     * @param string $key
     *            Chave da API.
     * @param string|null $nome
     *            Nome da operadora.
     * @param LoggerInterface|null $logger
     *            Instância do logger.
     */
    public function __construct(string $key, ?string $nome = null, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
        
        $this->apiKey = $key;

        if ($nome) {
            $this->nome = $nome;
        } else {
            $this->nome = 'Asaas';
        }

        // Define a URL base baseada no ambiente
        $this->baseUrl = defined('ASAAS_SANDBOX') && ASAAS_SANDBOX 
            ? 'https://api-sandbox.asaas.com/v3'
            : 'https://api.asaas.com/v3';
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * Faz uma requisição HTTP para a API Asaas.
     *
     * @param string $method
     *            Método HTTP (GET, POST, PUT, DELETE).
     * @param string $endpoint
     *            Endpoint da API (sem a URL base).
     * @param array|null $data
     *            Dados para enviar no body (para POST/PUT).
     *
     * @return array
     *            Resposta decodificada.
     *
     * @throws RuntimeException
     */
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
            
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'X-API-KEY: ' . $this->apiKey,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            
            if ($data !== null && in_array($method, ['POST', 'PUT'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            // Desabilita verificação SSL em desenvolvimento
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new RuntimeException("Erro na requisição cURL: $error");
            }

            $decoded = json_decode($response, true);

            if ($httpCode < 200 || $httpCode >= 300) {
                $message = $decoded['errors'][0]['detail'] ?? $decoded['message'] ?? 'Erro na API Asaas';
                $this->logger->error("ASAAS ERROR ($httpCode): $message\nResponse: $response");
                throw new RuntimeException($message, $httpCode);
            }

            return $decoded ?? [];
        } catch (Throwable $e) {
            $this->logger->error('ASAAS ERROR: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            throw new RuntimeException($e->getMessage(), $e->getCode() ?: 0, $e);
        }
    }

    /**
     * Cria ou atualiza um cliente no Asaas.
     *
     * @param Cliente $cliente
     *            Dados do cliente.
     *
     * @return array
     *            Resposta da API.
     *
     * @throws RuntimeException
     */
    private function syncCustomer(Cliente &$cliente): array
    {
        $data = [
            'name' => $cliente->getNome(),
            'email' => $cliente->getEmail(),
            'phone' => $cliente->getCelular(),
            'cpfCnpj' => $cliente->getCpf(),
        ];

        // Remove campos vazios
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        if ($cliente->getIdOperadora()) {
            // Atualizar cliente existente
            return $this->makeRequest('PUT', "/customers/{$cliente->getIdOperadora()}", $data);
        } else {
            // Criar novo cliente
            $response = $this->makeRequest('POST', '/customers', $data);
            if (isset($response['id'])) {
                $cliente->setIdOperadora($response['id']);
            }
            return $response;
        }
    }

    /**
     * Cria uma cobrança/pagamento no Asaas.
     *
     * @param string $customerId
     *            ID do cliente no Asaas.
     * @param Pedido $pedido
     *            Dados do pedido.
     * @param string|null $billingType
     *            Tipo de cobrança: BOLETO, CREDIT_CARD, PIX, DEBIT_ACCOUNT.
     * @param array|null $creditCard
     *            Dados do cartão (obrigatório para CREDIT_CARD).
     *
     * @return array
     *            Resposta da API.
     *
     * @throws RuntimeException
     */
    private function createPayment(
        string $customerId,
        Pedido $pedido,
        string $billingType = 'BOLETO',
        ?array $creditCard = null
    ): array {
        $data = [
            'customerId' => $customerId,
            'billingType' => $billingType,
            'value' => $pedido->getValor(),
            'description' => $pedido->getDescricao(),
            'dueDate' => $pedido->getDataVencimento() ?? date('Y-m-d', strtotime('+7 days')),
        ];

        if ($creditCard) {
            $data['creditCard'] = $creditCard;
        }

        return $this->makeRequest('POST', '/payments', $data);
    }

    // Implementação de PagamentosInterface

    public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao
    {
        try {
            // Sincroniza cliente
            $this->syncCustomer($cli);

            $creditCardData = [];
            if ($cartao instanceof Cartao) {
                $creditCardData = [
                    'holderName' => $cartao->getNome(),
                    'number' => $cartao->getNumero(),
                    'expiryMonth' => $cartao->getVencimentoMes(),
                    'expiryYear' => $cartao->getVencimentoAno(),
                    'ccv' => $cartao->getCodigo(),
                ];
            } else {
                // Assumir que é um token de cartão
                $creditCardData = ['creditCardToken' => $cartao];
            }

            $response = $this->createPayment(
                $cli->getIdOperadora(),
                $pedido,
                'CREDIT_CARD',
                $creditCardData
            );

            return $this->mapPaymentToTransacao($response);
        } catch (Throwable $e) {
            $this->logger->error('ASAAS CREDIT CARD ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao processar cartão de crédito: ' . $e->getMessage(), 0, $e);
        }
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        try {
            // Sincroniza cliente
            $this->syncCustomer($cli);

            $response = $this->createPayment($cli->getIdOperadora(), $pedido, 'PIX');

            return $this->mapPaymentToTransacao($response);
        } catch (Throwable $e) {
            $this->logger->error('ASAAS PIX ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao processar Pix: ' . $e->getMessage(), 0, $e);
        }
    }

    public function boleto(Cliente &$cli, Pedido $pedido): Transacao
    {
        try {
            // Sincroniza cliente
            $this->syncCustomer($cli);

            $response = $this->createPayment($cli->getIdOperadora(), $pedido, 'BOLETO');

            return $this->mapPaymentToTransacao($response);
        } catch (Throwable $e) {
            $this->logger->error('ASAAS BOLETO ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao processar boleto: ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(string $payment_id, int $amount): Transacao
    {
        try {
            $data = ['value' => $amount / 100]; // Converter centavos para reais

            $response = $this->makeRequest('POST', "/payments/$payment_id/refund", $data);

            $transacao = new Transacao();
            $transacao->setOperadoraID($response['id'] ?? null);
            $transacao->setOperadoraStatus('refunded');
            $transacao->setValorCancelado($amount / 100);
            $transacao->setOperadora('Asaas');
            $transacao->setOperadoraResposta(json_encode($response));
            $transacao->setConfirmada(true);

            return $transacao;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS REFUND ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao processar reembolso: ' . $e->getMessage(), 0, $e);
        }
    }

    public function saveCard(Cliente &$cli, Cartao $cartao): Cartao
    {
        try {
            $data = [
                'customerId' => $cli->getIdOperadora(),
                'creditCard' => [
                    'holderName' => $cartao->getNome(),
                    'number' => $cartao->getNumero(),
                    'expiryMonth' => $cartao->getVencimentoMes(),
                    'expiryYear' => $cartao->getVencimentoAno(),
                    'ccv' => $cartao->getCodigo(),
                ],
            ];

            $response = $this->makeRequest('POST', '/creditCards', $data);

            if (isset($response['id'])) {
                $cartao->setId($response['id']);
            }

            return $cartao;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS SAVE CARD ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao salvar cartão: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getCards(Cliente &$cli): array
    {
        try {
            $response = $this->makeRequest('GET', "/creditCards?customerId={$cli->getIdOperadora()}");

            $cards = [];
            foreach ($response['data'] ?? [] as $cardData) {
                $cartao = new Cartao();
                $cartao->setId($cardData['id']);
                $cartao->setNome($cardData['holderName'] ?? null);
                $cartao->setUltimosQuatro(substr($cardData['number'] ?? '', -4)); // Apenas últimos 4 dígitos
                $cards[] = $cartao;
            }

            return $cards;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS GET CARDS ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao recuperar cartões: ' . $e->getMessage(), 0, $e);
        }
    }

    public function updateCustumer(Cliente &$cli): Cliente
    {
        try {
            $this->syncCustomer($cli);
            return $cli;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS UPDATE CUSTOMER ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao atualizar cliente: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getCharge(string $payment_id): ?Transacao
    {
        try {
            $response = $this->makeRequest('GET', "/payments/$payment_id");

            return $this->mapPaymentToTransacao($response);
        } catch (Throwable $e) {
            $this->logger->error('ASAAS GET CHARGE ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao recuperar cobrança: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getReceivable(int $payable_id): ?Recebivel
    {
        // Asaas não tem um conceito direto de "recebível", mas podemos mapear
        // a resposta de uma cobrança para um recebível
        try {
            $response = $this->makeRequest('GET', "/receivables/$payable_id");

            $recebivel = new Recebivel();
            $recebivel->setOperadoraID($response['id'] ?? null);
            $recebivel->setOperadoraID($response['id'] ?? null);
            $recebivel->setValor($response['value'] ?? 0);
            $recebivel->setDataRecebimento($response['paymentDate'] ?? null);
            $recebivel->setOperadoraResposta(json_encode($response));

            return $recebivel;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS GET RECEIVABLE ERROR: ' . $e->getMessage());
            return null;
        }
    }

    public function getReceivables(
        string $charge_id = null,
        int $parcela_id = null,
        string $status = null,
        int $days = null
    ): ?array {
        try {
            $params = [];
            if ($charge_id) {
                $params['customerId'] = $charge_id;
            }
            if ($status) {
                $params['status'] = $status;
            }

            $queryString = http_build_query($params);
            $endpoint = $queryString ? "/receivables?$queryString" : '/receivables';

            $response = $this->makeRequest('GET', $endpoint);

            $recebiveis = [];
            foreach ($response['data'] ?? [] as $receivableData) {
                $recebivel = new Recebivel();
                $recebivel->setOperadoraID($receivableData['id'] ?? null);
                $recebivel->setValor($receivableData['value'] ?? 0);
                $recebivel->setDataRecebimento($receivableData['paymentDate'] ?? null);
                $recebivel->setOperadoraResposta(json_encode($receivableData));
                $recebiveis[] = $recebivel;
            }

            return $recebiveis;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS GET RECEIVABLES ERROR: ' . $e->getMessage());
            return null;
        }
    }

    public function cancelCharge(string $charge_id)
    {
        try {
            $response = $this->makeRequest('DELETE', "/payments/$charge_id");

            $this->logger->info("Cobrança $charge_id cancelada com sucesso");

            return $response;
        } catch (Throwable $e) {
            $this->logger->error('ASAAS CANCEL CHARGE ERROR: ' . $e->getMessage());
            throw new RuntimeException('Erro ao cancelar cobrança: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Mapeia resposta de pagamento Asaas para entidade Transacao.
     *
     * @param array $paymentData
     *            Dados do pagamento da API Asaas.
     *
     * @return Transacao
     */
    private function mapPaymentToTransacao(array $paymentData): Transacao
    {
        $transacao = new Transacao();
        $transacao->setOperadoraID($paymentData['id'] ?? null);
        $transacao->setOperadoraStatus($paymentData['status'] ?? null);
        $transacao->setValorBruto($paymentData['value'] ?? 0);
        $transacao->setValorLiquido(($paymentData['netValue'] ?? 0));
        $transacao->setDataTransacao($paymentData['createdAt'] ?? null);
        $transacao->setDataExpiracao($paymentData['dueDate'] ?? null);
        $transacao->setOperadoraCodigo($paymentData['externalReference'] ?? null);
        $transacao->setOperadora('Asaas');
        $transacao->setOperadoraResposta(json_encode($paymentData));

        // Mapear tipo de forma de pagamento
        $billingType = $paymentData['billingType'] ?? '';
        switch ($billingType) {
            case 'BOLETO':
                $transacao->setForma('boleto');
                break;
            case 'CREDIT_CARD':
                $transacao->setForma('creditcard');
                break;
            case 'PIX':
                $transacao->setForma('pix');
                if ($paymentData['pixQrCodeUrl'] ?? null) {
                    $transacao->setPixQrCodeUrl($paymentData['pixQrCodeUrl']);
                }
                break;
            case 'DEBIT_ACCOUNT':
                $transacao->setForma('debit');
                break;
        }

        // Mapeamento de status
        $status = $paymentData['status'] ?? '';
        $transacao->setConfirmada(in_array($status, ['CONFIRMED', 'RECEIVED', 'OVERDUE']));

        return $transacao;
    }
}
