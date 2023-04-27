<?php

namespace App\Model;

use App\Lib\Database;
use App\Lib\Response;
use stdClass;

class UserModel
{
    private $db;
    private $member_table = 'member';
    private $response;

    private $mbrid;
    private $email;
    private $password;
    private $first_name;
    private $last_name;
    private $filenmbr;
    private $address;
    private $city;
    private $home_phone;
    private $dni;

    public function __construct(int|string|bool $user_id, int|bool $mbrid = false)
    {
        $this->db = Database::StartUp();
        $this->response = new Response();

        if (strpos($user_id, "@")) {
            $this->getUserbyEmail($user_id);
        }
        if (is_numeric($user_id)) {
            $this->getUserbyFileNumber($user_id);
        }
        if (!$user_id) {
            $this->getUserbyMbrid($mbrid);
        }
    }
    public function getMbrid(): ?int
    {
        return $this->mbrid;
    }
    public function setMbrid(?int $mbrid): void
    {
        $this->mbrid = $mbrid;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }
    public function setFirstName(?string $first_name): void
    {
        $this->first_name = mb_convert_encoding($first_name, 'UTF-8', 'UTF-8');
    }
    public function getLastName(): ?string
    {
        return $this->last_name;
    }
    public function setLastName(?string $last_name): void
    {
        $this->last_name = mb_convert_encoding($last_name, 'UTF-8', 'UTF-8');
        ;
    }
    public function getFilenmbr(): ?int
    {
        return $this->filenmbr;
    }
    public function setFilenmbr(?int $filenmbr): void
    {
        $this->filenmbr = $filenmbr;
    }
    public function getAddress(): ?string
    {
        return $this->address;
    }
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }
    public function getCity(): ?string
    {
        return $this->city;
    }
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }
    public function getHomePhone(): ?string
    {
        return $this->home_phone;
    }
    public function setHomePhone(?string $home_phone): void
    {
        $this->home_phone = $home_phone;
    }
    public function getDni(): ?int
    {
        return $this->dni;
    }
    public function setDni(?int $dni): void
    {
        $this->dni = $dni;
    }
    private function setAttributes(stdClass|bool $user): void
    {
        if ($user) {
            $this->setMbrid($user->mbrid);
            $this->setEmail($user->email);
            $this->setPassword($user->pass_user);
            $this->setFirstName($user->first_name);
            $this->setLastName($user->last_name);
            $this->setFilenmbr($user->legajo);
            $this->setAddress($user->address);
            $this->setCity($user->city);
            $this->setHomePhone($user->home_phone);
            $this->setDni($user->dni);
        }
    }


    private function getUserbyEmail(string $email): bool|Response
    {
        try {
            $sql = "SELECT * FROM $this->member_table WHERE email = :email";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(":email", $email);
            $sth->execute();
            $user = $sth->fetchObject();
            $this->setAttributes($user);
            return true;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    private function getUserbyFileNumber(string $filenmbr): bool|Response
    {
        try {
            $sql = "SELECT * FROM $this->member_table WHERE legajo = :legajo";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(":legajo", $filenmbr);
            $sth->execute();
            $user = $sth->fetchObject();
            $this->setAttributes($user);
            return true;
        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

    private function getUserbyMbrid(string $mbrid): bool|Response
    {
        try {
            $sql = "SELECT * FROM $this->member_table WHERE mbrid = :mbrid";
            $sth = $this->db->prepare($sql);
            $sth->bindParam(":mbrid", $mbrid);
            $sth->execute();
            $user = $sth->fetchObject();
            $this->setAttributes($user);
            return true;

        } catch(Exception $e) {
            $this->response->setResponse(false, $e->getMessage());
            return $this->response;
        }
    }

   public function changePass(string $newpass): bool|Response
   {
       try {
           $newpass = md5($newpass);
           $sql = "UPDATE $this->member_table SET pass_user = :newpass WHERE mbrid = :mbrid";
           $sth = $this->db->prepare($sql);
           $sth->execute(array(":newpass" => $newpass, ":mbrid" => $this->mbrid));
           $this->setPassword($newpass);
           return true;

       } catch(Exception $e) {
           $this->response->setResponse(false, $e->getMessage());
           return $this->response;
       }
   }
}
