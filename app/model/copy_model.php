<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use App\Model\BookModel;
use App\Model\HoldModel;
use App\Model\Date;
use App\Handlers\apiError;
use PDO;

/*
Recibo el mbrid
Primero verifico que no haya sido prestada con anterioridad
Después verifico que no esté vencida
Después verifico que no sea la única copia
Después verifico que no estén todas las copias prestadas
Después verifico que no esté reservada
Enconces envio la información de la copia
*/

// Básicamente un copy paste de la clase Copy del sistema original. Algún día habría que fusionarla con CopyModel, representan lo mismo.
class Copy {
 
    // database connection and table name
    private $conn;
    private $db;
    private $biblio_table = "biblio";
    private $biblio_copy_table = "biblio_copy";
    private $checkout_privs_table = "checkout_privs";
    private $member_table = "member";
 
    // object properties
    public $bibid;
    public $title;
    public $author;
    public $copyid;
    public $renewal_count;
    public $days_late;
    public $due_back_dt;
    public $renewalLimit;
    public $mbrid;
    public $classification;
    public $renewable_cause;
    public $status;
    public $loan_begin_dt;
    public $filter;
    public $last_renewal_by;

    // constructor with $db as database connection
    public function __construct(){
        $this->db = Database::StartUp();
    }

    function bibid() {
        return $this->bibid;
    }

    function copyid() {
        return $this->copyid;
    }

    function renewalCount() {
        return $this->renewal_count;
    }

    function daysLate() {
        return $this->days_late;
    }

    function dueBackDt() {
        return $this->due_back_dt;
    }

    function renewalLimit() {
        return $this->renewalLimit;
    }

    function mbrid() {
        return $this->mbrid;
    }

    function classification() {
        return $this->classification;
    }

    function title() {
        return $this->title;
    }

    function author() {
        return $this->author;
    }

    function renewableCause() {
        return $this->renewable_cause;
    }

    function status() {
        return $this->status;
    }

    function loanBeginDt() {
        return $this->loan_begin_dt;
    }

    function filter() {
        return $this->filter;
    }

    function lastRenewalBy() {
        return $this->last_renewal_by;
    }

    function setBibid($value) {
        $this->bibid = $value;
    }

    function setCopyid($value) {
        $this->copyid = $value;
    }

    function setRenewalCount($value) {
        $this->renewal_count = $value;
    }

    function setDaysLate($value) {
        $this->days_late = $value;
    }

    function setDueBackDt($value) {
        $this->due_back_dt = $value;
    }

    function setRenewalLimit($value) {
        $this->renewalLimit = $value;
    }

    function setMbrid($value) {
        $this->mbrid = $value;
    }

    function setTitle($value) {
        $this->title = $value;
    }

    function setAuthor($value) {
        $this->author = $value;
    }

    function setClassification($value) {
        $this->classification = $value;
    }

    function setRenewableCause($value) {
        $this->renewable_cause = $value;
    }

    function setStatus($value) {
        $this->status = $value;
    }

    function setLoanBeginDt($value) {
        $this->loan_begin_dt = $value;
    }

    function setFilter($value) {
        $this->filter = $value;
    }  
    
    function setLastRenewalBy($value) {
        $this->last_renewal_by = $value;
    }

    public function update() {
        $sql = "UPDATE $this->biblio_copy_table SET renewal_count = ?, due_back_dt = ?, last_renewal_by = ? WHERE copyid = ? AND bibid = ?";
        $this->db->prepare($sql)->execute(array($this->renewalCount(), $this->dueBackDt(), $this->lastRenewalBy(), $this->copyid(), $this->bibid()));
    }

    //Busca el tipo de material
    public function findClassification() {
        try {
            $stm = $this->db->prepare("SELECT classification FROM $this->member_table WHERE mbrid = $this->mbrid");
            $stm->execute();
            $classification = $stm->fetch()->classification;
            return $classification;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    //Busca por cuántos días se puede renovar según la carrera y el tipo de material
    public function findRenewalLimit() {
        try {
            $stm = $this->db->prepare("SELECT 
                $this->checkout_privs_table.renewal_limit
                FROM $this->biblio_copy_table
                LEFT JOIN $this->biblio_table ON $this->biblio_table.bibid = $this->biblio_copy_table.bibid
                LEFT JOIN $this->checkout_privs_table ON $this->biblio_table.material_cd = $this->checkout_privs_table.material_cd
                WHERE $this->biblio_copy_table.copyid = :copyid
                AND $this->biblio_copy_table.bibid = :bibid
                AND $this->checkout_privs_table.classification = :classification");
            $stm->execute(array(":copyid" => $this->copyid, ":bibid" => $this->bibid, ":classification" => $this->classification));
            $renewal_limit = $stm->fetch()->renewal_limit;
            return $renewal_limit;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    //Busca cuántos días antes del vencimiento del préstamo se puede renovar según la carrera y el tipo de material
    public function findRenewalDelta() {
        try {
            $stm = $this->db->prepare("SELECT 
                $this->checkout_privs_table.renewal_delta
                FROM $this->biblio_copy_table
                LEFT JOIN $this->biblio_table ON $this->biblio_table.bibid = $this->biblio_copy_table.bibid
                LEFT JOIN $this->checkout_privs_table ON $this->biblio_table.material_cd = $this->checkout_privs_table.material_cd
                WHERE $this->biblio_copy_table.copyid = :copyid
                AND $this->biblio_copy_table.bibid = :bibid
                AND $this->checkout_privs_table.classification = :classification");
            $stm->execute(array(":copyid" => $this->copyid, ":bibid" => $this->bibid, ":classification" => $this->classification));
            $renewal_delta = $stm->fetch()->renewal_delta;
            return $renewal_delta;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}

class CopyModel {
    private $db;
    private $biblio_table = "biblio";
    private $biblio_copy_table = "biblio_copy";
    private $checkout_privs_table = "checkout_privs";
    private $response;

    public function __construct() {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function getCopiesbyBibid($bibid) {
        try {
            $stm = $this->db->prepare("SELECT $this->biblio_copy_table.*, greatest(0,to_days(sysdate()) - to_days($this->biblio_copy_table.due_back_dt)) days_late FROM $this->biblio_copy_table WHERE $this->biblio_copy_table.bibid = :bibid");
            $stm->bindParam(":bibid", $bibid);
            $stm->execute();
            return $stm;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getCopiesByMbrid($mbrid) {
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

    public function getCopy($bibid, $copyid) {
        try {
            //Devuelve la copia por copyid
            $stm = $this->db->prepare("SELECT 
                $this->biblio_copy_table.*, 
                greatest(0,to_days(sysdate()) - to_days($this->biblio_copy_table.due_back_dt)) AS days_late
                FROM $this->biblio_copy_table 
                WHERE $this->biblio_copy_table.copyid = :copyid
                AND $this->biblio_copy_table.bibid = :bibid");
            $stm->execute(array(":copyid" => $copyid, ":bibid" => $bibid));
            return $stm;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getDaysDueBack($copy, $_date) {
        try {
            $book = new BookModel();
            //Busco los días de prestamo para ese libro
            $days_due_back = $book->getCollectionInfo($copy->bibid());
            //Check for a date. If it is null use the current date. 
            if (is_null($_date)) {
                $date = date('Y-m-d');
            } else {
                $date = $_date;
            }
            //El préstamo es por días habiles. Reviso día a día si es fin de semana, si lo es sumo un día al préstamo.
            for ($i = 0; $i < $days_due_back; $i++) {
                $date = Date::addDays($date, 1);
                if (Date::isWeekend($date)) {
                    $days_due_back++;
                }
            }
            return $days_due_back;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function get($mbrid, $reqStatus) {
        try {
            $result = array();

            $stm = $this->getCopiesByMbrid($mbrid);           
            $copy_arr = array();
            
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {

                extract($row);
                $copy = new Copy();
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
                
                $renewalModel = new RenewalModel();
                $renewalcheck = $renewalModel->checkRenewal($copy, $this);

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
                
                if ($reqStatus == $copy->filter() or $reqStatus == "Todos" or $reqStatus == null) {
                    array_push($copy_arr, $copy);
                }
            }

            $this->response->setResponse(true);
            $this->response->result = $copy_arr;
            return $this->response;
    
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function hasReachedRenewalLimit($copy) {
        if($copy->renewalLimit() == 0) {
            //0 = unlimited
            return False;
        }
        if($copy->renewalCount()/24 < $copy->renewalLimit()) {
            return False;
        } else {
            return True;
        }
    }
}
/*

SELECT biblio.*,biblio_copy.copyid ,biblio_copy.barcode_nmbr ,biblio_copy.status_cd ,biblio_copy.status_begin_dt ,biblio_copy.due_back_dt ,biblio_copy.mbrid ,biblio_copy.renewal_count ,greatest(0,to_days(sysdate()) - to_days(biblio_copy.due_back_dt)) days_late FROM biblio, biblio_copy WHERE biblio.bibid = biblio_copy.bibid AND biblio_copy.mbrid = 2719 AND biblio_copy.status_cd='out' ORDER BY biblio_copy.status_begin_dt desc


            $stm = $this->db->prepare("SELECT $this->biblio_copy_table.*, 
                greatest(0,to_days(sysdate()) - to_days($this->biblio_copy_table.due_back_dt)) AS days_late,
                $this->checkout_privs_table.renewal_limit
                FROM $this->biblio_copy_table 
                LEFT JOIN $this->biblio_table ON $this->biblio_copy_table.bibid = $this->biblio_table.bibid
                LEFT JOIN $this->checkout_privs_table ON $this->biblio_table.material_cd = $this->checkout_privs_table.material_cd
                WHERE $this->biblio_copy_table.copyid = :copyid
                AND $this->biblio_table.bibid = :bibid");

*/
