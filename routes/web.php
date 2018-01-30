<?php

$r->addRoute('GET', '/bot', function (array $request) {
    echo "bot";
});

$r->addRoute('POST', '/bot', function (array $request) use ($updateDispatcher, $logger) {
    // get input stream and log
    $stream = fopen('php://input', 'r');

    // TODO: some error handling
    if ($stream === false) return;
    $content = stream_get_contents($stream);
    $request = json_decode($content);

    // DEBUG: log
    $logger->info("request: " . json_encode($request));

    // dispatch stream
    try {
        $routeInfo = $updateDispatcher->dispatch($request);
    } catch (\Exception $e) {
        $logger->error("error: " . $e->getMessage());
    }
    
    // DEBUG: logging for debug.
    // TODO: rewrite this as middleware.
    $logger->info("routing" . json_encode($routeInfo));

    // if there is command handler, handle it.
    if ($routeInfo != null) {
        list($handler, $args) = $routeInfo;
        $handler(...$args);
    } else {
        // TODO: some kind of logging for these strange cases.
    }
});

