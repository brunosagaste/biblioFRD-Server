<?php

namespace App\Lib;

use App\Lib\FCM;
use App\Lib\Push;
use App\Model\RegIDModel;
use App\Handlers\ApiError;

class Notification
{
    public function sendInfractionNotification(): array
    {
        $firebase = new FCM();
        $push = new Push();
        $regid_model = new RegIDModel();
        $regids =  $regid_model->getOverdueRegids();

        if (count($regids)==0) {
            throw new ApiError('No hay notificaciones para enviar');
        }

        $json = '';
        $response = '';
        $arrayResponse = array();

        foreach($regids as $regid) {
            if (!is_null($regid['regid']) and !empty($regid['regid'])) {
                // $late_books es la cantidad de préstamos vencidos
                if ($regid['late_books'] > 1) {
                    $title = "Actualmente tenés " . $regid['late_books'] . " préstamos vencidos";
                    $message = "¿Querés pasar por la biblioteca a devolverlos?";
                } else {
                    $title = "Actualmente tenés " . $regid['late_books'] . " préstamo vencido";
                    $message = "¿Querés pasar por la biblioteca a devolverlo?";
                }
                $push->setTitle($title);
                $push->setMessage($message);
                $push->setIsBackground(false);
                $json = $push->getPush();
                $response = $firebase->send($regid['regid'], $json);
                array_push($arrayResponse, $response);
            }
        }

        return $arrayResponse;
    }

    public function sendReminderNotification(): array
    {
        $firebase = new FCM();
        $push = new Push();
        $regid_model = new RegIDModel();
        $regids =  $regid_model->getReminderRegids();

        if (count($regids)==0) {
            throw new apiError('No hay notificaciones para enviar');
        }

        $json = '';
        $response = '';
        $arrayResponse = array();

        foreach($regids as $regid) {
            if (!is_null($regid['regid']) and !empty($regid['regid'])) {
                // $late_books es la cantidad de préstamos vencidos
                if ($regid['late_books']  > 1) {
                    $title = "Tenés " . $regid['late_books']  . " préstamos que vencen mañana";
                    $message = "No te olvides de pasar por la biblioteca a devolverlos";
                } else {
                    $title = "Tenés " . $regid['late_books']  . " préstamo que vence mañana";
                    $message = "No te olvides de pasar por la biblioteca a devolverlo";
                }
                $push->setTitle($title);
                $push->setMessage($message);
                $push->setIsBackground(false);
                $json = $push->getPush();
                $response = $firebase->send($regid['regid'], $json);
                array_push($arrayResponse, $response);
            }
        }

        return $arrayResponse;
    }
}
