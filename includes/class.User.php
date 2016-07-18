<?php
/*
 * Author: Justin Searle
 * Date: 7/11/2016
 * File: class.User.php
 * Description: class for holding all session and game functions
 */
class User {

    private $userid;
    private $gameid;
    private $hostip;
    private $sessionid;
    private $displayname;
    private $verifieduser;
    private $lastactivedate;
    private $fbtoken;
    private $fbuserid;

    /*
     * Inits the user class
     */
    public function  __construct($sessionid, $ip = 0) {

        if (!empty($sessionid)) {

            $this->sessionid = $sessionid;
            $this->uniquecode = 0;
            $this->hostip = $ip;
            $this->game = '';

        } else {
            throw new Exception ("You need to specify the sessionid and game!");
        }
    }


    /**
     * Determine if the user is in a game already
     * @return bool
     */
    public function isJoined() {
        global $db;

        $sql = "SELECT session_id FROM users WHERE session_id = :session_id AND game_id != 0";
        $result = $db->prepare($sql);
        $result->bindValue(":session_id", $this->sessionid);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function findUser($code) {

        global $db;

        if (!empty($code) && !empty($this->displayname)) {

            $this->gameid = $code;

            //find user
            $sql = 'SELECT * FROM users
                    WHERE display_name = :name
                    AND game_id = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":name", $this->displayname);
            $result->bindValue(":code", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0) {

                //user alerady exists if rowcount greater than 0
                if ($result->rowCount() > 0) {
                    return true;
                } else {
                    //user found in game and doesnt exist
                    return false;
                }
            }
        }
        //user not found in game
        return false;
    }

    public function addUser($fbaccesstoken, $fbuserid, $picture) {
        global $db;

        //create user reference in database
        $sql = 'INSERT INTO users (game_id, ip_address, session_id, display_name, fb_access_token, fb_user_id, picture, last_active_date)
                VALUES (:code, :ip, :session_id, :name, :fbaccesstoken, :fbuserid, :picture, NOW())';

        $result = $db->prepare($sql);
        $result->bindParam(":name", $this->displayname, PDO::PARAM_STR, 25);
        $result->bindParam(":code", $this->gameid, PDO::PARAM_INT);
        $result->bindParam(":fbaccesstoken", $fbaccesstoken, PDO::PARAM_STR, 300);
        $result->bindParam(":fbuserid", $fbuserid, PDO::PARAM_STR, 25);
        $result->bindParam(":picture", $picture, PDO::PARAM_STR, 100);
        $result->bindParam(":ip", $this->hostip, PDO::PARAM_STR, 25);
        $result->bindParam(":session_id", $this->sessionid, PDO::PARAM_STR, 150);

        if ($result->execute() && $result->errorCode() == 0) {

            //get the row details of this user
            $sql = 'SELECT id FROM users
                    WHERE game_id = :code
                    AND display_name = :name';

            $result = $db->prepare($sql);
            $result->bindValue(":name", $this->displayname);
            $result->bindValue(":code", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //fetch and store user details in object for later use
                $result = $result->fetch(PDO::FETCH_ASSOC);
                $this->userid = $result['id'];
                return true;
            }
        } else {
            throw new Exception ("Could not insert into users table.");
        }
    }

    public function setName($name) {

        if (!empty($name)) {
            $this->displayname = $name;
        } else {
            throw new Exception("You need to specify a name");
        }
    }


    /*
     * Return the current user information
     * @returns array, user information
     */
    public function getUser() {

        if (!empty($this->userid)) {

            return array(
                'userid' => $this->userid,
                'code' => $this->gameid,
                'name' => $this->displayname
            );
        }
        return false;
    }
}
?>