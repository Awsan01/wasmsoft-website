<?php

/**
 * Router of PHP's development server for the browser tests
 * (tests/Browser/server.mjs). Their uploaded media live in the tests' own
 * storage folder, which public/storage does not lead to, so they are served
 * from there; every other request goes to Laravel, as with
 * `php artisan serve`.
 */
$path = urldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$storage = getenv('LARAVEL_STORAGE_PATH');

if (is_string($storage) && $storage !== '' && str_starts_with($path, '/storage/')) {
    $public = realpath($storage.'/app/public');
    $file = realpath($storage.'/app/public/'.substr($path, strlen('/storage/')));

    if ($public === false || $file === false || ! str_starts_with($file, $public.DIRECTORY_SEPARATOR) || ! is_file($file)) {
        http_response_code(404);

        return true;
    }

    header('Content-Type: '.(mime_content_type($file) ?: 'application/octet-stream'));
    header('Content-Length: '.filesize($file));
    readfile($file);

    return true;
}

return require dirname(__DIR__, 2).'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
