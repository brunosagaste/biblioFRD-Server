<?php
namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use PDO;

class SearchModel
{
    private $db;
    private $table = 'biblio';
    private $response;

    public function __CONSTRUCT()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }


    public function Search($text)
    {
        try
        {
            $result = array();

            $stm = $this->db->prepare("SELECT *, SUM(if(biblio_copy.status_cd != 'out', 1, 0)) AS copy_free FROM biblio LEFT JOIN biblio_copy ON biblio.bibid = biblio_copy.bibid WHERE MATCH(title, author) AGAINST ('" . $text . "*' IN BOOLEAN MODE) GROUP BY biblio.bibid ORDER BY biblio.bibid DESC");
            $stm->execute(array($text));

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