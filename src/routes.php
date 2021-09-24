<?php

return function (\Framework\Routing\Router $router) {
    $router->add('GET', '/', fn() => 'hello world');
    $router->add('GET', 'old-home', $router->redirect('/'));
    $router->add('GET', 'has-server-error', throw new Exception());
    $router->add('GET', 'has-validation-error', $router->dispatchNotAllowed());
};