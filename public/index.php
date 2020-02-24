<?php

require '../vendor/autoload.php';

try {
    $app = \App\Application::run();
    $response = $app->instance(Core\Router\Router::class)->listen();

    echo $response->send();
} catch (\Exception $exception) {
    $oops = $app->instance(\Core\Error\Oops::class);

    echo $oops->capture($exception)->send();
}
