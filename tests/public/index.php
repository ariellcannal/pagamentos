<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Controllers\TransacoesController;
use App\Controllers\WebhookController;
use App\Support\Bootstrap;
use App\Support\Storage;

$testsRoot = dirname(__DIR__);
$projectRoot = dirname($testsRoot);

require_once $projectRoot . '/vendor/autoload.php';
require_once $testsRoot . '/app/Support/Bootstrap.php';
require_once $testsRoot . '/app/Support/Storage.php';
require_once $testsRoot . '/app/Controllers/BaseController.php';
require_once $testsRoot . '/app/Controllers/DashboardController.php';
require_once $testsRoot . '/app/Controllers/TransacoesController.php';
require_once $testsRoot . '/app/Controllers/WebhookController.php';

Bootstrap::loadEnv($testsRoot);
Bootstrap::assertDevelopmentOnly();
Storage::init($testsRoot);

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

try {
    if ($method === 'GET' && $path === '/') {
        $controller = new DashboardController();
        echo $controller->index();
        exit;
    }

    if ($method === 'POST' && $path === '/transacoes/criar') {
        header('Content-Type: application/json; charset=utf-8');
        $controller = new TransacoesController();
        echo $controller->criar();
        exit;
    }

    if ($method === 'POST' && $path === '/transacoes/reprocessar') {
        header('Content-Type: application/json; charset=utf-8');
        $controller = new TransacoesController();
        echo $controller->reprocessar();
        exit;
    }

    if ($method === 'POST' && $path === '/webhook/receber') {
        header('Content-Type: application/json; charset=utf-8');
        $controller = new WebhookController();
        echo $controller->receber();
        exit;
    }

    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'erro' => 'Rota nao encontrada'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'erro' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
