<?php

declare(strict_types=1);

/*
 * Shared bootstrap for every example script.
 *
 * - loads the library (Composer autoloader when present, bundled autoloader otherwise);
 * - reads the account credentials from the environment (never hardcode them);
 * - caches the 24h bearer token in examples_token.txt next to this file;
 * - exposes a ready-to-use Fancourier instance as $fan.
 *
 * Everything is resolved from __DIR__, so the examples can be run from any
 * working directory (repo root, examples/, etc.).
 */

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
require is_file($composerAutoload) ? $composerAutoload : __DIR__ . '/../src/autoload.php';

$tokenFile = __DIR__ . '/examples_token.txt';

// the bearer token is valid for 24 hours; drop a cached token that is older
if (is_file($tokenFile) && (filemtime($tokenFile) < time() - 86000)) {
    unlink($tokenFile);
}

// reuse the cached token, or use an empty string to signal "no token yet"
$token = is_file($tokenFile) ? file_get_contents($tokenFile) : '';

// account credentials come from the environment
$clientId = getenv('FANCOURIER_TEST_CLIENT_ID');
$username = getenv('FANCOURIER_TEST_USERNAME');
$password = getenv('FANCOURIER_TEST_PASSWORD');

if ($clientId === false || $username === false || $password === false) {
    fwrite(STDERR, "Set FANCOURIER_TEST_CLIENT_ID, FANCOURIER_TEST_USERNAME and FANCOURIER_TEST_PASSWORD environment variables.\n");
    exit(1);
}

// $fan is the shared client used by all examples below
$fan = new Fancourier\Fancourier($clientId, $username, $password, $token);

// examples often run from local machines; disable cURL certificate validation
// (keep it enabled in production)
$fan->setVerify(false, false);

// without a cached token, fetch one now. Requests also fetch a token
// automatically on first use; doing it here lets us cache it and fail fast.
if ($token == '') {
    $token = $fan->getToken(true); // force refresh (no argument returns the cached token or '')
    if ($token) {
        file_put_contents($tokenFile, $token);
    } else {
        echo $fan->getTokenMessage();
        exit;
    }
}
