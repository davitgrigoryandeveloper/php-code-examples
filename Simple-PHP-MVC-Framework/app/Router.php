<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Router
{
    public function __invoke(RouteCollection $routes): void
    {
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());

        // Routing can match routes with incoming requests
        $matcher = new UrlMatcher($routes, $context);
        try {
            $matcher = $matcher->match($_SERVER['REQUEST_URI']);

            // Cast params to int if numeric
            array_walk($matcher, function (&$param) {
                if (is_numeric($param)) {
                    $param = (int)$param;
                }
            });

            $className = '\\App\\Controllers\\' . $matcher['controller'];
            $classInstance = new $className();

            // Add routes as paramaters to the next class
            $params = array_merge((array)array_slice($matcher, 2, -1), array('routes' => $routes));

            call_user_func_array(array($classInstance, $matcher['method']), $params);

        } catch (MethodNotAllowedException) {
            echo 'Route method is not allowed.';
        } catch (ResourceNotFoundException) {
            echo 'Route does not exists.';
        }
    }
}

// Invoke
$router = new Router();
$router($routes);