<?php


$config['db'] = [
    'type' => 'mysql',
    'database' => 'wahelp',
    'host' => 'mariadb',
    'port' => '3306',
    'user' => 'wahelp',
    'password' => 'wahelp',
];

$config['app'] = [
    'document_root' => '/var/www/html',
];

return $config;