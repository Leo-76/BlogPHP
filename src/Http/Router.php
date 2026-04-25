<?php
namespace App\Http;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Symfony\Component\HttpFoundation\Response as SfResponse;
use App\Exceptions\HttpException;

/**
 * Router basé sur symfony/routing
 */
class Router
{
    private RouteCollection $routes;
    private string $prefix = '';
    private int $routeIndex = 0;

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function get(string $path, string|callable $action, array $middleware = []): void
    {
        $this->addRoute(['GET', 'HEAD'], $path, $action, $middleware);
    }

    public function post(string $path, string|callable $action, array $middleware = []): void
    {
        $this->addRoute(['POST'], $path, $action, $middleware);
    }

    public function group(array $options, callable $callback): void
    {
        $prev         = $this->prefix;
        $this->prefix .= $options['prefix'] ?? '';
        $callback($this);
        $this->prefix = $prev;
    }

    private function addRoute(array $methods, string $path, string|callable $action, array $middleware): void
    {
        $fullPath = $this->prefix . $path;

        // Symfony routing utilise {param} comme BlogPHP — compatibilité native !
        $route = new Route($fullPath, [
            '_action'     => $action,
            '_middleware' => $middleware,
        ], [], [], '', [], $methods);

        $this->routes->add('route_' . $this->routeIndex++, $route);
    }

    public function dispatch(SfRequest $request): SfResponse
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $params = $matcher->match($request->getPathInfo());
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException) {
            throw new HttpException(404, "Page introuvable : " . $request->getPathInfo());
        } catch (\Symfony\Component\Routing\Exception\MethodNotAllowedException) {
            throw new HttpException(405, "Méthode non autorisée.");
        }

        // Extraire l'action et les paramètres de route
        $action     = $params['_action'];
        $middleware = $params['_middleware'] ?? [];

        // Paramètres de route (ex: {slug}, {id})
        $routeParams = array_filter($params, fn($k) => !str_starts_with($k, '_'), ARRAY_FILTER_USE_KEY);

        // Construire notre Request avec les params
        $appRequest         = Request::capture();
        $appRequest->params = $routeParams;

        // Exécuter les middlewares
        foreach ($middleware as $mw) {
            $result = (new $mw())->handle($appRequest);
            if ($result instanceof SfResponse) return $result;
        }

        return $this->callAction($action, $appRequest);
    }

    private function callAction(string|callable $action, Request $request): SfResponse
    {
        if (is_callable($action)) return $action($request);

        [$class, $method] = explode('@', $action);
        $fullClass = "App\\Controllers\\{$class}";
        return (new $fullClass())->$method($request);
    }

    public function getRoutes(): RouteCollection { return $this->routes; }
}
