<?php

$vendor = getenv('COINSPAY_VENDOR') ?: __DIR__ . '/../vendor/autoload.php';
require $vendor;
require __DIR__ . '/../autoload.php';
require __DIR__ . '/Support/FakeHttp.php';
require __DIR__ . '/Support/Vectors.php';
