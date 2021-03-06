<?php


namespace Framework\Routing;

class Router
{
    public array $routes = [];
    protected array $errorHandlers = [];
    protected Route $current;

    /**
     * @return Route
     */
    public function current(): Route
    {
        return $this->current;
    }

    public function add(string $method, string $path, callable $handler): Route
    {
        $route = $this->routes[] = new Route($method, $path, $handler);
        return $route;
    }

    public function redirect(string $path)
    {
        header(
            "Location: {$path}", $replace = true, $code = 301
        );
        exit;
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
                return $this->dispatchError();
            }
        }

        if (in_array($requestPath, $paths)) {
            return $this->dispatchNotAllowed($requestMethod);
        }

        return $this->dispatchNotFound();
    }

    public function dispatchNotAllowed($requestMethod)
    {
        $this->errorHandlers[400] ??= fn() => "$requestMethod is Not allowed for this route";

        return $this->errorHandlers[400]();
    }

    public function dummy()
    {
        return 'dummy';
    }

    private function paths(): array
    {
        return array_map(fn(Route $route) => $route->path(), $this->routes);
    }

    private function match(mixed $requestMethod, mixed $requestPath): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($requestMethod, $requestPath)) {
                $this->current = $route;
                return $route;
            }
        }
        return null;
    }

    public function errorHandler(int $code, callable $handler)
    {
        $this->errorHandlers[$code] = $handler;
    }

    private function dispatchError()
    {
        $this->errorHandlers[500] ??= fn() => 'Server error';
        return $this->errorHandlers[500]();
    }

    private function dispatchNotFound()
    {
        $this->errorHandlers[404] ??= fn() => 'Not found';
        return $this->errorHandlers[404]();
    }

    public function route(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                $finds = [];
                $replaces = [];
                foreach ($parameters as $key => $value) {
                    // one set for required parameters
                    array_push($finds, "{{$key}}");
                    array_push($replaces, $value);
                    // ...and another for optional parameters
                    array_push($finds, "{{$key}?}");
                    array_push($replaces, $value);
                }
                $path = $route->path();
                $path = str_replace($finds, $replaces, $path);
                // remove any optional parameters not provided
                $path = preg_replace('#{[^}]+}#', '', $path);
                // we should think about warning if a required
                // parameter hasn't been provided...

                return $path;
            }
        }
        throw new Exception('no route with that name');
    }


}