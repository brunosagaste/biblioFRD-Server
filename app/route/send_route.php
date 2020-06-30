<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Lib\Notification;

//Endpoint para mandar notificaciones. Se supone que es necesario tener un token y también hay un control por ip, para verificar que el pedido sea del localhost.
//Cada vez que recibe un request envía una notificacion sobre préstamos vencidos. Se puede utilizar una o dos veces por día programando el SO a una hora determinada para que ejecute un request a este endpoint
$app->post('/send', function (Request $req, Response $res, array $args) {

    $input = $req->getParsedBody();
    $regid = $input['token'];
    $ipAddress = $req->getAttribute('ip_address');
    //var_dump($ipAddress); //Útil para testeos

    if ($input['token'] != "eltokensupersecretoconelquenodeberiashaceruncommitagithub") {
        return $this->response->withJson(['error' => true, 'status' => 400, 'message' => 'Missing or invalid token', 'developerMessage' => 'Missing token'], 400); 
    }

    if ($ipAddress != '::1') { //Si nos preguntás por qué ::1 es localhost, todavía lo estamos averiguando
        return $this->response->withJson(['error' => true, 'status' => 400, 'message' => 'Not a local request', 'developerMessage' => 'Not local request'], 400); 
    }

    $send = new Notification();
    $status = $send->sendNotification();

    return $this->response->withJson(['status' => $status], 200, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

});