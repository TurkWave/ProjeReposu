<?php
// ============================================================
// Staj Takip - Basit Router
// ============================================================
declare(strict_types=1);

namespace App;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void   { $this->add('GET', $path, $action); }
    public function post(string $path, array $action): void   { $this->add('POST', $path, $action); }

    /** Birden fazla HTTP metodu için aynı rotayı kaydet. */
    public function match(array $methods, string $path, array $action): void
    {
        foreach ($methods as $m) {
            $this->add(strtoupper($m), $path, $action);
        }
    }

            private function add(string $method, string $path, array $action): void
    {
        $this->routes[$method . ' ' . $this->normalize($path)] = [
            'method'  => $method,
            'path'    => $this->normalize($path),
            'handler' => $action,
        ];
    }

    private function normalize(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?? $path;
        return '/' . trim($path, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        // HEAD isteği GET rotasına yönlendir.
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $path = $this->normalize(parse_url($uri, PHP_URL_PATH) ?: '/');
        $params = [];
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if ($this->matches($route['path'], $path, $params)) {
                $this->execute($route['handler'], $params);
                return;
            }
        }
        http_response_code(404);
        echo '<h1>404 - Sayfa bulunamadı</h1>';
    }

    /** /company/{token} gibi dinamik bölümleri karşılaştırır. */
    private function matches(string $route, string $path, ?array &$params = []): bool
    {
        $routeRegex = preg_replace('#\{[^}]+\}#', '([^/]+)', $route);
        $routeRegex = '#^' . $routeRegex . '$#i';
        if (preg_match($routeRegex, $path, $m)) {
            preg_match_all('#\{([^}]+)\}#', $route, $names);
            $params = [];
            foreach ($names[1] as $i => $name) {
                $params[$name] = $m[$i + 1];
            }
            return true;
        }
        return false;
    }

    private function execute(array $action, array $params): void
    {
        [$class, $method] = $action;
        $controller = new $class();
        foreach ($controller->middleware() as $guard) {
            if (is_array($guard) && count($guard) === 2) {
                [$gClass, $gArg] = $guard;
                $gClass::check($gArg);
            } else {
                $guard::check();
            }
        }
        if (!is_callable([$controller, $method])) {
            http_response_code(500);
            echo '<h1>500 - Metod bulunamadı</h1>';
            return;
        }
        call_user_func_array([$controller, $method], $params);
    }
}
