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
    private $displayname;
    private $userid;

    public function  __construct($sessionid, $ip) {

        if (!empty($sessionid)) {

            $this->sessionid = $sessionid;
            $this->uniquecode = 0;
            $this->hostip = $ip;

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

    public function join($name, $code, $ip) {

        global $db;

        if (!empty($code)) {
            $sql = 'SELECT * FROM game_connections WHERE unique_code = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //game session found, now create user
                $sql = 'SELECT * FROM users
                        WHERE display_name = :name
                        AND game_id = :code';

                $result = $db->prepare($sql);
                $result->bindValue(":name", $name);
                $result->bindValue(":code", $code);

                if ($result->execute() && $result->errorCode() == 0) {

                    if ($result->rowCount() > 0) {
                        //user alerady exists
                        return "user-exists";
                    } else {
                        //create user and redirect
                        $sql = 'INSERT INTO users (game_id, ip_address, display_name, last_active_date)
                                VALUES (:code, :ip, :name, NOW())';

                        $result = $db->prepare($sql);
                        $result->bindValue(":name", $name);
                        $result->bindValue(":code", $code);
                        $result->bindValue(":ip", $ip);

                        if ($result->execute() && $result->errorCode() == 0) {

                            $sql = 'SELECT id FROM users
                                    WHERE game_id = :code
                                    AND display_name = :name';

                            $result = $db->prepare($sql);
                            $result->bindValue(":name", $name);
                            $result->bindValue(":code", $code);

                            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                                $result = $result->fetch(PDO::FETCH_ASSOC);
                                $this->userid = $result['id'];
                                $this->uniquecode = $code;
                                $this->displayname = $name;
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    public function load($code) {

        global $db;

        $sql = 'SELECT * FROM game_connections WHERE unique_code = :code';

        $result = $db->prepare($sql);
        $result->bindValue(":code", $code);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $sql = 'SELECT * FROM users WHERE game_id = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                return $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return false;
    }

    public function getUser() {
        if (!empty($this->userid)) {

            return array(
                'userid' => $this->userid,
                'code' => $this->uniquecode,
                'name' => $this->displayname
            );
        }
        return false;
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