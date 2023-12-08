<?php

namespace AriadnaJordi\Framework\Http;

class Response
{
    public function __construct(
        private ?string $content = '', // The ? tells us that it could be null value
        private int $status = 200,
        private array $headers = []
    )
    {
        // Must be set before sending content
        // So best to create on intantiation like here in the class constructor method
        http_response_code($this->status);
    }

    // It sends back this response
    public function send(): void
    {
        echo $this->content;
    }
}