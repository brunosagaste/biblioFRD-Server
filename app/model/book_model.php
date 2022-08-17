<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use PDO;

class BookModel {
    private $db;
    private $table = 'biblio';
    private $response;

    public function __construct() {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function get($bibid) {
        try {
            $result = array();
            $stm = $this->db->prepare("SELECT * FROM $this->table WHERE bibid = :bibid");
            $stm->bindParam(":bibid", $bibid);
            $stm->execute();

            $this->response->setResponse(true);
            $this->response->result = $stm->fetch();
            return $this->response;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }  
    }

    public function getCollectionInfo($bibid) {
        // first get collection code
        $stm = $this->db->prepare("SELECT collection_cd FROM biblio WHERE bibid = :bibid");
        $stm->bindParam(":bibid", $bibid);
        $stm->execute();
        if ($stm->rowCount()==0) {
            throw new apiError("No collection info");
        }
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        extract($row);
        // now read collection domain for days due back
        $stm = $this->db->prepare("SELECT * FROM collection_dm WHERE code = :collection_cd");
        $stm->bindParam(":collection_cd", $collection_cd);
        $stm->execute();
        if ($stm->rowCount()==0) {
          throw new apiError("No collection info");
        }
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        extract($row);
        return $days_due_back;
    }
}
