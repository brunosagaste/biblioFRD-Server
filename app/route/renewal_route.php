<?php
use App\Model\RenewalModel;

$app->group('/renewal/', function () {

    $this->get('book/{id}', function ($req, $res, $args) {
        $um = new RenewalModel();
        $user = $req->getAttribute('decoded_token_data');

        return $res->withJson($um->Renew($user->id, $args['id']), 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    });
});
