<?php

namespace App\Manager;

use App\Model\UserModel;
use Slim\Http\Response;

class AuthorizationManager
{
    public static function checkPassword(string $pass_user, string $form_newpw, string $form_newpwconfirm, string $form_actualpw): array
    {
        $error = false;
        $message = "Contraseña actualizada con éxito";
        $status = 200;
        $field = "";
    
        //Los mensajes de error están ordenados de tal forma de que siempre se muestre el mas 'grave' de todos.
        //No tiene sentido que el error que llegue sea que la clave no tiene un número, si la clave que eligió está vacía, o si se equivocó en la actual.
        //Tampoco tiene sentido mandar todos los errores al mismo tiempo, confundiendo al usuario en que debe corregir. Mejor uno por vez.
    
        if (!preg_match("#[0-9]+#", $form_newpw)) {
            $error = true;
            $message = "La contraseña debe contener al menos un número";
            $field = "newpw";
        }
    
        if (!preg_match("#.*[a-zA-Z].*#", $form_newpw)) {
            $error = true;
            $message = "La contraseña debe tener al menos una letra";
            $field = "newpw";
        }
    
        if (!preg_match("#.*[A-Z].*#", $form_newpw) and !$error) {
            $error = true;
            $message = "La contraseña debe tener al menos una mayúscula";
            $field = "newpw";
        }
    
        if (!preg_match("#[^A-Za-z0-9]#", $form_newpw)) {
            $error = true;
            $message = "La contraseña debe tener al menos un caracter especial";
            $field = "newpw";
        }
    
        if ($pass_user == md5($form_newpw)) {
            $error = true;
            $message = "La nueva contraseña debe ser distinta a la actual";
            $field = "newpw";
        }
    
        if ($form_newpw != $form_newpwconfirm) {
            $error = true;
            $message = "Las contraseñas no coinciden";
            $field = "confpw";
        }
    
        if (strlen($form_newpw) > 20) {
            $error = true;
            $message = "La contraseña debe tener menos de 20 caracteres";
            $field = "newpw"; //Útil para mostrar que campo el usuario debe corregir
        }
    
        if (strlen($form_newpw) < 8) {
            $error = true;
            $message = "La contraseña debe tener más de 8 caracteres";
            $field = "newpw";
        }
    
        if (is_null($form_newpw) or empty($form_newpw) or ctype_space($form_newpw)) {
            $error = true;
            $message = "La contraseña no debe ser vacía";
            $field = "newpw";
        }
    
        if ($pass_user != md5($form_actualpw)) {
            $error = true;
            $message = "La contraseña actual no es correcta";
            $field = "oldpw";
        }
    
        if ($error) {
            $status = 400;
        }
    
        return array('error' => $error, 'message' => $message, 'status' => $status, 'field' => $field);
    }
    
    public static function checkErrorsOnLogin(array $input, UserModel $user): array
    {
        if (is_null($input['email']) or is_null($input['password']) or empty($input['email']) or empty($input['password']) or ctype_space($input['email']) or ctype_space($input['password'])) {
            return array('error' => true, 'message' => 'Correo, legajo o contraseña vacías', 'status' => 401, 'developerMessage' => 'Empty email or password');
        }
        // verify email address.
        if(!$user->getMbrid()) {
            return array('error' => true, 'message' => 'Correo, legajo o contraseña incorrecta', 'status' => 401, 'developerMessage' => 'These credentials do not match our records');
        }
        // verify password.
        if (md5($input['password']) != $user->getPassword()) {
            return array('error' => true, 'message' => 'Correo, legajo o contraseña incorrecta', 'status' => 401, 'developerMessage' => 'These credentials do not match our records');
        }
        return array('error' => false);
    }

    public static function checkNotificationAuth(string $token, string $ipAddress, string $type): array
    {
        $settings = require dirname(__DIR__) . '/../src/settings.php';
        $app = new \Slim\App($settings);
        $container = $app->getContainer();
        $secret = $container->get('settings')['notifications']['secret'];

        if ($token != $secret) {
            return array(
                'error' => true,
                'status' => 400,
                'message' => 'Missing or invalid token',
                'developerMessage' => 'Missing token');
        }
    
        if ($ipAddress != '::1' and $ipAddress != '127.0.0.1') {
            return array(
                'error' => true,
                'status' => 400,
                'message' => 'Not a local request',
                'developerMessage' => 'Not a local request');
        }
    
        if (!isset($type)) {
            return array(
                'error' => true,
                'status' => 400,
                'message' => 'Missing request body',
                'developerMessage' => 'Missing request body');
        }
        return array('error' => false);
    }
}