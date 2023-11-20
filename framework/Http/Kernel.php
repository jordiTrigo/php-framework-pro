<?php

namespace AriadnaJordi\Framework\Http;

use AriadnaJordi\Framework\Http\Response;
use AriadnaJordi\Framework\Routing\Router;

class Kernel
{

    public function __construct(private Router $router)
    {
        
    }
    
    public function handle(Request $request): Response
    {
        try {
        
            [$routeHandler, $vars] =$this->router->dispatch($request);

            // Call the handler, provided by the route info, in order to create a Response
            $response = call_user_func_array($routeHandler, $vars);

        } catch (\Exception $exception) {

            $response = new Response($exception->getMessage(), 400);
        }
        
        return $response;
    }
}