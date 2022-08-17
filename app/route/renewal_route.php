<?php
use App\Model\RenewalModel;

$app->group('/renewal/', function () {

    $this->get('book', function ($req, $res, $args) {
        $allGetVars = $req->getQueryParams();
        $bibid = $allGetVars['bibid'];
        $copyid = $allGetVars['copyid'];
        $um = new RenewalModel();
        $user = $req->getAttribute('decoded_token_data');

        return $res->withJson($um->renew($user->id, $bibid, $copyid), 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    });
});
