<?php
namespace App\Lib;
use \Slim\App;
use PDO;

class Database {
    public static function StartUp() {
        
        $settings = require dirname(__DIR__) . '/../src/settings.php';
        $app = new \Slim\App($settings);
        $container = $app->getContainer();
        $host = $container->get('settings')['db']['host'];
        $dbname = $container->get('settings')['db']['dbname'];
        $user = $container->get('settings')['db']['user'];
        $pass = $container->get('settings')['db']['pass'];

        $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        
        return $pdo;
    }
}