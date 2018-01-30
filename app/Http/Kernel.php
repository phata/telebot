<?php

namespace App\Http;

use \FastRoute\Dispatcher;
use \Psr\Http\Message\ServerRequestInterface;

/**
 * Core http handler with FastRoute\Dispatcher instance.
 *
 * @category Class
 * @package  Phata\TeleCore
 */
class Kernel
{

    /**
     * Resolve the request path for routing
     *
     * @param ServerRequestInterface $request A standard PSR
     *                                        server request interface.
     *
     * @return string Path for routing.
     */
    private static function _requestPath(
        ServerRequestInterface $request
    ): string {
        $parsed = parse_url($request->getUri());
        return '/' . trim($parsed['path'] ?? '', '/');
    }

    /**
     * Dispatch route and handle the route
     *
     * @param Dispatcher             $dispatcher A FastRoute\Dispatcher
     *                                           instance.
     * @param ServerRequestInterface $request    A standard PSR server
     *                                           request interface.
     *
     * @return void
     */
    public static function dispatch(
        Dispatcher $dispatcher,
        ServerRequestInterface $request
    ): void {
        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            static::_requestPath($request)
        );
        switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            // ... 404 Not Found
            echo "not found";
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            // ... 405 Method Not Allowed
            echo "method not allowed";
            break;
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            try {
                $handler($vars);
            } catch (\Exception $e) {
                die($e->getMessage());
            }
            break;
        }
    }
}
