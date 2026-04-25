<?php
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $uri;

if ($uri !== '/' && file_exists($filePath) && is_file($filePath)) {
    $ext   = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff2' => 'font/woff2',
        'woff'  => 'font/woff',
        'ttf'   => 'font/ttf',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('Cache-Control: public, max-age=3600');
    }
    readfile($filePath);
    return true;
}

require __DIR__ . '/public/index.php';
