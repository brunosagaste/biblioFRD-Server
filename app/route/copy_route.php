<?php
use App\Model\CopyModel;

$app->group('/copy/', function () {

    $this->get('get', function ($req, $res, $args) {
        $um = new CopyModel();
        $user = $req->getAttribute('decoded_token_data');
        $allGetVars = $req->getQueryParams();
        if (isset($allGetVars['status'])) {
            $status =  $allGetVars['status'];
        } else {
            $status = null;
        }

        return $res->withJson($um->get($user->id, $status), 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    });
});

