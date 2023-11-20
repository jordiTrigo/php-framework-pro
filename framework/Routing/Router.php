<?php

namespace AriadnaJordi\Framework\Routing;

use AriadnaJordi\Framework\Http\Request;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;


class Router implements RouterInterface
{
    public function dispatch(Request $request)
    {
        // Create a dispatcher
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {

            // Using the "$routeCollector" we can start to add routes.
            // Adds a route to the collection. 
            // Parameters:
            // 1. @param string|string[] $httpMethod
            // 2. @param string $route
            // 3. @param mixed $handler

            $routes = include BASE_PATH . '/routes/web.php';
            foreach( $routes as $route ) {

                $routeCollector->addRoute( ...$route );
            };
        });

        // Dispatch a URI, to obtain the route info.
        // Returns array with one of the following formats:
        //
        //     [self::NOT_FOUND] [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']] [self::FOUND, $handler, ['varName' => 'value', ...]]
        //
        // Parameters:
        // 
        //     @param string $httpMethod
        //     @param string $uri
        //
        //     @return array{0:int, 1:list<string>|mixed, 2:array<string, string>}
        
        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getPathInfo()
        );

        [$status, [$controller, $method], $vars] = $routeInfo;
        
        return [[new $controller, $method], $vars];
    }
}