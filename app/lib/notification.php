<?php

namespace App\Lib;

use App\Lib\FCM;
use App\Lib\Push;
use App\Model\RegIDManager;
use PDO;

class Notification {
 
    public function sendNotification() {
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

        


		//$title = 'Hola';
		//$message = 'Crack';

		//$regId = 'fSbaUYGoSGScTRZA8BLDLI:APA91bEw0lu--R6mFPPoBxxBNqgfu2lPb6Cbrr0DL5q5GVMi4XtBkVlADkw9MNAu7ch701x4rI5rFPM009mpMAztUuZS20xDraqs6qlJnkbgIGJxwzSEhY0r_i0diY5g1DWb3kyfionq';

		//$push->setTitle($title);
		//$push->setMessage($message);
		//$push->setIsBackground(FALSE);

		//$push_type = 'individual';

		//$json = '';
		//$response = '';

		//if ($push_type == 'topic') {
		//    $json = $push->getPush();
		//    $response = $firebase->sendToTopic('global', $json);
		//} else if ($push_type == 'individual') {
		//    $json = $push->getPush();
		//    $response = $firebase->send($regId, $json);
		//    //var_dump($response);
		//}
		return $arrayResponse;
    }
}