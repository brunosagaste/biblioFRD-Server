<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;

class HoldModel
{
    private $db;
    //private $table = 'biblio';
    private $response;

    public function __CONSTRUCT()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function getHolds($bibid, $copyid) {
            //Devuelve la copia si está reservada
            $stm = $this->db->prepare("select * from biblio_hold where bibid = " . $bibid . " and copyid = " . $copyid . " order by hold_begin_dt");

            $stm->execute(array($bibid));

            return $stm;
    }


    public function Get($mbrid)
    {
        try
        {
            $result = array();

            $stm = $this->db->prepare("select biblio_hold.*, "
                        . "biblio.title, biblio.author, "
                        . "biblio.material_cd, biblio_copy.barcode_nmbr, "
                        . "biblio_copy.status_cd, biblio_copy.due_back_dt "
                        . "from biblio_hold, biblio_copy, biblio "
                        . "where biblio_hold.bibid = biblio_copy.bibid "
                        . "and biblio_hold.copyid = biblio_copy.copyid "
                        . "and biblio_hold.bibid = biblio.bibid "
                        . "and biblio_hold.mbrid = " . $mbrid 
                        . " order by biblio_hold.hold_begin_dt desc");
            $stm->execute(array($mbrid));

            $this->response->setResponse(true);
            $this->response->result = $stm->fetchall();

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
select biblio_hold.*, biblio.title, biblio.author, biblio.material_cd, biblio_copy.barcode_nmbr, biblio_copy.status_cd, biblio_copy.due_back_dt from biblio_hold, biblio_copy, biblio where biblio_hold.bibid = biblio_copy.bibid and biblio_hold.copyid = biblio_copy.copyid and biblio_hold.bibid = biblio.bibid and biblio_hold.mbrid = 2719 order by biblio_hold.hold_begin_dt desc 
*/