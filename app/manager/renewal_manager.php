<?php

namespace App\Manager;

use App\Lib\Database;
use App\Lib\Response;
use App\Handlers\ApiError;
use App\Model\CopyModel;
use App\Model\HoldModel;
use App\Model\BookModel;
use App\Manager\DateManager;
use Datetime;

/*
Recibo mbrid y numero de copia
Primero verifico que el mbrid realmente tenga esa copia prestada
Después verifico que no haya sido prestada con anterioridad
Después verifico que no sea la única copia
Después verifico que no esté reservada
Entonces renuevo
*/

class RenewalManager
{
    private $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    public function updateRenewalDate(CopyModel $copy): void
    {
        $date = $copy->getDueBackDt(); //Obtengo fecha de devolución.
        //Envío fecha de devolución y la copia. Obtengo cuantos días puede renovar
        $daysDueBackWithRenew = $copy->getDueBackDays($date);
        //Convierto días a horas y lo sumo a lo anterior. El sistema de administración trabaja en horas.
        $copy->setRenewalCount($copy->getRenewalCount() + $daysDueBackWithRenew*24);
        //Sumo los días a la fecha de renovación.
        $newDate = DateManager::addDays($copy->getDueBackDt(), $daysDueBackWithRenew);
        $copy->setDueBackDt($newDate);
        //Seteamos que la última renovación fue realizada por el socio
        //Esto permite ver un informe de las últimas renovaciones de socios en el sistema de administración
        $copy->setLastRenewalBy("member");
        //Actualizo la base de datos
        $copy->update();
    }

    public function renew(int $mbrid, int $bibid, int $copyid): Response
    {
        try {
            //Intento renovar una copia
            $copy_manager = new CopyManager();
            $copy = $copy_manager->getSpecificCopy($bibid, $copyid);
            
            //Verifico que el mbrid realmente tenga prestada esa copia
            if ($mbrid != $copy->getMbrid()) {
                throw new ApiError('No tiene un préstamo sobre la copia');
            }

            $renewalcheck = $this->checkRenewal($copy)['result'];

            if ($renewalcheck) {
                $this->updateRenewalDate($copy);
            } else {
                throw new ApiError('Esta copia no puede ser renovada');
            }

            //Respondo
            $this->response->setResponse(true);
            $this->response->result = ['message' => "Copia renovada", "date" => $copy->getDueBackDt()];

            return $this->response;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function checkRenewal(CopyModel $copy): array
    {
        try {
            //Busco las reservas
            $holdnum = $copy->getHoldsNmbr();

            //Obtengo todas las copias del libro
            $book = new BookModel($copy->getBibid());
            $copies = $book->getCopies();
            
            //Contamos la cantidad de copias que no están prestadas
            $copiesIn = 0;
            foreach ($copies as $arrcopy) {
                if ($arrcopy->due_back_dt == "") {
                    $copiesIn++;
                }
            }

            $renewalDelta = $copy->findRenewalDelta();
            $reachedLimit = $copy->hasReachedRenewalLimit();
            date_default_timezone_set("America/Argentina/Buenos_Aires");
            //Verifico si el préstamo se puede renovar
            //Es un quilombo pero la causa es importante para poner el color de la tarjeta y ocultar el botón de renovar en la app
            if (!$reachedLimit and $copiesIn != 0 and count($copies) > 1 and $copy->getDaysLate() == 0 and $holdnum == 0) {
                //Podría renovarse
                if ($renewalDelta != 0 and $copy->getDueBackDt() <= date_add(new DateTime('now'), date_interval_create_from_date_string($renewalDelta . " days"))->format('Y-m-d')) {
                    //Cumple todos los requisitos, renuevo
                    return array('result' => true, 'cause' => 'betweendates');
                } elseif ($renewalDelta == 0) {
                    //Cumple todos los requisitos y no hay una fecha de renovación configurada, renuevo
                    return array('result' => true, 'cause' => 'deltanotset');
                } else {
                    //No cumple con el requisito de estar en fecha de renovación
                    return array('result' => false, 'cause' => 'date', 'dateavailable' => date_sub(date_create_from_format('Y-m-d', $copy->getDueBackDt()), date_interval_create_from_date_string($renewalDelta . " days"))->format('d/m'));
                }
            } else {
                //No puede renovarse
                if ($copy->getDaysLate() > 0) {
                    //No puede renovarse por estar vencido
                    return array('result' => false, 'cause' => 'overdue');
                } else {
                    //No puede renovarse por otro motivo
                    return array('result' => false, 'cause' => 'else');
                }
            }
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}
