<?php

use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;
use App\Handlers\apiError;
 

$app->post('/login', function (Request $request, Response $response, array $args) {
 
    $input = $request->getParsedBody();
    $sql = "SELECT * FROM member WHERE email= :email";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("email", $input['email']);
    $sth->execute();
    $user = $sth->fetchObject();

    if ($input['email']==null or $input['password']==null) {
        return $this->response->withJson(['error' => true, 'status' => 401, 'message' => 'Correo o contraseña vacías', 'developerMessage' => 'Empty email or password'], 401); 
    }
 
    // verify email address.
    if(!$user) {
        return $this->response->withJson(['error' => true, 'status' => 401, 'message' => 'Correo o contraseña incorrecta', 'developerMessage' => 'These credentials do not match our records.'], 401);  
    }
 
    // verify password.
    if (md5($input['password']) != $user->pass_user) {
        return $this->response->withJson(['error' => true, 'status' => 401, 'message' => 'Correo o contraseña incorrecta', 'developerMessage' => 'These credentials do not match our records.'], 401);  
    }
 
    $settings = $this->get('settings'); // get settings array.
    
    $token = JWT::encode(['id' => $user->mbrid, 'email' => $user->email], $settings['jwt']['secret'], "HS256");
 
    return $this->response->withJson(['id' => $user->mbrid, 'name' => $user->first_name,'token' => $token]);

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