<?php
echo "Worker started...\n";

// Настройки подключений
$redisHost = getenv('REDIS_HOST');
$redisPort = getenv('REDIS_PORT');

$dbHost = getenv('POSTGRES_HOST');
$dbName = getenv('POSTGRES_DB');
$dbUser = getenv('POSTGRES_USER');
$dbPass = getenv('POSTGRES_PASSWORD');
$dbPort = getenv('POSTGRES_PORT');


$dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";


try {
    $redis = new Redis();
    $redis->connect($redisHost, $redisPort);

   $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connections established. Waiting for events...\n";

    while (true) {
        // Blocking read from Redis (0 = wait forever)
        // BRPOP returns [queue_name, value]
        $data = $redis->brPop(['events'], 0);
        $payload = $data[1];

        if ($payload) {
            // 1. Write to PostgreSQL
            $stmt = $pdo->prepare("INSERT INTO events (data, created_at) VALUES (?, NOW())");
            $stmt->execute([$payload]);
        
            // 2. Additional processing (e.g., logging)
            $redis->incr('stats:db_count');
        }
    }
} catch (\Throwable $e) {
    echo "Worker Error: " . $e->getMessage() . "\n";
    exit(1);
}