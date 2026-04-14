<?php
/**
 * Testes para a Integração Asaas
 * 
 * Este arquivo demonstra testes unitários para a integração com Asaas.
 * Pode ser executado com PHPUnit ou ferramenta similar.
 */

namespace CANNALPagamentos\Tests;

use CANNALPagamentos\Mocks\AsaaSDKMock;
use CANNALPagamentos\Webhooks\AsaasWebhookProcessor;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Cartao;
use Psr\Log\NullLogger;

class AsaasIntegrationTest
{
    private AsaaSDKMock $mock;
    private NullLogger $logger;

    public function setUp(): void
    {
        $this->mock = new AsaaSDKMock();
        $this->logger = new NullLogger();
    }

    public function tearDown(): void
    {
        $this->mock->reset();
    }

    // ===========================
    // Testes de Cliente
    // ===========================

    public function testCreateCustomer(): void
    {
        $customerData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpfCnpj' => '12345678901',
        ];

        $response = $this->mock->createCustomer($customerData);

        assert($response['id'] !== null, 'Customer ID should not be null');
        assert($response['name'] === 'João Silva', 'Customer name should match');
        assert($response['email'] === 'joao@example.com', 'Customer email should match');
        
        echo "✓ testCreateCustomer passed\n";
    }

    public function testUpdateCustomer(): void
    {
        $customerData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ];

        $customer = $this->mock->createCustomer($customerData);
        $customerId = $customer['id'];

        $updatedData = [
            'name' => 'João Silva Updated',
            'email' => 'novo-email@example.com',
        ];

        $updated = $this->mock->updateCustomer($customerId, $updatedData);

        assert($updated['name'] === 'João Silva Updated', 'Customer name should be updated');
        assert($updated['email'] === 'novo-email@example.com', 'Customer email should be updated');
        
        echo "✓ testUpdateCustomer passed\n";
    }

    public function testGetCustomer(): void
    {
        $customerData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ];

        $customer = $this->mock->createCustomer($customerData);
        $retrieved = $this->mock->getCustomer($customer['id']);

        assert($retrieved['id'] === $customer['id'], 'Retrieved customer ID should match');
        assert($retrieved['name'] === 'João Silva', 'Retrieved customer name should match');
        
        echo "✓ testGetCustomer passed\n";
    }

    // ===========================
    // Testes de Pagamento
    // ===========================

    public function testCreatePayment(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $paymentData = [
            'customerId' => $customer['id'],
            'billingType' => 'BOLETO',
            'value' => 100.00,
            'dueDate' => date('Y-m-d', strtotime('+7 days')),
        ];

        $payment = $this->mock->createPayment($paymentData);

        assert($payment['id'] !== null, 'Payment ID should not be null');
        assert($payment['status'] === 'PENDING', 'Payment status should be PENDING');
        assert($payment['value'] === 100.00, 'Payment value should match');
        assert($payment['billingType'] === 'BOLETO', 'Billing type should be BOLETO');
        
        echo "✓ testCreatePayment passed\n";
    }

    public function testCreatePixPayment(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $paymentData = [
            'customerId' => $customer['id'],
            'billingType' => 'PIX',
            'value' => 50.00,
        ];

        $payment = $this->mock->createPayment($paymentData);

        assert($payment['billingType'] === 'PIX', 'Billing type should be PIX');
        assert(!empty($payment['pixQrCode']), 'PIX payment should have QR code');
        assert(!empty($payment['pixQrCodeUrl']), 'PIX payment should have QR code URL');
        
        echo "✓ testCreatePixPayment passed\n";
    }

    public function testConfirmPayment(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $payment = $this->mock->createPayment([
            'customerId' => $customer['id'],
            'billingType' => 'CREDIT_CARD',
            'value' => 100.00,
        ]);

        $confirmed = $this->mock->confirmPayment($payment['id']);

        assert($confirmed['status'] === 'CONFIRMED', 'Payment status should be CONFIRMED');
        assert(!empty($confirmed['confirmedAt']), 'Payment should have confirmation date');
        
        echo "✓ testConfirmPayment passed\n";
    }

    public function testReceivePayment(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $payment = $this->mock->createPayment([
            'customerId' => $customer['id'],
            'billingType' => 'BOLETO',
            'value' => 100.00,
        ]);

        $received = $this->mock->receivePayment($payment['id']);

        assert($received['status'] === 'RECEIVED', 'Payment status should be RECEIVED');
        assert(!empty($received['paymentDate']), 'Payment should have payment date');
        
        echo "✓ testReceivePayment passed\n";
    }

    public function testRefundPayment(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $payment = $this->mock->createPayment([
            'customerId' => $customer['id'],
            'billingType' => 'CREDIT_CARD',
            'value' => 100.00,
        ]);

        $refunded = $this->mock->refundPayment($payment['id']);

        assert($refunded['status'] === 'REFUNDED', 'Payment status should be REFUNDED');
        assert($refunded['refundValue'] === 100.00, 'Refund value should match payment value');
        
        echo "✓ testRefundPayment passed\n";
    }

    public function testCancelPayment(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $payment = $this->mock->createPayment([
            'customerId' => $customer['id'],
            'billingType' => 'BOLETO',
            'value' => 100.00,
        ]);

        $result = $this->mock->cancelPayment($payment['id']);

        assert($result['success'] === true, 'Cancel should return success');
        
        echo "✓ testCancelPayment passed\n";
    }

    // ===========================
    // Testes de Cartão
    // ===========================

    public function testSaveCreditCard(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $cardData = [
            'customerId' => $customer['id'],
            'creditCard' => [
                'holderName' => 'JOAO SILVA',
                'number' => '4111111111111111',
                'expiryMonth' => 12,
                'expiryYear' => 2025,
                'ccv' => '123',
            ],
        ];

        $card = $this->mock->saveCreditCard($cardData);

        assert($card['id'] !== null, 'Card ID should not be null');
        assert(!empty($card['createdAt']), 'Card should have creation date');
        
        echo "✓ testSaveCreditCard passed\n";
    }

    public function testGetCustomerCreditCards(): void
    {
        $customer = $this->mock->createCustomer([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        $cardData = [
            'customerId' => $customer['id'],
            'creditCard' => [
                'holderName' => 'JOAO SILVA',
                'number' => '4111111111111111',
                'expiryMonth' => 12,
                'expiryYear' => 2025,
                'ccv' => '123',
            ],
        ];

        $this->mock->saveCreditCard($cardData);

        $cards = $this->mock->getCustomerCreditCards($customer['id']);

        assert(count($cards['data']) === 1, 'Should have one card');
        assert($cards['object'] === 'list', 'Response should be a list');
        
        echo "✓ testGetCustomerCreditCards passed\n";
    }

    // ===========================
    // Testes de Webhook
    // ===========================

    public function testProcessPaymentReceivedWebhook(): void
    {
        $payload = [
            'event' => 'PAYMENT_RECEIVED',
            'data' => [
                'id' => 'pay_test123',
                'value' => 100.00,
                'netValue' => 97.00,
                'billingType' => 'BOLETO',
                'status' => 'RECEIVED',
                'paymentDate' => date('Y-m-d H:i:s'),
            ]
        ];

        $headers = [
            'asaas-access-token' => 'test-token',
        ];

        $processor = new AsaasWebhookProcessor($this->logger, 'test-token');
        $transacao = $processor->process($payload, $headers);

        assert($transacao->getOperadoraID() === 'pay_test123', 'Transaction ID should match');
        assert($transacao->getValorBruto() === 100.00, 'Transaction amount should match');
        assert($transacao->getConfirmada() === true, 'Transaction should be confirmed');
        
        echo "✓ testProcessPaymentReceivedWebhook passed\n";
    }

    public function testProcessPaymentRefundedWebhook(): void
    {
        $payload = [
            'event' => 'PAYMENT_REFUNDED',
            'data' => [
                'id' => 'pay_test456',
                'value' => 100.00,
                'billingType' => 'CREDIT_CARD',
                'status' => 'REFUNDED',
            ]
        ];

        $processor = new AsaasWebhookProcessor($this->logger, null);
        $transacao = $processor->process($payload, []);

        assert($transacao->getOperadoraID() === 'pay_test456', 'Transaction ID should match');
        assert($transacao->getConfirmada() === false, 'Refunded transaction should not be confirmed');
        
        echo "✓ testProcessPaymentRefundedWebhook passed\n";
    }

    public function testWebhookTokenValidation(): void
    {
        $payload = [
            'event' => 'PAYMENT_RECEIVED',
            'data' => [
                'id' => 'pay_test789',
                'value' => 100.00,
                'billingType' => 'PIX',
                'status' => 'RECEIVED',
            ]
        ];

        $headers = [
            'asaas-access-token' => 'wrong-token',
        ];

        $processor = new AsaasWebhookProcessor($this->logger, 'correct-token');
        
        try {
            $processor->process($payload, $headers);
            assert(false, 'Should throw exception for invalid token');
        } catch (\Exception $e) {
            assert(strpos($e->getMessage(), 'Token') !== false, 'Error should mention token');
            echo "✓ testWebhookTokenValidation passed\n";
        }
    }

    // ===========================
    // Runner
    // ===========================

    public function runAllTests(): void
    {
        echo "=============================\n";
        echo "Running Asaas Integration Tests\n";
        echo "=============================\n\n";

        $methods = get_class_methods($this);
        $testCount = 0;
        $passCount = 0;

        foreach ($methods as $method) {
            if (strpos($method, 'test') === 0) {
                try {
                    $this->setUp();
                    $this->{$method}();
                    $passCount++;
                } catch (\Exception $e) {
                    echo "✗ $method failed: " . $e->getMessage() . "\n";
                } finally {
                    $this->tearDown();
                }
                $testCount++;
            }
        }

        echo "\n=============================\n";
        echo "Results: $passCount/$testCount tests passed\n";
        echo "=============================\n";
    }
}

// Executar testes
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../vendor/autoload.php';
    $test = new AsaasIntegrationTest();
    $test->runAllTests();
}
