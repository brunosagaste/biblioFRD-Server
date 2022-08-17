<?php

namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;

class UserModel {
    private $db;
    private $member_table = 'member';
    private $response;

    public function __construct() {
        $this->db = Database::StartUp();
        $this->response = new Response();
    }

    public function getUserbyEmail($email) {
        try {
            $sql = "SELECT * FROM $this->member_table WHERE email = :email";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(":email", $email);
            $sth->execute();
            $user = $sth->fetchObject();
            return $user;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }  
    }

    public function getUserbyFileNumber($filenmbr) {
        try {
            $sql = "SELECT * FROM $this->member_table WHERE legajo = :legajo";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(":legajo", $filenmbr);
            $sth->execute();
            $user = $sth->fetchObject();
            return $user;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }  
    }

    public function getUserbyMbrid($mbrid) {
        try {
            $sql = "SELECT * FROM $this->member_table WHERE mbrid = :mbrid";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(":mbrid", $mbrid);
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
            $sql = "UPDATE $this->member_table SET pass_user = :newpass WHERE mbrid = :mbrid";
            $sth = $this->db->prepare($sql);
            $sth->execute(array(":newpass" => $newpass, ":mbrid" => $mbrid));
            $this->response->setResponse(true);
            return $this->response;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }  
    }
}