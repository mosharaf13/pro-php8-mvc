<?php

namespace Framework\Routing;

class Route
{
    protected string $method;
    protected string $path;
    protected $handler;
    protected array $parameters = [];
    protected ?string $name = null;

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
        return call_user_func($this->handler);
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
        if ($this->method() == $method && $this->path() == $path) {
            return true;
        }
        $parameterNames = [];
        $parameterValues = [];
        // the normalisePath method ensures there's a '/'
        // before and after the path, while also
        // removing duplicate '/' characters
        //
        // examples:
        // → '' becomes '/'
        // → 'home' becomes '/home/'
        // → 'product/{id}' becomes '/product/{id}/'
        $pattern = $this->normalisePath($this->path);

        // get all the parameter names and replace them with
        // regular expression syntax, to match optional or
        // required parameters
        //
        // examples:
        // → '/home/' remains '/home/'
        // → '/product/{id}/' becomes '/product/([^/]+)/'
        // → '/blog/{slug?}/' becomes '/blog/([^/]*)(?:/?)'

        $pattern = preg_replace_callback(
            '#{([^}]+)}/#',
            function (array $found) use (&$parameterNames) {
                $val = rtrim($found[1], '?');
                array_push($parameterNames, $val);

                if (str_ends_with($found[1], '?')) {
                    return '([^/]*)(?:/?)';
                }

                return '([^/]+)/';
            },
            $pattern);

        // if there are no route parameters, and it
        // wasn't a literal match, then this route
        // will never match the requested path
        if (
            !str_contains($pattern, '+')
            && !str_contains($pattern, '*')
        ) {
            return false;
        }

        preg_match_all(
            "#{$pattern}#", $this->normalisePath($path), $matches
        );

        if (count($matches[1]) > 0) {

            // if the route matches the request path then
            // we need to assemble the parameters before
            // we can return true for the match
            foreach ($matches[1] as $value) {
                array_push($parameterValues, $value);
            }

            // make an empty array so that we can still
            // call array_combine with optional parameters
            // which may not have been provided
            $emptyValues = array_fill(
                0, count($parameterNames), null
            );

            // += syntax for arrays means: take values from the
            // right-hand side and only add them to the left-hand
            // side if the same key doesn't already exist.
            //
            // you'll usually want to use array_merge to combine
            // arrays, but this is an interesting use for +=
            $parameterValues += $emptyValues;

            $this->parameters = array_combine(
                $parameterNames,
                $parameterValues,
            );
            return true;
        }

        return false;
    }

    private function normalisePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/{$path}/";
        // remove multiple '/' in a row
        $path = preg_replace('/[\/]{2,}/', '/', $path);
        return $path;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function name(string $name = null)
    {
        if ($name) {
            $this->name = $name;
            return $this;
        }
        return $this->name;
    }
}