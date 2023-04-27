<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Firebase\JWT\JWT;
use App\Handlers\ApiError;
use App\Model\UserModel;
use App\Manager\AuthorizationManager;
use App\Lib\EncryptionManager;

$app->post('/login', function (Request $request, Response $response, array $args) {
    $input = $request->getParsedBody();
    $user = new UserModel($input['email']);
    $result = AuthorizationManager::checkErrorsOnLogin($input, $user);
    if ($result['error']) {
        return $response->withJson(
            [
            'error' => $result['error'],
            'status' => $result['status'],
            'message' => $result['message'],
            'developerMessage' => $result['developerMessage']],
            $result['status']
        );
    }
    $settings = $this->get('settings'); // get settings array.
    $token = JWT::encode(['id' => $user->getMbrid(), 'email' => $user->getEmail()], $settings['jwt']['secret'], "HS256");
    $encryptionmgr = new EncryptionManager();
    $encryptedToken = $encryptionmgr->encrypt($token, $user->getMbrid());

    return $this->response->withJson([
        'id' => $user->getMbrid(),
        'name' => $user->getFirstName(),
        'token' => $encryptedToken,
        'last_name' => $user->getLastName(),
        'file' => $user->getFilenmbr(),
        'address' => $user->getAddress(),
        'city' => $user->getCity(),
        'phone' => $user->getHomePhone(),
        'dni' => $user->getDni(),
        'mail' => $user->getEmail()]);
});
