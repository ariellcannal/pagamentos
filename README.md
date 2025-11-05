# Pagamentos - Gateway de Pagamentos Completo

Uma solução completa e escalável para gerenciar cobranças, pagamentos e integrações com múltiplos gateways de pagamento (Pagar.me, Banco Inter, C6 Bank) e sincronização com Bling ERP.

## 📋 Características

### Biblioteca PHP (`src/`)
- **Interface Padronizada** (`PagamentosInterface`) para múltiplos gateways
- **Adapters para Gateways:**
  - Pagar.me (implementação completa)
  - Banco Inter (SDK oficial)
  - C6 Bank (API HTTP)
- **Métodos Suportados:**
  - `creditCard()` - Pagamento com cartão de crédito
  - `boleto()` - Emissão de boletos
  - `pix()` - Pagamento via Pix
  - `refund()` - Estornos
  - `saveCard()` - Tokenização de cartões
  - `getCards()` - Recuperar cartões salvos
  - `getCharge()` - Obter detalhes da cobrança
  - `cancelCharge()` - Cancelar cobrança
- **Webhooks Padronizados** (Strategy Pattern)
  - Processadores para cada gateway
  - Validação de segurança (HMAC)
  - Tradução de payloads

### Aplicação Web CodeIgniter 4 (`app/`)
- **Autenticação** - Login/Logout de clientes
- **Dashboard** - Painel administrativo com estatísticas
- **Gestão de Cobranças** - Criar, listar, visualizar cobranças
- **Configurações** - Gerenciar credenciais dos bancos
- **Chaves API** - Gerar e gerenciar chaves para webhooks customizados
- **Templates de E-mail** - Criar e gerenciar templates personalizáveis
- **Integração com Bling** - Sincronização bidirecional de contas a receber
- **Webhooks** - Endpoints para receber notificações dos bancos

## 🚀 Início Rápido

### Instalação da Biblioteca

```bash
composer require ariellcannal/pagamentos
```

### Uso Básico

```php
<?php

use CANNALPagamentos\Pagarme;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;

// Inicializar o gateway
$pagarme = new Pagarme(
    apiKey: 'sua_chave_api',
    logger: $logger // PSR-3 Logger (opcional)
);

// Criar cliente
$cliente = new Cliente();
$cliente->setNome('João Silva');
$cliente->setEmail('joao@example.com');
$cliente->setCPF('12345678900');

// Criar pedido
$pedido = new Pedido();
$pedido->setValor(100.00);
$pedido->setDescricao('Produto de Teste');
$pedido->setDataVencimento('2025-12-31');

// Criar cobrança via Pix
$transacao = $pagarme->pix($cliente, $pedido);

echo "Pix QR Code: " . $transacao->getOperadoraResposta()['qr_code'];
```

## 📚 Documentação

- [Instalação Completa](INSTALLATION.md)
- [Documentação da API](API_DOCUMENTATION.md)
- [Integração com Bling](BLING_INTEGRATION.md)
- [Guia de Webhooks](WEBHOOKS.md)

## 🏗️ Arquitetura

### Padrões de Design

#### 1. **Adapter Pattern** (Gateways)
Cada gateway (Pagarme, Inter, C6) implementa a mesma interface, permitindo trocar entre eles sem alterar o código da aplicação consumidora.

```php
interface PagamentosInterface {
    public function creditCard(Cliente &$cli, Pedido $pedido): Transacao;
    public function boleto(Cliente &$cli, Pedido $pedido): Transacao;
    public function pix(Cliente &$cli, Pedido $pedido): Transacao;
    // ... outros métodos
}
```

#### 2. **Strategy Pattern** (Webhooks)
Cada gateway possui um processador de webhook que traduz o payload específico para a entidade `Transacao` padronizada.

```php
interface WebhookProcessorInterface {
    public function process(array $payload): Transacao;
    public function validate(array $payload): bool;
}
```

#### 3. **Factory Pattern** (Entidades)
As entidades possuem métodos estáticos para criar instâncias a partir de dados de APIs.

```php
$transacao = Transacao::fromPagarmeResponse($response);
$transacao = Transacao::fromInterResponse($response);
```

### Estrutura de Diretórios

```
pagamentos/
├── src/                           # Biblioteca PHP
│   ├── PagamentosInterface.php    # Interface principal
│   ├── Interfaces/                # Implementações de gateways
│   │   ├── Pagarme.php
│   │   ├── Inter.php
│   │   └── C6.php
│   ├── Webhooks/                  # Processadores de webhooks
│   │   ├── PagarmeWebhookProcessor.php
│   │   ├── InterWebhookProcessor.php
│   │   └── C6WebhookProcessor.php
│   ├── Entities/                  # Entidades de domínio
│   │   ├── Cliente.php
│   │   ├── Pedido.php
│   │   ├── Transacao.php
│   │   └── AbstractEntity.php
│   └── Mocks/                     # Mocks para SDKs (desenvolvimento)
│
├── app/                           # Aplicação CodeIgniter 4
│   ├── app/
│   │   ├── Controllers/           # Controllers
│   │   ├── Models/                # Models
│   │   ├── Views/                 # Views
│   │   ├── Services/              # Serviços (ex: BlingService)
│   │   ├── Database/
│   │   │   └── Migrations/        # Migrations do banco
│   │   └── Config/                # Configurações
│   └── public/                    # Arquivos públicos
│
└── composer.json                  # Dependências
```

## 🔧 Configuração

### Variáveis de Ambiente

Copie o arquivo `.env.example` para `.env` e configure:

```bash
cp app/.env.example app/.env
```

Edite o arquivo `.env` com suas credenciais:

```env
# Banco de Dados
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=pagamentos

# Pagar.me
PAGARME_API_KEY=seu_api_key

# Banco Inter
INTER_CLIENT_ID=seu_client_id
INTER_CLIENT_SECRET=seu_client_secret
INTER_CERTIFICATE_PATH=/caminho/para/certificado.pem
INTER_CERTIFICATE_PASSWORD=sua_senha

# C6 Bank
C6_API_KEY=seu_api_key
C6_API_SECRET=seu_api_secret

# Bling
BLING_API_KEY=seu_api_key

# E-mail
MAIL_FROM_ADDRESS=noreply@seu-dominio.com
MAIL_FROM_NAME="Seu Empresa"
MAIL_HOST=smtp.seu-provedor.com
MAIL_PORT=587
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
```

## 📦 Dependências

### Biblioteca
- `pagarme/pagarme-php-sdk` (~6.8) - SDK do Pagar.me
- `inter/sdk` - SDK do Banco Inter
- `guzzlehttp/guzzle` - Cliente HTTP para C6 Bank
- `psr/log` - Interface de logging

### Aplicação Web
- `codeigniter/framework` (4.x) - Framework web
- Todas as dependências da biblioteca acima
- `phpmailer/phpmailer` - Envio de e-mails

## 🧪 Testes

### Executar Testes da Biblioteca

```bash
cd /
composer test
```

### Executar Aplicação Web Localmente

```bash
cd app
php spark serve
```

Acesse `http://localhost:8080`

## 🔐 Segurança

### Validação de Webhooks
Todos os webhooks são validados usando HMAC-SHA256 para garantir que vieram do banco correto.

### Armazenamento de Credenciais
- Chaves de API são armazenadas criptografadas no banco de dados
- Senhas são hasheadas usando bcrypt
- Certificados digitais são armazenados de forma segura

### Proteção de Rotas
- Rotas administrativas requerem autenticação
- Webhooks são validados por assinatura
- CSRF protection em todos os formulários

## 📊 Banco de Dados

### Tabelas Principais

| Tabela | Descrição |
| --- | --- |
| `users` | Clientes da plataforma |
| `user_configurations` | Configurações e credenciais de cada cliente |
| `charges` | Cobranças criadas |
| `webhook_logs` | Histórico de webhooks recebidos |
| `email_templates` | Templates de e-mail personalizáveis |
| `api_keys` | Chaves API geradas pelos clientes |
| `bling_sync` | Histórico de sincronizações com Bling |

## 🔄 Fluxo de Cobrança

```
1. Cliente cria cobrança
   ↓
2. Sistema envia para gateway (Pagar.me, Inter ou C6)
   ↓
3. Gateway retorna dados (QR Code, código de barras, etc.)
   ↓
4. Sistema armazena cobrança no banco
   ↓
5. Cliente recebe e-mail com instruções de pagamento
   ↓
6. Pagador realiza o pagamento
   ↓
7. Gateway envia webhook para plataforma
   ↓
8. Plataforma processa webhook e atualiza status
   ↓
9. Sistema sincroniza com Bling (se configurado)
   ↓
10. Notificações são enviadas ao cliente
```

## 🐛 Troubleshooting

### Erro: "Chave de API inválida"
Verifique se a chave está correta no arquivo `.env` e se o gateway está configurado.

### Erro: "Webhook não validado"
Certifique-se de que a assinatura do webhook é válida e que o endpoint está correto.

### Erro: "Falha na sincronização com Bling"
Verifique se a chave de API do Bling está correta e se a conta tem permissões necessárias.

## 📝 Licença

Este projeto está licenciado sob a MIT License - veja o arquivo LICENSE para detalhes.

## 👨‍💻 Autor

Desenvolvido por **Ariell Cannal**

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, abra uma issue ou pull request.

## 📞 Suporte

Para suporte, abra uma issue no repositório ou entre em contato através do e-mail.

---

**Versão:** 2.0  
**Última Atualização:** Novembro de 2025

