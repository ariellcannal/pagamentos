CREATE TABLE IF NOT EXISTS transacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    banco TEXT NOT NULL,
    operadora_id TEXT,
    status TEXT,
    forma TEXT,
    valor REAL,
    payload_request_json TEXT,
    payload_response_json TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS webhooks_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    banco TEXT NOT NULL,
    evento TEXT,
    payload_json TEXT NOT NULL,
    headers_json TEXT,
    status_processamento TEXT NOT NULL,
    erro_mensagem TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
