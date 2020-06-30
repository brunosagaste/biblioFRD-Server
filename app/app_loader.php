<?php
$base = $_SERVER['DOCUMENT_ROOT'] . '/biblioteca/app/';

$folders = [
	'handlers',
    'lib',
    'model',
    'route',
    'service'
];

foreach($folders as $f)
{
    foreach (glob($base . "$f/*.php") as $filename)
    {
        require $filename;
    }
}