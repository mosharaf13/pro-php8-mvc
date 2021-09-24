<?php

return function (\Framework\Routing\Router $router) {
    $router->add('GET', '/', fn() => 'hello world');
    $router->add('GET', '/old-home', fn() => $router->redirect('/'));
    $router->add('GET', '/has-server-error', fn() => throw new Exception());
    $router->add('POST', '/has-validation-error', fn() => $router->dummy());
};