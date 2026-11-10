<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$base = getenv('APP_URL');
$database = getenv('DB_DATABASE');
if (getenv('APP_ENV') !== 'testing' || ! is_string($base) || ! preg_match('/\Ahttp:\/\/127\.0\.0\.1:\d+\z/', $base) ||
    ! is_string($database) || ! preg_match('/\Avault-smoke-[a-f0-9]{16}\.sqlite\z/', basename($database))) {
    throw new LogicException('Run the HTTP smoke check with composer smoke.');
}

$fixture = new Process([PHP_BINARY, 'artisan', 'vault:demo', '--json', '--no-ansi'], $root);
$fixture->mustRun();
$links = json_decode($fixture->getOutput(), true, flags: JSON_THROW_ON_ERROR)['links'];
$message = 'На рассвете мы открыли окна';
$checks = [
    'liveness' => [$base.'/up', 200, null, null],
    'readiness' => [$base.'/ready', 200, '"status":"ok"', null],
    'waiting' => [$links['waiting'], 200, 'Капсула ожидает открытия', $message],
    'open' => [$links['open'], 200, 'Капсула открыта', null],
    'accessible' => [$links['open'].'?view=plain', 200, $message, null],
    'revoked' => [$links['revoked'], 404, 'Капсула недоступна', $message],
];

foreach ($checks as $name => [$url, $expectedCode, $expectedText, $forbiddenText]) {
    if (! str_starts_with($url, $base.'/')) {
        throw new RuntimeException('An HTTP fixture tried to leave the isolated server.');
    }

    $request = curl_init($url);
    curl_setopt_array($request, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_FOLLOWLOCATION => false]);
    $content = curl_exec($request);
    $code = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
    if (! is_string($content) || $code !== $expectedCode ||
        ($expectedText !== null && ! str_contains($content, $expectedText)) ||
        ($forbiddenText !== null && str_contains($content, $forbiddenText))) {
        throw new RuntimeException("HTTP smoke check failed for $name (HTTP $code).");
    }

    printf("HTTP smoke %s: %d\n", $name, $code);
}
