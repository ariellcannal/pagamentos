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

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.paid {
            background: #d4edda;
            color: #155724;
        }

        .status.overdue {
            background: #f8d7da;
            color: #721c24;
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
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Pagamentos</h1>
        <div>
            <a href="/charges">Cobranças</a>
            <a href="/settings">Configurações</a>
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

        <?php if (session()->has('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <h1><?= $title ?></h1>

        <div class="stats">
            <div class="stat-card">
                <h3>Total de Cobranças</h3>
                <div class="number"><?= $stats['total_charges'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pendentes</h3>
                <div class="number"><?= $stats['pending_charges'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pagas</h3>
                <div class="number"><?= $stats['paid_charges'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Vencidas</h3>
                <div class="number"><?= $stats['overdue_charges'] ?></div>
            </div>
        </div>

        <div class="section">
            <h2>Cobranças Recentes</h2>
            <?php if (!empty($recent_charges)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Vencimento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_charges as $charge): ?>
                            <tr>
                                <td>#<?= $charge['id'] ?></td>
                                <td><?= $charge['customer_name'] ?></td>
                                <td>R$ <?= number_format($charge['amount'], 2, ',', '.') ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $charge['charge_type'])) ?></td>
                                <td>
                                    <span class="status <?= strtolower($charge['status']) ?>">
                                        <?= ucfirst($charge['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($charge['due_date'])) ?></td>
                                <td>
                                    <a href="/charges/view/<?= $charge['id'] ?>" class="btn btn-secondary">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhuma cobrança encontrada.</p>
            <?php endif; ?>
            <br>
            <a href="/charges" class="btn">Ver Todas as Cobranças</a>
            <a href="/charges/create" class="btn">Criar Nova Cobrança</a>
        </div>

        <?php if ($config): ?>
            <div class="section">
                <h2>Configuração Atual</h2>
                <p><strong>Banco Padrão:</strong> <?= ucfirst($config['bank_type']) ?></p>
                <a href="/settings" class="btn">Editar Configurações</a>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <strong>Atenção:</strong> Você ainda não configurou nenhum banco. <a href="/settings">Clique aqui para configurar.</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

