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
            max-width: 1200px;
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
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
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
            margin-right: 10px;
            margin-bottom: 10px;
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

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
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

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .sync-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sync-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .sync-info p {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .config-status {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .config-status.configured {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .config-status.not-configured {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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

        <?php if (session()->has('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1><?= $title ?></h1>
        </div>

        <!-- Status de Configuração -->
        <div class="config-status <?= $is_configured ? 'configured' : 'not-configured' ?>">
            <?php if ($is_configured): ?>
                <strong>✓ Bling Configurado</strong> - Sua integração com Bling está ativa e pronta para uso.
            <?php else: ?>
                <strong>⚠ Bling Não Configurado</strong> - Acesse as <a href="/settings" style="color: inherit; text-decoration: underline;">Configurações</a> para adicionar sua chave de API do Bling.
            <?php endif; ?>
        </div>

        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total de Sincronizações</h3>
                <div class="value"><?= $stats['total_syncs'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Sincronizações Bem-sucedidas</h3>
                <div class="value"><?= $stats['successful_syncs'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Sincronizações Pendentes</h3>
                <div class="value"><?= $stats['pending_syncs'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Sincronizações com Erro</h3>
                <div class="value"><?= $stats['failed_syncs'] ?></div>
            </div>
        </div>

        <!-- Ações -->
        <?php if ($is_configured): ?>
            <div class="section">
                <h2>Ações</h2>
                <div class="action-buttons">
                    <button class="btn btn-success" onclick="testConnection()">Testar Conexão</button>
                    <a href="/bling-integration/sync-all" class="btn btn-success" onclick="return confirm('Sincronizar todas as cobranças pendentes com Bling?')">Sincronizar Todas</a>
                    <a href="/bling-integration/import-receivables" class="btn btn-secondary" onclick="return confirm('Importar contas a receber do Bling?')">Importar do Bling</a>
                    <a href="/bling-integration/sync-history" class="btn btn-secondary">Ver Histórico</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sincronizações Recentes -->
        <div class="section">
            <h2>Sincronizações Recentes</h2>

            <?php if (!empty($recent_syncs)): ?>
                <?php foreach ($recent_syncs as $sync): ?>
                    <div class="sync-item">
                        <div class="sync-info">
                            <h3><?= ucfirst(str_replace('_', ' ', $sync['sync_type'])) ?></h3>
                            <p><strong>Cobrança ID:</strong> <?= $sync['charge_id'] ?></p>
                            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($sync['created_at'])) ?></p>
                            <?php if ($sync['error_message']): ?>
                                <p><strong>Erro:</strong> <?= $sync['error_message'] ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge <?= $sync['sync_status'] ?>">
                            <?= ucfirst($sync['sync_status']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhuma sincronização realizada ainda.</p>
                    <?php if ($is_configured): ?>
                        <a href="/bling-integration/sync-all" class="btn btn-success">Sincronizar Agora</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Informações -->
        <div class="section">
            <h2>Sobre a Integração</h2>
            <p>
                A integração com Bling permite sincronizar automaticamente suas cobranças com o sistema de gestão do Bling.
                Você pode:
            </p>
            <ul style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">
                <li>Sincronizar cobranças criadas nesta plataforma com contas a receber do Bling</li>
                <li>Importar contas a receber do Bling para esta plataforma</li>
                <li>Manter os status de cobranças sincronizados automaticamente</li>
                <li>Visualizar o histórico completo de sincronizações</li>
            </ul>
        </div>
    </div>

    <script>
        function testConnection() {
            fetch('/bling-integration/test-connection', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Conexão com Bling bem-sucedida!');
                } else {
                    alert('✗ Erro na conexão: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                alert('✗ Erro ao testar conexão: ' + error);
            });
        }
    </script>
</body>
</html>

