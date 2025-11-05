# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [2.0.0] - 2025-11-05

### Adicionado

#### Biblioteca PHP
- **Refatoração Arquitetural Completa**
  - Implementação do Padrão Adapter para múltiplos gateways
  - Implementação do Padrão Strategy para processamento de webhooks
  - Nova interface `WebhookProcessorInterface` para padronizar webhooks
  - Renomeação de `_EntityBase.php` para `AbstractEntity.php`

- **Novos Gateways**
  - Implementação completa do Banco Inter com SDK oficial
  - Implementação completa do C6 Bank com API HTTP (Guzzle)
  - Suporte para múltiplos gateways sem quebra de compatibilidade

- **Novo Método de Pagamento**
  - Método `boleto()` adicionado à `PagamentosInterface`
  - Implementação em todos os gateways (Pagar.me, Inter, C6)

- **Processadores de Webhook**
  - `PagarmeWebhookProcessor` - Processa webhooks do Pagar.me
  - `InterWebhookProcessor` - Processa webhooks do Banco Inter
  - `C6WebhookProcessor` - Processa webhooks do C6 Bank
  - Validação de segurança (HMAC-SHA256) para todos

- **Melhorias na Entidade Transacao**
  - Novo campo `operadoraCodigo` para rastreabilidade
  - Getters e setters para o novo campo

#### Aplicação Web CodeIgniter 4
- **Estrutura Base**
  - Inicialização do projeto CodeIgniter 4
  - Configuração de banco de dados MySQL
  - 8 migrations para tabelas principais

- **Autenticação**
  - Sistema de login/logout de usuários
  - Filtro de autenticação para rotas protegidas
  - Modelo `User` com suporte a bcrypt

- **Dashboard Administrativo**
  - Painel com estatísticas de cobranças
  - Listagem de cobranças recentes
  - Interface responsiva e intuitiva

- **Gestão de Cobranças**
  - Controller `Charges` para criar e listar cobranças
  - Integração com múltiplos gateways
  - Suporte para Pix, Boleto e Cartão de Crédito

- **Configurações**
  - Controller `Settings` para gerenciar credenciais dos bancos
  - Armazenamento seguro de chaves de API
  - Testes de conexão com cada gateway

- **Chaves API**
  - Controller `ApiKeys` para gerar chaves de API
  - Suporte para webhooks customizados
  - Documentação da API em JSON

- **Templates de E-mail**
  - Controller `EmailTemplates` para gerenciar templates
  - Suporte a variáveis personalizáveis
  - Envio de e-mails de teste
  - Suporte a 4 tipos de templates (charge_created, charge_paid, etc.)

- **Webhooks**
  - Controller `Webhooks` para receber notificações dos bancos
  - Endpoints para Pagar.me, Inter, C6 e Bling
  - Registro automático de webhooks no banco de dados
  - Validação de assinatura

- **Integração com Bling**
  - Service `BlingService` para abstração da API
  - Controller `BlingIntegration` para gerenciar sincronização
  - Sincronização bidirecional de contas a receber
  - Importação de contas do Bling
  - Histórico completo de sincronizações
  - Mapeamento automático de status
  - Model `BlingSync` para rastrear sincronizações

### Corrigido

- **Bug no método `cancelCharge` do Pagarme.php**
  - Corrigido uso de variável não definida
  - Agora usa corretamente o parâmetro `$charge_id`

- **Compatibilidade de Webhooks**
  - Análise completa de payloads dos três bancos
  - Mapeamento correto de campos entre sistemas

### Mudado

- **Estrutura de Diretórios**
  - Reorganização para melhor separação de responsabilidades
  - Novos diretórios: `Webhooks/`, `Services/`, `Mocks/`

- **Nomenclatura**
  - `_EntityBase.php` → `AbstractEntity.php`
  - Melhor alinhamento com convenções PHP

### Removido

- **Código Legado**
  - Remoção de mocks após implementação real dos SDKs
  - Limpeza de código duplicado

## [1.0.0] - 2025-10-15

### Adicionado

- **Versão Inicial**
  - Biblioteca PHP com suporte apenas ao Pagar.me
  - Interface `PagamentosInterface` com métodos básicos
  - Entidades de Domínio (Cliente, Pedido, Transacao)
  - Webhook processor básico para Pagar.me

---

## Notas de Atualização

### De 1.0.0 para 2.0.0

#### Breaking Changes
- A interface `PagamentosInterface` permanece a mesma, mas novos métodos foram adicionados
- Aplicações existentes continuarão funcionando sem alterações
- Novo método `boleto()` deve ser implementado em consumidores que desejam usar

#### Migração Recomendada
1. Atualizar a biblioteca via Composer
2. Implementar o novo método `boleto()` se necessário
3. Configurar novos gateways (Inter, C6) nas configurações
4. Testar webhooks dos novos gateways

#### Benefícios da Atualização
- Suporte para múltiplos gateways
- Melhor arquitetura e manutenibilidade
- Nova aplicação web para gerenciamento
- Integração com Bling
- Melhor tratamento de webhooks

---

## Roadmap Futuro

### Versão 2.1 (Planejado)
- [ ] Suporte para Apple Pay
- [ ] Suporte para Google Pay
- [ ] Dashboard com gráficos avançados
- [ ] Relatórios em PDF
- [ ] Agendamento de cobranças

### Versão 2.2 (Planejado)
- [ ] Suporte para Stripe
- [ ] Suporte para PayPal
- [ ] Integração com Shopify
- [ ] API REST completa
- [ ] Autenticação OAuth2

### Versão 3.0 (Planejado)
- [ ] Reescrita em arquitetura de microsserviços
- [ ] Suporte para múltiplos bancos internacionais
- [ ] Dashboard em React/Vue
- [ ] Aplicativo mobile nativo
- [ ] Suporte para criptomoedas

---

## Contribuindo

Para reportar bugs ou sugerir novas funcionalidades, abra uma issue no repositório.

## Suporte

Para suporte, consulte:
- [README](README.md)
- [Documentação da API](API_DOCUMENTATION.md)
- [Guia de Instalação](INSTALLATION.md)

---

**Última Atualização:** 5 de Novembro de 2025

