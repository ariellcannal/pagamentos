# 🛠️ PLANO DE IMPLEMENTAÇÃO DETALHADO

**Objetivo**: Reestruturar biblioteca para máxima agnose  
**Duração Estimada**: 10-15 dias (4 sprints de 2-3 dias)  
**Risco**: BAIXO (compatibilidade mantida)

---

## 📍 ROADMAP POR FASES

### **FASE 1: Fundações (2-3 dias)**
*Criar estrutura base sem quebrar código existente*

#### Sprint 1.1: DTOs e Entidades
```
✓ Criar src/DTO/CredenciaisAutenticacao.php
✓ Criar src/DTO/PagamentoResponse.php
✓ Criar src/DTO/ClienteResponse.php
✓ Criar src/DTO/CartaoResponse.php
✓ Criar src/DTO/ErroResponse.php
✓ Criar testes unitários (tests/DTO/)
```

**Arquivo Exemplo: CredenciaisAutenticacao.php**
```php
<?php
namespace CanalPagamentos\DTO;

class CredenciaisAutenticacao {
    public string $tipo;              // pagarme, asaas, c6, inter
    public string $chavePublica;      
    public string $chavePrivada;      
    public ?string $certificado = null;
    public ?string $senhaCertificado = null;
    public bool $sandbox = true;
    public ?string $nomeOperadora = null;
    
    // Validação
    public function validar(): void {
        if (empty($this->tipo)) {
            throw new \InvalidArgumentException("tipo obrigatório");
        }
        if (!in_array($this->tipo, ['pagarme', 'asaas', 'c6', 'inter'])) {
            throw new \InvalidArgumentException("tipo inválido: {$this->tipo}");
        }
        if (empty($this->chavePublica) || empty($this->chavePrivada)) {
            throw new \InvalidArgumentException("chavePublica e chavePrivada são obrigatórias");
        }
    }
    
    // Factory para .env
    public static function fromEnv(string $banco, ?string $envPrefix = null): self {
        $prefix = $envPrefix ?? strtoupper($banco) . '_';
        
        $creds = new self();
        $creds->tipo = $banco;
        $creds->chavePublica = getenv($prefix . 'CHAVE_PUBLICA') 
            ?: throw new \Exception("Variável {$prefix}CHAVE_PUBLICA não encontrada");
        $creds->chavePrivada = getenv($prefix . 'CHAVE_PRIVADA')
            ?: throw new \Exception("Variável {$prefix}CHAVE_PRIVADA não encontrada");
        $creds->sandbox = (bool)(getenv($prefix . 'SANDBOX') ?? true);
        $creds->nomeOperadora = getenv($prefix . 'NOME_OPERADORA');
        
        // Específico para Inter
        if ($banco === 'inter') {
            $creds->certificado = getenv($prefix . 'CERTIFICADO');
            $creds->senhaCertificado = getenv($prefix . 'SENHA_CERTIFICADO');
        }
        
        $creds->validar();
        return $creds;
    }
}
```

#### Sprint 1.2: Mappers Base
```
✓ Criar src/Mappers/BancoMapperInterface.php
✓ Criar src/Mappers/StatusMapper.php (mapeamento padrão)
✓ Criar tests/Mappers/StatusMapperTest.php
```

**Arquivo Exemplo: BancoMapperInterface.php**
```php
<?php
namespace CanalPagamentos\Mappers;

use CanalPagamentos\DTO\PagamentoResponse;
use CanalPagamentos\DTO\ErroResponse;

interface BancoMapperInterface {
    /**
     * Mapeia resposta bruta da API para DTO padronizado
     */
    public function mapearResposta(array $apiResponse): PagamentoResponse;
    
    /**
     * Mapeia erro da API para DTO de erro
     */
    public function mapearErro(array $apiResponse): ErroResponse;
    
    /**
     * Mapeia payload de webhook para Transacao
     */
    public function mapearWebhook(array $payload): array;
    
    /**
     * Mapeia status do banco para status padronizado
     */
    public function mapearStatus(string $statusOriginal): string;
    
    /**
     * Obtém status em português
     */
    public function obterDescricaoStatus(string $statusPadronizado): string;
}
```

#### Sprint 1.3: Factory Pattern
```
✓ Criar src/Factory/BancoFactory.php
✓ Criar documentation: Factory.md
✓ Criar tests/Factory/BancoFactoryTest.php
```

**Uso Esperado:**
```php
$credenciais = CredenciaisAutenticacao::fromEnv('pagarme');
$pagarme = BancoFactory::criar($credenciais, $logger);

// Ou direto:
$pagarme = BancoFactory::criar(
    tipo: 'pagarme',
    chavePublica: 'pk_...',
    chavePrivada: 'sk_...',
    sandbox: true
);
```

---

### **FASE 2: Response Mapping (2-3 dias)**
*Implementar mappers para cada banco*

#### Sprint 2.1: Mapper Pagarme
```
✓ Criar src/Mappers/PagarmeMapper.php
✓ Mapeamento de status Pagarme → Padrão
✓ Mapeamento de erros Pagarme
✓ Testes unitários
```

**Mapeamento de Status Pagarme:**
```php
// Pagarme Status → Padrão
waiting_payment         → AGUARDANDO
paid                    → PAGO
refused                 → FALHA
chargebacked           → ESTORNADO
canceled               → CANCELADO
```

#### Sprint 2.2: Mapper Asaas
```
✓ Criar src/Mappers/AsaasMapper.php
✓ Mapeamento de status Asaas → Padrão
✓ Mapeamento de erros Asaas
✓ Testes unitários
```

**Mapeamento de Status Asaas:**
```php
// Asaas Status → Padrão
PENDING         → AGUARDANDO
RECEIVED        → PAGO
REFUNDED        → ESTORNADO
EXPIRED         → EXPIRADO
CANCELED        → CANCELADO
```

#### Sprint 2.3: Mapper C6
```
✓ Criar src/Mappers/C6Mapper.php
✓ Mapeamento de status C6 → Padrão
✓ Mapeamento de erros C6
✓ Testes unitários
```

#### Sprint 2.4: Mapper Inter
```
✓ Criar src/Mappers/InterMapper.php
✓ Mapeamento de status Inter → Padrão
✓ Mapeamento de erros Inter
✓ Testes unitários
```

---

### **FASE 3: Refactor das Implementações (2-3 dias)**
*Adaptar cada banco para novo padrão*

#### Sprint 3.1: Refactor Pagarme
```
✓ Adaptar src/Interfaces/Pagarme.php
  - Novo construtor com CredenciaisAutenticacao
  - Retorno as respostas via mapper
  - Compatibilidade com interface atual (wrapper)
✓ Testes de regressão
```

**Novo Construtor:**
```php
// ANTES
public function __construct(string $key, ?string $nome, ?LoggerInterface $logger) {
    // ...
}

// DEPOIS - Opção 1 (Retrocompatível)
public function __construct(
    string|CredenciaisAutenticacao $credenciais,
    ?LoggerInterface $logger = null
) {
    if (is_string($credenciais)) {
        // Modo compatibilidade: assumir é $key, nome null
        $credenciais = new CredenciaisAutenticacao();
        $credenciais->tipo = 'pagarme';
        $credenciais->chavePrivada = $credenciais;
    }
    // ... resto do código
}
```

#### Sprint 3.2: Refactor Asaas
```
✓ Adaptar src/Interfaces/Asaas.php (similar ao Pagarme)
✓ Testes de regressão
```

#### Sprint 3.3: Refactor C6
```
✓ Adaptar src/Interfaces/C6.php
✓ Testes de regressão
```

#### Sprint 3.4: Refactor Inter
```
✓ Adaptar src/Interfaces/Inter.php (mais complexo - certificado)
✓ Testes de regressão
```

---

### **FASE 4: Webhooks (2 dias)**
*Completar sistema de webhooks*

#### Sprint 4.1: Webhook Genérico
```
✓ Criar src/Webhooks/WebhookRouter.php
✓ Criar src/Webhooks/WebhookValidator.php (abstrato)
✓ Testes unitários
```

**Arquivo: WebhookRouter.php**
```php
<?php
namespace CanalPagamentos\Webhooks;

use CanalPagamentos\Entities\Transacao;

class WebhookRouter {
    private array $processors = [];
    
    public function registrar(string $banco, WebhookProcessorInterface $processor): self {
        $this->processors[$banco] = $processor;
        return $this;
    }
    
    public function processar(
        string $banco,
        array $payload, 
        array $headers = []
    ): Transacao {
        if (!isset($this->processors[$banco])) {
            throw new \Exception("Nenhum processador registrado para: {$banco}");
        }
        
        $processor = $this->processors[$banco];
        return $processor->process($payload, $headers);
    }
    
    /**
     * Auto-detecta banco pelos headers/payload
     */
    public function processarAuto(array $payload, array $headers): Transacao {
        $banco = $this->detectarBanco($payload, $headers);
        return $this->processar($banco, $payload, $headers);
    }
    
    private function detectarBanco(array $payload, array $headers): string {
        // Pagarme: X-Hub-Signature
        if (isset($headers['X-Hub-Signature'])) {
            return 'pagarme';
        }
        
        // Asaas: asaas-access-token
        if (isset($headers['asaas-access-token'])) {
            return 'asaas';
        }
        
        // C6: Custom header
        if (isset($headers['X-C6-Signature'])) {
            return 'c6';
        }
        
        // Inter: Custom header
        if (isset($headers['X-Inter-Signature'])) {
            return 'inter';
        }
        
        throw new \Exception("Não consegui detectar o banco do webhook");
    }
}
```

#### Sprint 4.2: Processadores Faltantes
```
✓ Criar src/Webhooks/InterWebhookProcessor.php
✓ Criar src/Webhooks/C6WebhookProcessor.php
✓ Melhorar src/Webhooks/AsaasWebhookProcessor.php
✓ Manter src/Webhooks/PagarmeWebhookProcessor.php
✓ Testes unitários
```

---

### **FASE 5: App de Testes (3-4 dias)**
*Criar aplicação CI4 para sandbox*

#### Sprint 5.1: Setup CI4
```
✓ Criar /tests directory com CI4
✓ Criar .env.example
✓ Criar composer.json
✓ Criar database migrations
```

#### Sprint 5.2: Controllers
```
✓ Criar DashboardController
✓ Criar TransacoesController
✓ Criar WebhookController
```

#### Sprint 5.3: Views
```
✓ Criar layout base
✓ Criar dashboard (com abas)
✓ Criar formulários por banco
✓ Criar tabelas de resultado
```

#### Sprint 5.4: Database
```
✓ Migration: create_transacoes
✓ Migration: create_webhooks_log
✓ Models: TransacaoModel, WebhookLogModel
```

---

## 📊 TIMELINE VISUAL

```
Semana 1: FASE 1 (DTOs, Mappers Base, Factory)
├─ Dia 1-2: Sprint 1.1, 1.2
├─ Dia 3: Sprint 1.3
└─ Testes: 100%

Semana 2: FASE 2 (Mappers Específicos)
├─ Dia 1: Sprint 2.1 + 2.2
├─ Dia 2-3: Sprint 2.3 + 2.4
└─ Testes: 100%

Semana 3: FASE 3 (Refactor Implementações)
├─ Dia 1: Sprint 3.1
├─ Dia 2: Sprint 3.2 + 3.3
├─ Dia 3: Sprint 3.4
└─ Testes de Regressão: 100%

Semana 4: FASE 4 + 5 (Webhooks + App)
├─ Dia 1: Sprint 4.1 + 4.2
├─ Dia 2-3: Sprint 5.1 + 5.2 + 5.3
├─ Dia 4: Sprint 5.4
└─ QA: 100%
```

---

## 🧪 ESTRATÉGIA DE TESTES

### Compatibilidade Regressão
```php
// Código antigo DEVE continuar funcionando
$pagarme = new Pagarme('sk_...', 'Minha Loja', $logger);
$transacao = $pagarme->creditCard($cliente, $pedido, $cartao);
// ^ Ainda funciona
```

### Novo Código
```php
// Novo código com Factory
$credenciais = CredenciaisAutenticacao::fromEnv('pagarme');
$pagarme = BancoFactory::criar($credenciais, $logger);
$transacao = $pagarme->creditCard($cliente, $pedido, $cartao);
// ^ Tambem funciona
```

### Estrutura de Testes
```
tests/
├── Unit/
│   ├── DTO/
│   │   ├── CredenciaisAutenticacaoTest.php
│   │   ├── PagamentoResponseTest.php
│   │   └── ...
│   ├── Mappers/
│   │   ├── PagarmeMapperTest.php
│   │   ├── AsaasMapperTest.php
│   │   ├── C6MapperTest.php
│   │   ├── InterMapperTest.php
│   │   └── StatusMapperTest.php
│   ├── Factory/
│   │   └── BancoFactoryTest.php
│   └── Webhooks/
│       ├── WebhookRouterTest.php
│       ├── PagarmeWebhookProcessorTest.php
│       ├── AsaasWebhookProcessorTest.php
│       ├── C6WebhookProcessorTest.php
│       └── InterWebhookProcessorTest.php
│
├── Integration/
│   ├── PagarmeIntegrationTest.php
│   ├── AsaasIntegrationTest.php
│   ├── C6IntegrationTest.php
│   └── InterIntegrationTest.php
│
└── Sandbox/ (App CI4)
    ├── DashboardControllerTest.php
    ├── TransacoesControllerTest.php
    └── WebhookControllerTest.php
```

---

## 🔐 CHECKLIST DE VALIDAÇÃO

### Antes de Merge
- [ ] Todos os testes unitários passam
- [ ] Cobertura de código >= 80%
- [ ] Testes de regressão passam (código antigo)
- [ ] Code style PSR-12
- [ ] Documentation atualizada
- [ ] Exemplo prático funciona

### Antes de Deploy
- [ ] App CI4 conecta em sandbox de cada banco
- [ ] Webhooks recebem e processam corretamente
- [ ] Responses mapeadas para português
- [ ] Errors tratados corretamente
- [ ] Logs funcionam

---

## 📚 DOCUMENTAÇÃO A CRIAR

```
docs/
├── FACTORY_PATTERN.md           # Como usar BancoFactory
├── RESPONSE_MAPPING.md          # Como funciona mapeamento
├── WEBHOOKS.md                  # Como configurar webhooks
├── CREDENTIALS.md               # Como passar credenciais
├── MIGRATION_GUIDE.md           # Migração do código antigo
└── SANDBOX_APP.md               # Como usar app de testes
```

---

## ⚡ COMANDOS PARA EXECUÇÃO

### Setup
```bash
# 1. Criar branch
git checkout -b refactor/agnose-completo

# 2. Instalar dependências
composer install

# 3. Rodar testes
composer test

# 4. Check code style
composer lint

# 5. Build docs
composer docs
```

### Durante o refactor
```bash
# Após cada Sprint
composer test
composer lint

# Cobertura
composer test:coverage

# Validar uma implementação
composer test -- tests/Unit/Mappers/PagarmeMapperTest.php
```

---

## 🎯 MÉTRICAS DE SUCESSO

| Métrica | Meta | Atual | Final |
|---------|------|-------|-------|
| Testes Passando | 100% | ~80%* | 100% |
| Compatibilidade Regressão | 100% | ✗ | ✓ |
| Cobertura Código | 80%+ | ~60% | 80%+ |
| Métodos Interface Padrão | 100% | ~60% | 100% |
| Webhooks Implementados | 4/4 | 1/4 | 4/4 |
| Documentação | Completa | 50% | 100% |
| App Sandbox | Funcional | ✗ | ✓ |

---

## 🚨 RISCOS E MITIGAÇÃO

| Risco | Impacto | Probabilidade | Mitigação |
|-------|---------|---|---|
| Quebra compatibilidade | Alto | Baixa | Manter wrapper compatível |
| Mapper incompleto | Médio | Média | Testes com responses reais |
| Webhook mal validando | Alto | Baixa | Testar com payloads reais |
| Performance degradada | Médio | Baixa | Benchmarks antes/depois |

---

## ❓ PERGUNTAS ABERTAS

1. **Versão de Release**: V2 ou V1.1?
2. **Suportar ambos construtores**: Por quanto tempo?
3. **Testes de Sandbox**: Com credenciais reais?
4. **CI/CD**: GitHub Actions ou GitLab?
5. **Deploy**: Quando fazer breaking change?

---

**🎬 Próximo passo: APROVAÇÃO e kick-off da Fase 1!**
