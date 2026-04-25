<?php
use App\Middleware\AuthMiddleware;
use App\Middleware\ApiAuthMiddleware;

/** @var \App\Http\Router $router */

// ── Blog public ──────────────────────────────────────────────
$router->get('/',                   'BlogController@index');
$router->get('/article/{slug}',     'BlogController@show');
$router->post('/article/{slug}',    'BlogController@show');
$router->get('/categorie/{slug}',   'BlogController@categorie');
$router->get('/recherche',          'BlogController@recherche');

// ── Auth ─────────────────────────────────────────────────────
$router->get('/login',              'AuthController@loginForm');
$router->post('/login',             'AuthController@login');
$router->get('/logout',             'AuthController@logout');
$router->get('/register',           'AuthController@registerForm');
$router->post('/register',          'AuthController@register');
$router->get('/verify/{token}',     'AuthController@verify');
$router->get('/forgot-password',    'AuthController@forgotForm');
$router->post('/forgot-password',   'AuthController@forgot');
$router->get('/reset/{token}',      'AuthController@resetForm');
$router->post('/reset/{token}',     'AuthController@reset');

// ── Admin (protégé) ──────────────────────────────────────────
$router->group(['prefix' => '/admin'], function($router) {
    $mw = [AuthMiddleware::class];
    $router->get('',                      'AdminController@dashboard',  $mw);
    $router->get('/articles',             'AdminController@articles',   $mw);
    $router->get('/article/new',          'AdminController@newForm',    $mw);
    $router->post('/article/new',         'AdminController@create',     $mw);
    $router->get('/article/{id}',         'AdminController@editForm',   $mw);
    $router->post('/article/{id}',        'AdminController@update',     $mw);
    $router->post('/article/{id}/delete', 'AdminController@delete',     $mw);
    $router->get('/profil',               'AdminController@profil',     $mw);
    $router->post('/profil',              'AdminController@updateProfil',$mw);
});

// ── API REST ─────────────────────────────────────────────────
$router->group(['prefix' => '/api'], function($router) {
    $mw = [ApiAuthMiddleware::class];

    // Public
    $router->get('/articles',           'Api\ArticleApiController@index');
    $router->get('/articles/{slug}',    'Api\ArticleApiController@show');
    $router->get('/categories',         'Api\ArticleApiController@categories');

    // Protégé par token
    $router->post('/articles',          'Api\ArticleApiController@store',   $mw);
    $router->post('/articles/{slug}',   'Api\ArticleApiController@update',  $mw);
    $router->post('/articles/{slug}/delete', 'Api\ArticleApiController@destroy', $mw);
});
