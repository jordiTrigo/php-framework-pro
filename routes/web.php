<?php

use App\Controller\HomeController;
use App\Controller\PostsController;

use \AriadnaJordi\Framework\Http\Response;


return [
    ['POST', '/', [HomeController::class, 'index']],
    ['GET', '/posts/{id:\d+}', [PostsController::class, 'show']],
    ['GET', '/hello/{name:.+}', function( string $name ) {
        return new Response("Hello $name");
    }],
];