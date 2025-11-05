# Guia de Instalação

## Pré-requisitos

- PHP 8.1 ou superior
- Composer
- MySQL 5.7 ou superior
- Git

## Instalação da Biblioteca

### Via Composer (Recomendado)

```bash
composer require ariellcannal/pagamentos
```

### Via Git

```bash
git clone https://github.com/ariellcannal/pagamentos.git
cd pagamentos
composer install
```

## Instalação da Aplicação Web

### 1. Clonar o Repositório

```bash
git clone https://github.com/ariellcannal/pagamentos.git
cd pagamentos
git checkout 2.0
```

### 2. Instalar Dependências

```bash
cd app
composer install
```

### 3. Configurar Banco de Dados

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar .env com suas configurações
nano .env
```

Configure as seguintes variáveis:

```env
CI_ENVIRONMENT = development

# Banco de Dados
DB_HOST = localhost
DB_USER = root
DB_PASSWORD = sua_senha
DB_NAME = pagamentos
DB_DBDriver = MySQLi
DB_Port = 3306

# Aplicação
app.baseURL = http://localhost:8080

# E-mail
MAIL_FROM_ADDRESS = noreply@seu-dominio.com
MAIL_FROM_NAME = "Seu Empresa"
```

### 4. Criar Banco de Dados

```bash
# Via MySQL
mysql -u root -p -e "CREATE DATABASE pagamentos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ou via CodeIgniter
php spark db:create pagamentos
```

### 5. Executar Migrations

```bash
php spark migrate
```

### 6. Iniciar o Servidor

```bash
php spark serve
```

Acesse `http://localhost:8080` no seu navegador.

## Configuração dos Gateways

### Pagar.me

1. Acesse https://dashboard.pagar.me
2. Vá para **Configurações > Chaves de API**
3. Copie a chave de API
4. No painel da aplicação, vá para **Configurações** e cole a chave

### Banco Inter

1. Acesse https://developers.inter.co
2. Crie uma aplicação e obtenha:
   - Client ID
   - Client Secret
   - Certificado digital (arquivo `.pem`)
3. No painel da aplicação, vá para **Configurações** e configure os dados

### C6 Bank

1. Acesse https://developers.c6bank.com.br
2. Crie uma aplicação e obtenha a chave de API
3. No painel da aplicação, vá para **Configurações** e cole a chave

### Bling

1. Acesse https://www.bling.com.br
2. Vá para **Configurações > Integrações > API**
3. Gere uma chave de API
4. No painel da aplicação, vá para **Configurações** e cole a chave

## Configuração de Webhooks

### Pagar.me

1. No dashboard do Pagar.me, vá para **Webhooks**
2. Adicione a URL: `https://seu-dominio.com/hook/pagarme/{seu_usuario}`
3. Selecione os eventos: `order.paid`, `order.pending`, `order.canceled`

### Banco Inter

1. No portal do desenvolvedor do Inter, configure o webhook
2. URL: `https://seu-dominio.com/hook/inter/{seu_usuario}`
3. Eventos: `charge.paid`, `charge.pending`, `charge.overdue`

### C6 Bank

1. No portal do desenvolvedor do C6, configure o webhook
2. URL: `https://seu-dominio.com/hook/c6/{seu_usuario}`
3. Eventos: `payment.received`, `payment.pending`, `payment.failed`

### Bling

1. No Bling, vá para **Configurações > Integrações > Webhooks**
2. URL: `https://seu-dominio.com/hook/bling/{seu_usuario}`
3. Eventos: `contas_receber.atualizado`, `contas_receber.criado`

## Configuração de E-mail

### Gmail (Recomendado para Teste)

1. Ative a autenticação de dois fatores
2. Gere uma senha de aplicativo: https://myaccount.google.com/apppasswords
3. Configure no `.env`:

```env
MAIL_HOST = smtp.gmail.com
MAIL_PORT = 587
MAIL_USERNAME = seu_email@gmail.com
MAIL_PASSWORD = sua_senha_de_aplicativo
MAIL_FROM_ADDRESS = seu_email@gmail.com
MAIL_FROM_NAME = "Seu Empresa"
```

### Outro Provedor SMTP

Configure com os dados do seu provedor:

```env
MAIL_HOST = smtp.seu-provedor.com
MAIL_PORT = 587
MAIL_USERNAME = seu_usuario
MAIL_PASSWORD = sua_senha
MAIL_FROM_ADDRESS = noreply@seu-dominio.com
MAIL_FROM_NAME = "Seu Empresa"
```

## Teste de Conexão

### Testar Biblioteca

```php
<?php

use CANNALPagamentos\Pagarme;

$pagarme = new Pagarme(apiKey: 'sua_chave_api');

// Testar conexão
try {
    // Fazer uma chamada simples
    echo "Conexão bem-sucedida!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Testar Aplicação Web

1. Acesse `http://localhost:8080`
2. Crie uma conta
3. Vá para **Configurações** e configure os gateways
4. Clique em **Testar Conexão**

## Próximos Passos

1. Leia a [Documentação da API](API_DOCUMENTATION.md)
2. Configure os templates de e-mail em **Templates de E-mail**
3. Gere chaves API em **Chaves API** para webhooks customizados
4. Configure a integração com Bling em **Integração com Bling**

## Troubleshooting

### Erro: "SQLSTATE[HY000]: General error: 1030"
Aumente o tamanho máximo de pacotes MySQL:

```sql
SET GLOBAL max_allowed_packet=16777216;
```

### Erro: "Call to undefined function curl_init()"
Instale a extensão cURL do PHP:

```bash
# Ubuntu/Debian
sudo apt-get install php-curl

# CentOS
sudo yum install php-curl
```

### Erro: "Permission denied" ao criar arquivos
Ajuste as permissões:

```bash
chmod -R 755 app/writable
chmod -R 755 app/public/uploads
```

### Erro: "Class not found"
Execute o autoload do Composer:

```bash
composer dump-autoload
```

## Suporte

Para mais informações, consulte:
- [README](README.md)
- [Documentação da API](API_DOCUMENTATION.md)
- [Integração com Bling](BLING_INTEGRATION.md)

---

**Última Atualização:** Novembro de 2025

