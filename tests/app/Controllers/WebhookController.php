<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Support\Storage;
use CANNALPagamentos\CannalPagamentos;
use CANNALPagamentos\Webhooks\WebhookSecurityConfig;

class WebhookController extends BaseController
{
    public function receber()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $headers = array_change_key_case($this->request->headers(), CASE_LOWER);

        $normalizedHeaders = [];
        foreach ($headers as $name => $value) {
            $normalizedHeaders[$name] = is_array($value)
                ? implode(',', array_map('strval', $value))
                : (string) $value;
        }

        $security = new WebhookSecurityConfig(
            tokens: [
                'pagarme' => (string) getenv('WEBHOOK_TOKEN_PAGARME'),
                'c6' => (string) getenv('WEBHOOK_TOKEN_C6'),
                'inter' => (string) getenv('WEBHOOK_TOKEN_INTER'),
                'asaas' => (string) getenv('WEBHOOK_TOKEN_ASAAS'),
            ],
            signatureKeys: [
                'pagarme' => (string) getenv('WEBHOOK_SIGNATURE_KEY_PAGARME'),
                'c6' => (string) getenv('WEBHOOK_SIGNATURE_KEY_C6'),
                'inter' => (string) getenv('WEBHOOK_SIGNATURE_KEY_INTER'),
                'asaas' => (string) getenv('WEBHOOK_SIGNATURE_KEY_ASAAS'),
            ]
        );

        $cannal = new CannalPagamentos($security);
        $response = $cannal->webhook($payload, $normalizedHeaders, (string) $this->request->getBody());

        $json = [
            'ok' => true,
            'banco' => $response->getBanco(),
            'status' => $response->getStatus(),
            'statusDescricao' => $response->getStatusDescricao(),
            'idOperadora' => $response->getIdOperadora(),
        ];

        Storage::insertWebhook([
            'banco' => $response->getBanco(),
            'evento' => $normalizedHeaders['x-event'] ?? $normalizedHeaders['event'] ?? null,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'headers_json' => json_encode($normalizedHeaders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status_processamento' => 'processado',
        ]);

        return $this->response->setJSON($json);
    }
}
