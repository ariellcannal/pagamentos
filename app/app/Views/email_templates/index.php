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

        .template-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .template-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .template-info p {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .template-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .template-type-card {
            background: white;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .template-type-card:hover {
            border-color: #667eea;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
        }

        .template-type-card h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .template-type-card p {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }

        .template-type-card .btn {
            width: 100%;
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
        </div>

        <!-- Listagem de Templates -->
        <div class="section">
            <h2>Seus Templates</h2>

            <?php if (!empty($templates)): ?>
                <?php foreach ($templates as $template): ?>
                    <div class="template-card">
                        <div class="template-info">
                            <h3><?= $template_types[$template['template_type']] ?? $template['template_type'] ?></h3>
                            <p><strong>Assunto:</strong> <?= substr($template['subject'], 0, 50) ?>...</p>
                            <p><strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($template['created_at'])) ?></p>
                            <span class="status <?= $template['is_enabled'] ? 'active' : 'inactive' ?>">
                                <?= $template['is_enabled'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>
                        <div class="template-actions">
                            <a href="/email-templates/edit/<?= $template['id'] ?>" class="btn btn-secondary btn-small">Editar</a>
                            <a href="/email-templates/toggle/<?= $template['id'] ?>" class="btn btn-secondary btn-small">
                                <?= $template['is_enabled'] ? 'Desativar' : 'Ativar' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Você ainda não possui nenhum template de e-mail. Crie um para começar a enviar e-mails personalizados.
                </div>
            <?php endif; ?>
        </div>

        <!-- Criar Novo Template -->
        <div class="section">
            <h2>Criar Novo Template</h2>
            <div class="template-grid">
                <?php foreach ($template_types as $type => $label): ?>
                    <div class="template-type-card">
                        <h3><?= $label ?></h3>
                        <p>Template para <?= strtolower($label) ?></p>
                        <button class="btn" onclick="openCreateForm('<?= $type ?>', '<?= $label ?>')">Criar</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Formulário de Criação (Oculto por padrão) -->
        <div id="createForm" class="section" style="display: none;">
            <h2>Criar Novo Template: <span id="templateTypeName"></span></h2>
            <form method="POST" action="/email-templates/create">
                <?= csrf_field() ?>
                <input type="hidden" id="template_type" name="template_type">

                <div style="margin-bottom: 20px;">
                    <label for="subject">Assunto do E-mail</label>
                    <input type="text" id="subject" name="subject" required placeholder="Ex: Sua cobrança foi criada" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="html_content">Conteúdo HTML</label>
                    <textarea id="html_content" name="html_content" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 300px;"></textarea>
                    <p style="font-size: 12px; color: #666; margin-top: 10px;">
                        <strong>Variáveis disponíveis:</strong> {customer_name}, {customer_email}, {charge_id}, {charge_amount}, {charge_due_date}, {charge_description}, {pix_qr_code}, {boleto_barcode}, {payment_link}, {company_name}, {current_date}
                    </p>
                </div>

                <button type="submit" class="btn">Criar Template</button>
                <button type="button" class="btn btn-secondary" onclick="closeCreateForm()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function openCreateForm(type, label) {
            document.getElementById('template_type').value = type;
            document.getElementById('templateTypeName').textContent = label;
            document.getElementById('createForm').style.display = 'block';
            document.getElementById('html_content').focus();
        }

        function closeCreateForm() {
            document.getElementById('createForm').style.display = 'none';
        }
    </script>
</body>
</html>

