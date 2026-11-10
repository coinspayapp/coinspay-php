<?php

/**
 * Autoloader for projects without Composer:
 *
 *     require __DIR__ . '/coinspay-php/autoload.php';
 */
spl_autoload_register(function ($class) {
    if (strpos($class, 'CoinsPay\\') !== 0) {
        return;
    }
    $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen('CoinsPay\\'))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
