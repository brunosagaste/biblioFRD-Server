<?php
use App\Model\CopyModel;

$app->group('/copy/', function () {

    $this->get('test', function ($req, $res, $args) {
        return $res->getBody()
                   ->write('Hello Copy');
    });


    $this->get('get', function ($req, $res, $args) {
        $um = new CopyModel();
        $user = $req->getAttribute('decoded_token_data');

/*
        return $res
           ->withStatus(400)
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->Get($args['mbrid']),JSON_UNESCAPED_UNICODE
            )
            

        );*/

        return $res->withJson($um->Get($user->id), 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    });
});

