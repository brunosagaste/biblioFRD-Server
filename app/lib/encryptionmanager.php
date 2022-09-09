<?php

namespace App\Lib;

use App\Model\UserModel;

class EncryptionManager {

    private $secret_iv;

    function __construct() {
        $settings = require dirname(__DIR__) . '/../src/settings.php';
        $app = new \Slim\App($settings);
        $container = $app->getContainer();
        $secret_iv = $container->get('settings')['encryptionmanager']['secret_iv'];
    }

    function encrypt($string, $id) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $um = new UserModel();
        $user = $um->getUserbyMbrid($id);
        $secret_key = $user->pass_user;
        // hash
        $key = hash('sha256', $secret_key);    
        // iv - encrypt method AES-256-CBC expects 16 bytes 
        $iv = substr(hash('sha256', $this->secret_iv), 0, 16);
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

        return $output;
    }

    function decrypt($string, $id) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $um = new UserModel();
        $user = $um->getUserbyMbrid($id);
        $secret_key = $user->pass_user;
        // hash
        $key = hash('sha256', $secret_key);    
        // iv - encrypt method AES-256-CBC expects 16 bytes 
        $iv = substr(hash('sha256', $this->secret_iv), 0, 16);
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);

        return $output;
    }
}