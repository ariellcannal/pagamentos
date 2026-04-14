# 🚀 Guia Rápido - Asaas

## Instalação (1 minuto)

Nada a fazer - os arquivos já estão na biblioteca!

```
src/Interfaces/Asaas.php           ✅
src/Webhooks/AsaasWebhookProcessor.php ✅
src/Mocks/AsaaSDKMock.php          ✅
```

---

## Uso Básico (2 minutos)

### 1. Criar uma Cobrança de Boleto

```php
<?php
use CANNALPagamentos\Interfaces\Asaas;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use Psr\Log\NullLogger;

// Inicializar
$asaas = new Asaas('seu_token_api', 'Minha Empresa', new NullLogger());

// Dados do cliente
$cliente = new Cliente();
$cliente->setNome('João Silva');
$cliente->setEmail('joao@example.com');
$cliente->setCpf('12345678901');

// Dados do pedido
$pedido = new Pedido();
$pedido->setValor(100.00);
$pedido->setDescricao('Seu Produto');

// Gerar boleto
$transacao = $asaas->boleto($cliente, $pedido);

echo "Cobrança criada: " . $transacao->getOperadoraID();
```

### 2. Pagamento via PIX

```php
// Mesmo cliente e pedido do exemplo anterior
$transacao = $asaas->pix($cliente, $pedido);
$qrCode = $transacao->getPixQrCodeUrl();

echo "QR Code: $qrCode";
```

### 3. Cartão de Crédito

```php
use CANNALPagamentos\Entities\Cartao;

$cartao = new Cartao();
$cartao->setNumero('4111111111111111');
$cartao->setNome('JOAO SILVA');
$cartao->setVencimentoMes(12);
$cartao->setVencimentoAno(2025);
$cartao->setCodigo('123');

$transacao = $asaas->creditCard($cliente, $pedido, $cartao);
echo "Transação processada: " . $transacao->getOperadoraID();
```

---

## Webhooks (1 minuto)

### Receber Eventos em Seu Sistema

```php
<?php
use CANNALPagamentos\Webhooks\AsaasWebhookProcessor;
use Psr\Log\NullLogger;

// Seu arquivo webhook_handler.php
$payload = json_decode(file_get_contents('php://input'), true);
$headers = getallheaders();

$processor = new AsaasWebhookProcessor(new NullLogger(), 'seu_token_aqui');

try {
    $transacao = $processor->process($payload, $headers);
    
    // Atualizar banco de dados
    update_transaction($transacao->getOperadoraID(), $transacao->getOperadoraStatus());
    
    http_response_code(200);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
```

**Configurar Webhook:**
1. Ir em https://app.asaas.com/ → Integrações → Webhooks
2. Adicionar sua URL
3. Definir um token seguro
4. Selecionar eventos desejados

---

## Testes com Mock (30 segundo)

```php
<?php
use CANNALPagamentos\Mocks\AsaaSDKMock;

$mock = new AsaaSDKMock();

// Dados fictícios
$customer = $mock->createCustomer([
    'name' => 'João Silva',
    'email' => 'joao@example.com',
]);

$payment = $mock->createPayment([
    'customerId' => $customer['id'],
    'billingType' => 'BOLETO',
    'value' => 100.00,
]);

echo json_encode($payment, JSON_PRETTY_PRINT);
```

---

## Roadmap Simples (5 minutos)

```
1. Obter chave de API
   └─ Acessar https://app.asaas.com/

2. Testar com Sandbox
   define('ASAAS_SANDBOX', true);

3. Implementar pagamentos
   ├─ Boleto
   ├─ PIX
   └─ Cartão

4. Configurar Webhooks
   └─ Receber eventos em tempo real

5. Deploy em Produção
   └─ Usar chave de produção
```

---

## Cheat Sheet de Métodos

```php
// Cliente
$asaas->updateCustumer($cliente);

// Pagamentos
$asaas->boleto($cliente, $pedido);
$asaas->pix($cliente, $pedido);
$asaas->creditCard($cliente, $pedido, $cartao);

// Cartões
$asaas->saveCard($cliente, $cartao);
$asaas->getCards($cliente);

// Consultas
$asaas->getCharge($payment_id);
$asaas->getReceivables();

// Operações
$asaas->refund($payment_id, $amount);
$asaas->cancelCharge($payment_id);

// Webhook
$processor->process($payload, $headers);
```

---

## Status HTTP esperados

```
200-299  ✅ Sucesso
400      ⚠️  Erro - Validar dados
401      🔐 Erro - Token inválido
404      ❌ Erro - Recurso não encontrado
500      💥 Erro - Servidor
```

---

## Sandbox vs Produção

```php
// Sandbox (Testes)
define('ASAAS_SANDBOX', true);
$token = 'seu_token_sandbox';

// Produção (Real)
// Não defina ASAAS_SANDBOX
$token = 'seu_token_producao';
```

---

## 🐛 Dicas de Debug

1. **Ativar logging:**
   ```php
   use Monolog\Logger;
   use Monolog\Handlers\StreamHandler;
   
   $log = new Logger('asaas');
   $log->pushHandler(new StreamHandler('logs/asaas.log'));
   $asaas = new Asaas($token, 'Empresa', $log);
   ```

2. **Ver resposta bruta:**
   ```php
   $transacao->getOperadoraResposta(); // JSON da API
   ```

3. **Testar webhook localmente:**
   ```php
   // Use ngrok para expor localhost
   ngrok http 8000
   // Depois registre a URL do ngrok no painel
   ```

---

## Documentação Completa

Veja [INTEGRACAO_ASAAS.md](INTEGRACAO_ASAAS.md) para documentação detalhada.

Veja [ASAAS_IMPLEMENTACAO.md](ASAAS_IMPLEMENTACAO.md) para detalhes de implementação.

Veja [examples/asaas_example.php](examples/asaas_example.php) para 12 exemplos práticos.

Veja [tests/AsaasIntegrationTest.php](tests/AsaasIntegrationTest.php) para testes.

---

## Suporte

- Docs: https://docs.asaas.com/
- Status: https://status.asaas.com/
- Discord: https://discord.gg/invite/X2kgZm69HV

---

**Pronto para começar? Copie um dos exemplos acima e comece! 🎉**
