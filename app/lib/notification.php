<?php

namespace App\Lib;

use App\Lib\FCM;
use App\Lib\Push;
use App\Model\RegIDManager;
use PDO;

class Notification {
 
    public function sendInfractionNotification() {
        $firebase = new FCM();
		$push = new Push();
		$regidMan = new RegIDManager();
		$regidstm =  $regidMan->getOverdueRegids();

		if ($regidstm->rowCount()==0) {
            throw new apiError('No hay notificaciones para enviar');
        }

        $json = '';
		$response = '';
		$arrayResponse = array();

		while ($regidrow = $regidstm->fetch(PDO::FETCH_ASSOC)) {
            extract($regidrow);
            if (!is_null($regid) and !empty($regid)) {
            	// $late_books es la cantidad de préstamos vencidos
            	if ($late_books > 1) {
            		$title = "Actualmente tenés " . $late_books . " préstamos vencidos";
	                $message = "¿Querés pasar por la biblioteca a devolverlos?";
            	} else {
	                $title = "Actualmente tenés " . $late_books . " préstamo vencido";
	                $message = "¿Querés pasar por la biblioteca a devolverlo?";
                }
                $push->setTitle($title);
				$push->setMessage($message);
				$push->setIsBackground(FALSE);
				$json = $push->getPush();
                $response = $firebase->send($regid, $json);
                array_push($arrayResponse, $response);
            }
        }
        
		return $arrayResponse;
    }

    public function sendReminderNotification() {
        $firebase = new FCM();
		$push = new Push();
		$regidMan = new RegIDManager();
		$regidstm =  $regidMan->getReminderRegids();

		if ($regidstm->rowCount()==0) {
            throw new apiError('No hay notificaciones para enviar');
        }

        $json = '';
		$response = '';
		$arrayResponse = array();

		while ($regidrow = $regidstm->fetch(PDO::FETCH_ASSOC)) {
            extract($regidrow);
            if (!is_null($regid) and !empty($regid)) {
            	// $late_books es la cantidad de préstamos vencidos
            	if ($remind_books > 1) {
            		$title = "Tenés " . $remind_books . " préstamos que vencen mañana";
	                $message = "No te olvides de pasar por la biblioteca a devolverlos";
            	} else {
	                $title = "Tenés " . $remind_books . " préstamo que vence mañana";
	                $message = "No te olvides de pasar por la biblioteca a devolverlo";
                }
                $push->setTitle($title);
				$push->setMessage($message);
				$push->setIsBackground(FALSE);
				$json = $push->getPush();
                $response = $firebase->send($regid, $json);
                array_push($arrayResponse, $response);
            }
        }
        
		return $arrayResponse;
    }
}