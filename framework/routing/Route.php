<?php

namespace Framework\Routing;

class Route
{
    protected string $method;
    protected string $path;
    protected $handler;

    public function __construct(
        string   $method,
        string   $path,
        callable $handler
    )
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function dispatch()
    {

    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @param $method
     * @param $path
     * @return bool
     */
    public function matches($method, $path): bool
    {
        return $this->method() == $method && $this->path() == $path;
    }
}