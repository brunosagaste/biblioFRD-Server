<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\UserModel;

$app->post('/password', function (Request $req, Response $res, array $args) {

    $input = $req->getParsedBody();
    $form_actualpw = $input['oldpw'];
    $form_newpw = $input['newpw'];
    $form_newpwconfirm = $input['newpwconfirm'];
    $user_data = $req->getAttribute('decoded_token_data');
    $um = new UserModel();
    $user = $um->getUserbyMbrid($user_data->id);
    $result = checkPass($user->pass_user, $form_newpw, $form_newpwconfirm, $form_actualpw);
    $bdstatus = null;
    //Si la clave está bien la cambiamos
    if(!$result['error']) {
    	$bdstatus = $um->changePass(md5($form_newpw), $user->mbrid);
    }
    return $this->response->withJson(['error' =>  $result['error'], 'message' => $result['message'], 'developerMessage' => $result['field']], $result['status'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

});


function checkPass($pass_user, $form_newpw, $form_newpwconfirm, $form_actualpw) {

	$error = false;
	$message = "Contraseña actualizada con éxito";
	$status = 200;

	//Los mensajes de error están ordenados de tal forma de que siempre se muestre el mas 'grave' de todos. 
	//No tiene sentido que el error que llegue sea que la clave no tiene un número, si la clave que eligió está vacía, o si se equivocó en la actual.
	//Tampoco tiene sentido mandar todos los errores al mismo tiempo, confundiendo al usuario en que debe corregir. Mejor uno por vez.

	if (strlen($form_newpw) > 20) {
		$error = true;
        $message = "La contraseña debe tener menos de 20 caracteres";
        $field = "newpw"; //Útil para mostrar que campo el usuario debe corregir
    }  

    if (!preg_match("#[0-9]+#", $form_newpw)) {
    	$error = true;
        $message = "La contraseña debe contener al menos un número";
        $field = "newpw";
    }

    if (!preg_match("#[a-zA-Z]+#", $form_newpw)) {
    	$error = true;
        $message = "La contraseña debe tener al menos una letra";
        $field = "newpw";
    }

    if (strlen($form_newpw) < 8) {
		$error = true;
        $message = "La contraseña debe tener más de 8 caracteres";
        $field = "newpw";
    }    

	if(is_null($form_newpw) or empty($form_newpw) or ctype_space($form_newpw)) {
		$error = true;
		$message = "La contraseña no debe ser vacía";
		$field = "newpw";
	}

	if($pass_user == md5($form_newpw)) {
		$error = true;
		$message = "La nueva contraseña debe ser distinta a la actual";
		$field = "newpw";
	}

	if($form_newpw != $form_newpwconfirm) {
		$error = true;
		$message = "Las contraseñas no coinciden";
		$field = "confpw";
	}

	if($pass_user != md5($form_actualpw)) {
		$error = true;
		$message = "La contraseña actual no es correcta";
		$field = "oldpw";
	}

    if ($error) {
    	$status = 400;
    } 

	return array('error' => $error, 'message' => $message, 'status' => $status, 'field' => $field);
} 
