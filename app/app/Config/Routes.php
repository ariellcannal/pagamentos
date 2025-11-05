<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Rotas públicas
$routes->get('/auth/login', 'Auth::login');
$routes->post('/auth/login', 'Auth::processLogin');
$routes->get('/auth/register', 'Auth::register');
$routes->post('/auth/register', 'Auth::processRegister');
$routes->get('/auth/logout', 'Auth::logout');

// Rotas protegidas (requerem autenticação)
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/charges', 'Charges::index');
    $routes->post('/charges/create', 'Charges::create');
    $routes->get('/settings', 'Settings::index');
    $routes->post('/settings/update', 'Settings::update');
    $routes->get('/api-keys', 'ApiKeys::index');
    $routes->post('/api-keys/create', 'ApiKeys::create');
    $routes->delete('/api-keys/delete/(:num)', 'ApiKeys::delete/$1');
    $routes->get('/email-templates', 'EmailTemplates::index');
    $routes->post('/email-templates/create', 'EmailTemplates::create');
    $routes->get('/email-templates/edit/(:num)', 'EmailTemplates::edit/$1');
    $routes->post('/email-templates/update/(:num)', 'EmailTemplates::update/$1');
    $routes->get('/email-templates/toggle/(:num)', 'EmailTemplates::toggle/$1');
    $routes->post('/email-templates/send-test/(:num)', 'EmailTemplates::sendTest/$1');
    $routes->get('/api-keys/documentation', 'ApiKeys::documentation');
    $routes->get('/bling-integration', 'BlingIntegration::index');
    $routes->post('/bling-integration/test-connection', 'BlingIntegration::testConnection');
    $routes->post('/bling-integration/sync-charge/(:num)', 'BlingIntegration::syncCharge/$1');
    $routes->get('/bling-integration/sync-all', 'BlingIntegration::syncAllCharges');
    $routes->get('/bling-integration/import-receivables', 'BlingIntegration::importReceivables');
    $routes->get('/bling-integration/sync-history', 'BlingIntegration::syncHistory');
});

// Rotas de Webhooks (sem autenticação, mas com validação de assinatura)
$routes->post('/hook/pagarme/(:any)', 'Webhooks::pagarme/$1');
$routes->post('/hook/inter/(:any)', 'Webhooks::inter/$1');
$routes->post('/hook/bling/(:any)', 'Webhooks::bling/$1');
$routes->post('/hook/api/(:any)/(:any)', 'Webhooks::api/$1/$2');
