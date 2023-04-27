<?php

use App\Model\SearchModel;

$app->group('/search/', function () {

    $this->get('text/{text}', function ($req, $res, $args) {
        $um = new SearchModel();
        return $res->withJson($um->search($args['text']), 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    });
});
