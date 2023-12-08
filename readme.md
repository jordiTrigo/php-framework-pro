# The Request -> Response Cycle

## The Front Controller

The `Front Controller` is a software design pattern which provides just a single entrypoint to a web application.

This pattern is used by all of the PHP frameworks that you can think of and provides many benefits, the main ones being:

- Centralized control
- System maintainability
- Configurability


We will be using a simple `Docker` configuration but please use whatever works for you if you have a preferred setup.

What I will add is that, when I started working on this, I hadn't already worked out what tech was going to be involved so I started out with `Docker` just in case I needed any installable tech or non-common PHP extensions.

As it turned out, I kept everything really simple and even the DB is just an sqlite file. SO...if you have PHP and Composer installed on your computer, it's totally possible to complete this course just with PHP's built-in server. Simply run this command and you're up and running:

```bash
php -S localhost:8000 public/index.php
```

If you do want to use the same setup as me, then just copy the `docker-compose.yaml` file, which is attached to the lesson, into your project root. You will need to have Docker Desktop installed. The command to get things running is:

`$ docker compose -f docker-compose.yaml up -d`

I won't be covering Docker in any more detail than that but you can learn more about it by enrolling in my Docker course for free here:
```
https://www.garyclarke.tech/p/learn-docker-and-php
```


## Autoloading

Before we start to create Http classes to represent request and response, let's dive into `Composer` in order to set up `autoloading`.

The folder structure will be important here because we are creating framework files and classes but also application files and classes (i.e. the kind of files that a framework user would create). I intend to keep these separate by having a src folder and a framework folder.

Because I am using docker, I execute these commands in my running app container by prefixing them with this: 
```bash
docker compose exec app
```

If you are just using Composer installed locally on your computer, just run the `Composer` commands as normal.

*Note* - I don't mention this in the recording but I also added the `/vendor` folder to the `.gitignore` file. 


### composer.json 

In the file `/php-framework-pro/composer.json` we map certain namespaces to certain folders:
1. `App\\` namespace maps the `src/` folder.
2. `AriadnaJordi\\Framework\\` namespace maps the `framework/` folder.

With that file:

```json
{
    "name": "ajt-30/php-framework-pro",
    "description": "A PHP framework tutorial project",
    "minimum-stability": "dev",
    "license": "MIT",
    "authors": [
        {
            "name": "AJT",
            "email": "ajt@gmail.com"
        }
    ],
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "AriadnaJordi\\Framework\\": "framework/"
        }
    },
    "require": {        
    }
}
```

We now execute in the terminal:

```bash
jordi@jordi-HP-Laptop-15s-fq1xxx:~/Dev/PHP/php-framework-pro$ docker compose exec app composer dump-autoload
Generating autoload files
Generated autoload files

jordi@jordi-HP-Laptop-15s-fq1xxx:~/Dev/PHP/php-framework-pro$ docker compose exec app composer require symfony/var-dumper
Using version 7.0.x-dev for symfony/var-dumper
./composer.json has been updated
Running composer update symfony/var-dumper
Loading composer repositories with package information
Updating dependencies
Lock file operations: 2 installs, 0 updates, 0 removals
  - Locking symfony/polyfill-mbstring (1.x-dev 42292d9)
  - Locking symfony/var-dumper (7.0.x-dev 3c833bc)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 2 installs, 0 updates, 0 removals
  - Downloading symfony/polyfill-mbstring (1.x-dev 42292d9)
  - Downloading symfony/var-dumper (7.0.x-dev 3c833bc)
  - Installing symfony/polyfill-mbstring (1.x-dev 42292d9): Extracting archive
  - Installing symfony/var-dumper (7.0.x-dev 3c833bc): Extracting archive
Generating autoload files
2 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found
```

Now the file `/php-framework-pro/composer.json` converts into:

```json
{
    "name": "ajt-30/php-framework-pro",
    "description": "A PHP framework tutorial project",
    "minimum-stability": "dev",
    "license": "MIT",
    "authors": [
        {
            "name": "AJT",
            "email": "ajt@gmail.com"
        }
    ],
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "AriadnaJordi\\Framework\\": "framework/"
        }
    },
    "require": {
        "symfony/var-dumper": "7.0.x-dev"
    }
}
```

We modify the `require` key to `require-dev`:

```json
{
    "name": "ajt-30/php-framework-pro",
    "description": "A PHP framework tutorial project",
    "minimum-stability": "dev",
    "license": "MIT",
    "authors": [
        {
            "name": "AJT",
            "email": "ajt@gmail.com"
        }
    ],
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "AriadnaJordi\\Framework\\": "framework/"
        }
    },
    "require-dev": {
        "symfony/var-dumper": "7.0.x-dev"
    }
}
```

Now it has appeared a new folder called `/php-framework-pro/vendor/` and inside it we got the `autoload.php` file
which means we can start autoloading files and they'll autoloading from or using the namespaces `App\\` and the
folder location `src/` and the same for `AriadnaJordi\\Framework\\` and `framework/`

Now we go to our `/php-framework-pro/public/index.php` file and we add autoloading at the top:

```php
<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// request received

// perform some logic

// send response (string of content)
echo 'Hello World';
```

Also as we have installed the `symfony/var-dumper` inside our docker container app, so now we can use the function `dd()` that stands for die and dump that it will kill the application and then dump out whatever variable(s) we pass it.

```php
<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';


dd('Here!');

// request received

// perform some logic

// send response (string of content)
echo 'Hello World';
```


## Request Class

All PHP frameworks use objects to represent the incoming request. One of the greatest advantages of this is encapsulation: we can store all of the superglobal values as properties on our request object and those values will be preserved and protected from being tampered with unlike the superglobals which can have their values altered.

We will be able to use some of the properties on our request object in order to perform important operations such as routing the request to the correct handler (controller) etc.

The `Request` class which I create here is a superlight model based on the `Symfony Http Foundation request class`:
```
https://symfony.com/doc/current/components/http_foundation.html
```

We have created that request class in order to encapsulate the data available to us when the HTTP request is received by our application.


## Response Class

In the same way that we did with the request, let's also encapsulate the response data by creating a response class. There are 3 main pieces of data associated with a response and they are:

- Content
- Status (code)
- Headers

ie, all the information which gets send back with the HTTP response.

The content will always be a string (or null) so we can send it by echoing it from a `$response->send()` method.



## Http Kernel

We've now created both ends of the `request-response cycle` so, we've looked at the `Request` class and the `Response` class so now let's consider a class which is responsible for taking that `Request` and returning a `Response`.

For this we are going to create a `HTTP Kernel` class which is the heart of your application. It is used both for Laravel and Symfony to represent the core of the applicaton from a very high level. This class will be composed of the main components that we are going to need to complete the `request -> response cycle`. It's responsability is to receive a request and output a response and, this is handle by a sole method called `handle()`.

```php
$response = $kernel->handle($request);
```


# Routing

Now we have our HTTP essentials in place, that's our `Request` class, our `Response` class and our `Kernel` class. What we want to do now is to be able to have custom handling for different requests to different URIs. The way we can do that is with `routing`.

Once the `request` is received by our application, it is forwarded always to our public `index.php`, but now we need to `route` that `request` to a `handler` and we use the **path info or parts of the URI to determine what handler it should be forwarded to**.

``` 
/path  ===>  handler()
```

We do this by having pre-defined routes which pattern-match URI's. If a requested uri matches the pattern for a route, the request is then forwarded on to the correct handler for that route. For example, we have an user's URI and also a post's URI and we want to handle a request to a user's endpoint differently than we do a post's endpoint. So we **forward them to different handlers**.

```
/users  ===>  users-handler()

/posts  ===>  posts-handler()
```

A handler is simply a callable function which has custom handling for requests to URI's matching that particular route. It can be:

- A callback, a
- A function in the same file, or 
- An array containing an object and the name of a method on that object.

Usually we'll use regular expressions or `regex` to match the URI to an established route and to direct the `request` to the `handler` for a route.

```
URI: /users/55

matches...

Route: /users/{id:\d+}

forwards to...

Handler: user-handler($id)
```

## FastRoute Router

For our routing we are going to use a 3rd party package called `FastRoute` which uses regular expressions to match URI's to routes and their handlers.

You can find `FastRoute` here: https://github.com/nikic/FastRoute and we will install it using composer.

Using `FastRoute` you obtain a dispatcher object 

```php
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/users', 'get_all_users_handler');
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});
```

and basically you're adding a route like this

```php
$r->addRoute('GET', '/users', 'get_all_users_handler');
```

where we say what method, for example `GET` (so if I make a client request in the browser to a webpage, that is a `GET` request and if I submit a form that would be a `POST` request), then you provide the URI (`/users`) or patterns (`/user/{id:\d+}`)

Now we install the `FastRoute` package:

```bash
$ docker compose exec app composer require nikic/fast-route

Using version 2.0.x-dev for nikic/fast-route
./composer.json has been updated
Running composer update nikic/fast-route
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking nikic/fast-route (dev-master 7a2713c)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
  - Downloading nikic/fast-route (dev-master 7a2713c)
  - Installing nikic/fast-route (dev-master 7a2713c): Extracting archive
Generating autoload files
2 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found
```

Now if we check the `composer.json` file:

```json
{
    "name": "ajt-30/php-framework-pro",
    "description": "A PHP framework tutorial project",
    "minimum-stability": "dev",
    "license": "MIT",
    "authors": [
        {
            "name": "AJT",
            "email": "ajt@gmail.com"
        }
    ],
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "AriadnaJordi\\Framework\\": "framework/"
        }
    },
    "require-dev": {
        "symfony/var-dumper": "7.0.x-dev"
    },
    "require": {
        "nikic/fast-route": "2.0.x-dev"
    }
}
```

## Adding Routes

Let's add some routes. For the time being we are just going to add matching and handling for routes which are actually found.

We'll start out by using callbacks as handlers but later we are going to progress onto controller classes and methods.

What we need to do is

1. Create a `dispatcher`

2. Dispatch a `URI`, to obtain the `route info`: We dispatch a `URI`, so we will take the `URI` and also the `request` method and pass that into the `dispatcher` in order to obtain the `route info`. There's three pieces of information that we want back if everything has going ok:

    - `Status code`: To say that a route was found
    - `Handler`
    - `Variables`: If we pass in any variables for example, an article ID of 55 then we want to get back a variable ID with the value of 55.

3. Take the `handler`, provided by the `route info`, which is returned to us and then we're going to call it (we'll create a `Response`).

First we create a `dispatcher`. So we must edit `/php-framework-pro/framework/Http/Kernel.php` and add the next code:

```php
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
        });

        dd($dispatcher);

        // Dispatch a URI, to obtain the route info


        // Call the handler, provided by the route info, in order to create a Response
    }
}
```

We've added a `dd($dispatcher)` and we get:

```
 FastRoute\Dispatcher\MarkBased {#6 ▼
  #staticRouteMap: array:1 [▼
    "GET" => array:1 [▼
      "/" => Closure() {#10 ▼
        class: "AriadnaJordi\Framework\Http\Kernel"
        this: AriadnaJordi\Framework\Http\Kernel {#2 …}
      }
    ]
  ]
  #variableRouteData: []
}
```

We get a MarkBased Dispatcher and inside of that we have an array which is a static array that is `staticRouteMap` and here we can see something familiar that is the route that we just defined:

```
"GET" => array:1 [
    "/" => Closure()
]
```

The base URI ("/") is pointing towards a closure (closure: is an anonymous function that can access variables imported from outside scope like we're doing here).

Now we're using this `$dispatcher` to get back the route information ie, three pieces of information

1. The status to say that the route was found.
2. The handler.
3. Any variables that we can pass to the handler.

We'll use the method `$dispatcher->dispatch()` which need two parameters, the HTTP method that in our case is `GET` and, we also need the URI. We can get both those pieces of information off of our request. This is something which is dynamic and it will change with each request that comes into our application. So we'll dump our `request` via `dd()` to see where we can get the information that we're looking for.

```php
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
        });

        dd($request);

        // Dispatch a URI, to obtain the route info


        // Call the handler, provided by the route info, in order to create a Response
    }
}
```

The variables we want are on the `server` array:

```php
 AriadnaJordi\Framework\Http\Request {#3 ▼
  +getParams: []
  +postParams: []
  +cookies: []
  +files: []
  +server: array:57 [▼
    "HOSTNAME" => "d93ba1e42e7b"
    "PHP_INI_DIR" => "/usr/local/etc/php"
    "SHLVL" => "1"
    "HOME" => "/home/www-data"
    "PHP_LDFLAGS" => "-Wl,-O1 -pie"
    "PHP_CFLAGS" => "-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
    "PHP_VERSION" => "8.2.3"
    "GPG_KEYS" => "39B641343D8C104B2B146DC3F9C39DC0B9698544 E60913E4DF209907D8E30D96659A97C9CF2A795A 1198C0117593497A5EC5C199286AF1F9897469DC"
    "PHP_CPPFLAGS" => "-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
    "PHP_ASC_URL" => "https://www.php.net/distributions/php-8.2.3.tar.xz.asc"
    "COMPOSER_ALLOW_SUPERUSER" => "1"
    "PHP_URL" => "https://www.php.net/distributions/php-8.2.3.tar.xz"
    "PATH" => "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
    "PHPIZE_DEPS" => "autoconf \t\tdpkg-dev dpkg \t\tfile \t\tg++ \t\tgcc \t\tlibc-dev \t\tmake \t\tpkgconf \t\tre2c"
    "PWD" => "/var/www/html"
    "PHP_SHA256" => "b9b566686e351125d67568a33291650eb8dfa26614d205d70d82e6e92613d457"
    "USER" => "www-data"
    "HTTP_ACCEPT_LANGUAGE" => "en-US,en;q=0.9"
    "HTTP_ACCEPT_ENCODING" => "gzip, deflate, br"
    "HTTP_SEC_FETCH_DEST" => "document"
    "HTTP_SEC_FETCH_USER" => "?1"
    "HTTP_SEC_FETCH_MODE" => "navigate"
    "HTTP_SEC_FETCH_SITE" => "none"
    "HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7"
    "HTTP_USER_AGENT" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36"
    "HTTP_UPGRADE_INSECURE_REQUESTS" => "1"
    "HTTP_SEC_CH_UA_PLATFORM" => ""Linux""
    "HTTP_SEC_CH_UA_MOBILE" => "?0"
    "HTTP_SEC_CH_UA" => ""Chromium";v="118", "Google Chrome";v="118", "Not=A?Brand";v="99""
    "HTTP_CACHE_CONTROL" => "max-age=0"
    "HTTP_CONNECTION" => "keep-alive"
    "HTTP_HOST" => "localhost:8080"
    "REDIRECT_STATUS" => "200"
    "SERVER_NAME" => "localhost"
    "SERVER_PORT" => "80"
    "SERVER_ADDR" => "172.18.0.2"
    "REMOTE_PORT" => "53464"
    "REMOTE_ADDR" => "172.18.0.1"
    "SERVER_SOFTWARE" => "nginx/1.23.2"
    "GATEWAY_INTERFACE" => "CGI/1.1"
    "REQUEST_SCHEME" => "http"
    "SERVER_PROTOCOL" => "HTTP/1.1"
    "DOCUMENT_URI" => "/index.php"
    "REQUEST_URI" => "/"
    "SCRIPT_NAME" => "/index.php"
    "CONTENT_LENGTH" => ""
    "CONTENT_TYPE" => ""
    "REQUEST_METHOD" => "GET"
    "QUERY_STRING" => ""
    "DOCUMENT_ROOT" => "/var/www/html/public"
    "SCRIPT_FILENAME" => "/var/www/html/public/index.php"
    "FCGI_ROLE" => "RESPONDER"
    "PHP_SELF" => "/index.php"
    "REQUEST_TIME_FLOAT" => 1699886721.6427
    "REQUEST_TIME" => 1699886721
    "argv" => []
    "argc" => 0
  ]
}
```

In fact we're looking for the `REQUEST_URI`, `REQUEST_METHOD` variables:

```
"REQUEST_URI" => "/"
"REQUEST_METHOD" => "GET"
```

So we can access this off of the `server` array variable on our `request` object. So we go ahead and do that:

```php
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
        });

        // Dispatch a URI, to obtain the route info
        $routeInfo = $dispatcher->dispatch(
            $request->server['REQUEST_METHOD'],
            $request->server['REQUEST_URI']
        );

        dd($routeInfo);

        // Call the handler, provided by the route info, in order to create a Response
    }
}
```

We've added the `dd($routeInfo)` in order to show the result of the `dispatch()` method and to check if everything is going ok. So we get:

```php
 array:3 [▼
  0 => 1
  1 => Closure() {#10 ▼
    class: "AriadnaJordi\Framework\Http\Kernel"
    this: AriadnaJordi\Framework\Http\Kernel {#2 …}
  }
  2 => []
]
```

So we get our `Closure` and our handler `AriadnaJordi\Framework\Http\Kernel`. 
We have a dispatcher interface that holds the three status code here:

```php
<?php
declare(strict_types=1);

namespace FastRoute;

interface Dispatcher
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    ...
}
```

So we're going to take our `$routeInfo` and we're going to sort of unpack it into variables. The first one is the status `$status`, the second one is the handler `$handler`, and the third one is the variables `$vars`:

```php
[$status, $handler, $vars] = $routeInfo;

dd([$status, $handler, $vars]);
```

We get the unpacked:

```php
 array:3 [▼
  0 => 1
  1 => Closure() {#10 ▼
    class: "AriadnaJordi\Framework\Http\Kernel"
    this: AriadnaJordi\Framework\Http\Kernel {#2 …}
  }
  2 => []
]
```

Now what we want to do is call the `handler` method passing in the variables and then return whatever that returns.

```php
return $handler($vars);
```

The code is:

```php
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
            $request->server['REQUEST_METHOD'],
            $request->server['REQUEST_URI']
        );

        [$status, $handler, $vars] = $routeInfo;

        // Call the handler, provided by the route info, in order to create a Response
        return $handler($vars);
    }
}
```

Now we're going to show what's returned:

```php
dd($handler($vars));
```

We get:

```php
 AriadnaJordi\Framework\Http\Response {#4 ▼
  -content: "<h1>Hello World</h1>"
  -status: 200
  -headers: []
}
```

Now we're going to do a route that contains parameters something like:

`localhost/posts/23`

That's the kind of route that we need to match. We can have as many routes as we like. We'll use a handler method on the kernel to centralize all the routes in a single point. We're going to refactor and store all the routes in their own file.

Altought first we're going to write down the next route that contains route parameters. This route parameter named `id` is an integer and we use the regular expression `{id:\d+}` to match it. The regex `{id:\d+}` match one (`\d`) or more (`+`) digits. As we define our routes parameters that means that we can actually pass an argument named `$routeParams` that's an array with our `id` value in it, to our callback function ie `function($routeParams)`:

```php
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

    .....

    }
}
```

If we try `localhost:8080/posts/23` or `localhost:8080/posts/55`, everything goes as it should be.

Now, if we pass a route that has not match any route we've already registered we must get an error code. For example, if we pass `foo` as a route parameter as in the next url:

`localhos:8080/posts/foo`

We get an undefined array key and we get some errors because we don't actually have handling setup for if we pass the wrong kind of information:

```
Warning: Undefined array key 1 in /var/www/html/framework/Http/Kernel.php on line 64

Warning: Undefined array key 2 in /var/www/html/framework/Http/Kernel.php on line 64

Fatal error: Uncaught Error: Value of type null is not callable in /var/www/html/framework/Http/Kernel.php:69 Stack trace: #0 /var/www/html/public/index.php(29): AriadnaJordi\Framework\Http\Kernel->handle(Object(AriadnaJordi\Framework\Http\Request)) #1 {main} thrown in /var/www/html/framework/Http/Kernel.php on line 69
```


## Retrieving Path Info

We need to make sure that the paths we use for route matching contain only the path without any get parameters so let's create an accessor method on the request class which will do that for us.

We want to make sure that it converts paths like this `/posts?name=Gary` to just the plain path like this `/posts`.

Now we want to create an accessor method for our requested URI because there are instances where it might not come back the way we want it. For example if we go to the url:

`localhost:8080/posts?name=Jordi`

and if we put an `dd()` in the code to see what we get as a variable `server['REQUEST_URI']`.

```PHP
dd($request->server['REQUEST_URI']);
```

When we go to `localhost:8080/posts?name=Jordi` we get:

```
"/posts?name=Jordi"
```

We actually still get the query parameter appended into the end of the URI. Actually for matching our routes and our URIs that is not what we want. We want to actually create an accessor method where we remove the query parameters from the end of that. So we're going to create a method in the `Request` classe called `getPathInfo()`:

```php
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
            $request->server['REQUEST_METHOD'],
            $request->getPathInfo()
        );

        [$status, $handler, $vars] = $routeInfo;

        // Call the handler, provided by the route info, in order to create a Response
        // dd($handler($vars));
        
        return $handler($vars);
    }
}
```

Now we'll add the new class method `getPathInfo()` in `/php-framework-pro/framework/Http/Request. php`. To remove the query parameter we'll use the `strtok()` function where the first parameter is the string and the second one is the token (`?`): 

```php
public function getPathInfo(): string 
{
    return strtok($this->server['REQUEST_URI'], '?');
}
```

Using `strtok()` the string passed as the first parameter will be tokenized when any of the characters in the argument are found. In our case that question mark (?) will be found and then that is where the string will actually be split and we'll just return the URI the path being the part that we want.

Now we'll show using `dd($request->getPathInfo())` in the file `php-framework-pro/framework/Http/Kernel.php`

```php
class Kernel
{

    public function handle(Request $request): Response
    {
        // Create a dispatcher
        
        ...
        
        $routeInfo = $dispatcher->dispatch(
            $request->server['REQUEST_METHOD'],
            //$request->server['REQUEST_URI']
            $request->getPathInfo()
        );

        dd($request->getPathInfo());

        ...
    }
}
```

Now back over to the browser and going to `localhost:8080/posts?name=Jordi`

```
 "/posts"
```

Now we'll add a new `Request` method named `getMethod()`:

```php
public function getMethod(): string
{
    return $this->server['REQUEST_METHOD'];
}
```

and also we add it in the logic of the `/php-framework-pro/framework/Http/Kernel.php`

```php
$routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getPathInfo()
        );
```

If we go to the browser we'll see everything's working exactly the way that it was. So if we go to `localhost:8080` and `localhost:8080/posts/23` we can see the `Hello World` and `This is Post 23` as it should be.


## Adding to git

php-framework-pro$ git init
php-framework-pro$ git add *
php-framework-pro$ git commit -m "first commit"
php-framework-pro$ git branch -M main
php-framework-pro$ git remote add origin https://github.com/jordiTrigo/php-framework-pro.git
php-framework-pro$ git push -u origin main


## Defining Routes

It wouldn't be feasible to keep our route definitions inside the `Kernel` class because that would be part of the framework, i.e. a vendor file.

Each individual application that uses the framework would have to define it's own routes....somewhere. So in this one, we're going to move our route definitions into their own dedicated file, which is how other frameworks handle this.

We've defined our routes inside of the `Kernel` class in our `/php-framework-pro/framework/Http` folder. But, this is wrong for a lot of reasons, for example, this code, these endpoints, are specific to a particular application, but what we're building here inside the framework folder is actually meant to be reusable code which you share and can be installed into all different projects. We need to have something dynamic. For that reason, we're going to move our routes out into a dedicated file (that's something that they do in all frameworks). 

Now we're going to create a new folder `/php-framework-pro/routes` and inside we're going to create a new file called `/php-framework-pro/routes/web.php` that will contain all the endpoints for a web application.

Well, if we return to our file `/php-framework-pro/framework/Http/Kernel.php` if we remember, a route is made up of three pieces of information:

1. The request method (`GET`, `POST`, `PUT`, `DELETE`, etc).
2. The URI (`/posts/{id:\d+}`, `/`, etc).
3. The handler that can be a callable.

So if we go back to our file `/php-framework-pro/routes/web.php`, we need to return three pieces of information for each route. Here we're going to return a multidimensional array, which can contain many routes. We're just going to stick to creating one route for the time being to keep things simple, just so that we actually understand this. We'll have the `method`, the `URI` and as a `handler` we're going to use `controllers`. So up to now, we've just used a callback in the `Kernel` class, but a `controller` is a special class and we'll have methods on that `controller` which are dedicated to handling particular requests. In our case we're going to create the controller named `HomeController` that will have a method called `index`, which will be dedicated to handling get ('GET') requests to the endpoint '/'.

```php
<?php

return [
    ['GET', '/', [HomeController::class. 'index']]
];

```

Now we're going to the `/php-framework-pro/public/index.php` file and define a base path, which will make it easy to find files such as these throughout our application. So at the very top of the file `/php-framework-pro/public/index.php`, we'll define a constant named `BASE_PATH` with the value of the `dirname(__DIR__)` where `__DIR__` is the magic constant that will point to the parent of the directory `__DIR__`. So as in this case, `__DIR__` points to the folder `/php-framework-pro/public`, it means that `dirname(__DIR__)` points to the folder `/php-framework-pro`. Now we can use our new constant `BASE_PATH` throughout our application.

```php
<?php declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// With the autoloader we meant that we can autoload all our classes
// which we create ourselves or vendor classes 
require_once dirname(__DIR__) . '/vendor/autoload.php';

use AriadnaJordi\Framework\Http\Kernel;
use AriadnaJordi\Framework\Http\Request;
use AriadnaJordi\Framework\Http\Response;

...
```

So we go back to our file `/php-framework-pro/framework/Http/Kernel.php` and inside the `$simpleDispatcher` instantiation, we'll add the next code:

```php

// Create a dispatcher
$dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {

    /*
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
    */

    $routes = include BASE_PATH . '/routes/web.php';

    // Now we loop over those routes.
    foreach( $routes as $route ) {

        // We add each $route in our $routeCollector.
        // We unpack the array $route using the operator `...` that is called spread or splat operator and
        // it will take the array and unpack it into the parts to make up that array.

        $routeCollector->addRoute( ...$route );
    }
});
```

Now we test it doing `localhost:8080` and if it has worked, we'll see an error to say that it can't find a HomeController because we haven't actually created that yet. But it will mean that it's got as far as that point where we're actually passing in the route to the `addRoute` in the `$routeCollector`. We get:

```
Fatal error: Uncaught Error: Class "HomeController" not found in /var/www/html/framework/Http/Kernel.php:83 Stack trace: #0 /var/www/html/public/index.php(32): AriadnaJordi\Framework\Http\Kernel->handle(Object(AriadnaJordi\Framework\Http\Request)) #1 {main} thrown in /var/www/html/framework/Http/Kernel.php on line 83
```



## Controller Classes

We need to create a controller class and also update the `Kernel->handle()` method so that it knows how to call methods on the controller. So this would be classes in the part of the application code, it's not part of the framework code. So inside the folder `/php-framework-pro/src` we're going to create a new folder called `Controller` (the same as what they currently do in the Symphony framework). Inside this folder `/php-framework-pro/src/Controller` we're going to create our controller class called `HomeController`. So this will reside in a namespace of the following `psr4` which will be `App\Controller`:

```php
<?php


namespace App\Controller;

class HomeController
{

}
```

Now if we go back to our file `/php-framework-pro/routes/web.php`, we'll update the `HomeController` class using the namespace:

```php
<?php

use App\Controller\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']]
];
```

Next let's go back to our `Kernel` class and at the bottom we need to decide how we are going to handle this. So first, let's see what `$routeInfo` looks like now that we have actually added that handler in that format. So we'll add `dd($routeInfo)` to see what we get:

```php
...

$routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getPathInfo()
        );

dd($routeInfo);

...
```

and after going to `localhost:8080`: 

```php
 array:3 [▼
  0 => 1
  1 => array:2 [▼
    0 => "App\Controller\HomeController"
    1 => "index"
  ]
  2 => []
]
```

We get an array with three parts: 

1. The status (`0 => 1`) and the `1` means **found**, 

2. Then number two, this is our handler that is a combination of the controller `HomeController` and a method on that controller that is `index`,  

3. Finally, the vars ie, basically any get parameters. In this case, we haven't used any of those so we're not actually passing any of those into our controller.


Now in the `Kernel` class we're going to unpack the `$routeInfo` as

```php
[$status, [$controller, $method], $vars] = $routeInfo;
```

Remember that at the moment that `$controller` is just a string with the namespace, so we need to actually instantiate that in order to get a response and return it:

```php
[$status, [$controller, $method], $vars] = $routeInfo;

$response = (new $controller())->$method($vars);

return $response;
```

The `Kernel.php` file will be

```php
<?php

namespace AriadnaJordi\Framework\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

use AriadnaJordi\Framework\Http\Response;

class Kernel
{

    public function handle(Request $request): Response
    {
        // Create a dispatcher
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {

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

        // Call the handler, provided by the route info, in order to create a Response
        $response = (new $controller())->$method($vars);
        
        return $response;
    }
}
```

Now we must declare the `index` method of the class `HomeController`. So we go back to the file `/php-framework-pro/src/Controller/HomeController.php`

```php
use AriadnaJordi\Framework\Http\Response;


class HomeController 
{
    public function index(): Response 
    {
        $content = '<h1>Hello World</h1>';

        return new Response($content);
    }
}
```

Now if we return to our browser and go to `localhost:8080`, we'll get the Hello World.

So if we check

```php
<?php

use App\Controller\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']]
];
```

what it's saying to us is that a `GET` request to the `URI` `/`, routes to the `index` method of the `HomeController` and then all of the functionality in the creating of the response is handled by the class `HomeController` and finally the `Kernel` does the rest of the process.


## Controller Method Arguments

It is essential for any framework to be able to parameterize its routes and pass those parameters to the route handler.

Here is an example using a numeric id as part of the route. Our router pattern matches this and passes it as a required argument to the handler function (which in our case will be a controller method)

Route: /posts/{id}

Handler: function handler(int $id) {}

First we go to our file `/php-framework-pro/routes/web.php` and we'll add the route with parameters:

```php
<?php

use App\Controller\HomeController;
use App\Controller\PostsController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/posts/{id:\d+}', [PostsController::class, 'show']],
];
```

As the `PostsController` class still does not exists, we must add a new controller in the new file `/php-framework-pro/src/Controller/PostsController.php` and inside it a new method called `show`. 

Obs important: We must add the `namespace` part.

```php
<?php

namespace App\Controller;

use AriadnaJordi\Framework\Http\Response;


class PostsController
{
    public function show(int $id): Response
    {
        $content = 'This is Post {$id}';

        return new Response($content);
    }
}
```

Now if we test it on the browser and go to `http://localhost:8080/posts/23`, we get

```
Fatal error: Uncaught TypeError: App\Controller\PostsController::show(): Argument #1 ($id) must be of type int, array given, called in /var/www/html/framework/Http/Kernel.php on line 84 and defined in /var/www/html/src/Controller/PostsController.php:9 Stack trace: #0 /var/www/html/framework/Http/Kernel.php(84): App\Controller\PostsController->show(Array) #1 /var/www/html/public/index.php(32): AriadnaJordi\Framework\Http\Kernel->handle(Object(AriadnaJordi\Framework\Http\Request)) #2 {main} thrown in /var/www/html/src/Controller/PostsController.php on line 9
```

Well, we were expecting this because the PostsController's method, `show` has one argument of type int. The clue to how we solve this is back in our `Kernel` class. As we can see here, 

```php
$response = (new $controller())->$method($vars);
```

`$vars` is an array but, we're calling the method `show()` passing to it the array `$vars` but it needs as a parameter an integer (`$id`) as we can check in his declaration:

```php
public function show(int $id): Response
{
    $content = 'This is Post ' . $id;

    return new Response($content);
}
```

So we must change the code in the `Kernel` class and we'll use the built-in function `call_user_func_array()` that will be more flexible. The signature of the function `call_user_func_array()`

```php
function call_user_func_array(callable $callback, array $args): mixed { }

@param callable $callback — The function to be called.

@param array $args

@return mixed — the function result, or false on error.

@link https://php.net/manual/en/function.call-user-func-array.php

Call a callback with an array of parameters

call_user_func_array( callable $callback , array $param_arr ): mixed
```

has two arguments, the first one is a callable so we can use a callback for that and it'll be an object and then the string representation of a method on that object. The second one is an array of arguments. So we modify the `Kernel` class:

```php
$response = call_user_func_array([new $controller, $method], $vars);
```
And now we can go to the browser to check if this is working. Go to `http://localhost:8080/posts/23` and it's ok.


## Router Class Part 1

We are putting a lot of routing logic in our `Kernel` class and there is still some more routing logic stuff to figure out so I think this is enough to warrant creating a dedicated `Router` class which can be injected into the `Kernel` constructor (Laravel also does this) and then we can use a `Router` object to get us the elements out `Kernel` needs.

We're currently handling all of our routing inside of the `handle` method of the `Kernel` class. But what happens if the handler might not be found or there might not be any route, or we might be sending a GET request to a URI where there is a route, but where that route doesn't actually handle GET requests, it can handle other types of requests instead. We don't want to handle all those cases in the `handle` method of our `Kernel`, don't we? What we really want to do is to handle that in a dedicated environment, so we're going to create our own `Router` class, and that will wrap around the routing functionality that we're using from `FastRoute`.

So if we edit the file `/php-framework-pro/framework/Http/Kernel.php`, we want to try to get the route handler and the arguments, and if that is not possible then we shall catch some kind of exception (We'll use `\Exception` from the global namespace). We're going to try to get the handler and the variables via the `router` property of the `Kernel` class. If it does not go well, then we're going to build an error response, with the message provided by the exception.

```php
class Kernel
{

    public function handle(Request $request): Response
    {
        try {

            [$routeHandler, $vars] = $this->router->dispatch($request);

            $response = call_user_func_array($routeHandler, $vars);

        } catch(\Excepton $exception) {

            $response = new Response($exception->getMessage(), 400);
        }

        return $response;
    }
}
```

Now we need to create a `Router` class with a method `dispatch`. So we're going to create a new folder `/php-framework-pro/framework/Routing` and inside it, a new interface named `/php-framework-pro/framework/Routing/RouterInterface.php`. Here we're sort of building a contract for our routing and we just want to have a `dispatch` method on there.

```php
<?php

namespace AriadnaJordi\Framework\Routing;

use AriadnaJordi\Framework\Http\Request;


interface RouterInterface
{

    // This dispatch() method will be present on any class which implements this interface.
    public function dispatch(Request $request);

}
```

Now in the exact same folder we're going to create a `Router` class. The `namespace` should be the same as what we have for the interface ie `namespace AriadnaJordi\Framework\Routing` and we're going to use the previous interface in the definition of the `Router` class ie, `Router` is going to implement the interface `RouterInterface`. In this `dispatch` method that we declare in the `Router` class, we'll cut all the code that we have in the class `Kernel` and put in here.

```php
<?php

namespace AriadnaJordi\Framework\Routing;

use AriadnaJordi\Framework\Http\Request;


class Router implements RouterInterface
{

    public function dispatch(Request $request)
    {

    }

}
```

Now we go to the `handle` method of the `Kernel` class and cut next code:

```php
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

// Call the handler, provided by the route info, in order to create a Response
$response = call_user_func_array([new $controller, $method], $vars);

return $response;
```

So the `handle` method of the `Kernel` class will be:

```php
namespace AriadnaJordi\Framework\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

use AriadnaJordi\Framework\Http\Response;


class Kernel
{

    public function handle(Request $request): Response
    {
        try {        
            
            [$routeHandler, $vars] = $this->router->dispatch($request);

            // Call the handler in order to create a Response
            $response = call_user_func_array($routeHandler, $vars);

        } catch (\Exception $exception) {

            $response = new Response($exception->getMessage(), 400);
        }
        
        return $response;
    }
}
```

Now our `Router` class will be:

```php
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
```

Now if we visit `localhost:8080`, we'll see the next error:

```
Warning: Undefined property: AriadnaJordi\Framework\Http\Kernel::$router in /var/www/html/framework/Http/Kernel.php on line 18

Fatal error: Uncaught Error: Call to a member function dispatch() on null in /var/www/html/framework/Http/Kernel.php:18 Stack trace: #0 /var/www/html/public/index.php(32): 
AriadnaJordi\Framework\Http\Kernel->handle(Object(AriadnaJordi\Framework\Http\Request)) #1 {main} thrown in /var/www/html/framework/Http/Kernel.php on line 18
```

That's because we're trying to call a `dispatch` method on a `router` object, but we don't actually have that `router` object yet. That's something we'll need to create and we'll need to pass it into our HTTP kernel. So in the `Kernel` class we're going to create a constructor wher we pass in a `router`. I'll need to do that inside the file `index.php` because that's where we're actually creating our `kernel` object.

Now edit the `index.php` file:

```php
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
```

Now we go back to the `Kernel` class and we add a constructor and we also clean the imports and use clauses:

```php
<?php

namespace AriadnaJordi\Framework\Http;

use AriadnaJordi\Framework\Routing\Router;


class Kernel
{

    public function __construct(private Router $router)
    {
    }

    public function handle(Request $request): Response
    {
        ...
    }
}
```

Now if we visit `localhost:8080` we'll see everything goes well.

Also we edit the `Router` class and set the return of the function `dispatch` to `array`:

```php
<?php

namespace AriadnaJordi\Framework\Routing;

use AriadnaJordi\Framework\Http\Request;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;


class Router implements RouterInterface
{

    public function dispatch(Request $request): array
    {
        ...
    }
}
```



## Router Class Part 2

Our code currently only handles 'happy path' routing where a route and a handler can always be found.

But that is not real-world. We need to be able to extract the route info if things go to plan but bubble up some errors and handle them when things go wrong.

In this part we're going to actually create a dedicated method for getting the route info or for handling the route info and just sending back the information that you need if things have gone well. If things haven't go well, then we can throw an exception which can be caught in our `Kernel` class. 

Now we'll take a look to the different types of statuses we can get back from the route info.

We go to our file `/PHP/php-framework-pro/routes/web.php` where we've defined our routes and if we comment the next line of code, so now we don't have a base route:

```php

...

return [
    //['GET', '/', [HomeController::class, 'index']],

    ...
]
```

and try to go to `localhost:8080`, then we get an error because the actual arrays that you get back from routes which are not found, are different in structure and content than the ones when a route is found. So the error we get is:

```
Warning: Undefined array key 1 in /var/www/html/framework/Routing/Router.php on line 48

Warning: Undefined array key 2 in /var/www/html/framework/Routing/Router.php on line 48

Fatal error: Uncaught Error: Class name must be a valid object or a string in /var/www/html/framework/Routing/Router.php:50 Stack trace: #0 /var/www/html/framework/Http/Kernel.php(20): AriadnaJordi\Framework\Routing\Router->dispatch(Object(AriadnaJordi\Framework\Http\Request)) #1 /var/www/html/public/index.php(29): AriadnaJordi\Framework\Http\Kernel->handle(Object(AriadnaJordi\Framework\Http\Request)) #2 {main} thrown in /var/www/html/framework/Routing/Router.php on line 50
```

As we can see in `/php-framework-pro/vendor/nikic/fast-route/src/Dispatcher.php`, we have three options as a result of dispatch:

```php
public const NOT_FOUND = 0;
public const FOUND = 1;
public const METHOD_NOT_ALLOWED = 2;
```

So in our router, let's actually go and dump out the route info here. So we go back to our file `/php-framework-pro/framework/Routing/Router.php` and add

```php
...

$routeInfo = $dispatcher->dispatch(
    $request->getMethod(),
    $request->getPathInfo()
);

dd($routeInfo);

...
```

Again we'll go to `localhost:8080` and we get:

```
 array:1 [▼
  0 => 0
]
```

ie, is an array with one element inside it and that element is just the status and the status as we can see, is ZERO which, as we've just seen means NOT FOUND. 

So we need to put different handling in place for when that happens. How about if we go back to the `web.php` file and instead of making a `GET` request we make it a `POST` request (`POST` is when you would submit a form or something like that):

```php
return [
    ['POST', '/', [HomeController::class, 'index']],
    ...
];
```

and now we go back to `localhost:8080` and see what happens:

```
 array:2 [▼
  0 => 2
  1 => array:1 [▼
    0 => "POST"
  ]
]
```

This time we have different information:

- We have the status, which is *2* ie, the method is not allowed (when we do `localhost:8080` we're making a `GET` request). That tells us that the route *does exists but we cannot make a `GET` request to that route*, only a `POST` request is allowed as we'll see in the next array.

- We have another array with the methods which are allowed, in this case, `POST`.

So let's now go and create a method which is going to do all the hard work for us determining what route info we're going to send back if any. So what we're going to do is at the top of our `dispatch` method of the `Router` class, we're just going to get our `$routeInfo` back by calling a method on the same class named `$this->extractRouteInfo($request)`:

```php
class Router implements RouterInterface
{
    $routeInfo = $this->extractRouteInfo($request);
    
    public function dispatch(Request $request)
    {
        ...
    }
}
```

So we now create the method `extractRouteInfo($request)` as a private method:

```php
class Router implements RouterInterface
{
    $routeInfo = $this->extractRouteInfo($request);
    
    public function dispatch(Request $request)
    {
        ...
    }

    private function extractRouteInfo(Request $request)
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
    }
}
```

So we have three different scenarios

```php
public const NOT_FOUND = 0;
public const FOUND = 1;
public const METHOD_NOT_ALLOWED = 2;
```

and we need to have three different ways of handling this because we get back different data in different format for each of those scenarios. So we're going to use a `switch` block to check the status and that is always the first element of our `$routeInfo` array ie, is the element 0-index ie, `$routeInfo[0]`. The first case will handle is found, so `Dispatcher::FOUND` and we return the parts that we need which will be the `handler` (that will be the second element in the array: `$routeInfo[1]`), and the variables (that will be the third element in the array: `$routeInfo[2]`). 

```php
class Router implements RouterInterface
{
    $routeInfo = $this->extractRouteInfo($request);
    
    public function dispatch(Request $request)
    {
        ...
    }

    private function extractRouteInfo(Request $request)
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

        switch($routeInfo[0]) {

            case Dispatcher::FOUND:


        }
    }
}
```