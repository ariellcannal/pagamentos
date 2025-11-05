<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Pagamentos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover {
            background: #764ba2;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 12px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .instructions-group {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .instructions-group .form-group {
            margin-bottom: 15px;
        }

        .instructions-group label {
            font-size: 13px;
        }

        .test-connection {
            margin-top: 10px;
        }

        .test-connection .btn {
            margin-right: 10px;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .tabs button {
            background: none;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tabs button.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Pagamentos</h1>
        <div>
            <a href="/dashboard">Dashboard</a>
            <a href="/charges">Cobranças</a>
            <a href="/api-keys">Chaves API</a>
            <a href="/auth/logout">Sair</a>
        </div>
    </div>

    <div class="container">
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-error">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1><?= $title ?></h1>
        </div>

        <form method="POST" action="/settings/update">
            <?= csrf_field() ?>

            <!-- Dados da Empresa -->
            <div class="section">
                <h2>Dados da Empresa</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="company_name">Nome da Empresa</label>
                        <input type="text" id="company_name" name="company_name" value="<?= $user['company_name'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="company_document">CNPJ/CPF</label>
                        <input type="text" id="company_document" name="company_document" value="<?= $user['company_document'] ?? '' ?>">
                    </div>
                </div>
            </div>

            <!-- Configurações de Bancos -->
            <div class="section">
                <h2>Configurações de Bancos</h2>

                <div class="form-group">
                    <label for="bank_type">Banco Padrão</label>
                    <select id="bank_type" name="bank_type">
                        <option value="">Selecione um banco</option>
                        <?php foreach ($banks as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($config['bank_type'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pagar.me -->
                <div class="form-group">
                    <label for="pagarme_api_key">Chave de API - Pagar.me</label>
                    <input type="password" id="pagarme_api_key" name="pagarme_api_key" value="<?= $config['pagarme_api_key'] ?? '' ?>">
                    <div class="help-text">Obtenha sua chave em <a href="https://dashboard.pagar.me" target="_blank">dashboard.pagar.me</a></div>
                </div>

                <!-- Banco Inter -->
                <div class="form-group">
                    <label for="inter_client_id">Client ID - Banco Inter</label>
                    <input type="text" id="inter_client_id" name="inter_client_id" value="<?= $config['inter_client_id'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="inter_client_secret">Client Secret - Banco Inter</label>
                    <input type="password" id="inter_client_secret" name="inter_client_secret" value="<?= $config['inter_client_secret'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="inter_certificate_path">Caminho do Certificado - Banco Inter</label>
                    <input type="text" id="inter_certificate_path" name="inter_certificate_path" value="<?= $config['inter_certificate_path'] ?? '' ?>">
                    <div class="help-text">Caminho para o arquivo .p12 do certificado</div>
                </div>

                <div class="form-group">
                    <label for="inter_certificate_password">Senha do Certificado - Banco Inter</label>
                    <input type="password" id="inter_certificate_password" name="inter_certificate_password" value="<?= $config['inter_certificate_password'] ?? '' ?>">
                </div>

                <!-- C6 Bank -->
                <div class="form-group">
                    <label for="c6_api_key">Chave de API - C6 Bank</label>
                    <input type="password" id="c6_api_key" name="c6_api_key" value="<?= $config['c6_api_key'] ?? '' ?>">
                </div>

                <!-- Bling -->
                <div class="form-group">
                    <label for="bling_api_key">Chave de API - Bling</label>
                    <input type="password" id="bling_api_key" name="bling_api_key" value="<?= $config['bling_api_key'] ?? '' ?>">
                    <div class="help-text">Obtenha sua chave em <a href="https://bling.com.br" target="_blank">bling.com.br</a></div>
                </div>

                <div class="form-group">
                    <label for="bling_webhook_url">URL de Webhook - Bling</label>
                    <input type="url" id="bling_webhook_url" name="bling_webhook_url" value="<?= $config['bling_webhook_url'] ?? '' ?>">
                    <div class="help-text">URL para receber notificações do Bling</div>
                </div>
            </div>

            <!-- Instruções de Boleto -->
            <div class="section">
                <h2>Instruções de Boleto</h2>
                <p class="help-text">Configure até 4 instruções que aparecerão nos boletos emitidos:</p>

                <div class="instructions-group">
                    <?php 
                    $instructions = [];
                    if ($config && $config['boleto_instructions']) {
                        $instructions = json_decode($config['boleto_instructions'], true) ?? [];
                    }
                    for ($i = 1; $i <= 4; $i++): 
                    ?>
                        <div class="form-group">
                            <label for="boleto_instruction_<?= $i ?>">Instrução <?= $i ?></label>
                            <input type="text" id="boleto_instruction_<?= $i ?>" name="boleto_instruction_<?= $i ?>" 
                                   value="<?= $instructions[$i - 1] ?? '' ?>" 
                                   placeholder="Ex: Não receber após o vencimento">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Botões -->
            <div class="section">
                <button type="submit" class="btn">Salvar Configurações</button>
                <a href="/dashboard" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>

