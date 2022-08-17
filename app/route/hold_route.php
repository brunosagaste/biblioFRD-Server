<?php
use App\Model\HoldModel;

$app->group('/hold/', function () {

    $this->get('get', function ($req, $res, $args) {
        $um = new HoldModel();
        $user = $req->getAttribute('decoded_token_data');
        
        return $res->withJson($um->get($user->id), 200);
    });
});

