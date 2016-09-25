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
    public function  __construct($sessionid, $ip = 0, $name = '', $game_id = 0) {

        if (!empty($sessionid)) {

            //required variables
            $this->sessionid = $sessionid;
            $this->hostip = $ip;

            //game variables
            $this->displayname = $name;
            $this->gameid = $game_id;

            //defaults
            $this->game = '';
            $this->isJoined = false;
            $this->isHost = false;
            $this->isDisplay = false;
        } else {
            throw new Exception ("You need to specify the sessionid!");
        }
    }

    public function erase ($userid = 0) {

        global $db;

        if (!empty($userid)) {

            $sql = 'DELETE FROM users WHERE id = :userid';

            $result = $db->prepare($sql);
            $result->bindValue(":userid", $userid);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
        } else {
            throw new Exception ("cannot erase user without userid");
        }

        return false;
    }

    /**
     * Determine if the user is in a game already
     * @return bool
     */
    public function isJoined($check = true) {
        global $db;

        if ($check) {
            $sql = "SELECT session_id FROM users 
                  WHERE session_id = :session_id                   
                  AND game_id != 0";

            $result = $db->prepare($sql);
            $result->bindValue(":session_id", $this->sessionid);
            //$result->bindValue(":displayname", $this->displayname);

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
    public function findUser($code = 0) {

        global $db;

        if (!empty($code) && !empty($this->displayname)) {

            $this->gameid = $code;

            //find user
            $sql = 'SELECT id FROM users
                    WHERE display_name = :name
                    AND game_id = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":name", $this->displayname);
            $result->bindValue(":code", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                return true;
            }
        } else {
            throw new Exception("Cannot find user without a code and displayname");
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
    public function addUser($fbaccesstoken = '', $fbuserid = '', $picture = '') {
        global $db;

        //delete any rows to avoid conflicts for this session id
        if (!empty($this->sessionid) && !empty($this->displayname) && !empty($this->gameid)) {

            $sql = 'DELETE FROM users WHERE session_id = :session_id AND display_name = :name';

            $checkResult = $db->prepare($sql);
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
    }

    /**
     * @param $name
     * @throws Exception
     */
    public function setName($name) {

        global $db;

        if (!empty($name)) {
            $this->displayname = $name;

            //update in database
            $sql = 'UPDATE users SET display_name = :displayname WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindValue(":displayname", $this->displayname);
            $result->bindValue(":sessionid", $this->sessionid);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
        } else {
            throw new Exception("You need to specify a name");
        }
    }


    /**
     * Return the current user information
     * @returns array, user information
     */
    public function getUser($specificUserId = 0) {
        global $db;

        if ($specificUserId > 0) {
            $sql = 'SELECT * FROM users WHERE id = :userid';

            $result = $db->prepare($sql);
            $result->bindParam(":userid", $specificUserId);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $result = $result->fetch(PDO::FETCH_ASSOC);
                return $result;
            }
        } else if (!empty($this->sessionid)) {

            $sql = 'SELECT * FROM users WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindParam(":sessionid", $this->sessionid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $result = $result->fetch(PDO::FETCH_ASSOC);
                $this->userid = $result['id'];
                $this->gameid = $result['game_id'];
                $this->displayname = $result['display_name'];
                $this->isHost = $result['is_host'];
                $this->isDisplay = $result['is_display'];

                if (!empty($this->gameid)) {
                    $this->isJoined = true;
                }

                return $result;
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

    public function addPoints($points = 0, $userid) {

        global $db;

        $sql = 'SELECT points FROM users WHERE id = :userid';

        $result = $db->prepare($sql);
        $result->bindValue(":userid", $userid);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $result = $result->fetch(PDO::FETCH_ASSOC);

            $points = $result['points'];

            $sql = 'UPDATE users SET points = :points WHERE id = :userid';

            $result = $db->prepare($sql);
            $result->bindValue(":userid", $userid);
            $result->bindValue(":points", $points);

            if ($result->execute() && $result->errorCode() == 0) {

                return true;
            }
        }

        return false;
    }

    /**
     * @param $code
     * @param $orderByPoints
     * @return array|bool
     */
    public function getAll($code = 0, $orderByPoints = false) {
        global $db;

        if (!empty($code)) {
            //select all users in current game
            $sql = 'SELECT * FROM users WHERE game_id = :code';
            if($orderByPoints) {
                $sql .= ' ORDER BY score DESC';
            }

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //add the users to the game
                $result = $result->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            }
        }

        return false;
    }

    /**
     * if this works, great! if not oh well users will be deleted when un active anyway
     * @param $code
     * @return bool
     */
    public function deleteUsers($code = 0) {
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
     * @param $code
     * @return bool
     */
    public function switchGame($code = 0) {
        global $db;

        if (!empty($code) && !empty($this->sessionid)) {

            $sql = "UPDATE users 
                    SET game_id = :code 
                    WHERE session_id = :session_id";

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);
            $result->bindValue(":session_id", $this->sessionid);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $action - "get" or "set"
     * @param $userId - user id to target
     * @return bool
     * Set or Get the isHost value for the user based on the requested action
     */
    public function isHost($action = '', $userId = 0) {
        global $db;

        if($action == "set" && !empty($userId)) {
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
        } else if ($action == "get" && !empty($userId)) {
            $sql = 'SELECT is_host FROM users WHERE id = :id AND is_host = 1 LIMIT 1';

            $result = $db->prepare($sql);
            $result->bindParam(":id", $userId, PDO::PARAM_INT);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() == 1) {
                return true;
            }
        }
        return false;
    }

    public function getId() {

    }

    /**
     * @param $action - "get" or "set"
     * @param $userId - user id to target
     * @param $isDisplay - true or false
     * @return bool
     * Set or Get the isDisplay value for the user based on the requested action
     */
    public function isDisplay($action = '', $userId = 0, $isDisplay = false) {
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
        } else if ($action == "get" && !empty($userId)) {
            $sql = 'SELECT is_display FROM users WHERE id = :id AND is_display = 1';

            $result = $db->prepare($sql);
            $result->bindParam(":id", $userId);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }

    public function getIsHost() {

        return $this->isHost;
    }


    /*
     * Update the users information
     * @param string, name
     * @param int, code
     * @param string, fbaccesstoken
     * @returns boolean, true/false
     */
    public function updateUser($name, $code, $fbaccesstoken, $fbuserid, $picture) {

        global $db;

        if (!empty($code)) {
            //check for current game sessions via unique code
            $sql = 'SELECT * FROM game_connections WHERE unique_code = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //game session found, now check for existing user else create user
                $sql = 'SELECT * FROM users
                        WHERE display_name = :name
                        AND game_id = :code';

                $result = $db->prepare($sql);
                $result->bindValue(":name", $name);
                $result->bindValue(":code", $code);

                if ($result->execute() && $result->errorCode() == 0) {

                    //user alerady exists if rowcount greater than 0
                    if ($result->rowCount() > 0) {
                        //ensure there are no duplicate users in different games
                        $sql = 'DELETE FROM users
                                    WHERE game_id != :code
                                    AND fb_user_id = :fbuserid';
                        $result = $db->prepare($sql);
                        $result->bindParam(":fbuserid", $fbuserid);
                        $result->bindParam(":code", $code);
                        $result->execute();

                        $sqlUpdate = 'UPDATE users SET game_id = :code, ip_address = :ip, session_id = :session_id, display_name = :name, fb_access_token = :fbaccesstoken, picture = :picture, last_active_date = NOW() 
                                      WHERE fb_user_id = :fbuserid';

                        $resultUpdate = $db->prepare($sqlUpdate);
                        $resultUpdate->bindParam(":name", $name, PDO::PARAM_STR, 25);
                        $resultUpdate->bindParam(":code", $code, PDO::PARAM_INT);
                        $resultUpdate->bindParam(":fbaccesstoken", $fbaccesstoken, PDO::PARAM_STR, 300);
                        $resultUpdate->bindParam(":fbuserid", $fbuserid, PDO::PARAM_STR, 25);
                        $resultUpdate->bindParam(":picture", $picture, PDO::PARAM_STR, 100);
                        $resultUpdate->bindParam(":ip", $this->hostip, PDO::PARAM_STR, 25);
                        $result->bindParam(":session_id", $this->sessionid, PDO::PARAM_STR, 150);

                        if ($resultUpdate->execute() && $resultUpdate->errorCode() == 0) {
                            //get the row details of this user
                            $sql = 'SELECT id FROM users
                                    WHERE game_id = :code
                                    AND fb_user_id = :fbuserid';

                            $result = $db->prepare($sql);
                            $result->bindParam(":fbuserid", $fbuserid);
                            $result->bindParam(":code", $code);

                            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                                //fetch and store user details in object for later use
                                $result = $result->fetch(PDO::FETCH_ASSOC);
                                $this->userid = $result['id'];
                                $this->uniquecode = $code;
                                $this->displayname = $name;
                                return true;
                            }
                        }
                    } else {
                        //create user reference in database
                        $sql = 'INSERT INTO users (game_id, ip_address, session_id, display_name, fb_access_token, fb_user_id, picture, last_active_date)
                                VALUES (:code, :ip, :session_id, :name, :fbaccesstoken, :fbuserid, :picture, NOW())';

                        $result = $db->prepare($sql);
                        $result->bindParam(":name", $name, PDO::PARAM_STR, 25);
                        $result->bindParam(":code", $code, PDO::PARAM_INT);
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
                            $result->bindValue(":name", $name);
                            $result->bindValue(":code", $code);

                            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                                //fetch and store user details in object for later use
                                $result = $result->fetch(PDO::FETCH_ASSOC);
                                $this->userid = $result['id'];
                                $this->uniquecode = $code;
                                $this->displayname = $name;
                                return true;
                            }
                        } else {
                            throw new Exception ("Could not insert into users table.");
                        }
                    }
                } else {
                    throw new Exception ("Users table could not be queried.");
                }
            } else {
                return false;
            }
        }

        //something went wrong if this function returns false
        return false;
    }

}
?>