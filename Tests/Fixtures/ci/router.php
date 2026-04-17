<?php

/**
 * PHP built-in server router for TYPO3.
 *
 * Serves existing static files directly.
 * Routes everything else through TYPO3's front controller.
 *
 * Usage: php -S 0.0.0.0:8080 -t .Build/Web/ Tests/Fixtures/ci/router.php
 */

declare(strict_types=1);

$requestUri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '/';
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? (string)$_SERVER['DOCUMENT_ROOT'] : '';
$path = (string)parse_url($requestUri, PHP_URL_PATH);
$file = $documentRoot . $path;

if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

require $documentRoot . '/index.php';
