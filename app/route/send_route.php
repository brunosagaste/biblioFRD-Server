<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Lib\Notification;

//Endpoint para mandar notificaciones. Se supone que es necesario tener un token y también hay un control por ip, para verificar que el pedido sea del localhost.
//Cada vez que recibe un request envía una notificacion sobre préstamos vencidos.
$app->post('/send', function (Request $req, Response $res, array $args) {

    $input = $req->getParsedBody();
    $ipAddress = $req->getAttribute('ip_address');
    $status = "";
    //var_dump($ipAddress); //Útil para testeos

    if ($input['token'] != "eltokensupersecretoconelquenodeberiashaceruncommitagithub") {
        return $this->response->withJson(['error' => true, 'status' => 400, 'message' => 'Missing or invalid token', 'developerMessage' => 'Missing token'], 400); 
    }

    if ($ipAddress != '::1') { //Si nos preguntás por qué ::1 es localhost, todavía lo estamos averiguando
        return $this->response->withJson(['error' => true, 'status' => 400, 'message' => 'Not a local request', 'developerMessage' => 'Not local request'], 400); 
    }

    if (!isset($input['type'])) {
        return $this->response->withJson(['error' => true, 'status' => 400, 'message' => 'Missing request body', 'developerMessage' => 'Missing request body'], 400); 
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