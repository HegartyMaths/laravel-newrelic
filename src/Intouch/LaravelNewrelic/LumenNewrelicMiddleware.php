<?php

namespace Intouch\LaravelNewrelic;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class LumenNewrelicMiddleware
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function handle(Request $request, Closure $next)
    {
        if (config('newrelic.auto_name_transactions')) {
            app('newrelic')->nameTransaction($this->getTransactionName($request));
        }

        return $next($request);
    }

    public function getTransactionName(Request $request)
    {
        $matchedRoute = $this->router->getRoutes()->match($request);
        $routeName = $matchedRoute->getName();
        if (str_starts_with($routeName, 'generated::')) {
            $routeName = null;
        }

        return str_replace(
            [
                '{controller}',
                '{method}',
                '{route}',
                '{path}',
                '{uri}',
            ],
            [
                $matchedRoute->getActionName(),
                $request->getMethod(),
                $routeName ?? $matchedRoute->getActionName(),
                $request->getPathInfo(),
                $request->getUri(),
            ],
            config('newrelic.name_provider')
        );
    }
}
