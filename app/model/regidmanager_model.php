<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;

class RegIDManager
{
    private $db;
    private $table = "fcm_regid";
    private $response;

    public function __CONSTRUCT()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function saveRegID($regid, $mbrid) {
     
            $stm = $this->db->prepare("INSERT INTO " . $this->table . " (mbrid, regid) VALUES(" . $mbrid . ", '" . $regid . "') ON DUPLICATE KEY UPDATE mbrid=" . $mbrid . ", regid='" . $regid . "'");

            //INSERT INTO fcm_regid (mbrid, regid) VALUES(2371, 'hola') ON DUPLICATE KEY UPDATE mbrid=2371, regid='hola'

            $stm->execute();

            $this->response->setResponse(true);

            return $this->response;
    }


    public function getOverdueRegids()
    {
        try
        {
            $result = array();

            //select c.bibid, c.copyid, m.mbrid, c.barcode_nmbr, f.regid, concat_ws(' ', b.call_nmbr1, b.call_nmbr2, b.call_nmbr3) as callno, b.title, b.author, //c.status_begin_dt, c.due_back_dt, m.barcode_nmbr member_bcode, concat(m.last_name, ', ', m.first_name) name, floor(to_days(now())-to_days(c.due_back_dt)) days_late //from biblio b, biblio_copy c, member m, fcm_regid f where b.bibid = c.bibid and c.mbrid = m.mbrid and c.status_cd = 'out' and c.mbrid = f.mbrid 

            //select r.title, r.regid, SUM(if(days_late > 0, 1, 0)) AS late FROM (select c.bibid, c.copyid, m.mbrid, c.barcode_nmbr, f.regid, concat_ws(' ', b.call_nmbr1, b.call_nmbr2, b.call_nmbr3) as callno, b.title, b.author, c.status_begin_dt, c.due_back_dt, m.barcode_nmbr member_bcode, concat(m.last_name, ', ', m.first_name) name, floor(to_days(now())-to_days(c.due_back_dt)) days_late from biblio b, biblio_copy c, member m, fcm_regid f where b.bibid = c.bibid and c.mbrid = m.mbrid and c.status_cd = 'out' and c.mbrid = f.mbrid) AS r GROUP BY r.regid 

            // Vamos a obtener todos los regids de los mostros que deben libros junto con la cantidad de libros que deben
            // Para que te diviertas leyendo SQL
            $stm = $this->db->prepare("select r.regid, SUM(if(days_late > 0, 1, 0)) AS late_books FROM (select c.bibid, c.copyid, m.mbrid, c.barcode_nmbr, f.regid, concat_ws(' ', b.call_nmbr1, b.call_nmbr2, b.call_nmbr3) as callno, b.title, b.author, c.status_begin_dt, c.due_back_dt, m.barcode_nmbr member_bcode, concat(m.last_name, ', ', m.first_name) name, floor(to_days(now())-to_days(c.due_back_dt)) days_late from biblio b, biblio_copy c, member m, fcm_regid f where b.bibid = c.bibid and c.mbrid = m.mbrid and c.status_cd = 'out' and c.mbrid = f.mbrid) AS r GROUP BY r.mbrid");
            $stm->execute();

            //$this->response->setResponse(true);
            //$this->response->result = $stm->fetchall();

            return $stm;
        }
        catch(Exception $e)
        {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }  
    }
}

