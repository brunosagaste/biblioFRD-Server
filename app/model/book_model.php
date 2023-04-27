<?php

namespace App\Model;

use App\Lib\Database;
use App\Handlers\ApiError;
use PDO;
use stdClass;

# Esta clase representa un libro
class BookModel
{
    private $db;
    private $biblio_table = 'biblio';
    private $biblio_copy_table = "biblio_copy";
    private $collection_table = 'collection_dm';
    private $response;

    public $bibid;
    public $create_dt;
    public $last_change_dt;
    public $last_change_userid;
    public $material_cd;
    public $collection_cd;
    public $call_nmbr1;
    public $call_nmbr2;
    public $call_nmbr3;
    public $title;
    public $title_reminder;
    public $responsibility_stmt;
    public $author;
    public $topic1;
    public $topic2;
    public $topic3;
    public $topic4;
    public $topic5;
    public $opac_flg;
    public $has_cover;

    public function __construct(int $bibid)
    {
        $this->db = Database::StartUp();
        $book = $this->getBook($bibid);
        $this->bibid = $bibid;
        $this->create_dt = $book->create_dt;
        $this->last_change_dt = $book->last_change_dt;
        $this->last_change_userid = $book->last_change_userid;
        $this->material_cd = $book->material_cd;
        $this->collection_cd = $book->collection_cd;
        $this->call_nmbr1 = $book->call_nmbr1;
        $this->call_nmbr2 = $book->call_nmbr2;
        $this->call_nmbr3 = $book->call_nmbr3;
        $this->title = $book->title;
        $this->title_reminder = $book->title_remainder;
        $this->responsibility_stmt = $book->responsibility_stmt;
        $this->author = $book->author;
        $this->topic1 = $book->topic1;
        $this->topic2 = $book->topic2;
        $this->topic3 = $book->topic3;
        $this->topic4 = $book->topic4;
        $this->topic5 = $book->topic5;
        $this->opac_flg = $book->opac_flg;
        $this->has_cover = $book->has_cover;
    }

    private function getBook(int $bibid): stdClass
    {
        try {
            $result = array();
            $stm = $this->db->prepare("SELECT * FROM $this->biblio_table WHERE bibid = :bibid");
            $stm->bindParam(":bibid", $bibid);
            $stm->execute();
            if ($stm->rowCount()==0) {
                throw new ApiError("No book with id " . $bibid);
            }
            return $stm->fetch();
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getCopies(): array
    {
        try {
            $stm = $this->db->prepare("SELECT $this->biblio_copy_table.*, greatest(0,to_days(sysdate()) - to_days($this->biblio_copy_table.due_back_dt)) days_late FROM $this->biblio_copy_table WHERE $this->biblio_copy_table.bibid = :bibid");
            $stm->bindParam(":bibid", $this->bibid);
            $stm->execute();
            $copies = [];
            while ($copyrow = $stm->fetch(PDO::FETCH_ASSOC)) {
                extract($copyrow);
                $copy = new CopyModel();
                $copy->setBibid($bibid);
                $copy->setCopyid($copyid);
                $copy->setRenewalCount($renewal_count);
                $copy->setMbrid($mbrid);
                $copy->setTitle($this->title);
                $copy->setAuthor($this->author);
                $copy->setLoanBeginDt($status_begin_dt);
                $copy->setLastRenewalBy($last_renewal_by);
                array_push($copies, $copy);
            }
            return $copies;
        } catch(Exception $e) {
            throw new ApiError($e);
        }
    }

    public function getDueBackConfiguredDays(): int
    {
        // first get collection code
        $stm = $this->db->prepare("SELECT collection_cd FROM $this->biblio_table WHERE bibid = :bibid");
        $stm->bindParam(":bibid", $this->bibid);
        $stm->execute();
        if ($stm->rowCount()==0) {
            throw new ApiError("No collection information available");
        }
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        extract($row);
        // now read collection domain for days due back
        $stm = $this->db->prepare("SELECT * FROM $this->collection_table WHERE code = :collection_cd");
        $stm->bindParam(":collection_cd", $collection_cd);
        $stm->execute();
        if ($stm->rowCount()==0) {
            throw new ApiError("No collection information available");
        }
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        extract($row);
        return $days_due_back;
    }
}
