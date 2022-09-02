<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use App\Handlers\apiError;
use App\Model\CopyModel;
use App\Model\HoldModel;
use App\Model\Copy;
use PDO;

/*
Recibo mbrid y numero de copia
Primero verifico que el mbrid realmente tenga esa copia prestada
Después verifico que no haya sido prestada con anterioridad
Después verifico que no sea la única copia
Después verifico que no esté reservada
Entonces renuevo
*/

class RenewalModel {
    private $db;
    //private $table = 'biblio';
    private $response;

    public function __construct() {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function updateRenewalDate($copy) {
        
        // Lo armé yo y todavía no se cómo funciona
        // Pero equipo que gana no se toca, no lo vas a modificar que anda

        $date = $copy->dueBackDt(); //Obtengo fecha de devolución.
        $copy_model = new CopyModel();
        //Envío fecha de devolución y la copia. Obtengo cuantos días puede renovar
        $daysDueBackWithRenew = $copy_model->getDaysDueBack($copy, $date);
        //Convierto días a horas y lo sumo a lo anterior. El sistema de administración trabaja en horas.
        $copy->setRenewalCount($copy->renewalCount() + $daysDueBackWithRenew*24);
        //Sumo los días a la fecha de renovación.
        $newDate = Date::addDays($copy->dueBackDt(), $daysDueBackWithRenew);
        $copy->setDueBackDt($newDate);
        //Actualizo la base de datos
        $copy->update();
    }

    public function renew($user_mbrid, $user_bibid, $user_copyid) {
        try {   
            //Intento renovar una copia
            $copy_model = new CopyModel();
            $copystm = $copy_model->getCopy($user_bibid, $user_copyid);
            //Verifico que la copia exista
            if ($copystm->rowCount()==0) {
                throw new apiError('La copia no existe');
            }

            $row = $copystm->fetch(PDO::FETCH_ASSOC);
            extract($row);
            //Verifico que el mbrid realmente tenga prestada esa copia
            if ($user_mbrid != $mbrid) {
                throw new apiError('No tiene un préstamo sobre la copia');
            }

            $copy = new Copy();
            $copy->setCopyid($copyid);
            $copy->setBibid($bibid);
            $copy->setDueBackDt($due_back_dt);
            $copy->setRenewalCount($renewal_count);
            $copy->setDaysLate($days_late);
            $copy->setMbrid($mbrid);
            $copy->setClassification($copy->findClassification());
            $copy->setRenewalLimit($copy->findRenewalLimit());
            
            $renewalcheck = $this->checkRenewal($copy, $copy_model);

            if ($renewalcheck) {
                $this->updateRenewalDate($copy); 
            } else {
                throw new apiError('Esta copia no puede ser renovada');
            }

            //Respondo
            $this->response->setResponse(true);
            $this->response->result = ['message' => "Copia renovada", "date" => $copy->dueBackDt()];

            return $this->response;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function checkRenewal($copy, $copy_model) {
        try {
            //Busco las reservas
            $hold = new HoldModel();
            $holdstm = $hold->getHolds($copy->bibid(), $copy->copyid());
            $holdnum = $holdstm->rowCount();
            //Busco las demás copias
            $bibstm = $copy_model->getCopiesByBibid($copy->bibid());
            $bibnum = $bibstm->rowCount(); //Cuento cuantas son
            $copiesIn = 0;
            //Cuento las copias prestadas
            while ($bibrow = $bibstm->fetch(PDO::FETCH_ASSOC)) {
                extract($bibrow);
                if ($due_back_dt == "") {
                    $copiesIn++;
                }
            }

            $reachedLimit = $copy_model->hasReachedRenewalLimit($copy);
            //Verifico si la copia se puede prestar
            if (!$reachedLimit and $copiesIn != 0 and $bibnum > 1 and $copy->daysLate() == 0 and $holdnum == 0) {
                //Renuevo
                return True;
            } else {
                return False;
            }
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}