<?php

use App\Model\BookModel;

$app->group('/book/', function () {

    $this->get('get/{id}', function ($req, $res, $args) {
        $um = new BookModel();
        return $res->withJson($um->get($args['id']), 200);
    });
});
