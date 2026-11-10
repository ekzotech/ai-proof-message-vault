<?php

declare(strict_types=1);

$errors = [];

if (PHP_VERSION_ID < 80400) {
    $errors[] = 'PHP 8.4 or later is required. Found '.PHP_VERSION.'.';
}

$extensions = [
    'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'intl', 'mbstring',
    'openssl', 'pcre', 'pdo', 'pdo_sqlite', 'session', 'tokenizer', 'xml', 'zip',
];

foreach ($extensions as $extension) {
    if (! extension_loaded($extension)) {
        $errors[] = 'Enable the PHP extension '.$extension.'.';
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);
    exit(1);
}

echo 'PHP '.PHP_VERSION.' and the required extensions are available.'.PHP_EOL;
