<?php

ini_set('display_error', 1);
error_reporting(E_ALL);

include __DIR__ . '/../vendor/autoload.php';

use \FastRoute\Dispatcher;
use \FastRoute\RouteCollector;
use function \FastRoute\simpleDispatcher;
use \TelegramBot\Api\BotApi;
use \GuzzleHttp\Psr7\ServerRequest;
use \Symfony\Component\Dotenv\Dotenv;
use \Predis\Client as RedisClient;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\ErrorHandler;

use \Phata\TeleCore\Dispatcher as UpdateDispatcher;
use \Phata\TeleCore\Session\RedisSessionFactory;

// bootstrap code
$dotenv = new Dotenv();
$dotenv->load(
    __DIR__ . '/../.env.dist',
    __DIR__ . '/../.env'
);

// initiate bot api
$botApi = new BotApi(getenv('TELEGRAM_BOT_TOKEN'));

// logger to use
$logger = $log = new Logger('log');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/log', Logger::INFO));
$errorHandler = new ErrorHandler($logger);
$errorHandler->registerErrorHandler(); // register as global error handler

// redis client for session
if (getenv('REDIS_SCHEME') == 'tcp') {
    $redisClient = new Predis\Client(
        [
            'scheme' => getenv('REDIS_SCHEME'),
            'host' => getenv('REDIS_HOST'),
            'port' => getenv('REDIS_PORT'),
        ]
    );
} else if (getenv('REDIS_SCHEME') == 'unix') {
    $redisClient = new RedisClient(
        [
            'scheme' => getenv('REDIS_SCHEME'),
            'path' => getenv('REDIS_PATH'),
        ]
    );
} else {
    throw new \Exception(sprintf('unknown redis connection scheme "%s"', getenv('REDIS_SCHEME')));
}

// command dispatcher
// register command for dispatch
$updateDispatcher = new UpdateDispatcher();
$updateDispatcher->setLogger($logger);
$updateDispatcher->setSessionFactory(
  new RedisSessionFactory($redisClient)
);

// define update handler(s) and command handler(s)
(function () use ($updateDispatcher, $botApi, $logger) {
    include __DIR__ . '/../routes/update.php';
})();

// http dispatcher
$dispatcher = simpleDispatcher(function(RouteCollector $r) use ($updateDispatcher, $botApi, $logger) {
    include __DIR__ . '/../routes/web.php';
});

// get server request from globals
$request = ServerRequest::fromGlobals();
