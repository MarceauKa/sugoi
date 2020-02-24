<?php

/** @var \Core\Router\Router $router */
$router->get('/', 'HomeController@index', 'home');
$router->get('/redirect', 'HomeController@redirect');
$router->get('/{name}', 'HomeController@show', 'show');

$router->group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => 'auth',
], function ($router) {
    $router->get('/home', 'AdminController@index', 'home');
});
