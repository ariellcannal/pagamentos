# 📊 ANÁLISE ESTRATÉGICA - BIBLIOTECA CANAL PAGAMENTOS

**Data**: 2026-01-15  
**Status**: FASE DE PLANEJAMENTO - Sem alterações na base de código  
**Objetivo**: Reestruturar para máxima agnose e padronização

---

## 🔍 DIAGNÓSTICO ATUAL

### ✅ Pontos Positivos

1. **Padrão de Interface bem definido** - `PagamentosInterface` fornece contrato claro
2. **Entidades genéricas** - `Cliente`, `Pedido`, `Cartao`, `Transacao` funcionam com todos bancos
3. **Webhook Processor abstrato** - `WebhookProcessorInterface` permite múltiplas implementações
4. **Logger integrado** - PSR-3 para logging consistente
5. **Estrutura de pastas lógica** - Interfaces, Entities, Webhooks, Mocks bem organizados

### ❌ Problemas Identificados

#### 1. **Construtor Inconsistente** 🔴 CRÍTICO
```php
// PAGARME - Atual
new Pagarme($key, $nome = null, $logger = null)

// INTER - Atual  
new Inter($logger, $clientId, $clientSecret, $certPath, $certPassword, $environment)

// C6 - Atual
new C6($logger, $baseUrl, $clientId, $clientSecret)

// ASAAS - Atual
new Asaas($apiKey, $nome = null, $logger = null)
```
**Problema**: Cada banco tem assinatura diferente. Consumidor não pode trocar de banco facilmente.

#### 2. **Métodos com Parâmetros Inconsistentes** 🔴 CRÍTICO
```php
// Pagarme
public function creditCard(Cliente &$cli, Pedido $pedido, Cartao|string $cartao): Transacao

// Inter (faltam parâmetros obrigatórios)
public function creditCard(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao

// C6 (mesma inconsistência)
public function creditCard(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
```

#### 3. **Falta de Método Criar Cliente** 🔴 CRÍTICO
Não há `createCustomer()` na interface, mas é necessário.

#### 4. **Resposta Sem Padronização em Português** 🔴 CRÍTICO
`Transacao` retorna dados brutos da operadora sem mapeamento:
- `operadoraStatus`: "CONFIRMED", "PENDING", "RECEIVED" (inglês, varia por banco)
- Falta campo padronizado como `statusPadronizado` em português

#### 5. **Webhook com Validação Inconsistente** 🟡 IMPORTANTE
Cada webhook faz validação diferente:
- Pagarme: `X-Hub-Signature`
- Asaas: `asaas-access-token`
- Falta abstração de segurança

#### 6. **Falta de Mapeador de Resposta** 🟡 IMPORTANTE
Não há estrutura para mapear respostas dos bancos para padrão agnóstico.

#### 7. **Parâmetros de Autenticação Soltos** 🟡 IMPORTANTE
São passados no construtor sem validação de quais são obrigatórios.

#### 8. **Falta Entidade de Resposta Padronizada** 🟡 IMPORTANTE
Não há `PagamentoResponse` ou similar que garanta os mesmos campos em português.

#### 9. **Sandbox/Produção Não Padronizado** 🟡 IMPORTANTE
- Pagarme: Define via constante `ENVIRONMENT`
- Asaas: Define via constante `ASAAS_SANDBOX`
- C6/Inter: Via parâmetro `$environment`

#### 10. **Mock Não Completo** 🟡 IMPORTANTE
- Mocks para Pagarme, Inter, C6, Asaas existem
- Mas não são consistentes entre si

---

## 🏗️ ARQUITETURA PROPOSTA

### 1. **Padrão de Instanciação (RESOLVER INCONSISTÊNCIA)**

```php
// Proposta unificada
$banco = new Pagarme(
    chavePublica: 'pk_...',
    chavePrivada: 'sk_...',     // Novo parâmetro padrão
    sandbox: true,               // Parâmetro padrão obrigatório
    nomeOperadora: null,
    logger: null
);

$banco = new Asaas(
    chavePublica: 'pk_...',
    chavePrivada: 'sk_...',      // Novo parâmetro padrão
    sandbox: true,
    nomeOperadora: null,
    logger: null
);

$banco = new C6(
    chavePublica: 'pk_...',
    chavePrivada: 'sk_...',
    sandbox: true,
    nomeOperadora: null,
    logger: null
);

$banco = new Inter(
    chavePublica: 'pk_...',
    chavePrivada: 'sk_...',
    sandbox: true,
    nomeOperadora: null,
    logger: null
    // Se Inter usar certificado: adicionar após logger
);
```
GOSTO MUITO DESSA ESTRUTURA
**Benefício**: Mesmo padrão para todos. Trocar de banco = trocar só a classe.

### 2. **Estrutura de Resposta Padronizada**

Criar novo arquivo: `src/DTO/PagamentoResponse.php`

```php
class PagamentoResponse {
    // Dados padronizados (nossa camada agnóstica)
    public string $id;                    // UUID nosso
    public string $idOperadora;          // ID do banco
    public string $status;               // PENDENTE, CONFIRMADO, PAGO, FALHA, CANCELADO
    public string $statusDescricao;      // "Pagamento confirmado"
    public string $forma;                // PIX, BOLETO, CARTAO, DEBITO
    public float $valor;
    public float $valorLiquido;
    public ?float $valorTaxa;
    public ?\DateTime $dataCriacao;
    public ?\DateTime $dataConfirmacao;
    public string $moeda;                // BRL, USD
    
    // Metadados da operadora (para debug)
    public array $dadosOperadora;        // Resposta bruta mapeada
    
    // Dados específicos por forma
    public ?string $pixQrCode;
    public ?string $pixQrCodeUrl;
    public ?string $boletoUrl;
    public ?string $boletoNumero;
    
    // Info de erro (se houver)
    public ?string $erroMensagem;
    public ?string $erroCodigo;
}
```
Essa classe deve ter também os atributos com o objeto Pedido, Cliente e Transação
### 3. **Factory Pattern para Instanciação**

Criar: `src/Factory/BancoFactory.php`

```php
class BancoFactory {
    public static function criar(
        string $tipo,           // 'pagarme', 'asaas', 'c6', 'inter'
        array $credenciais,     // Credenciais específicas
        bool $sandbox = true,
        ?LoggerInterface $logger = null
    ): PagamentosInterface {
        // Valida credenciais obrigatórias
        // Retorna instância correta
    }
}
```
Não quero esse Factory. 

### 4. **Mapper Pattern para Respostas**

Criar: `src/Mappers/BancoMapper.php` (abstrato)
E implementações: `src/Mappers/PagarmeMapper.php`, etc.

```php
abstract class BancoMapper {
    abstract public function mapResponse(array $apiResponse): PagamentoResponse;
    abstract public function mapError(array $apiResponse): \Exception;
    abstract public function mapWhookPayload(array $payload): array;
    abstract public function mapStatus(string $status): string; // Para português
}
```

### 5. **Estructura de Credenciais Padronizadas**

Criar: `src/DTO/CredenciaisAutenticacao.php`

```php
class CredenciaisAutenticacao {
    public string $tipo;              // 'pagarme', 'asaas', 'c6', 'inter'
    public string $chavePublica;      // Pagarme: pk_*, Asaas: key_*
    public string $chavePrivada;      // Pagarme: sk_*, Asaas: key_*
    public ?string $certificado;      // Inter
    public ?string $senhaCertificado; // Inter
    public bool $sandbox;
    
    // Método validação
    public function validate(): void
    
    // Método para ser usado na instanciação
    public static function fromEnv(string $banco): self
}
```
Naõ precisa de uma classe de credenciais. Elas podem ser passadas no contrutor da Classe d o Banco.

### 6. **Melhorias na Interface PagamentosInterface**

```php
interface PagamentosInterface {
    // Existentes (sem mudança funcional, mas com tipos melhores)
    public function creditCard(...): PagamentoResponse;
    public function pix(...): PagamentoResponse;
    public function boleto(...): PagamentoResponse;
    
    // NOVOS - Necessários
    public function criarCliente(Cliente $cliente): Cliente;  // FALTAVA
    public function atualizarCliente(Cliente $cliente): Cliente;
    public function obterCliente(string $id): Cliente;
    
    // NOVOS - Cartões
    public function salvarCartao(...): CartaoSalvo;
    public function listarCartoes(...): array;
    public function deletarCartao(string $id): bool;
    
    // NOVOS - Obter informações
    public function obterTransacao(string $id): PagamentoResponse;
    public function listarTransacoes(...): array;
    
    // EXISTENTES (renomear para padrão)
    public function estornar(string $idTransacao, float $valor): PagamentoResponse; // Rename: refund
    public function cancelar(string $idTransacao): PagamentoResponse;           // Rename: cancelCharge
    
    // INFO
    public function obterNome(): string; // Rename: getNome
}
```
Se algum banco não suportar alguma dessas funções retornar throw new CANNALPagamentosException
---

## 🎣 SUGESTÕES PARA WEBHOOKS

### Abordagem Proposta: **Webhook Genérico com Router**

```
POST /webhooks/processar
Body: {
    "banco": "pagarme",
    "evento": "pagamento.confirmado",
    "payload": {...}
}
```

**Implementação:**

1. **WebhookRouter** - Detecta qual banco + processador usar
   ```php
   class WebhookRouter {
       public function processar(array $payload, array $headers): PagamentoResponse {
           $banco = $this->detectBanco($payload, $headers);
           $processor = $this->getProcessor($banco);
           return $processor->processar($payload, $headers);
       }
   }
   ```

2. **WebhookProcessor** - Abstrato, com implementações específicas
   ```php
   abstract class WebhookProcessor {
       abstract public function processar(array $payload, array $headers): PagamentoResponse;
       abstract protected function validarAssinatura(array $headers): bool;
   }
   ```

3. **Tipos de Eventos Padronizados**
   - `pagamento.aguardando` → PENDENTE
   - `pagamento.confirmado` → CONFIRMADO
   - `pagamento.pago` → PAGO
   - `pagamento.recusado` → FALHA
   - `pagamento.cancelado` → CANCELADO
   - `pagamento.estornado` → ESTORNADO

4. **Assinatura/Token em Header Genérico**
   ```
   X-Webhook-Signature: sha256=...
   X-Webhook-Token: token_aqui
   X-Webhook-Timestamp: 1234567890
   ```
Pretendo que o consumidor da lib chame $cannalPagamentos->webhook(array $payload, array $headers): PagamentoResponse;
O método identifica o banco e retorna PagamentoResponse;
---

## 🧪 PROPOSTA: APLICAÇÃO DE TESTES (CI4)

### Estrutura de Pastas
```
tests/
├── .env                          # Config: API_KEYS, SANDBOX_MODE
├── .env.example                  
├── composer.json                 # Dependency: CodeIgniter 4
├── public/
│   └── index.php
├── app/
│   ├── Controllers/
│   │   ├── DashboardController.php     # Página principal
│   │   ├── TransacoesController.php
│   │   └── WebhookController.php
│   ├── Models/
│   │   ├── TransacaoModel.php
│   │   └── WebhookLogModel.php
│   ├── Views/
│   │   ├── layout.php
│   │   ├── dashboard.php
│   │   ├── pagarme/
│   │   │   ├── transacoes.php
│   │   │   └── webhooks.php
│   │   ├── asaas/
│   │   │   ├── transacoes.php
│   │   │   └── webhooks.php
│   │   ├── c6/
│   │   │   ├── transacoes.php
│   │   │   └── webhooks.php
│   │   └── inter/
│   │       ├── transacoes.php
│   │       └── webhooks.php
│   └── Database/
│       └── Migrations/
│           └── 001_criar_tabelas.php
├── writable/
│   └── logs/
└── README.md
```

### Funcionalidades da Aplicação

1. **Dashboard Principal**
   - Menu lateral com abas por banco
   - Cada banco tem 2 sub-abas: Transações e Webhooks
   - Resumo de transações (últimas 10)

2. **Aba Transações (de cada banco)**
   - Tabela com:
     - ID interno, ID Operadora
     - Status, Forma de Pagamento
     - Valor, Data de Criação
     - Ações: Ver detalhes, Reembolsar, Cancelar
   - Formulário para criar teste:
     - Tipo de pagamento (PIX, Boleto, Cartão)
     - Valor
     - Botão: Enviar para Sandbox
   - Detalhes completos da transação:
     - Resposta bruta da operadora (JSON)
     - Resposta mapeada (padrão agnóstico)
     - Comparação: Esperado vs Real

3. **Aba Webhooks (de cada banco)**
   - Tabela com:
     - ID Transação, Tipo Evento
     - Timestamp do Webhook
     - Status do Processamento
     - Payload (colapses)
   - Botão: Simular Webhook (para teste manual)
   - Log de erros ao processar

4. **Banco de Dados**
   ```sql
   transacoes:
   - id (PK)
   - banco (pagarme, asaas, c6, inter)
   - id_operadora
   - tipo (pix, boleto, cartao)
   - status
   - valor
   - resposta_bruta (JSON)
   - resposta_mapeada (JSON)
   - criado_em
   
   webhook_logs:
   - id (PK)
   - transacao_id (FK)
   - banco
   - tipo_evento
   - payload (JSON)
   - assinatura_valida (boolean)
   - processado (boolean)
   - erro_mensagem (nullable)
   - recebido_em
   - processado_em
   ```

5. **Features Especiais**
   - Sincronização automática: Clica em "Recarregar" e consulta status na API
   - Comparador de versões: Mostra o que foi retornado vs o que era esperado
   - Export em CSV/JSON - Não precisa

---

## 📋 MAPEAMENTO DE RESPOSTAS

### Exemplo: Status em Português

| Banco | Status Original | Mapeado para | Português |
|-------|---|---|---|
| Pagarme | `waiting_payment` | `PENDENTE` | Em Aberto |
| Pagarme | `paid` | `PAGO` | Pago |
| Asaas | `PENDING` | `PENDENTE` | Em Aberto |
| Asaas | `RECEIVED` | `PAGO` | Pago |
| C6 | `CREATED` | `PENDENTE` | Criado |
| C6 | `APPROVED` | `CONFIRMADO` | Aprovado |
| Inter | `EM_ABERTO` | `PENDENTE` | Em Aberto |
| Inter | `PAGO` | `PAGO` | Pago |

### Exemplo: Mapper Pagarme

```php
class PagarmeMapper extends BancoMapper {
    public function mapStatus(string $status): string {
        $mapping = [
            'waiting_payment' => 'PENDENTE',
            'paid' => 'PAGO',
            'canceled' => 'CANCELADO',
            'refunded' => 'ESTORNADO',
        ];
        return $mapping[$status] ?? 'DESCONHECIDO';
    }
    
    public function mapResponse(array $apiResponse): PagamentoResponse {
        $resp = new PagamentoResponse();
        $resp->idOperadora = $apiResponse['id'];
        $resp->status = $this->mapStatus($apiResponse['status']);
        // ... mapear outros campos
        return $resp;
    }
}
```

---

## 🔄 FLUXO PROPOSTO DE MUDANÇA

### Fase 1: REFACTOR (Preparação)
1. Criar DTOs (CredenciaisAutenticacao, PagamentoResponse)
2. Criar Mappers abstrato + implementações
3. Criar Factory
4. **SEM QUEBRAR** a código atual

### Fase 2: TRANSIÇÃO (Novo interface)
1. Criar `PagamentosInterfaceV2` (novo padrão)
2. Adaptar todas as implementações
3. Manter `PagamentosInterface` antiga por compatibilidade

### Fase 3: DEPRECAÇÃO
1. Marcar Interface antiga como `@deprecated`
2. Documentar migração
3. Remover em versão major (3.0)

### Fase 4: TESTES
1. Criar app CI4
2. Testar cada banco sandbox
3. Validar mapeamentos

---

## 📝 CHECKLIST PRE-IMPLEMENTAÇÃO

### Decisões a Tomar

- [ ] Usar Factory Pattern ou IoC Container?
- [ ] Única interface ou interface + classe abstrata?
- [ ] Suportar múltiplos sandbox/produção no mesmo objeto?
- [ ] Versionamento de API (V1, V2)?
- [ ] Banco de dados SQLite ou externo na app testes?
- [ ] CI4 ou Laravel para app testes?

### Validações Necessárias

- [ ] Pagarme: Quais parâmetros são obrigatórios?
- [ ] Asaas: Como funciona certificado?
- [ ] C6: Precisa OAuth2 ou chave simples?
- [ ] Inter: Suporta PIX direto ou precisa de Boleto?

### Setup Técnico

- [ ] Criar branch para refactor
- [ ] Setup CI(GitHub Actions/GitLab)
- [ ] Criar templates de testes automatizados

---

## 🎯 RESUMO EXECUTIVO

| Aspecto | Problema | Solução |
|---|---|---|
| **Construtor** | Inconsistente por banco | Factory + Credenciais unificadas |
| **Resposta** | Sem padrão português | PagamentoResponse + Mappers |
| **Webhook** | Validação variada | WebhookRouter + Processadores |
| **Método Criar Cliente** | Falta | Adicionar na interface |
| **Testes** | Nenhuma app | CI4 com sandbox integrado |
| **Manutenção** | Difícil trocar banco | Agnóstico completo |

---

## 💡 PRÓXIMAS AÇÕES

1. **Seus comentários**: Qual abordagem prefer?
2. **Validar propostas** com stakeholders
3. **Priorizadas por banco**: Começar com Pagarme?
4. **Timeline estimado**: 2-3 sprints para completar
5. **Testes**: Cada mudança com testes de regressão

---

**Aguardando seu feedback para prosseguir! 🚀**
