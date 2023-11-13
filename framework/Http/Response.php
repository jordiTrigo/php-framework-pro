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
        
    }

    // It sends back this response
    public function send(): void
    {
        echo $this->content;
    }
}