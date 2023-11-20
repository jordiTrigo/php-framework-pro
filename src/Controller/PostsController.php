<?php

namespace App\Controller;

use AriadnaJordi\Framework\Http\Response;


class PostsController
{
    public function show(int $id): Response
    {
        $content = 'This is Post ' . $id;

        return new Response($content);
    }
}