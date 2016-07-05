<?php
/*
 * Author: Justin Searle
 * Date: 7/4/2016
 * File: class.GameSession.php
 * Description: class for holding all session functions
 */
class GameSession {

    private $sessionid;
    private $uniquecode;
    private $hostip;

    public function  __construct($sessionid, $ip) {

        if (!empty($sessionid)) {

            $this->sessionid = $sessionid;
            $this->uniquecode = 0;
            $this->hostip = $ip;
            self::setup();

        } else {
            throw new Exception ("You need to specify the sessionid!");
        }
    }

    public function setup() {

        global $db;

        $sql = 'SELECT * FROM game_connections WHERE session_id = :sessionid';

        $result = $db->prepare($sql);
        $result->bindValue(":sessionid", $this->sessionid);

        //query database for current session
        if ($result->execute() && $result->errorCode()) {

            if ($result->rowCount() > 0) {

                //fetch current settings
                $result = $result->fetch(PDO::FETCH_ASSOC);
                $this->uniquecode = $result['unique_code'];

            } else {

                //insert new session into database
                $this->uniquecode = self::setCode();
                $sql = 'INSERT INTO game_connections (session_id, unique_code, host_ip_address, date, game_active)
                        VALUES (:sessionid, :uniquecode, :hostip, NOW(), 1)';

                $result = $db->prepare($sql);
                $result->bindValue(":sessionid", $this->sessionid);
                $result->bindValue(":uniquecode", $this->uniquecode);
                $result->bindValue(":hostip", $this->hostip);

                if ($result->execute() && $result->errorCode()) {
                    return true;
                } else {
                    throw new Exception ("New session could not be created.");
                }
            }
        } else {
            throw new Exception ("Database could not be queried.");
        }
    }

    public function newCode() {

        global $db;

        $this->uniquecode = self::setCode();

        $sql = 'UPDATE game_connections 
                SET unique_code = :uniquecode,
                date = NOW(),
                game_active = 1
                WHERE session_id = :sessionid';

        $result = $db->prepare($sql);
        $result->bindValue(":uniquecode", $this->uniquecode);
        $result->bindValue(":sessionid", $this->sessionid);

        if ($result->execute() && $result->errorCode()) {
            return true;
        } else {
            throw new Error ("Session could not be updated.");
        }
    }

    public function removeSession($sessionid) {

        global $db;

        $sql = 'DELETE FROM game_connections      
                WHERE session_id = :sessionid';

        $result = $db->prepare($sql);
        $result->bindValue(":sessionid", $sessionid);

        if ($result->execute() && $result->errorCode()) {
            return true;
        } else {
            throw new Exception ("Session could not be updated.");
        }
    }

    public function setCode() {
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float) $sec + ((float) $usec * 100000);
        srand($seed);
        return rand(999, 9998) +1;
    }

    public function getCode() {
        return $this->uniquecode;
    }
}