<?php

use App\Model\BookModel;

$app->group('/book/', function () {

    $this->get('getAll', function ($req, $res, $args) {
        $um = new BookModel();

        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->getAll(),JSON_UNESCAPED_UNICODE
            )
        );
    });

    $this->get('get/{id}', function ($req, $res, $args) {
        $um = new BookModel();
        return $res->withJson($um->get($args['id']), 200);
    });
});


/*
    $this->post('save', function ($req, $res) {
        $um = new UserModel();

        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->InsertOrUpdate(
                    $req->getParsedBody()
                )
            )
        );
    });

    $this->post('delete/{id}', function ($req, $res, $args) {
        $um = new UserModel();

        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->Delete($args['id'])
            )
        );
    });
*/

/*
        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->Get($args['id']),JSON_UNESCAPED_UNICODE
            )
        );*/

