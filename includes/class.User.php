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

    /**
     * Inits the user class
     */
    public function  __construct($sessionid, $ip = 0, $name = null) {

        if (!empty($sessionid)) {

            $this->sessionid = $sessionid;
            $this->uniquecode = 0;
            $this->hostip = $ip;
            $this->game = '';

            if (!empty($name)) {
                $this->displayname = $name;
            }

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

    /**
     * @param $code
     * @return bool
     */
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

    /**
     * @param $fbaccesstoken
     * @param $fbuserid
     * @param $picture
     * @return bool
     * @throws Exception
     */
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

    /**
     * @param $name
     * @throws Exception
     */
    public function setName($name) {

        if (!empty($name)) {
            $this->displayname = $name;
        } else {
            throw new Exception("You need to specify a name");
        }
    }


    /**
     * Return the current user information
     * @returns array, user information
     */
    public function getUser() {
        global $db;

        if (!empty($this->userid)) {

            return array(
                'userid' => $this->userid,
                'code' => $this->gameid,
                'gameid' => $this->gameid,
                'name' => $this->displayname
            );
        } else if (!empty($this->sessionid)) {

            $sql = 'SELECT * FROM users WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindParam(":sessionid", $this->sessionid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $result = $result->fetch(PDO::FETCH_ASSOC);
                $this->userid = $result['userid'];
                $this->code = $result['gameid'];
                $this->name = $result['displayname'];
                $this->gameid = $result['gameid'];

                return array(
                    'userid' => $this->userid,
                    'code' => $this->gameid,
                    'gameid' => $this->gameid,
                    'name' => $this->displayname
                );
            }
        }

        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function getPicture($user_id) {
        global $db;

        //select all users in current game
        $sql = 'SELECT picture FROM users WHERE id = :user_id LIMIT 1';

        $result = $db->prepare($sql);
        $result->bindValue(":user_id", $user_id);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
            $result = $result->fetch(PDO::FETCH_ASSOC);
            return $result['picture'];
        }
        return false;
    }

    /**
     * @param $code
     * @return array|bool
     */
    public function getAll($code) {
        global $db;

        if (!empty($code)) {
            //select all users in current game
            $sql = 'SELECT * FROM users WHERE game_id = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //add the users to the game
                return $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return false;
    }

    /**
     * @param $code
     * @return bool
     */
    public function deleteUsers($code) {
        global $db;

        //delete non verified users from this game session
        $sql = 'DELETE FROM users WHERE game_id = :gameid';

        $result = $db->prepare($sql);
        $result->bindValue(":gameid", $this->uniquecode);

        if ($result->execute() && $result->errorCode()) {
            //if this works, great! if not oh well users will be deleted when un active anyway
            return true;
        }
        return false;
    }

    /**
     * @param null $id
     * @return bool|null
     */
    public function getName($id = null) {
        global $db;

        if (!empty($id)) {

            $sql = 'SELECT display_name FROM users WHERE id = :id';

            $result = $db->prepare($sql);
            $result->bindParam(":id", $id);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $result = $result->fetch(PDO::FETCH_ASSOC);

                return $result['display_name'];
            }
        } else if (!empty($this->displayname)) {

            return $this->displayname;
        }
        //noting found
        return false;
    }

    /**
     * @param $action - "get" or "set"
     * @param $userId - user id to target
     * @return bool
     * Set or Get the isHost value for the user based on the requested action
     */
    public function isHost($action, $userId) {
        global $db;

        if($action == "set") {
            $oldHostsSql = 'UPDATE users SET is_host = false WHERE game_id = :game_id';

            $oldHostsResult = $db->prepare($oldHostsSql);
            $oldHostsResult->bindParam(":game_id", $this->gameid);

            $newHostSql = 'UPDATE users SET is_host = true WHERE id = :id AND game_id = :game_id';

            $newHostResult = $db->prepare($newHostSql);
            $newHostResult->bindParam(":id", $userId);
            $newHostResult->bindParam(":game_id", $this->gameid);

            if ($oldHostsResult->execute() && $oldHostsResult->errorCode() == 0 && $newHostResult->execute() && $newHostResult->errorCode() == 0) {
                return true;
            }
            return false;
        } else if ($action == "get") {
            $sql = 'SELECT is_host FROM users WHERE id = :id AND is_host = true';

            $result = $db->prepare($sql);
            $result->bindParam(":id", $userId);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() == 1) {
                return true;
            }
            return false;
        }
    }

    /**
     * @param $action - "get" or "set"
     * @param $userId - user id to target
     * @return bool
     * Set or Get the isDisplay value for the user based on the requested action
     */
    public function isDisplay($action, $userId) {
        global $db;

        if($action == "set") {
            $oldHostsSql = 'UPDATE users SET is_display = false WHERE game_id = :game_id';

            $oldHostsResult = $db->prepare($oldHostsSql);
            $oldHostsResult->bindParam(":game_id", $this->gameid);

            $newHostSql = 'UPDATE users SET is_display = true WHERE id = :id AND game_id = :game_id';

            $newHostResult = $db->prepare($newHostSql);
            $newHostResult->bindParam(":id", $userId);
            $newHostResult->bindParam(":game_id", $this->gameid);

            if ($oldHostsResult->execute() && $oldHostsResult->errorCode() == 0 && $newHostResult->execute() && $newHostResult->errorCode() == 0) {
                return true;
            }
            return false;
        } else if ($action == "get") {
            $sql = 'SELECT is_display FROM users WHERE id = :id AND is_display = true';

            $result = $db->prepare($sql);
            $result->bindParam(":id", $userId);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                $result = $result->fetch(PDO::FETCH_ASSOC);

                return $result['is_display'];
            }
            return false;
        }
    }
}
?>