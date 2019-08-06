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

class Copy{
 
    // database connection and table name
    private $conn;
    private $db;
    private $table_name = "biblio_copy";
 
    // object properties
    public $bibid;
    public $title;
    public $author;
    public $copyid;
    public $renewal_count;
    public $days_late;
    public $due_back_dt;
    //public $category_id;
    //public $category_name;
    //public $created;
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

    public function update() {

        $sql = "UPDATE $this->table_name SET 
                            renewal_count   = ?, 
                            due_back_dt     = ?
                        WHERE copyid = ?";

                $this->db->prepare($sql)
                     ->execute(
                        array(
                            $this->renewalCount(), 
                            $this->dueBackDt(),
                            $this->copyid()
                        )
                    );
    }
 
 
    // constructor with $db as database connection
    public function __construct(){
        //$this->conn = $db;
        $this->db = Database::StartUp();
    }
}


class CopyModel
{
    private $db;
    //private $table = 'biblio';
    private $response;

    public function __CONSTRUCT()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function getCopiesbyBibid($bibid) {

            $stm = $this->db->prepare("select biblio_copy.* ,greatest(0,to_days(sysdate()) - to_days(biblio_copy.due_back_dt)) days_late from biblio_copy where biblio_copy.bibid = " . $bibid);

            $stm->execute(array($bibid));

            return $stm;
    }

    public function getCopiesByMbrid($mbrid) {
            //Busca todas las copias por mbrid
            $stm = $this->db->prepare("SELECT biblio.*,biblio_copy.copyid ,biblio_copy.barcode_nmbr ,biblio_copy.status_cd ,biblio_copy.status_begin_dt ,biblio_copy.due_back_dt ,biblio_copy.mbrid ,biblio_copy.renewal_count ,greatest(0,to_days(sysdate()) - to_days(biblio_copy.due_back_dt)) days_late FROM biblio, biblio_copy WHERE biblio.bibid = biblio_copy.bibid AND biblio_copy.mbrid =" . $mbrid . " AND biblio_copy.status_cd='out' ORDER BY biblio_copy.status_begin_dt desc");

            $stm->execute(array($mbrid));

            return $stm;
    }

    public function getCopy($copyid) {
            //Devuelve la copia por copyid
            $stm = $this->db->prepare("select biblio_copy.*, greatest(0,to_days(sysdate()) - to_days(biblio_copy.due_back_dt)) days_late from biblio_copy where biblio_copy.copyid = " . $copyid);
            
            $stm->execute(array($copyid));

            return $stm;
    }

    public function getDaysDueBack($copy, $_date) {

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
            for ($i=0; $i<$days_due_back; $i++) {
              $date = Date::addDays($date, 1);
              if (Date::isWeekend($date)) {
                $days_due_back++;
              }
            }

            return $days_due_back;
        }


    public function Get($mbrid)
    {
        try
        {
                $result = array();

                $stm = $this->getCopiesByMbrid($mbrid);           
                // products array
                $copy_arr=array();
                $copy_arr["copy"]=array();
             
                // retrieve our table contents
                // fetch() is faster than fetchAll()
                // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
                while ($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    // extract row
                    // this will make $row['name'] to
                    // just $name only
                    extract($row);

                    $copy_item=array(
                        "bibid" => $bibid,
                        "title" => $title,
                        "copyid" => $copyid,
                        "author" => $author,
                        "due_back_dt" => $due_back_dt,
                        "days_late" => $days_late,
                        "renew" => ""
                    );

                    $renewal_count_copy = $renewal_count;
                    $days_late_copy = $days_late;

                    $hold = new HoldModel();
                    $holdstm = $hold->getHolds($bibid, $copyid);
                    $holdnum = $holdstm->rowCount();

                    $bibstm = $this->getCopiesbyBibid($bibid);
                    $bibnum = $bibstm->rowCount();
                    $copiesIn = 0;

                    while ($bibrow = $bibstm->fetch(PDO::FETCH_ASSOC)) {
                        extract($bibrow);
                        if ($due_back_dt == "") {
                          $copiesIn++;
                        }
                    }

                    if ($renewal_count_copy==0 and $copiesIn!=0 and $bibnum>0 and $days_late_copy==0 and $holdnum==0) {
                        $renew = true;
                    } else {
                        $renew = false;
                    }
             
                    $copy_item["renew"] = $renew;
             
                    array_push($copy_arr["copy"], $copy_item);
                }

            $this->response->setResponse(true);
            $this->response->result = $copy_arr;

            return $this->response;
        }
        catch(Exception $e)
        {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }  
    }
}
/*

SELECT biblio.*,biblio_copy.copyid ,biblio_copy.barcode_nmbr ,biblio_copy.status_cd ,biblio_copy.status_begin_dt ,biblio_copy.due_back_dt ,biblio_copy.mbrid ,biblio_copy.renewal_count ,greatest(0,to_days(sysdate()) - to_days(biblio_copy.due_back_dt)) days_late FROM biblio, biblio_copy WHERE biblio.bibid = biblio_copy.bibid AND biblio_copy.mbrid = 2719 AND biblio_copy.status_cd='out' ORDER BY biblio_copy.status_begin_dt desc

*/

/*
Pruebas:

Aceptacion
Instalacion
Alfa y beta
Conformidad
Regresion

Rendimiento
Desgaste
Configuracion
Usabilidad

Integracion:
Big bang
Ascendente
Descendente
De regresion
De humo
*/