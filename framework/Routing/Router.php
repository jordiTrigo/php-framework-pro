<?php

namespace AriadnaJordi\Framework\Routing;

use AriadnaJordi\Framework\Http\HttpException;
use AriadnaJordi\Framework\Http\HttpRequestMethodException;
use AriadnaJordi\Framework\Http\Request;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;


class Router implements RouterInterface
{
    public function dispatch(Request $request)
    {
        $routeInfo = $this->extractRouteInfo($request);

        [$handler, $vars] = $routeInfo;

        if( is_array($handler) ) {
            [$controller, $method] = $handler;

            return [[new $controller, $method], $vars];
        }

        return [$handler, $vars];
    }

    private function extractRouteInfo(Request $request): array
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

        //dd($routeInfo);


        switch( $routeInfo[0] ) {
            case Dispatcher::FOUND:
                return [$routeInfo[1], $routeInfo[2]]; // routeHandler, vars

            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = implode(', ', $routeInfo[1]);
                $myException = new HttpRequestMethodException('The allowed methods are ' . $allowedMethods);
                $myException->setStatusCode(405);
                throw $myException;

            default:
                $myException = new HttpException('Not found');
                $myException->setStatusCode(404);
                throw $myException;
        };
    }
}