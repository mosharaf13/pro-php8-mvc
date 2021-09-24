<?php

namespace Framework\Routing;

class Router
{
    public array $routes = [];

    public function add(string $method, string $path, callable $handler): Route
    {
        $route = $this->routes[] = new Route($method, $path, $handler);
        return $route;
    }

    public function redirect(string $path)
    {
        //todo implementation is coming soon

        return fn() => 'redirect';
    }

    public function dispatch()
    {
        $paths = $this->paths();
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestPath = $_SERVER['REQUEST_URI'] ?? '/';

        $matching = $this->match($requestMethod, $requestPath);
        if ($matching) {
            try {
                return $matching->dispatch();
            } catch (\Throwable $exception) {
                $this->dispatchError();
            }
        }

        if (in_array($requestPath, $paths)) {
            return $this->dispatchNotAllowed();
        }

        return $this->dispatchNotFound();
    }

    public function dispatchNotAllowed()
    {
        return fn() => 'Not allowed';
    }

    private function paths(): array
    {
        return array_map(fn(Route $route) => $route->path(), $this->routes);
    }

    private function match(mixed $requestMethod, mixed $requestPath): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($requestMethod, $requestPath)) {
                return $route;
            }
        }
        return null;
    }

    private function dispatchError()
    {
    }

    private function dispatchNotFound()
    {

    }
}