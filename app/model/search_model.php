<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use PDO;

class SearchModel {
    private $db;
    private $biblio_table = "biblio";
    private $biblio_copy_table = "biblio_copy";
    private $response;

    public function __construct() {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function search($text) {
        try {
            $result = array();
            $stm = $this->db->prepare("SELECT 
                *, 
                SUM(IF($this->biblio_copy_table.status_cd != 'out', 1, 0)) AS copy_free,
                MATCH(`title`, `author`) AGAINST(:text IN NATURAL LANGUAGE MODE) AS bibidorder
                FROM $this->biblio_table 
                LEFT JOIN $this->biblio_copy_table 
                ON $this->biblio_table.bibid = $this->biblio_copy_table.bibid 
                WHERE MATCH(title, author) AGAINST (:text IN NATURAL LANGUAGE MODE) 
                GROUP BY $this->biblio_table.bibid
                ORDER BY bibidorder DESC");
            $stm->bindParam(":text", $text);
            $stm->execute();

            $this->response->setResponse(true);
            $this->response->result = $stm->fetchall();
            return $this->response;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}