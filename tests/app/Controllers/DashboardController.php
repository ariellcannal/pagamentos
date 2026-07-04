<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Support\Storage;

class DashboardController extends BaseController
{
    public function index()
    {
        return \App\Controllers\view('dashboard', [
            'bancoAtivo' => getenv('BANCO_ATIVO') ?: 'pagarme',
            'transacoes' => Storage::ultimasTransacoes(20),
            'webhooks' => Storage::ultimosWebhooks(20),
        ]);
    }
}
