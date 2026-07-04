<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sandbox Pagamentos</title>
  <style>
    body { font-family: Segoe UI, sans-serif; margin: 24px; background: #f7f9fb; color: #1e293b; }
    .card { background: #fff; border: 1px solid #d9e2ec; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
    label { display: block; font-size: 12px; margin-bottom: 4px; color: #475569; }
    input, select, button, textarea { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
    button { cursor: pointer; background: #0f766e; color: #fff; border: none; }
    button:hover { background: #115e59; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th, td { border-bottom: 1px solid #e2e8f0; text-align: left; padding: 8px; }
    pre { background: #0f172a; color: #e2e8f0; padding: 12px; border-radius: 8px; overflow: auto; }
  </style>
</head>
<body>
  <h1>Sandbox de Pagamentos</h1>
  <p>Banco ativo no ambiente: <strong><?= htmlspecialchars((string) ($bancoAtivo ?? 'pagarme'), ENT_QUOTES, 'UTF-8') ?></strong></p>

  <div class="card">
    <h2>Criar transacao</h2>
    <form id="form-criar" class="grid">
      <div><label>Banco</label><select name="banco"><option>pagarme</option><option>c6</option><option>inter</option><option>asaas</option></select></div>
      <div><label>Tipo</label><select name="tipo"><option value="pix">PIX</option><option value="boleto">Boleto</option><option value="credit_card">Cartao</option></select></div>
      <div><label>Valor</label><input name="valor" value="10.00"></div>
      <div><label>Nome</label><input name="nome" value="Cliente Sandbox"></div>
      <div><label>CPF</label><input name="cpf" value="00000000000"></div>
      <div><label>Email</label><input name="email" value="sandbox@local.test"></div>
      <div><label>Numero cartao</label><input name="cartao_numero" value="4111111111111111"></div>
      <div><label>Mes</label><input name="cartao_mes" value="12"></div>
      <div><label>Ano</label><input name="cartao_ano" value="2030"></div>
      <div><label>CVV</label><input name="cartao_cvv" value="123"></div>
      <div style="align-self:end;"><button type="submit">Enviar transacao</button></div>
    </form>
  </div>

  <div class="card">
    <h2>Webhook manual</h2>
    <form id="form-webhook">
      <label>Payload JSON</label>
      <textarea name="payload" rows="5">{"id":"evt_1","status":"paid"}</textarea>
      <button type="submit">Enviar webhook</button>
    </form>
  </div>

  <div class="card">
    <h2>Resposta</h2>
    <pre id="saida">Aguardando requisicao...</pre>
  </div>

  <div class="card">
    <h2>Ultimas transacoes</h2>
    <table>
      <thead><tr><th>ID</th><th>Banco</th><th>Status</th><th>Forma</th><th>Valor</th><th>Criado em</th></tr></thead>
      <tbody>
      <?php foreach (($transacoes ?? []) as $row): ?>
        <tr>
          <td><?= (int) ($row['id'] ?? 0) ?></td>
          <td><?= htmlspecialchars((string) ($row['banco'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['forma'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['valor'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Ultimos webhooks</h2>
    <table>
      <thead><tr><th>ID</th><th>Banco</th><th>Evento</th><th>Status</th><th>Criado em</th></tr></thead>
      <tbody>
      <?php foreach (($webhooks ?? []) as $row): ?>
        <tr>
          <td><?= (int) ($row['id'] ?? 0) ?></td>
          <td><?= htmlspecialchars((string) ($row['banco'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['evento'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['status_processamento'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    const saida = document.getElementById('saida');

    document.getElementById('form-criar').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = new FormData(e.target);
      const payload = Object.fromEntries(form.entries());
      const res = await fetch('/transacoes/criar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      saida.textContent = JSON.stringify(json, null, 2);
    });

    document.getElementById('form-webhook').addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = JSON.parse(new FormData(e.target).get('payload'));
      const res = await fetch('/webhook/receber', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      saida.textContent = JSON.stringify(json, null, 2);
    });
  </script>
</body>
</html>
