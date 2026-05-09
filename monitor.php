<?php

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;


Swoole\Runtime::enableCoroutine();


$redisHost = getenv('REDIS_HOST') ?: 'redis';
$redisPort = getenv('REDIS_PORT') ?: 6379;

$server = new Server("0.0.0.0", 80);

$server->set([
    'worker_num' => 2, // 2 workers are enough for monitoring
    'enable_static_handler' => false,
]);

/**
 * Initialize Redis once when the worker starts,
* to avoid reconnecting for every request.
 */
$server->on("WorkerStart", function ($server, $workerId) use ($redisHost, $redisPort) {
    global $redis;
    $redis = new Redis();
    try {
        $redis->pconnect($redisHost, $redisPort);
    } catch (\Exception $e) {
        echo "Redis connection error: " . $e->getMessage() . "\n";
    }
});

$server->on("Request", function (Request $request, Response $response) {
    global $redis;

    $uri = $request->server['request_uri'];

    // 1. Return of the main page with a graph
    if ($uri === '/' || $uri === '/index.html') {
        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->sendfile(__DIR__ . '/index.html');
        return;
    }

    // 2. API endpoint for receiving monitoring data
    if ($uri === '/api/status') {
        try {
            
            if (!$redis->ping()) {
                $redis->connect(getenv('REDIS_HOST'), 6379);
            }

            $info = $redis->info();

          
            $eventsCount = $redis->get('stats:events_count') ?: 0;
            $dbCount = $redis->get('stats:db_count') ?: 0;

            $response->header("Content-Type", "application/json");
            $response->header("Access-Control-Allow-Origin", "*"); 

            $response->end(json_encode([
                'used_memory' => round($info['used_memory'] / 1024 / 1024, 2),
                'events_total' => (int) $eventsCount,
                'db_total' => (int) $dbCount,
                'timestamp' => date('H:i:s')
            ]));

        } catch (\Throwable $e) {
            $response->status(500);
            $response->end(json_encode(['error' => 'Redis unreachable', 'message' => $e->getMessage()]));
        }
        return;
    }

    // 3. 404 for other paths
    $response->status(404);
    $response->end("Not Found");
});

echo "Monitor UI started at http://localhost:9502\n";
$server->start();