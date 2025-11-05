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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
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

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .api-key-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .api-key-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .api-key-info p {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .api-key-value {
            background: white;
            padding: 8px 12px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            margin-top: 5px;
            border: 1px solid #ddd;
        }

        .api-key-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.inactive {
            background: #f8d7da;
            color: #721c24;
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
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state p {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Pagamentos</h1>
        <div>
            <a href="/dashboard">Dashboard</a>
            <a href="/charges">Cobranças</a>
            <a href="/settings">Configurações</a>
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
            <button class="btn" onclick="toggleCreateForm()">+ Gerar Nova Chave</button>
        </div>

        <!-- Formulário de Criação (Oculto por padrão) -->
        <div id="createForm" class="section" style="display: none;">
            <h2>Gerar Nova Chave API</h2>
            <form method="POST" action="/api-keys/create">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="alias">Apelido da Chave</label>
                    <input type="text" id="alias" name="alias" required placeholder="Ex: Integração com Shopify">
                    <div class="help-text">Um nome descritivo para identificar esta chave</div>
                </div>

                <div class="form-group">
                    <label for="webhook_url">URL de Webhook</label>
                    <input type="url" id="webhook_url" name="webhook_url" required placeholder="https://seu-dominio.com/webhook">
                    <div class="help-text">URL onde você deseja receber as notificações de eventos</div>
                </div>

                <button type="submit" class="btn">Gerar Chave</button>
                <button type="button" class="btn btn-secondary" onclick="toggleCreateForm()">Cancelar</button>
            </form>
        </div>

        <!-- Listagem de Chaves -->
        <div class="section">
            <h2>Suas Chaves API</h2>

            <?php if (!empty($api_keys)): ?>
                <?php foreach ($api_keys as $key): ?>
                    <div class="api-key-card">
                        <div class="api-key-info">
                            <h3><?= $key['alias'] ?></h3>
                            <p><strong>Criada em:</strong> <?= date('d/m/Y H:i', strtotime($key['created_at'])) ?></p>
                            <p><strong>Último uso:</strong> <?= $key['last_used_at'] ? date('d/m/Y H:i', strtotime($key['last_used_at'])) : 'Nunca' ?></p>
                            <p><strong>URL de Webhook:</strong> <?= $key['webhook_url'] ?></p>
                            <div class="api-key-value">
                                <strong>Chave:</strong> <?= $key['api_key'] ?>
                            </div>
                            <span class="status <?= $key['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $key['is_active'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </div>
                        <div class="api-key-actions">
                            <a href="/api-keys/view/<?= $key['id'] ?>" class="btn btn-secondary btn-small">Ver</a>
                            <a href="/api-keys/regenerate/<?= $key['id'] ?>" class="btn btn-secondary btn-small" onclick="return confirm('Deseja regenerar esta chave? A chave atual deixará de funcionar.')">Regenerar</a>
                            <a href="/api-keys/delete/<?= $key['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Deseja desativar esta chave?')">Desativar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>Você ainda não possui nenhuma chave API.</p>
                    <button class="btn" onclick="toggleCreateForm()">Gerar Primeira Chave</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Documentação -->
        <div class="section">
            <h2>Documentação da API</h2>
            <div class="alert alert-info">
                <strong>Endpoint:</strong> <code>POST /hook/api/{seu_usuario}/{sua_chave_api}</code>
            </div>
            <p>Para mais detalhes, consulte a <a href="/api-keys/documentation" target="_blank">documentação completa</a>.</p>
        </div>
    </div>

    <script>
        function toggleCreateForm() {
            const form = document.getElementById('createForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>

