<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;

class HoldModel {
    private $db;
    private $biblio_hold_table = "biblio_hold";
    private $biblio_copy_table = "biblio_copy";
    private $biblio_table = "biblio";
    private $response;

    public function __construct() {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function getHolds($bibid, $copyid) {
        //Devuelve la copia si está reservada
        try {
            $stm = $this->db->prepare("SELECT * FROM $this->biblio_hold_table WHERE bibid = :bibid AND copyid = :copyid ORDER BY hold_begin_dt");
            $stm->execute(array(":bibid" => $bibid, ":copyid" => $copyid));
            return $stm;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function get($mbrid) {
        try {
            $result = array();
            $stm = $this->db->prepare("SELECT $this->biblio_hold_table.*, 
                biblio.title, biblio.author,
                biblio.material_cd, biblio_copy.barcode_nmbr,
                biblio_copy.status_cd, biblio_copy.due_back_dt
                FROM $this->biblio_hold_table, $this->biblio_copy_table, $this->biblio_table 
                WHERE $this->biblio_hold_table.bibid = $this->biblio_copy_table.bibid 
                AND $this->biblio_hold_table.copyid = $this->biblio_copy_table.copyid
                AND $this->biblio_hold_table.bibid = $this->biblio_table .bibid
                AND $this->biblio_hold_table.mbrid = ? 
                ORDER BY $this->biblio_hold_table.hold_begin_dt DESC");
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