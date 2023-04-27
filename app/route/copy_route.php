<?php

use App\Manager\CopyManager;
use App\Lib\Response;

$app->group('/copy/', function () {

    $this->get('get', function ($req, $res, $args) {
        $response = new Response();
        $cm = new CopyManager();
        $user = $req->getAttribute('decoded_token_data');
        $allGetVars = $req->getQueryParams();
        if (isset($allGetVars['status'])) {
            $status =  $allGetVars['status'];
        } else {
            $status = null;
        }

        $response->result = $cm->getCopiesByMbrid($user['id'], $status);
        $response->setResponse(true);

        return $res->withJson($response, 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    });
});
