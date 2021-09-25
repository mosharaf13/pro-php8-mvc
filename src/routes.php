<?php

use Framework\Routing\Router;

return function (Router $router) {
    $router->add('GET', '/', fn() => 'hello world');
    $router->add('GET', '/old-home', fn() => $router->redirect('/'));
    $router->add('GET', '/has-server-error', fn() => throw new Exception());
    $router->add('POST', '/has-validation-error', fn() => $router->dummy());
    $router->add(
        'GET', '/services/view/{service?}',
        function () use ($router) {
            $parameters = $router->current()->parameters();
            if (empty($parameters['service'])) {
                return 'all services';
            }
            return "service is {$parameters['service']}";
        },
    );
    $router->add(
        'GET', '/products/view/{product}',
        function () use ($router) {
            $parameters = $router->current()->parameters();
            return "product is {$parameters['product']}";
        },
    );

};