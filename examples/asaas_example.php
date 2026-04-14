<?php
/**
 * Exemplo de Uso da Integração Asaas
 * 
 * Este arquivo demonstra como usar a biblioteca CANAL Pagamentos
 * com a plataforma Asaas.
 */

require_once 'vendor/autoload.php';

use CANNALPagamentos\Interfaces\Asaas;
use CANNALPagamentos\Webhooks\AsaasWebhookProcessor;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Cartao;
use Psr\Log\NullLogger;

// ============================================
// EXEMPLO 1: Configuração Básica
// ============================================

// Em ambiente de testes, descomente:
// define('ASAAS_SANDBOX', true);
// define('ENVIRONMENT', 'development');

$apiKey = getenv('ASAAS_API_KEY') ?: 'seu_token_aqui';
$logger = new NullLogger();

$asaas = new Asaas($apiKey, 'Minha Loja', $logger);

echo "=== ASAAS INTEGRATION EXAMPLES ===\n";

// ============================================
// EXEMPLO 2: Criar Cliente
// ============================================

echo "\n2. Creating Customer...\n";

$cliente = new Cliente();
$cliente->setNome('João Silva');
$cliente->setEmail('joao.silva@example.com');
$cliente->setCpf('12345678901');
$cliente->setCelular('11987654321');
$cliente->setEndereco('Rua das Flores');
$cliente->setEnderecoNumero('123');
$cliente->setEnderecoComplemento('Apto 45');
$cliente->setEnderecoBairro('Centro');
$cliente->setEnderecoCidade('São Paulo');
$cliente->setEnderecoEstado('SP');
$cliente->setEnderecoCep('01234567');

try {
    $asaas->updateCustumer($cliente);
    printf("Customer created: %s\n", $cliente->getIdOperadora());
} catch (Exception $e) {
    printf("ERROR creating customer: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 3: Criar Pedido
// ============================================

echo "\n3. Creating Order...\n";

$pedido = new Pedido();
$pedido->setValor(199.90);
$pedido->setDescricao('Produto Premium - Assinatura Anual');
$pedido->setDataVencimento(date('Y-m-d', strtotime('+30 days')));

// ============================================
// EXEMPLO 4: Pagamento via Boleto
// ============================================

echo "\n4. Processing Boleto Payment...\n";

try {
    $transacao = $asaas->boleto($cliente, $pedido);
    printf("Boleto created!\n");
    printf("  Transaction ID: %s\n", $transacao->getOperadoraID());
    printf("  Status: %s\n", $transacao->getOperadoraStatus());
    printf("  Amount: R$ %.2f\n", $transacao->getValorBruto());
} catch (Exception $e) {
    printf("ERROR creating boleto: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 5: Pagamento via PIX
// ============================================

echo "\n5. Processing PIX Payment...\n";

try {
    $transacao = $asaas->pix($cliente, $pedido);
    printf("PIX created!\n");
    printf("  Transaction ID: %s\n", $transacao->getOperadoraID());
    printf("  QR Code URL: %s\n", $transacao->getPixQrCodeUrl());
} catch (Exception $e) {
    printf("ERROR creating PIX: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 6: Pagamento via Cartão de Crédito
// ============================================

echo "\n6. Processing Credit Card Payment...\n";

$cartao = new Cartao();
$cartao->setNumero('4111111111111111');
$cartao->setNome('JOAO SILVA');
$cartao->setVencimentoMes(12);
$cartao->setVencimentoAno(2025);
$cartao->setCodigo('123');

try {
    $transacao = $asaas->creditCard($cliente, $pedido, $cartao);
    printf("Credit Card payment created!\n");
    printf("  Transaction ID: %s\n", $transacao->getOperadoraID());
    printf("  Status: %s\n", $transacao->getOperadoraStatus());
    printf("  Net Amount: R$ %.2f\n", $transacao->getValorLiquido());
} catch (Exception $e) {
    printf("ERROR creating credit card payment: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 7: Salvar Cartão
// ============================================

echo "\n7. Saving Credit Card...\n";

try {
    $cartaoSalvo = $asaas->saveCard($cliente, $cartao);
    printf("Credit card saved!\n");
    printf("  Card ID: %s\n", $cartaoSalvo->getId());
} catch (Exception $e) {
    printf("ERROR saving credit card: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 8: Listar Cartões
// ============================================

echo "\n8. Listing Saved Credit Cards...\n";

try {
    $cartoes = $asaas->getCards($cliente);
    printf("Found %d credit card(s):\n", count($cartoes));
    foreach ($cartoes as $c) {
        printf("  - %s: %s\n", $c->getNome(), $c->getUltimosQuatro());
    }
} catch (Exception $e) {
    printf("ERROR listing credit cards: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 9: Recuperar Cobrança
// ============================================

echo "\n9. Retrieving Charge Information...\n";

if (isset($transacao) && $transacao->getOperadoraID()) {
    try {
        $cobrada = $asaas->getCharge($transacao->getOperadoraID());
        printf("Charge retrieved:\n");
        printf("  ID: %s\n", $cobrada->getOperadoraID());
        printf("  Status: %s\n", $cobrada->getOperadoraStatus());
        printf("  Amount: R$ %.2f\n", $cobrada->getValorBruto());
    } catch (Exception $e) {
        printf("ERROR retrieving charge: %s\n", $e->getMessage());
    }
}

// ============================================
// EXEMPLO 10: Processar Webhook
// ============================================

echo "\n10. Processing Webhook...\n";

// Simular webhook do Asaas
$webhookPayload = [
    'event' => 'PAYMENT_RECEIVED',
    'data' => [
        'id' => 'pay_test123',
        'customerId' => 'cust_test456',
        'value' => 199.90,
        'netValue' => 190.90,
        'billingType' => 'BOLETO',
        'status' => 'RECEIVED',
        'paymentDate' => date('Y-m-d H:i:s'),
        'dueDate' => date('Y-m-d'),
        'createdAt' => date('Y-m-d H:i:s'),
        'updatedAt' => date('Y-m-d H:i:s'),
    ]
];

$webhookHeaders = [
    'asaas-access-token' => 'seu_token_webhook',
];

try {
    $processor = new AsaasWebhookProcessor($logger, 'seu_token_webhook');
    $transacaoWebhook = $processor->process($webhookPayload, $webhookHeaders);
    printf("Webhook processed successfully!\n");
    printf("  Transaction ID: %s\n", $transacaoWebhook->getOperadoraID());
    printf("  Status: %s\n", $transacaoWebhook->getOperadoraStatus());
    printf("  Amount: R$ %.2f\n", $transacaoWebhook->getValorBruto());
} catch (Exception $e) {
    printf("ERROR processing webhook: %s\n", $e->getMessage());
}

// ============================================
// EXEMPLO 11: Reembolsar Cobrança
// ============================================

echo "\n11. Refunding Charge...\n";

if (isset($transacao) && $transacao->getOperadoraID()) {
    try {
        $reembolso = $asaas->refund($transacao->getOperadoraID(), 10000); // 100.00 em centavos
        printf("Refund processed!\n");
        printf("  Refund Amount: R$ %.2f\n", $reembolso->getValorCancelado());
    } catch (Exception $e) {
        printf("ERROR processing refund: %s\n", $e->getMessage());
    }
}

// ============================================
// EXEMPLO 12: Cancelar Cobrança
// ============================================

echo "\n12. Canceling Charge...\n";

if (isset($transacao) && $transacao->getOperadoraID()) {
    try {
        $asaas->cancelCharge($transacao->getOperadoraID());
        printf("Charge canceled successfully!\n");
    } catch (Exception $e) {
        printf("ERROR canceling charge: %s\n", $e->getMessage());
    }
}

echo "\n=== END OF EXAMPLES ===\n";
