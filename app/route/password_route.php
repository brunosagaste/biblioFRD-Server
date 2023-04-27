<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\UserModel;
use App\Lib\EncryptionManager;
use App\Manager\AuthorizationManager;

$app->post('/password', function (Request $req, Response $res, array $args) {

    $input = $req->getParsedBody();
    $form_actualpw = $input['oldpw'];
    $form_newpw = $input['newpw'];
    $form_newpwconfirm = $input['newpwconfirm'];
    $user_data = $req->getAttribute('decoded_token_data');
    $user = new UserModel(false, $user_data['id']);
    $result = AuthorizationManager::checkPassword($user->getPassword(), $form_newpw, $form_newpwconfirm, $form_actualpw);
    //Si la clave está bien la cambiamos
    if(!$result['error']) {
        $user->changePass($form_newpw);
        $encryptionmgr = new EncryptionManager();
        $encryptedToken = $encryptionmgr->encrypt(explode(' ', $req->getHeaderLine('Authorization'))[1], $user->getMbrid());
    } else {
        $encryptedToken = '';
    }
    return $this->response->withJson(['error' =>  $result['error'], 'message' => $result['message'], 'developerMessage' => $result['field'], 'token' => $encryptedToken], $result['status'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

});
