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
});

// Rotas de Webhooks (sem autenticação, mas com validação de assinatura)
$routes->post('/hook/pagarme/(:any)', 'Webhooks::pagarme/$1');
$routes->post('/hook/inter/(:any)', 'Webhooks::inter/$1');
$routes->post('/hook/bling/(:any)', 'Webhooks::bling/$1');
$routes->post('/hook/api/(:any)/(:any)', 'Webhooks::api/$1/$2');
