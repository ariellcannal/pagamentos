# Guia de Integração com Bling

## Visão Geral

A integração com Bling permite sincronizar automaticamente suas cobranças com o sistema de gestão do Bling. Você pode:

- **Sincronizar cobranças** criadas nesta plataforma com contas a receber do Bling
- **Importar contas a receber** do Bling para esta plataforma
- **Manter status sincronizados** automaticamente
- **Visualizar histórico** completo de sincronizações

## Configuração Inicial

### 1. Obter Chave de API do Bling

1. Acesse https://www.bling.com.br
2. Faça login com sua conta
3. Vá para **Configurações > Integrações > API**
4. Clique em **Gerar Nova Chave**
5. Copie a chave gerada

### 2. Configurar no Painel

1. Acesse a aplicação em `http://localhost:8080`
2. Vá para **Configurações**
3. Cole a chave de API do Bling no campo **Chave de API do Bling**
4. Clique em **Salvar**

### 3. Testar Conexão

1. Vá para **Integração com Bling**
2. Clique em **Testar Conexão**
3. Você deve ver a mensagem: "✓ Conexão com Bling bem-sucedida!"

## Sincronização de Cobranças

### Sincronizar Uma Cobrança

1. Vá para **Cobranças**
2. Clique na cobrança que deseja sincronizar
3. Clique em **Sincronizar com Bling**
4. A cobrança será criada como conta a receber no Bling

### Sincronizar Todas as Cobranças

1. Vá para **Integração com Bling**
2. Clique em **Sincronizar Todas**
3. Todas as cobranças pendentes serão sincronizadas

## Importação de Contas a Receber

### Importar do Bling

1. Vá para **Integração com Bling**
2. Clique em **Importar do Bling**
3. Todas as contas a receber do Bling serão importadas como cobranças

### Dados Importados

Os seguintes dados são importados:

| Campo Bling | Campo Pagamentos |
| --- | --- |
| `numero` | `id` |
| `descricao` | `description` |
| `valor` | `amount` |
| `dataVencimento` | `due_date` |
| `contato.nome` | `customer_name` |
| `contato.email` | `customer_email` |
| `contato.documento` | `customer_document` |
| `situacao` | `status` |

## Mapeamento de Status

### Status de Cobrança → Status Bling

| Pagamentos | Bling |
| --- | --- |
| `pending` | `aberto` |
| `paid` | `recebido` |
| `overdue` | `atrasado` |
| `cancelled` | `cancelado` |

## Histórico de Sincronizações

### Visualizar Histórico

1. Vá para **Integração com Bling**
2. Clique em **Ver Histórico**
3. Você verá todas as sincronizações realizadas

### Informações do Histórico

Para cada sincronização, você pode ver:

- **Tipo:** charge_to_bling (cobrança → Bling) ou bling_to_charge (Bling → cobrança)
- **Status:** pending, success ou failed
- **Data:** Quando a sincronização foi realizada
- **Erro:** Mensagem de erro (se houver)

## Webhooks do Bling

### Configurar Webhook no Bling

1. No Bling, vá para **Configurações > Integrações > Webhooks**
2. Clique em **Adicionar Webhook**
3. Configure:
   - **URL:** `https://seu-dominio.com/hook/bling/{seu_usuario}`
   - **Eventos:** Selecione os eventos desejados
4. Clique em **Salvar**

### Eventos Suportados

- `contas_receber.criado` - Conta a receber criada
- `contas_receber.atualizado` - Conta a receber atualizada
- `contas_receber.recebido` - Conta a receber recebida
- `contas_receber.cancelado` - Conta a receber cancelada

## Tratamento de Erros

### Erro: "Chave de API inválida"

**Causa:** A chave de API do Bling está incorreta ou expirou.

**Solução:**
1. Verifique a chave no Bling
2. Gere uma nova chave se necessário
3. Atualize a chave nas configurações

### Erro: "Falha na sincronização"

**Causa:** Problema na comunicação com a API do Bling.

**Solução:**
1. Verifique sua conexão com a internet
2. Verifique se o Bling está operacional
3. Tente novamente em alguns minutos

### Erro: "Conta a receber já existe"

**Causa:** A cobrança já foi sincronizada anteriormente.

**Solução:**
1. Verifique o histórico de sincronizações
2. Se necessário, delete a cobrança e tente novamente

## Casos de Uso

### Caso 1: Sincronizar Cobranças Automaticamente

```
1. Cliente cria cobrança na plataforma
2. Sistema sincroniza automaticamente com Bling
3. Conta a receber é criada no Bling
4. Quando paga, status é atualizado em ambos os sistemas
```

### Caso 2: Importar Contas do Bling

```
1. Você tem contas a receber no Bling
2. Clica em "Importar do Bling"
3. Todas as contas são importadas como cobranças
4. Você pode gerenciar tudo em um único lugar
```

### Caso 3: Sincronização Bidirecional

```
1. Cobrança criada na plataforma → sincroniza com Bling
2. Pagamento recebido no Bling → status atualizado na plataforma
3. Cobrança cancelada na plataforma → cancelada no Bling
```

## Melhores Práticas

### 1. Testar Conexão Regularmente

Teste a conexão com o Bling regularmente para garantir que tudo está funcionando:

```
Integração com Bling → Testar Conexão
```

### 2. Revisar Histórico de Sincronizações

Revise o histórico regularmente para identificar erros:

```
Integração com Bling → Ver Histórico
```

### 3. Fazer Backup dos Dados

Faça backup regular do seu banco de dados:

```bash
mysqldump -u root -p pagamentos > backup.sql
```

### 4. Usar Chaves de API Seguras

- Gere chaves de API fortes
- Não compartilhe chaves com terceiros
- Regenere chaves periodicamente

## Troubleshooting

### Sincronização Lenta

**Causa:** Muitas cobranças para sincronizar.

**Solução:**
1. Sincronize em lotes menores
2. Sincronize em horários de baixo uso

### Dados Inconsistentes

**Causa:** Sincronização interrompida ou falha.

**Solução:**
1. Verifique o histórico de sincronizações
2. Identifique as cobranças com erro
3. Sincronize novamente

### Webhook Não Recebido

**Causa:** Webhook não configurado corretamente ou URL inacessível.

**Solução:**
1. Verifique a URL do webhook
2. Certifique-se de que o domínio é acessível
3. Verifique os logs do servidor

## API de Integração

### Sincronizar Cobrança

```php
$blingService = new BlingService($apiKey);

$receivableData = [
    'numero' => '123',
    'descricao' => 'Cobrança de Teste',
    'valor' => 100.00,
    'dataVencimento' => '2025-12-31',
    'contato' => [
        'nome' => 'João Silva',
        'email' => 'joao@example.com',
        'documento' => '12345678900',
    ],
];

$response = $blingService->createReceivable($receivableData);
```

### Importar Contas a Receber

```php
$blingService = new BlingService($apiKey);

$receivables = $blingService->listReceivables(['limit' => 100]);

foreach ($receivables['data'] as $receivable) {
    // Processar conta a receber
}
```

### Atualizar Status

```php
$blingService = new BlingService($apiKey);

$data = [
    'situacao' => 'recebido',
    'dataRecebimento' => date('Y-m-d'),
];

$response = $blingService->updateReceivable($receivableId, $data);
```

## Suporte

Para suporte, consulte:
- [README](README.md)
- [Documentação da API](API_DOCUMENTATION.md)
- [Guia de Instalação](INSTALLATION.md)

---

**Última Atualização:** Novembro de 2025

