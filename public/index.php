<?php declare(strict_types=1);

// Constant that defines the base path
define('BASE_PATH', dirname(__DIR__));

// With the autoloader we meant that we can autoload all our classes
// which we create ourselves or vendor classes 
require_once dirname(__DIR__) . '/vendor/autoload.php';

use AriadnaJordi\Framework\Http\Kernel;
use AriadnaJordi\Framework\Http\Request;
use AriadnaJordi\Framework\Http\Response;


// request received
$request = Request::createFromGlobals();

$router = new \AriadnaJordi\Framework\Routing\Router();

// perform some logic

// send response (string of content)
// $content = '<h1>Hello World</h1>';


$kernel = new Kernel($router);


$response = $kernel->handle($request);


// Now we send back the response. So we use the similar kind of interface as what's 
// used by the Symfony HTTP Foundation and in the same way as what both Symfony and 
// Laravel send back a response, we're just going to call a method on that called "send()".
// That means that we now need to go over to our "framework/Http" folder and we'll create
// a Response class (the same as we did with the Request class).

$response->send();