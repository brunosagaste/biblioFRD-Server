<?php

namespace App\Lib;

use App\Model\UserModel;

class EncryptionManager
{
    private $secret_iv;

    public function __construct()
    {
        $settings = require dirname(__DIR__) . '/../src/settings.php';
        $app = new \Slim\App($settings);
        $container = $app->getContainer();
        $this->secret_iv = $container->get('settings')['encryptionmanager']['secret_iv'];
    }

    public function encrypt(string $string, int $id): string
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $user = new UserModel(false, $id);
        $secret_key = $user->getPassword();
        // hash
        $key = hash('sha256', $secret_key);
        // iv - encrypt method AES-256-CBC expects 16 bytes
        $iv = substr(hash('sha256', $this->secret_iv), 0, 16);
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

        return $output;
    }

    public function decrypt(string $string, int $id): string
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $user = new UserModel(false, $id);
        $secret_key = $user->getPassword();
        // hash
        $key = hash('sha256', $secret_key);
        // iv - encrypt method AES-256-CBC expects 16 bytes
        $iv = substr(hash('sha256', $this->secret_iv), 0, 16);
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);

        return $output;
    }
}
