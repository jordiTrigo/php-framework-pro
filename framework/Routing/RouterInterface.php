<?php

namespace AriadnaJordi\Framework\Routing;

use AriadnaJordi\Framework\Http\Request;


interface RouterInterface
{
    // This dispatch() method will be present on any class which implements this interface.
    public function dispatch(Request $request);
}