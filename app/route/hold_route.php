<?php
use App\Model\HoldModel;

$app->group('/hold/', function () {

    $this->get('test', function ($req, $res, $args) {
        return $res->getBody()
                   ->write('Hello Hold');
    });


    $this->get('get', function ($req, $res, $args) {
        $um = new HoldModel();
        $user = $req->getAttribute('decoded_token_data');

/*
        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->Get($args['mbrid']),JSON_UNESCAPED_UNICODE
            )
        );*/
        return $res->withJson($um->Get($user->id), 200);
    });
});

