<?php

namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use App\Model\BookModel;
use App\Model\HoldModel;
use App\Handlers\ApiError;
use App\Manager\DateManager;
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

// Esta clase representa una copia
class CopyModel
{
    // database connection and table name
    private $response;
    private $db;
    private $biblio_table = "biblio";
    private $biblio_copy_table = "biblio_copy";
    private $checkout_privs_table = "checkout_privs";
    private $member_table = "member";
    private $biblio_hold_table = "biblio_hold";

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
    public function __construct()
    {
        $this->response = new Response();
        $this->db = Database::StartUp();
    }

    public function getBibid()
    {
        return $this->bibid;
    }

    public function getCopyid()
    {
        return $this->copyid;
    }

    public function getRenewalCount()
    {
        return $this->renewal_count;
    }

    public function getDaysLate()
    {
        return $this->days_late;
    }

    public function getDueBackDt()
    {
        return $this->due_back_dt;
    }

    public function getRenewalLimit()
    {
        return $this->renewalLimit;
    }

    public function getMbrid()
    {
        return $this->mbrid;
    }

    public function getClassification()
    {
        return $this->classification;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getRenewableCause()
    {
        return $this->renewable_cause;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getLoanBeginDt()
    {
        return $this->loan_begin_dt;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getLastRenewalBy()
    {
        return $this->last_renewal_by;
    }

    public function setBibid($value)
    {
        $this->bibid = $value;
    }

    public function setCopyid($value)
    {
        $this->copyid = $value;
    }

    public function setRenewalCount($value)
    {
        $this->renewal_count = $value;
    }

    public function setDaysLate($value)
    {
        $this->days_late = $value;
    }

    public function setDueBackDt($value)
    {
        $this->due_back_dt = $value;
    }

    public function setRenewalLimit($value)
    {
        $this->renewalLimit = $value;
    }

    public function setMbrid($value)
    {
        $this->mbrid = $value;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function setAuthor($value)
    {
        $this->author = $value;
    }

    public function setClassification($value)
    {
        $this->classification = $value;
    }

    public function setRenewableCause($value)
    {
        $this->renewable_cause = $value;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function setLoanBeginDt($value)
    {
        $this->loan_begin_dt = $value;
    }

    public function setFilter($value)
    {
        $this->filter = $value;
    }

    public function setLastRenewalBy($value)
    {
        $this->last_renewal_by = $value;
    }

    public function update(): void
    {
        $sql = "UPDATE $this->biblio_copy_table SET renewal_count = ?, due_back_dt = ?, last_renewal_by = ? WHERE copyid = ? AND bibid = ?";
        $this->db->prepare($sql)->execute(array($this->getRenewalCount(), $this->getDueBackDt(), $this->getLastRenewalBy(), $this->getCopyid(), $this->getBibid()));
    }

    //Busca el tipo de material
    public function findClassification(): int
    {
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

    //Busca por cuántas veces puede renovar según la carrera y el tipo de material
    public function findRenewalLimit(): int
    {
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
            if ($stm->rowCount() > 0) {
                $renewal_limit = $stm->fetch()->renewal_limit;
            } else {
                $renewal_limit = 0;
            }
            return $renewal_limit;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    //Busca cuántos días antes del vencimiento del préstamo se puede renovar según la carrera y el tipo de material
    public function findRenewalDelta(): int
    {
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
            if ($stm->rowCount() > 0) {
                $renewal_delta = $stm->fetch()->renewal_delta;
            } else {
                $renewal_delta = 0;
            }
            return $renewal_delta;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getDueBackDays(string $_date): int
    {
        try {
            $book = new BookModel($this->bibid);
            //Busco los días de prestamo para ese libro
            $days_due_back = $book->getDueBackConfiguredDays();
            //Si no se pasó una fecha específica, se usa la actual
            if (is_null($_date)) {
                $date = date('Y-m-d');
            } else {
                $date = $_date;
            }
            //El préstamo es por días habiles. Reviso día a día si es fin de semana, si lo es sumo un día al préstamo.
            for ($i = 0; $i < $days_due_back; $i++) {
                $date = DateManager::addDays($date, 1);
                if (DateManager::isWeekend($date)) {
                    $days_due_back++;
                }
            }
            return $days_due_back;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function hasReachedRenewalLimit(): bool
    {
        if($this->getRenewalLimit() == 0) {
            //0 = unlimited
            return false;
        }
        if($this->getRenewalCount()/24 < $this->getRenewalLimit()) {
            return false;
        } else {
            return true;
        }
    }

    public function getHoldsNmbr(): int
    {
        //Devuelve la cantidad de reservas que tiene la copia
        try {
            $stm = $this->db->prepare("SELECT * FROM $this->biblio_hold_table WHERE bibid = :bibid AND copyid = :copyid ORDER BY hold_begin_dt");
            $stm->execute(array(":bibid" => $this->bibid, ":copyid" => $this->copyid));
            return $stm->rowCount();
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}
