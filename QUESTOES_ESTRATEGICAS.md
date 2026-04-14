# ❓ QUESTÕES ESTRATÉGICAS PARA APROVAÇÃO

Para proceder com a implementação, preciso entender suas preferências e restrições:

---

## 1️⃣ PADRÃO DE CONSTRUTOR

### Opção A: Factory Pattern Puro (Recomendado)
```php
// Uso
$credenciais = CredenciaisAutenticacao::fromEnv('pagarme');
$pagarme = BancoFactory::criar($credenciais);

// Vantagem: Máxima flexibilidade, agnóstico
// Desvantagem: Quebra código existente (mas com wrapper)
```

### Opção B: Construtor Único com Variadic
```php
// Uso
$pagarme = new Pagarme(...CredenciaisAutenticacao::fromEnv('pagarme')->toArray());

// Vantagem: Menos classes novas
// Desvantagem: Menos claro, mais confuso
```

### Opção C: ServiceProvider (Laravel/Pimple)
```php
// Uso
$pagarme = $container->get('pagarme');

// Vantagem: Máxima integração com frameworks
// Desvantagem: Dependência externa
```

**QUAL PREFERE? A (Factory), B (Variadic), ou C (ServiceProvider)?**
A
---

## 2️⃣ RESPOSTA AO CONSUMIDOR

A resposta final das operações deve ser:

### Opção A: DTO com Propriedades Públicas
```php
$resposta = $pagarme->creditCard($cliente, $pedido, $cartao);
echo $resposta->status;           // "PAGO"
echo $resposta->valor;            // 100.00
echo $resposta->dataConfirmacao;  // DateTime
```

### Opção B: DTO com Getters
```php
$resposta = $pagarme->creditCard($cliente, $pedido, $cartao);
echo $resposta->getStatus();         // "PAGO"
echo $resposta->getValor();          // 100.00
```

### Opção C: Array Simples
```php
$resposta = $pagarme->creditCard($cliente, $pedido, $cartao);
echo $resposta['status'];    // "PAGO"
echo $resposta['valor'];     // 100.00
```

**QUAL PREFERE? A (Properties), B (Getters), ou C (Array)?**
B
---

## 3️⃣ VERSIONAMENTO

### Opção A: V2 (Breaking Change)
```
- Versão 2.0 com novo padrão
- Versão 1.x deprecated
- Timeline: Descontinuar 1.x em 6 meses
```

### Opção B: V1.1 (Compatível)
```
- Mantém backward compatibility
- Novo padrão é opção
- Ambos construtores funcionam indefinidamente
```

### Opção C: Separate Namespace
```
// Antigo continua como é
namespace CanalPagamentos\v1;

// Novo em namespace separado
namespace CanalPagamentos\v2;
```

**QUAL ESTRATÉGIA? V2, V1.1, ou Namespace?**
A
---

## 4️⃣ WEBHOOK SECURITY

### Opção A: Token Único (Simples)
```php
// .env
WEBHOOK_TOKEN_PAGARME=token_secreto_123
WEBHOOK_TOKEN_ASAAS=token_secreto_456

// Validação
if ($headers['X-Webhook-Token'] !== env('WEBHOOK_TOKEN_' . strtoupper($banco))) {
    throw new Exception("Token inválido");
}
```

### Opção B: IP Whitelist (Seguro)
```php
// .env
WEBHOOK_IPS_PAGARME=213.136.0.0/16,API.PAGARME.COM
WEBHOOK_IPS_ASAAS=177.71.0.0/16

// Validação
if (!ipInCidr($_SERVER['REMOTE_ADDR'], env('WEBHOOK_IPS_' . strtoupper($banco)))) {
    throw new Exception("IP não autorizado");
}
```

### Opção C: Assinatura (Mais Seguro)
```php
// Pagarme: X-Hub-Signature SHA256
// Asaas: POST HMAC-SHA256
// Validar assinatura conforme especificação de cada banco
```

### Opção D: Combinado (Recomendado)
```
IP Whitelist (camada 1)
+ Token (camada 2)
+ Timestamp validado (camada 3)
```

**QUAL NÍVEL DE SEGURANÇA? A, B, C, ou D?**
D
---

## 5️⃣ APLICAÇÃO DE TESTES

### Opção A: CodeIgniter 4 (Completo)
```
- Framework completo
- Migrations, Models, Views
- Autenticação, validação
- Mais pesado, ~ 3 dias
```

### Opção B: Slim Framework (Leve)
```
- Microframework
- Endpoints simples
- API REST apenas
- Menos pesado, ~ 1 dia
```

### Opção C: Apenas Funções Auxiliares (Mínimo)
```
- Sem framework
- Função PHP pura para testar
- Apenas cURL + DB
- ~2 horas
```

**QUAL ESCOLHE? A (CI4), B (Slim), ou C (Funções)?**
A
---

## 6️⃣ BANCO DE DADOS

### Opção A: SQLite (Local)
```
// .env
DB_DRIVER=sqlite
DB_DATABASE=./tests/database.sqlite

// Vantagens: Zero config, portável
// Desvantagens: Não escalável, apenas dev
```

### Opção B: MySQL (Ambientes)
```
// .env
DB_DRIVER=mysql
DB_HOST=localhost
DB_DATABASE=pagamentos_test

// Vantagens: Realista, production-like
// Desvantagens: Precisa MySQL instalado
```

### Opção C: PostgreSQL (Robusto)
```
// Similar ao MySQL, mas PostgreSQL
// Mais robusto para JSON/JSONB
```

**QUAL BANCO? SQLite, MySQL, ou PostgreSQL?**
SQLite
---

## 7️⃣ PRIORIDADE POR BANCO

Qual banco testar primeiro em detalhes?

### Opção A: Pagarme (Mais Simples)
```
- SDK pronto
- Documentação completa
- Exemplo: 1 dia
```

### Opção B: Asaas (Implementado)
```
- Já tem implementação nova
- cURL direto
- Exemplo: 1 dia
```

### Opção C: C6 (Complexo)
```
- Guzzle HTTP
- OAuth2
- Exemplo: 2 dias
```

### Opção D: Inter (Certificado)
```
- Certificate SSL
- Mais validações
- Exemplo: 2 dias
```

**QUAL ORDEM? Pagarme → Asaas → C6 → Inter?**
Pagarme → Asaas → C6 → Inter
---

## 8️⃣ INTEGRAÇÕES EXTERNAS

Precisa de alguma integração específica?

- [ ] Slack (notificações de erros)
- [ ] Sentry (error tracking)
- [ ] DataDog (monitoring)
- [ ] Stripe (referência de API?)
- [ ] Nenhuma (manter simples)

**QUAIS INTEGRAR?**
Nenhuma
---

## 9️⃣ TRATAMENTO DE ERROS

### Opção A: Exceções Customizadas (OOP)
```php
throw new PaymentFailedException("Cartão recusado");
throw new PaymentAuthenticationException("API Key inválida");
throw new WebhookValidationException("Token inválido");

// Catch específico
try {
    $resultado = $banco->pagar();
} catch (PaymentFailedException $e) {
    // Lógica específica
} catch (PaymentException $e) {
    // Genérico
}
```

### Opção B: Código de Erro Numérico
```php
return [
    'sucesso' => false,
    'codigo_erro' => 4001,  // Cartão recusado
    'mensagem_erro' => "Cartão recusado pelo banco"
]
```

### Opção C: Híbrido (Recomendado)
```php
// Exceções para DEV
throw new PaymentException(code: 4001, message: "Cartão recusado");

// OU Resposta para API
return PagamentoResponse::erro(codigo: 4001, mensagem: "Cartão recusado");
```

**QUAL ABORDAGEM? A (Exceções), B (Códigos), ou C (Híbrido)?**
C
---

## 🔟 STATUS CODES PADRÃO

Quais status padrão em português?

```
✓ PENDENTE          → Aguardando processamento
✓ PAGAMENTO_RECEBIDO → Pago/Confirmado
✓ PAGAMENTO_VENCIDO  → Vencido
✓ PAGAMENTO_CANCELADO → Cancelado
✓ PAGAMENTO_RECUSADO → Recusado/Falha
✓ REEMBOLSADO        → Reembolsado
```

Adiciona mais algum?
Aguardando processamento deve ser "Em Processamento"
"Recusado/Falha" deve ser só "Falha"
Deve haver status para "Reembolsado" e "Reembolsado Parcialmente"
---

## 1️⃣1️⃣ FEATURES EXTRAS

Implementar:

- [ ] Retry automático (em caso de falha)
- [ ] Rate limiting interno
- [ ] Cache de respostas (reduzir queries)
- [ ] Audit log (quem chamou o quê)
- [ ] Webhook simulator (para testes)
- [ ] Dashboard para visualizar erros
- [ ] Export de transações (CSV/Excel)
O consumidor da lib deve ter a opção para "Reenviar/Reprocessar" a transação, para os casos em que a transação foi recusada por um antifraude que agora está liberado, por exemplo. Não vai mais ser necessário criar uma nova transação, basta reenviar.
---

## 1️⃣2️⃣ TIMELINE PRÉVIA

Baseado nas minhas estimativas:

- **CURTA (5-7 dias)**: Factory + Response Mapping (sem app teste)
- **MÉDIA (10-15 dias)**: + App teste simples (Slim)
- **LONGA (15-20 dias)**: + App teste completo (CI4)

Qual horizonte tem?

---

## 📋 RESUMO - DECISION MATRIX

| Questão | Opção Sugerida | Razão |
|---------|---|---|
| **1. Constructor** | A (Factory) | Máxima agnose |
| **2. Response** | A (DTO Props) | Simples, claro |
| **3. Versionamento** | B (V1.1) | Compatibilidade |
| **4. Security** | D (Combinado) | Mais seguro |
| **5. App Testes** | A (CI4) | Mais completo |
| **6. Database** | A (SQLite) | Dev local |
| **7. Prioridade Banco** | Pagarme → Asaas → C6 → Inter | Do simples para complexo |
| **8. Integrações** | Nenhuma | Manter simples |
| **9. Erros** | C (Híbrido) | Flexível |
| **10. Status Codes** | Os listados | Suficientes |
| **11. Extras** | Webhook Simulator | Facilita testes |
| **12. Timeline** | MÉDIA (10-15 dias) | Equilíbrio |

---

## 🎯 PRÓXIMAS AÇÕES

1. **Você responde as 12 questões acima**
2. **Eu monto documento com suas decisões**
3. **Iniciamos Fase 1 (Foundations)**

Quer que eu comece já alguma coisa enquanto espero feedback?

---

## 💬 COMENTÁRIOS FINAIS

Este refactor é importante para:

✅ **Agnose verdadeira**: Trocar banco = 1 linha  
✅ **Manutenibilidade**: Novo dev entende rapidamente  
✅ **Testes**: App sandbox testa tudo uniformemente  
✅ **Segurança**: Webhooks validados centralizmente  
✅ **Escalabilidade**: Fácil adicionar novo banco (Stripe, Braspag, etc)  

---

**Aguardando suas respostas! 🚀**
