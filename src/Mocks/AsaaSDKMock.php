<?php

namespace CANNALPagamentos\Mocks;

/**
 * Mock da SDK do Asaas para testes.
 * 
 * Esta classe simula as respostas da API do Asaas para uso em testes.
 */
class AsaaSDKMock
{
    private array $customers = [];
    private array $payments = [];
    private array $creditCards = [];

    private int $customerIdCounter = 1;
    private int $paymentIdCounter = 1;
    private int $creditCardIdCounter = 1;

    /**
     * Simula criar um cliente.
     */
    public function createCustomer(array $data): array
    {
        $id = 'cust_' . $this->customerIdCounter++;
        
        $customer = array_merge($data, [
            'id' => $id,
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s'),
        ]);

        $this->customers[$id] = $customer;
        return $customer;
    }

    /**
     * Simula atualizar um cliente.
     */
    public function updateCustomer(string $customerId, array $data): array
    {
        if (!isset($this->customers[$customerId])) {
            throw new \Exception("Customer $customerId not found");
        }

        $this->customers[$customerId] = array_merge($this->customers[$customerId], $data);
        $this->customers[$customerId]['updatedAt'] = date('Y-m-d H:i:s');

        return $this->customers[$customerId];
    }

    /**
     * Simula recuperar um cliente.
     */
    public function getCustomer(string $customerId): array
    {
        if (!isset($this->customers[$customerId])) {
            throw new \Exception("Customer $customerId not found");
        }

        return $this->customers[$customerId];
    }

    /**
     * Simula criar um pagamento.
     */
    public function createPayment(array $data): array
    {
        $id = 'pay_' . $this->paymentIdCounter++;
        
        $payment = array_merge($data, [
            'id' => $id,
            'status' => 'PENDING',
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s'),
            'billingType' => $data['billingType'] ?? 'BOLETO',
            'value' => $data['value'] ?? 0,
            'netValue' => ($data['value'] ?? 0) * 0.97, // Simular taxa
        ]);

        // Gerar QR Code para Pix
        if ($payment['billingType'] === 'PIX') {
            $payment['pixQrCode'] = '00020126580014br.gov.bcb.brcode01051.0.063047eb6e51a4b30eba32c3cc3cd58ffb5000520420520880140414123456789' . time();
            $payment['pixQrCodeUrl'] = 'https://api-sandbox.asaas.com/api/v3/payments/' . $id . '/qrCode';
        }

        // Gerar boleto para BOLETO
        if ($payment['billingType'] === 'BOLETO') {
            $payment['bankSlipUrl'] = 'https://api-sandbox.asaas.com/api/v3/payments/' . $id . '/bankSlip';
        }

        $this->payments[$id] = $payment;
        return $payment;
    }

    /**
     * Simula recuperar um pagamento.
     */
    public function getPayment(string $paymentId): array
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \Exception("Payment $paymentId not found");
        }

        return $this->payments[$paymentId];
    }

    /**
     * Simula confirmar (pagar) um pagamento.
     */
    public function confirmPayment(string $paymentId): array
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \Exception("Payment $paymentId not found");
        }

        $this->payments[$paymentId]['status'] = 'CONFIRMED';
        $this->payments[$paymentId]['confirmedAt'] = date('Y-m-d H:i:s');
        $this->payments[$paymentId]['updatedAt'] = date('Y-m-d H:i:s');

        return $this->payments[$paymentId];
    }

    /**
     * Simula receber um pagamento.
     */
    public function receivePayment(string $paymentId): array
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \Exception("Payment $paymentId not found");
        }

        $this->payments[$paymentId]['status'] = 'RECEIVED';
        $this->payments[$paymentId]['paymentDate'] = date('Y-m-d H:i:s');
        $this->payments[$paymentId]['updatedAt'] = date('Y-m-d H:i:s');

        return $this->payments[$paymentId];
    }

    /**
     * Simula reembolsar um pagamento.
     */
    public function refundPayment(string $paymentId, ?int $value = null): array
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \Exception("Payment $paymentId not found");
        }

        $refundValue = $value ?? $this->payments[$paymentId]['value'];
        
        $this->payments[$paymentId]['status'] = 'REFUNDED';
        $this->payments[$paymentId]['refundValue'] = $refundValue;
        $this->payments[$paymentId]['updatedAt'] = date('Y-m-d H:i:s');

        return $this->payments[$paymentId];
    }

    /**
     * Simula cancelar um pagamento.
     */
    public function cancelPayment(string $paymentId): array
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \Exception("Payment $paymentId not found");
        }

        unset($this->payments[$paymentId]);
        
        return ['success' => true, 'id' => $paymentId];
    }

    /**
     * Simula salvar um cartão de crédito.
     */
    public function saveCreditCard(array $data): array
    {
        $id = 'card_' . $this->creditCardIdCounter++;
        
        $card = array_merge($data, [
            'id' => $id,
            'createdAt' => date('Y-m-d H:i:s'),
        ]);

        $this->creditCards[$id] = $card;
        return $card;
    }

    /**
     * Simula listar cartões de um cliente.
     */
    public function getCustomerCreditCards(string $customerId): array
    {
        $customerCards = [];
        
        foreach ($this->creditCards as $card) {
            if ($card['customerId'] === $customerId) {
                $customerCards[] = $card;
            }
        }

        return [
            'object' => 'list',
            'hasMore' => false,
            'data' => $customerCards,
        ];
    }

    /**
     * Retorna todos os pagamentos criados (útil para testes).
     */
    public function getAllPayments(): array
    {
        return $this->payments;
    }

    /**
     * Retorna todos os clientes criados (útil para testes).
     */
    public function getAllCustomers(): array
    {
        return $this->customers;
    }

    /**
     * Limpa todos os dados (útil entre testes).
     */
    public function reset(): void
    {
        $this->customers = [];
        $this->payments = [];
        $this->creditCards = [];
        $this->customerIdCounter = 1;
        $this->paymentIdCounter = 1;
        $this->creditCardIdCounter = 1;
    }
}
