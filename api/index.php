<?php

declare(strict_types=1);

$publicIndex = realpath(__DIR__.'/../public/index.php');
$publicRoot = realpath(__DIR__.'/../public');

if ($publicIndex === false || $publicRoot === false) {
    http_response_code(500);

    echo 'Laravel public entrypoint was not found.';

    return;
}

$_SERVER['SCRIPT_FILENAME'] = $publicIndex;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['DOCUMENT_ROOT'] = $publicRoot;

require $publicIndex;
