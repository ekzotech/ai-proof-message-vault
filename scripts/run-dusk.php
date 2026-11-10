<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

if (is_file($root.'/public/hot') || ! is_file($root.'/public/build/manifest.json')) {
    fwrite(STDERR, "Stop Vite's dev server and build assets with npm run build before starting Dusk.\n");
    exit(1);
}

$allocateSocket = static function () {
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    if ($socket === false) {
        throw new RuntimeException("Could not allocate a test port: $errorMessage", $errorCode);
    }

    return $socket;
};
$appSocket = $allocateSocket();
$driverSocket = $allocateSocket();
$address = stream_socket_get_name($appSocket, false);
$driverPort = parse_url('tcp://'.stream_socket_get_name($driverSocket, false), PHP_URL_PORT);
fclose($appSocket);
fclose($driverSocket);

$database = sys_get_temp_dir().DIRECTORY_SEPARATOR.'vault-dusk-'.bin2hex(random_bytes(8)).'.sqlite';
$file = fopen($database, 'x');
if ($file === false) {
    throw new RuntimeException('Could not create an isolated test database.');
}
fclose($file);

$environment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'APP_PREVIOUS_KEYS' => '(null)',
    'APP_URL' => 'http://'.$address,
    'APP_LOCALE' => 'ru',
    'APP_CONFIG_CACHE' => $database.'.config',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $database,
    'DB_URL' => '(null)',
    'CACHE_STORE' => 'database',
    'SESSION_DRIVER' => 'database',
    'SESSION_SECURE_COOKIE' => 'false',
    'SESSION_DOMAIN' => '(null)',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'BCRYPT_ROUNDS' => '4',
    'VAULT_DUSK_ISOLATED' => 'true',
    'VAULT_DUSK_DRIVER_PORT' => (string) $driverPort,
];

$server = new Process([
    PHP_BINARY, '-S', $address,
    $root.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php',
], $root.'/public', $environment, timeout: null);

try {
    $migrate = new Process([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], $root, $environment);
    $migrate->mustRun();
    $server->disableOutput()->start();

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $connection = @stream_socket_client('tcp://'.$address, timeout: 0.2);
        if ($connection !== false) {
            fclose($connection);
            break;
        }
        if (! $server->isRunning() || $attempt === 49) {
            throw new RuntimeException('The isolated Dusk server failed to start.');
        }
        usleep(100_000);
    }

    $dusk = new Process([PHP_BINARY, 'artisan', 'dusk', '--without-tty', ...array_slice($argv, 1)], $root, $environment, timeout: 240);
    $status = $dusk->run(fn (string $type, string $output) => fwrite($type === Process::ERR ? STDERR : STDOUT, $output));
} finally {
    $server->stop(1);
    foreach ([$database, $database.'-wal', $database.'-shm', $database.'-journal'] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }
}

exit($status);
