<?php

namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;

class UserModel
{
    private $db;
    //private $table = 'member';
    private $response;

    public function __CONSTRUCT()
    {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function getUserbyEmail($email) {
     
		$sql = "SELECT * FROM member WHERE email= :email";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("email", $email);
		$sth->execute();
		$user = $sth->fetchObject();

        return $user;
    }

    public function getUserbyFileNumber($filenmbr) {
     
        $sql = "SELECT * FROM member WHERE legajo= :legajo";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("legajo", $filenmbr);
        $sth->execute();
        $user = $sth->fetchObject();

        return $user;
    }

    public function getUserbyMbrid($mbrid) {

        try{

            $sql = "SELECT * FROM member WHERE mbrid=" . $mbrid;
            $sth = $this->db->prepare($sql);
            $sth->execute();
            $user = $sth->fetchObject();

            return $user;

        } catch(Exception $e) {

            $this->response->setResponse(false, $e->getMessage());
            return $this->response;

        }  
    }

   public function changePass($newpass, $mbrid) {

        try{

            $sql = "UPDATE member SET pass_user = '" . $newpass . "' WHERE mbrid=" . $mbrid;
            $sth = $this->db->prepare($sql);
            $sth->execute();
            $this->response->setResponse(true);

            return $this->response;

        } catch(Exception $e) {

            $this->response->setResponse(false, $e->getMessage());
            return $this->response;

        }  
    }


}