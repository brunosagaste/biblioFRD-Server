<?php

use App\Model\BookModel;
use App\Lib\Response;

$app->group('/book/', function () {

    $this->get('get/{id}', function ($req, $res, $args) {
        $response = new Response();
        try {
            $response->result = new BookModel($args['id']);
            $response->setResponse(true);
        } catch (ApiError $e) {
            $response->message = $e;
        }
        return $res->withJson($response, 200);
    });
});