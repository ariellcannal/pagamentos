<?php

$routes->get('/', 'DashboardController::index');
$routes->post('/transacoes/criar', 'TransacoesController::criar');
$routes->post('/transacoes/reprocessar', 'TransacoesController::reprocessar');
$routes->post('/webhook/receber', 'WebhookController::receber');
