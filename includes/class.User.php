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
    private $isJoined;
    private $verifieduser;
    private $lastactivedate;
    private $fbtoken;
    private $fbuserid;
    public $isHost;
    public $isDisplay;

    /**
     * Inits the user class
     */
    public function  __construct($sessionid, $ip = 0, $name = null, $game_id = 0) {

        if (!empty($sessionid)) {

            $this->sessionid = $sessionid;
            $this->uniquecode = 0;
            $this->hostip = $ip;
            $this->game = '';
            $this->gameid = $game_id;
            $this->isJoined = false;
            $this->isHost = false;
            $this->isDisplay = false;

            $this->displayname =  (!empty($name) ? $name : '');

        } else {
            throw new Exception ("You need to specify the sessionid and game!");
        }
    }


    /**
     * Determine if the user is in a game already
     * @return bool
     */
    public function isJoined($check = false) {
        global $db;

        if ($check) {

            $sql = "SELECT session_id FROM users WHERE session_id = :session_id AND game_id != 0";
            $result = $db->prepare($sql);
            $result->bindValue(":session_id", $this->sessionid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                $this->isJoined = true;
            }
        }

        return $this->isJoined;
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

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                return true;
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

        $check = 'DELETE FROM users WHERE session_id = :session_id AND display_name = :name';

        $checkResult = $db->prepare($check);
        $checkResult->bindParam(":name", $this->displayname, PDO::PARAM_STR, 25);
        $checkResult->bindParam(":session_id", $this->sessionid, PDO::PARAM_STR, 150);
        $checkResult->execute();

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
                $this->isHost = $result['is_host'];
                $this->isDisplay = $result['is_display'];

                if (!empty($this->code)) {
                    $this->isJoined = true;
                }

                return array(
                    'userid' => $this->userid,
                    'code' => $this->gameid,
                    'gameid' => $this->gameid,
                    'name' => $this->displayname,
                    'isjoined' => $this->isJoined
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
     * @param $orderByPoints
     * @return array|bool
     */
    public function getAll($code, $orderByPoints) {
        global $db;

        if (!empty($code)) {
            //select all users in current game
            $sql = 'SELECT * FROM users WHERE game_id = :code';
            if($orderByPoints) {
                $sql .= ' ORDER BY points DESC';
            }

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
     * if this works, great! if not oh well users will be deleted when un active anyway
     * @param $code
     * @return bool
     */
    public function deleteUsers($code) {
        global $db;

        if (!empty($code)) {
            //delete non verified users from this game session
            $sql = 'DELETE FROM users WHERE game_id = :gameid AND verified_user = 0';

            $result = $db->prepare($sql);
            $result->bindValue(":gameid", $code);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
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
            $oldHostsSql = 'UPDATE users SET is_host = 0 WHERE game_id = :game_id';

            $oldHostsResult = $db->prepare($oldHostsSql);
            $oldHostsResult->bindParam(":game_id", $this->gameid, PDO::PARAM_INT);

            $newHostSql = 'UPDATE users SET is_host = 1 WHERE id = :id AND game_id = :game_id';

            $newHostResult = $db->prepare($newHostSql);
            $newHostResult->bindParam(":id", $userId, PDO::PARAM_INT);
            $newHostResult->bindParam(":game_id", $this->gameid, PDO::PARAM_INT);

            if ($oldHostsResult->execute() && $oldHostsResult->errorCode() == 0) {
                if($newHostResult->execute() && $newHostResult->errorCode() == 0) {
                    return true;
                }
            }
            return false;
        } else if ($action == "get") {
            $sql = 'SELECT is_host FROM users WHERE id = :id AND is_host = 1';

            $result = $db->prepare($sql);
            $result->bindParam(":id", $userId, PDO::PARAM_INT);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() == 1) {
                return true;
            }
            return false;
        }
    }

    /**
     * @param $action - "get" or "set"
     * @param $userId - user id to target
     * @param $isDisplay - true or false
     * @return bool
     * Set or Get the isDisplay value for the user based on the requested action
     */
    public function isDisplay($action, $userId, $isDisplay) {
        global $db;

        if($action == "set") {
            $newHostSql = 'UPDATE users SET is_display = :is_display WHERE id = :id AND game_id = :game_id';

            $newHostResult = $db->prepare($newHostSql);
            $newHostResult->bindParam(":id", $userId, PDO::PARAM_INT);
            $newHostResult->bindParam(":is_display", $isDisplay, PDO::PARAM_INT);
            $newHostResult->bindParam(":game_id", $this->gameid, PDO::PARAM_INT);

            if ($newHostResult->execute() && $newHostResult->errorCode() == 0) {
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