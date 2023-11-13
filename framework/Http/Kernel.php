<?php

namespace AriadnaJordi\Framework\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

class Kernel
{

    public function handle(Request $request): Response
    {
        // Create a dispatcher
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {

            // Using the "$routeCollector" we can start to add routes.
            // Adds a route to the collection. 
            // Parameters:
            // 1. @param string|string[] $httpMethod
            // 2. @param string $route
            // 3. @param mixed $handler

            $routeCollector->addRoute('GET', '/', function () {

                // We return from this callback handler function, 
                // a new Response with the content:

                $content = '<h1>Hello World</h1>';

                return new Response($content);
            });

            // Another route
            $routeCollector->addRoute('GET', '/posts/{id:\d+}', function ($routeParams) {

                // We return from this callback handler function, 
                // a new Response with the content:

                $content = "<h1>This is Post {$routeParams['id']}</h1>";

                return new Response($content);
            });
        });

        //dd($request->server['REQUEST_URI']);

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
            //$request->server['REQUEST_METHOD'],
            //$request->server['REQUEST_URI']
            $request->getMethod(),
            $request->getPathInfo()
        );

        //dd($request->getPathInfo());

        [$status, $handler, $vars] = $routeInfo;

        // Call the handler, provided by the route info, in order to create a Response
        // dd($handler($vars));
        
        return $handler($vars);
    }
}