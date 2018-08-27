<?php

// Использовать ее вместо vendor/autoload.php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Создание клиента
    $amo = new \AmoCRM\Client('lfs', 'admin@1stlfs.com', '7dde7a93bd13877c73bbe89f3bd0ddc0');

    
    
    
   
    
    
    
} catch (\AmoCRM\Exception $e) {
    printf('Error (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
