# Integração Asaas

## Visão Geral

A biblioteca agora suporta integração com a plataforma **Asaas** para processamento de pagamentos. O Asaas é uma solução de pagamento brasileira que suporta múltiplas formas de cobrança: Boleto, Crédito, PIX e Débito.

## Configuração

### 1. Instalação

As classes já estão incluídas na biblioteca:

- **Interface**: `CANNALPagamentos\Interfaces\Asaas`
- **Webhook Processor**: `CANNALPagamentos\Webhooks\AsaasWebhookProcessor`
- **Mock para Testes**: `CANNALPagamentos\Mocks\AsaaSDKMock`

### 2. Utilização Básica

```php
<?php

use CANNALPagamentos\Interfaces\Asaas;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use Psr\Log\NullLogger;

// Inicializar o cliente Asaas
$apiKey = 'seu_token_api_asaas';
$asaas = new Asaas($apiKey, 'Minha Empresa', new NullLogger());

// Criar um cliente
$cliente = new Cliente();
$cliente->setNome('João Silva');
$cliente->setEmail('joao@example.com');
$cliente->setCpf('12345678900');
$cliente->setCelular('11999999999');

// Criar um pedido
$pedido = new Pedido();
$pedido->setValor(100.00);
$pedido->setDescricao('Produto XYZ');
$pedido->setDataVencimento('2025-12-31');

// Processar pagamento via Boleto
try {
    $transacao = $asaas->boleto($cliente, $pedido);
    echo "Transação criada: " . $transacao->getOperadoraID();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
```

## Formas de Pagamento

### 1. Boleto

```php
$transacao = $asaas->boleto($cliente, $pedido);
$boletoUrl = $transacao->getOperadoraResposta(); // Contém todos os dados do boleto
```

### 2. PIX (QR Code Dinâmico)

```php
$transacao = $asaas->pix($cliente, $pedido);
$pixQrCodeUrl = $transacao->getPixQrCodeUrl(); // URL do QR Code
```

### 3. Cartão de Crédito

```php
use CANNALPagamentos\Entities\Cartao;

$cartao = new Cartao();
$cartao->setNumero('4111111111111111');
$cartao->setNome('JOÃO SILVA');
$cartao->setVencimentoMes(12);
$cartao->setVencimentoAno(2025);
$cartao->setCodigo('123');

$transacao = $asaas->creditCard($cliente, $pedido, $cartao);
```

### 4. Salvar Cartão

```php
$cartaoSalvo = $asaas->saveCard($cliente, $cartao);
$tokenCartao = $cartaoSalvo->getId();

// Usar cartão salvo em futuras transações
$cartaoToken = new Cartao();
$cartaoToken->setId($tokenCartao);
$transacao = $asaas->creditCard($cliente, $pedido, $tokenCartao);
```

## Webhooks

### 1. Configuração de Webhook

Para receber eventos do Asaas, configure um webhook em sua conta:

1. Acesse [https://app.asaas.com/](https://app.asaas.com/)
2. Vá para Integrações → Webhooks
3. Configure a URL que receberá os eventos
4. Configure um token de autenticação (recomendado)
5. Selecione os eventos desejados:
   - `PAYMENT_CREATED` - Cobrança criada
   - `PAYMENT_CONFIRMED` - Cobrança confirmada
   - `PAYMENT_RECEIVED` - Cobrança recebida
   - `PAYMENT_REFUNDED` - Cobrança reembolsada
   - `PAYMENT_OVERDUE` - Cobrança vencida

### 2. Processar Webhooks

```php
<?php

use CANNALPagamentos\Webhooks\AsaasWebhookProcessor;
use Psr\Log\NullLogger;

// Receiving webhook request
$payload = json_decode(file_get_contents('php://input'), true);
$headers = getallheaders();

// Initialize processor with your auth token
$authToken = 'seu_token_webhook_configurado';
$processor = new AsaasWebhookProcessor(new NullLogger(), $authToken);

try {
    $transacao = $processor->process($payload, $headers);
    
    // Atualizar seu banco de dados com os dados da transação
    echo json_encode(['success' => true, 'id' => $transacao->getOperadoraID()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### 3. Estrutura do Webhook

O Asaas envia webhooks com a seguinte estrutura:

```json
{
  "event": "PAYMENT_RECEIVED",
  "data": {
    "id": "pay_xxxxx",
    "customerId": "cust_xxxxx",
    "value": 100.00,
    "netValue": 97.00,
    "billingType": "BOLETO",
    "status": "RECEIVED",
    "paymentDate": "2025-01-15",
    "dueDate": "2025-01-20",
    "createdAt": "2025-01-15",
    "updatedAt": "2025-01-15"
  }
}
```

## Ambientes

### Sandbox (Testes)

```php
// Defina a constante antes de instanciar
define('ASAAS_SANDBOX', true);
define('ENVIRONMENT', 'development');

$asaas = new Asaas($sandboxKey);
```

URL base: `https://api-sandbox.asaas.com/v3`

### Produção

```php
// Sem definir ASAAS_SANDBOX, usa produção
$asaas = new Asaas($productionKey);
```

URL base: `https://api.asaas.com/v3`

## Operações Avançadas

### Recuperar Cobrança

```php
$transacao = $asaas->getCharge('pay_xxxxx');
echo $transacao->getValorBruto();
echo $transacao->getOperadoraStatus();
```

### Listar Cartões Salvos

```php
$cartoes = $asaas->getCards($cliente);
foreach ($cartoes as $cartao) {
    echo $cartao->getNome() . " - " . $cartao->getUltimosQuatro();
}
```

### Reembolsar Cobrança

```php
$transacao = $asaas->refund('pay_xxxxx', 10000); // Valor em centavos
```

### Cancelar Cobrança

```php
$asaas->cancelCharge('pay_xxxxx');
```

### Atualizar Cliente

```php
$cliente->setEmail('novo-email@example.com');
$asaas->updateCustumer($cliente);
```

## Tratamento de Erros

```php
<?php

use CANNALPagamentos\Interfaces\Asaas;
use RuntimeException;

try {
    $transacao = $asaas->pix($cliente, $pedido);
} catch (RuntimeException $e) {
    // Erro específico da API Asaas
    echo "Erro Asaas: " . $e->getMessage();
    echo "Código: " . $e->getCode();
} catch (Exception $e) {
    // Erro genérico
    echo "Erro: " . $e->getMessage();
}
```

## Logging

A classe Asaas aceita uma instância de `Psr\Log\LoggerInterface`:

```php
<?php

use CANNALPagamentos\Interfaces\Asaas;
use Monolog\Logger;
use Monolog\Handlers\StreamHandler;

$logger = new Logger('asaas');
$logger->pushHandler(new StreamHandler('logs/asaas.log'));

$asaas = new Asaas($apiKey, 'Minha Empresa', $logger);
```

## Testes com Mock

Para testes sem fazer chamadas reais à API:

```php
<?php

use CANNALPagamentos\Mocks\AsaaSDKMock;

$mock = new AsaaSDKMock();

// Simular criação de cliente
$customer = $mock->createCustomer([
    'name' => 'João Silva',
    'email' => 'joao@example.com',
]);

// Simular criação de pagamento
$payment = $mock->createPayment([
    'customerId' => $customer['id'],
    'billingType' => 'BOLETO',
    'value' => 100.00,
]);

// Simular recebimento
$mock->receivePayment($payment['id']);

// Recuperar dados
$allPayments = $mock->getAllPayments();
$allCustomers = $mock->getAllCustomers();

// Limpar para próximo teste
$mock->reset();
```

## Segurança

### Validação de Webhooks

Sempre valide o token de autenticação:

```php
$authToken = 'seu_token_seguro_aqui';
$processor = new AsaasWebhookProcessor($logger, $authToken);
```

O token é enviado no header `asaas-access-token` em cada webhook.

### Idempotência

O Asaas garante entrega "at least once". Para evitar processar o mesmo evento múltiplas vezes:

```php
// Armazenar o ID do evento em seu banco de dados
$eventId = $payload['event']; // ou usar um ID único do webhook

// Verificar se já foi processado
if (already_processed($eventId)) {
    exit(200); // Responder sucesso sem processar novamente
}

// Processar webhook...
mark_as_processed($eventId);
```

## Mápas de Status

### Status de Pagamento

| Status | Descrição |
|--------|-----------|
| `PENDING` | Aguardando pagamento |
| `CONFIRMED` | Confirmado |
| `RECEIVED` | Recebido |
| `OVERDUE` | Vencido |
| `REFUNDED` | Reembolsado |
| `CANCELLED` | Cancelado |

### Tipo de Cobrança (billingType)

| Tipo | Descrição |
|------|-----------|
| `BOLETO` | Boleto bancário |
| `CREDIT_CARD` | Cartão de crédito |
| `PIX` | PIX (Instantâneo) |
| `DEBIT_ACCOUNT` | Débito em conta |

## Suporte

Documentação completa: https://docs.asaas.com/
Status: https://status.asaas.com/

## Changelog

### Versão 1.0.0 (2025-01-15)
- Suporte para Asaas
- Integração de formas de pagamento: Boleto, PIX, Cartão de Crédito
- Suporte a webhooks
- Mock para testes
