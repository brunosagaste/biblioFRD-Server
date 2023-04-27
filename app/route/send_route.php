<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Lib\Notification;
use App\Manager\AuthorizationManager;

//Endpoint para mandar notificaciones. Se supone que es necesario tener un token y también hay un control por ip, para verificar que el pedido sea del localhost.
//Cada vez que recibe un request envía una notificacion sobre préstamos vencidos.
$app->post('/send', function (Request $req, Response $res, array $args) {

    $input = $req->getParsedBody();
    $ipAddress = $req->getAttribute('ip_address');

    $result = AuthorizationManager::checkNotificationAuth($input['token'], $ipAddress, $input['type']);

    if ($result['error']) {
        return $this->response->withJson([
            'error' => $result['error'],
            'status' => $result['status'],
            'message' => $result['message'],
            'developerMessage' => $result['developerMessage']]);
    }

    if ($input['type'] == "infraction") {
        $send = new Notification();
        $status = $send->sendInfractionNotification();
    }

    if ($input['type'] == "reminder") {
        $send = new Notification();
        $status = $send->sendReminderNotification();
    }

    return $this->response->withJson(['status' => $status], 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

});
