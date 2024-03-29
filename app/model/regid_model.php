<?php

namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use PDO;

class RegIDModel
{
    private $db;
    private $fcm_regid_table = "fcm_regid";
    private $biblio_table = "biblio";
    private $biblio_copy_table = "biblio_copy";
    private $member_table = "member";
    private $response;

    private $reminders;
    private $overdues;

    public function __construct()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
        $this->reminders = $this->getReminderRegidsData();
        $this->overdues = $this->getOverdueRegidsData();
    }

    public function getReminderRegids(): array
    {
        return $this->reminders;
    }

    public function getOverdueRegids(): array
    {
        return $this->overdues;
    }

    public function saveRegID(string $regid, int $mbrid): Response
    {
        try {
            $stm = $this->db->prepare("INSERT INTO $this->fcm_regid_table (mbrid, regid) VALUES(:mbrid, :regid) ON DUPLICATE KEY UPDATE mbrid = :mbrid, regid = :regid");
            $stm->execute(array(":mbrid" => $mbrid, ":regid" => $regid));
            $this->response->setResponse(true);
            return $this->response;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getOverdueRegidsData(): array
    {
        try {
            // Vamos a obtener todos los regids de los socios que tienen préstamos vencidos junto con su cantidad
            $stm = $this->db->prepare("SELECT 
                r.regid, 
                SUM(if(days_late > 0, 1, 0)) AS late_books 
                FROM (
                    SELECT
                    c.bibid, 
                    c.copyid, 
                    m.mbrid,
                    c.barcode_nmbr, 
                    f.regid, 
                    concat_ws(' ', b.call_nmbr1, b.call_nmbr2, b.call_nmbr3) AS callno, 
                    b.title, 
                    b.author, 
                    c.status_begin_dt, 
                    c.due_back_dt, 
                    m.barcode_nmbr member_bcode, 
                    concat(m.last_name, ', ', m.first_name) name,
                    floor(to_days(now())-to_days(c.due_back_dt)) days_late
                    FROM $this->biblio_table b, $this->biblio_copy_table c, $this->member_table m, $this->fcm_regid_table f
                    WHERE b.bibid = c.bibid AND c.mbrid = m.mbrid AND c.status_cd = 'out' AND c.mbrid = f.mbrid
                ) AS r
                GROUP BY r.mbrid");
            $stm->execute();

            $regids = array();
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $regid = array(
                    'regid' => $regid,
                    'late_books' => $late_books
                );
                array_push($regids, $regid);
            }
            return $regids;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    public function getReminderRegidsData(): array
    {
        try {
            // Vamos a obtener todos los regids de los socios que tienen un préstamo que vence mañana
            $stm = $this->db->prepare("SELECT 
                reminders.regid, 
                COUNT(copyid) AS remind_books 
                FROM (
                    SELECT
                    c.bibid,
                    c.copyid, 
                    m.mbrid,
                    c.barcode_nmbr, 
                    f.regid, 
                    concat_ws(' ', b.call_nmbr1, b.call_nmbr2, b.call_nmbr3) AS callno, 
                    b.title, 
                    b.author, 
                    c.status_begin_dt, 
                    c.due_back_dt, 
                    m.barcode_nmbr member_bcode, 
                    concat(m.last_name, ', ', m.first_name) name,
                    floor(to_days(now()) - to_days(c.due_back_dt)) days_late
                    FROM $this->biblio_table b, $this->biblio_copy_table c, $this->member_table m, $this->fcm_regid_table f
                    WHERE b.bibid = c.bibid
                    AND c.mbrid = m.mbrid 
                    AND c.status_cd = 'out'
                    AND c.mbrid = f.mbrid
                    AND c.due_back_dt = DATE_ADD(CURDATE(), INTERVAL 1 DAY)) AS reminders
                GROUP BY reminders.mbrid;");
            $stm->execute();

            $regids = array();
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $regid = array(
                    'regid' => $regid,
                    'remind_books' => $remind_books
                );
                array_push($regids, $regid);
            }
            return $regids;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }
}
