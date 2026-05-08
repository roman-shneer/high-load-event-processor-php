<?php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

$redisHost = getenv('REDIS_HOST');
$redisPort = getenv('REDIS_PORT');

$server = new Server("0.0.0.0", 8000);

$server->set([
    'worker_num' => 20,
    'enable_coroutine' => true,
]);

// Create a pool at startup
$server->on('WorkerStart', function ($server, $workerId) {
    global $pool, $redisHost, $redisPort;
    $pool = new RedisPool(
        (new RedisConfig)
            ->withHost($redisHost)
            ->withPort($redisPort)
    );
});

$server->on("Request", function (Request $request, Response $response) {
    global $pool;

    try {
        $redis = $pool->get(); // Get a ready connection
        $redis->lPush('events', $request->rawContent() ?: 'empty');
        $pool->put($redis);    // Return to the pool

        $response->header("Content-Type", "application/json");
        $response->end(json_encode(["status" => "ok"]));
    } catch (\Throwable $e) {
        $response->status(500);
        $response->end(json_encode(["error" => "Redis busy"]));
    }
});

$server->start();