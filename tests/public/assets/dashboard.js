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
