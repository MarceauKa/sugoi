<?php

// Autoloader
require '../vendor/autoload.php';

try {

    // Lance l'application
    $app = \Core\Foundation\App::run();

    // Récupère une réponse depuis le Router
    $response = $app->instance(Core\Router\Router::class)->listen();

    // Envoi la réponse de la route au navigateur
    echo $response->send();

} catch (\Exception $exception) {

    // Crée une instance de Oops en cas d'erreur
    $oops = $app->instance(\Core\Error\Oops::class);

    // Envoi une réponse d'erreur au navigateur
    echo $oops->capture($exception)->send();

}
