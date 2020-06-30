<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\RegIDManager;

$app->post('/regid', function (Request $req, Response $res, array $args) {

    $input = $req->getParsedBody();
    $regid = $input['regid'];
    $user = $req->getAttribute('decoded_token_data');

    $regidMan = new RegIDManager();
    $status = $regidMan->saveRegID($regid, $user->id);

    return $this->response->withJson(['status' => $status], 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

});
