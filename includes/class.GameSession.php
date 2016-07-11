<?php
/*
 * Author: Justin Searle
 * Date: 7/4/2016
 * File: class.GameSession.php
 * Description: class for holding all session and game functions
 */
class GameSession {

    private $sessionid;
    private $uniquecode;
    private $hostip;
    private $displayname;
    private $userid;

    /*
     * Inits the game class
     */
    public function  __construct($sessionid, $ip) {

        if (!empty($sessionid)) {

            $this->sessionid = $sessionid;
            $this->uniquecode = 0;
            $this->hostip = $ip;

        } else {
            throw new Exception ("You need to specify the sessionid!");
        }
    }

    /*
     * Creates a new game session and saves a reference in the database
     * @returns boolean, true/false
     */
    public function setup($new = false) {

        global $db;

        if (!empty($this->sessionid)) {

            //query for already created game with current sessionid
            $sql = 'SELECT * FROM game_connections WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindValue(":sessionid", $this->sessionid);

            //query database for current session
            if ($result->execute() && $result->errorCode()) {

                if ($result->rowCount() > 0) {

                    //fetch current settings
                    $result = $result->fetch(PDO::FETCH_ASSOC);
                    $this->uniquecode = $result['unique_code'];

                } else if ($new) {

                    //insert new session into database
                    $this->uniquecode = self::setCode();
                    $sql = 'INSERT INTO game_connections (session_id, unique_code, host_ip_address, date, game_active)
                            VALUES (:sessionid, :uniquecode, :hostip, NOW(), 1)';

                    $result = $db->prepare($sql);
                    $result->bindValue(":sessionid", $this->sessionid);
                    $result->bindValue(":uniquecode", $this->uniquecode);
                    $result->bindValue(":hostip", $this->hostip);

                    //check to see if game session was created
                    if ($result->execute() && $result->errorCode()) {
                        return true;
                    } else {
                        throw new Exception ("New session could not be created.");
                    }
                }
            } else {
                throw new Exception ("Database could not be queried.");
            }
        } else {
            throw new Exception ("No sessionid set.");
        }
        return false;
    }

    /*
     * Fetches a new random code and updates the reference in the database
     * @returns boolean, true/false
     */
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
        return false;
    }

    /*
     * Deletes a session from the game_connection table
     * @param string, sessionid
     * #returns boolean, true/false
     */
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
        return false;
    }

    /*
     * Allows a user to join a game session
     * @param string, name
     * @param int, code
     * @param string, fbaccesstoken
     * @returns boolean, true/false
     */
    public function join($name, $code, $fbaccesstoken, $fbuserid) {

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
                        return "user-exists";
                    } else {
                        //create user reference in database
                        $sql = 'INSERT INTO users (game_id, ip_address, display_name, fb_access_token, fb_user_id, last_active_date)
                                VALUES (:code, :ip, :name, :fbaccesstoken, :fbuserid, NOW())';

                        $result = $db->prepare($sql);
                        $result->bindParam(":name", $name, PDO::PARAM_STR, 25);
                        $result->bindParam(":code", $code, PDO::PARAM_INT);
                        $result->bindParam(":fbaccesstoken", $fbaccesstoken, PDO::PARAM_STR, 300);
                        $result->bindParam(":fbuserid", $fbuserid, PDO::PARAM_STR, 25);
                        $result->bindParam(":ip", $this->hostip, PDO::PARAM_STR, 25);

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

    /*
     * Update the users information
     * @param string, name
     * @param int, code
     * @param string, fbaccesstoken
     * @returns boolean, true/false
     */
    public function updateUser($name, $code, $fbaccesstoken, $fbuserid) {

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

                        $sqlUpdate = 'UPDATE users SET game_id = :code, ip_address = :ip, display_name = :name, fb_access_token = :fbaccesstoken, last_active_date = NOW() 
                                      WHERE fb_user_id = :fbuserid';

                        $resultUpdate = $db->prepare($sqlUpdate);
                        $resultUpdate->bindParam(":name", $name, PDO::PARAM_STR, 25);
                        $resultUpdate->bindParam(":code", $code, PDO::PARAM_INT);
                        $resultUpdate->bindParam(":fbaccesstoken", $fbaccesstoken, PDO::PARAM_STR, 300);
                        $resultUpdate->bindParam(":fbuserid", $fbuserid, PDO::PARAM_STR, 25);
                        $resultUpdate->bindParam(":ip", $this->hostip, PDO::PARAM_STR, 25);

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
                        $sql = 'INSERT INTO users (game_id, ip_address, display_name, fb_access_token, fb_user_id, last_active_date)
                                VALUES (:code, :ip, :name, :fbaccesstoken, :fbuserid, NOW())';

                        $result = $db->prepare($sql);
                        $result->bindParam(":name", $name, PDO::PARAM_STR, 25);
                        $result->bindParam(":code", $code, PDO::PARAM_INT);
                        $result->bindParam(":fbaccesstoken", $fbaccesstoken, PDO::PARAM_STR, 300);
                        $result->bindParam(":fbuserid", $fbuserid, PDO::PARAM_STR, 25);
                        $result->bindParam(":fbpicture", $fbpicture, PDO::PARAM_STR, 250);
                        $result->bindParam(":ip", $this->hostip, PDO::PARAM_STR, 25);

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

    /*
     * Load a game session based on a game code
     * @param int, code
     * @returns array, array of all users in current game session
     */
    public function loadUsers($code) {

        global $db;

        //query the game details
        $sql = 'SELECT * FROM game_connections WHERE unique_code = :code';

        $result = $db->prepare($sql);
        $result->bindValue(":code", $code);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            //select all users in current game
            $sql = 'SELECT * FROM users WHERE game_id = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //return associative array of users
                return $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        //if returned false, game could not be found
        return false;
    }

    /*
     * Return the current user information
     * @returns array, user information
     */
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

    /*
     * Generates a random 4 digit code between 1000-9999
     * @returns int
     */
    public function setCode() {
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float) $sec + ((float) $usec * 100000);
        srand($seed);
        return rand(999, 9998) +1;
    }

    /*
     * Retruns the current game session code
     * @param string, sessionid
     * @returns int
     */
    public function getCode($sessionid = null) {

        global $db;

        //if session id not empty, lookup that associated code
        if (!empty($sessionid)) {

            $sql = 'SELECT * FROM game_connections WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindValue(":sessionid", $sessionid);

            //query
            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $result = $result->fetch(PDO::FETCH_ASSOC);
                $this->uniquecode = $result['unique_code'];
            }
        }

        return $this->uniquecode;
    }
}