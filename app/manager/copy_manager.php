<?php

namespace App\Manager;

use App\Lib\Database;
use App\Lib\Response;
use App\Model\BookModel;
use App\Model\HoldModel;
use App\Model\CopyModel;
use App\Model\Date;
use App\Handlers\ApiError;
use DateTime;
use PDO;
use stdClass;

// Esta clase permite obtener una o varias copias al mismo tiempo usando un dato relacionado a ellas
// Se usa para instanciar CopyModel en todos los casos donde no tiene sentido instanciarlo a través de BookModel,
// porque se quiere una sola copia o todas las copias de un usuario (este método podría moverse al usuario), y no todas las copias de un libro.
class CopyManager
{
    private $db;
    private $biblio_table = "biblio";
    private $biblio_copy_table = "biblio_copy";
    private $response;

    public function __construct()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    private function getCopiesDataByMbrid(int $mbrid): \PDOStatement
    {
        try {
            //Busca todas las copias por mbrid
            $stm = $this->db->prepare("SELECT $this->biblio_table.*, $this->biblio_copy_table.copyid ,$this->biblio_copy_table.barcode_nmbr, $this->biblio_copy_table.status_cd, $this->biblio_copy_table.status_begin_dt, $this->biblio_copy_table.due_back_dt, $this->biblio_copy_table.mbrid, $this->biblio_copy_table.renewal_count, greatest(0,to_days(sysdate()) - to_days($this->biblio_copy_table.due_back_dt)) days_late FROM $this->biblio_table, $this->biblio_copy_table WHERE $this->biblio_table.bibid = $this->biblio_copy_table.bibid AND $this->biblio_copy_table.mbrid = :mbrid AND $this->biblio_copy_table.status_cd='out' ORDER BY $this->biblio_copy_table.status_begin_dt desc");
            $stm->bindParam(":mbrid", $mbrid);
            $stm->execute();
            return $stm;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getSpecificCopy(int $bibid, int $copyid): CopyModel
    {
        try {
            //Devuelve la copia por bibid y copyid
            //Nunca utilizar solo copyid porque podría estar repetido
            //La clave primaria es bibid + copyid
            $stm = $this->db->prepare("SELECT 
                $this->biblio_copy_table.*,
                greatest(0,to_days(sysdate()) - to_days($this->biblio_copy_table.due_back_dt)) AS days_late
                FROM $this->biblio_copy_table 
                WHERE $this->biblio_copy_table.copyid = :copyid
                AND $this->biblio_copy_table.bibid = :bibid");
            $stm->execute(array(":copyid" => $copyid, ":bibid" => $bibid));
            //Verifico que la copia exista
            if ($stm->rowCount()==0) {
                throw new ApiError('La copia no existe');
            }
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            extract($row);
            $copy = new CopyModel();
            $copy->setCopyid($copyid);
            $copy->setBibid($bibid);
            $copy->setDueBackDt($due_back_dt);
            $copy->setRenewalCount($renewal_count);
            $copy->setDaysLate($days_late);
            $copy->setMbrid($mbrid);
            $copy->setClassification($copy->findClassification());
            $copy->setRenewalLimit($copy->findRenewalLimit());

            return $copy;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    // Obtener copias del socio utilizando su id
    public function getCopiesByMbrid(int $mbrid, ?string $reqStatus): array //array de CopyModel
    {
        try {
            $result = array();

            $stm = $this->getCopiesDataByMbrid($mbrid);
            $copy_arr = array();

            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {

                extract($row);
                $copy = new CopyModel();
                $copy->setCopyid($copyid);
                $copy->setBibid($bibid);
                $copy->setDueBackDt($due_back_dt);
                $copy->setRenewalCount($renewal_count);
                $copy->setDaysLate($days_late);
                $copy->setMbrid($mbrid);
                $copy->setAuthor($author);
                $copy->setTitle($title);
                $copy->setClassification($copy->findClassification());
                $copy->setRenewalLimit($copy->findRenewalLimit());
                $copy->setLoanBeginDt($status_begin_dt);
                //Evitar setear LastRenewalBy para no exponer el dato

                //RenewalManager nos va a decir en que estado está el préstamo para mostrar
                //el botón de renovar o setear el correcto color de tarjeta en la app
                $renewalModel = new RenewalManager();
                $renewalcheck = $renewalModel->checkRenewal($copy);

                if ($renewalcheck['result']) {
                    //Podemos renovar
                    $copy->setRenewableCause($renewalcheck['cause']);
                    $copy->setStatus("Renovable");
                    $copy->setFilter("renewable");
                } else {
                    //No podemos renovar
                    $copy->setRenewableCause($renewalcheck['cause']);
                    $copy->setFilter("nonrenewable");
                    if ($renewalcheck['cause'] == "overdue") {
                        //No podemos renovar por estar vencido
                        $copy->setStatus("Vencido");
                        $copy->setRenewableCause("overdue");
                        $copy->setFilter("overdue");
                    } elseif ($renewalcheck['cause'] == 'date') {
                        //No podemos renovar por estar fuera de fecha
                        $copy->setStatus("Renovable a partir del " . $renewalcheck['dateavailable']);
                    } else {
                        //No podemos renovar por otra cosa
                        $copy->setStatus("No renovable");
                    }
                }

                if ($reqStatus == $copy->getFilter() or $reqStatus == "Todos" or $reqStatus == null) {
                    array_push($copy_arr, $copy);
                }
            }
            return $copy_arr;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}
