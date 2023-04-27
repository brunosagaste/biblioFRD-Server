<?php

$base = $_SERVER['DOCUMENT_ROOT'] . '/biblioFRD-Server/app/';

$folders = [
    'handlers',
    'lib',
    'manager',
    'middleware',
    'model',
    'route',
    'service'
];

foreach($folders as $f) {
    foreach (glob($base . "$f/*.php") as $filename) {
        require $filename;
    }
}
