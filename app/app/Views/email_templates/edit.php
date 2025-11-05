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

        .row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .row {
                grid-template-columns: 1fr;
            }
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

        .form-group textarea {
            min-height: 400px;
            resize: vertical;
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

        .variables-list {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            font-size: 12px;
        }

        .variables-list h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .variable-item {
            background: white;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 3px;
            border-left: 3px solid #667eea;
        }

        .variable-item code {
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .checkbox-group label {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Pagamentos</h1>
        <div>
            <a href="/dashboard">Dashboard</a>
            <a href="/email-templates">Templates</a>
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

        <div class="row">
            <div>
                <form method="POST" action="/email-templates/update/<?= $template['id'] ?>">
                    <?= csrf_field() ?>

                    <div class="section">
                        <h2>Editar Template</h2>

                        <div class="form-group">
                            <label for="subject">Assunto do E-mail</label>
                            <input type="text" id="subject" name="subject" required value="<?= $template['subject'] ?>">
                        </div>

                        <div class="form-group">
                            <label for="html_content">Conteúdo HTML</label>
                            <textarea id="html_content" name="html_content" required><?= $template['html_content'] ?></textarea>
                        </div>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?= $template['is_enabled'] ? 'checked' : '' ?>>
                            <label for="is_enabled">Ativar este template</label>
                        </div>

                        <button type="submit" class="btn">Salvar Alterações</button>
                        <a href="/email-templates" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>

                <div class="section">
                    <h2>Enviar E-mail de Teste</h2>
                    <form method="POST" action="/email-templates/send-test/<?= $template['id'] ?>">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="test_email">E-mail de Teste</label>
                            <input type="email" id="test_email" name="test_email" required placeholder="seu@email.com">
                        </div>

                        <button type="submit" class="btn">Enviar E-mail de Teste</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="section">
                    <h2>Variáveis Disponíveis</h2>
                    <div class="variables-list">
                        <?php foreach ($variables as $variable => $description): ?>
                            <div class="variable-item">
                                <code><?= $variable ?></code><br>
                                <small><?= $description ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="section">
                    <h2>Dicas</h2>
                    <ul style="font-size: 12px; line-height: 1.8;">
                        <li>Use as variáveis listadas ao lado para personalizar o template</li>
                        <li>Você pode usar HTML completo no conteúdo</li>
                        <li>Sempre teste o template antes de ativar</li>
                        <li>Use estilos inline para melhor compatibilidade com clientes de e-mail</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

