<?php

namespace AriadnaJordi\Framework\Http;

class Request
{
    // Constructor using constructor promoted properties
    public function __construct(
        // $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER
        public readonly array $getParams,
        public readonly array $postParams,
        public readonly array $cookies,
        public readonly array $files,
        public readonly array $server
    )
    {

    }


    public static function createFromGlobals(): static
    {
        // This function returns an instance of itself (with the PHP superglobals ie $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER) 
        // that's why we use as a return value "static"

        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }


    public function getPathInfo(): string 
    {
        // To remove the query parameter we'll use strtok()
        // First parameter the string
        // Second parameter the token ('?')
        return strtok($this->server['REQUEST_URI'], '?');
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }
}