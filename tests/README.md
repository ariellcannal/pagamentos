# Sandbox App

Este diretorio contem uma aplicacao de sandbox funcional para validar transacoes e webhooks usando a biblioteca.

## Objetivo

- Testar transacoes por banco (Pagarme, C6, Inter, Asaas)
- Testar processamento de webhook unificado
- Persistir requests e responses em SQLite
- Rodar de forma simples via front-controller em `tests/public/index.php`

## Estrutura

- app/Controllers/DashboardController.php
- app/Controllers/TransacoesController.php
- app/Controllers/WebhookController.php
- app/Support/Bootstrap.php
- app/Support/Storage.php
- app/Views/dashboard.php
- public/index.php
- database/schema.sql
- .env.example

## Como iniciar

1. Copie `tests/.env.example` para `tests/.env`.
2. Preencha as credenciais sandbox necessarias.
3. Inicie o servidor web na pasta de testes:
   - `php -S localhost:8080 -t tests/public`
4. Acesse `http://localhost:8080`.

## Restricao de ambiente

- Esta app so funciona com `APP_ENV=development` (ou `ENVIRONMENT=development`).
- Em qualquer outro ambiente ela retorna `403`.
