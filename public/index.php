<?php

/**
 * Routing endpoint for webhook
 *
 * @category File
 */
require __DIR__ . '/../bootstrap/app.php';

// dispatch the routing
App\Http::dispatch($dispatcher, $request);
