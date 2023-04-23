<?php
return [
    'settings' => [
        'displayErrorDetails' => false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'determineRouteBeforeAppMiddleware' => true,
        
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Key para generar el token de identificación de usuario. No hacer un commit a Github ni compartir.
        "jwt" => [
            'secret' => 'supersecretkeyyoushouldnotcommittogithub'
        ],

        // Key para generar la encriptación del token de identificación de usuario. No hacer un commit a Github ni compartir.
        "encryptionmanager" => [
            'secret_iv' => 'xxxxxxxxxxxxxxxxxxxxxxxxx'
        ],

        "db" => [
             "host" => "localhost",
             "dbname" => "espa33",
             "user" => "root",
             "pass" => ""
         ],

        // Key para enviar notificaciones. No hacer un commit a Github ni compartir.
        "firebase" => [
            'firebase_secret' => 'supersecretkeyyoushouldnotcommittogithub'
        ],

        // Key para enviar notificaciones. No hacer un commit a Github ni compartir.
        "notifications" => [
            'secret' => 'supersecretkeyyoushouldnotcommittogithub'
        ],

    ],

];
