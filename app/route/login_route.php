<?php

use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;
use App\Handlers\apiError;
use App\Model\UserModel;

$app->post('/login', function (Request $request, Response $response, array $args) {

    $input = $request->getParsedBody();
    $um = new UserModel();
    $user = null;
    if (strpos($input['email'], "@")) {
        $user = $um->getUserbyEmail($input["email"]);
    }
    if (is_numeric($input['email'])) {
        $user = $um->getUserbyFileNumber($input["email"]);
    }


    if (is_null($input['email']) or is_null($input['password']) or empty($input['email']) or empty($input['password']) or ctype_space($input['email']) or ctype_space($input['password'])) {
        return $this->response->withJson(['error' => true, 'status' => 401, 'message' => 'Correo, legajo o contraseña vacías', 'developerMessage' => 'Empty email or password'], 401); 
    }
 
    // verify email address.
    if(!$user) {
        return $this->response->withJson(['error' => true, 'status' => 401, 'message' => 'Correo, legajo o contraseña incorrecta', 'developerMessage' => 'These credentials do not match our records.'], 401);  
    }
 
    // verify password.
    if (md5($input['password']) != $user->pass_user) {
        return $this->response->withJson(['error' => true, 'status' => 401, 'message' => 'Correo, legajo o contraseña incorrecta', 'developerMessage' => 'These credentials do not match our records.'], 401);  
    }

    $settings = $this->get('settings'); // get settings array.

    $user->first_name = mb_convert_encoding($user->first_name, 'UTF-8', 'UTF-8'); //Al JSON no le gustan los tildes
    
    $token = JWT::encode(['id' => $user->mbrid, 'email' => $user->email], $settings['jwt']['secret'], "HS256");
 
    return $this->response->withJson(['id' => $user->mbrid, 'name' => $user->first_name,'token' => $token, 'last_name' => $user->last_name, 'file' => $user->legajo, 'address' => $user->address, 'city' => $user->city, 'phone' => $user->home_phone, 'dni' => $user->dni, 'mail' => $user->email]);

    $app->log->debug('Response status: ' . $response->getStatus());

});



	
$app->group('/api', function(\Slim\App $app) {
 
    $app->get('/user',function(Request $request, Response $response, array $args) {
        //print_r($request->getAttribute('decoded_token_data'));
        $user = $request->getAttribute('decoded_token_data');
        print_r($user);
        /*output 
        stdClass Object
            (
                [id] => 2
                [email] => arjunphp@gmail.com
            )
                    
        */
    });
   
});