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
    private $game;
    private $gameActive;

    /*
     * Inits the game class
     */
    public function  __construct($sessionid = '', $ip = 0) {

        if (!empty($sessionid)) {

            //required variables
            $this->sessionid = $sessionid;
            $this->hostip = $ip;

            $this->uniquecode = 0;
            $this->game = '';

        } else {
            throw new Exception ("You need to specify the sessionid!");
        }
    }

    /*
     * Creates a new game session and saves a reference in the database
     * @returns boolean, true/false
     */
    public function setup($gameName = '') {

        global $db;

        if (!empty($this->sessionid)) {

            //query for already created game with current sessionid
            $sql = 'SELECT * FROM game_connections WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindValue(":sessionid", $this->sessionid);

            //query database for current session
            if ($result->execute() && $result->errorCode() == 0) {

                if ($result->rowCount() > 0) {

                    //fetch current settings
                    $result = $result->fetch(PDO::FETCH_ASSOC);
                    $this->uniquecode = $result['unique_code'];
                    $this->game = $result['game_name'];
                    $this->gameActive = $result['game_active'];

                } else if (!empty($gameName)) {

                    //insert new session into database
                    $this->uniquecode = self::getRandomCode();
                    $this->game = $gameName;

                    $sql = 'INSERT INTO game_connections (session_id, unique_code, host_ip_address, date, game_active, game_name)
                            VALUES (:sessionid, :uniquecode, :hostip, NOW(), 1, :game)';

                    $result = $db->prepare($sql);
                    $result->bindValue(":sessionid", $this->sessionid);
                    $result->bindValue(":uniquecode", $this->uniquecode);
                    $result->bindValue(":hostip", $this->hostip);
                    $result->bindValue(":game", $this->game);

                    //check to see if game session was created
                    if ($result->execute() && $result->errorCode() == 0) {
                        return true;
                    } else {
                        throw new Exception ("New session could not be created.");
                    }
                } else if (empty($gameName)) {
                    throw new Exception ("You need to specify a game name");
                }
            } else {
                throw new Exception ("Database could not be queried.");
            }
        } else {
            throw new Exception ("No sessionid set.");
        }
        return false;
    }

    /**
     * @param $code
     * @return bool
     */
    public function destroy($code = 0) {
        global $db;

        if (!empty($code)) {
            //delete game code from game conenction
            $sql = 'DELETE FROM game_connections WHERE unique_code = :unique_code';

            $result = $db->prepare($sql);
            $result->bindValue(":unique_code", $code);

            if ($result->execute() && $result->errorCode() == 0) {

                //reset users game code if game gets deleted
                $sqlUsers = 'UPDATE users SET game_id = 0 WHERE game_id = :unique_code';

                $resultUsers = $db->prepare($sqlUsers);
                $resultUsers->bindValue(":unique_code", $code);

                if ($resultUsers->execute() && $resultUsers->errorCode()) {

                    self::clearSessionVars();
                    return true;
                }
            }
        }
        return false;
    }

    /*
     * Fetches a new random code and updates the reference in the database
     * @returns boolean, true/false
     */
    public function newCode() {

        global $db;

        if (!empty($this->sessionid) && !empty($this->game)) {
            $this->uniquecode = self::getRandomCode();

            $sql = 'UPDATE game_connections 
                    SET unique_code = :uniquecode,
                    date = NOW(),
                    game_active = 0,
                    game_name = :game
                    WHERE session_id = :sessionid';

            $result = $db->prepare($sql);
            $result->bindValue(":uniquecode", $this->uniquecode);
            $result->bindValue(":sessionid", $this->sessionid);
            $result->bindValue(":game", $this->game);

            if ($result->execute() && $result->errorCode() == 0) {

                self::clearSessionVars();
                return true;
            } else {
                throw new Error ("Session could not be updated.");
            }
        } else {
            throw new Exception("Game must have a sessionid and game name to update the code.");
        }
        return false;
    }

    /*
     * Deletes a session from the game_connection table
     * @param string, sessionid
     * #returns boolean, true/false
     */
    public function removeSession($sessionid = null) {

        global $db, $user;

        if (!empty($sessionid)) {

            //check for empty code then delete users from current game
            if (!empty($this->uniquecode)&& $user->deleteUsers($this->uniquecode)) {

                //delete game connection
                $sql = 'DELETE FROM game_connections      
                        WHERE session_id = :sessionid';

                $result = $db->prepare($sql);
                $result->bindValue(":sessionid", $sessionid);

                if ($result->execute() && $result->errorCode() == 0) {

                    self::clearSessionVars();
                    return true;
                } else {
                    throw new Exception ("Session could not be deleted.");
                }
            }
        } else {
            throw new Exception ("Sessionid cannot be empty");
        }
        return false;
    }

    public function clearSessionVars() {

        unset($_SESSION['game']);
        unset($_SESSION['user']);
    }

    public function validateGame($code = 0) {
        global $db;

        if(!empty($code) && is_numeric($code)) {
            $this->uniquecode = $code;

            $sql = 'SELECT * FROM game_connections WHERE unique_code = :code LIMIT 1';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $this->uniquecode);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() == 1) {
                //game found
                return true;
            }
        }
        //Game wasn't found
        return false;
    }

    /*
     * Allows a user to join a game session
     * @param string, name
     * @param int, code
     * @param string, fbaccesstoken
     * @returns boolean, true/false
     */
    public function join($name = '', $fbaccesstoken = '', $fbuserid = '', $picture = '') {

        global $db, $user;

        //basic error handling
        if (empty($name)) {
            throw new Exception("No name specified");
        }
        $user->setName($name);

        if (!empty($this->uniquecode)) {

            //check for current game sessions via unique code
            $sql = 'SELECT * FROM game_connections WHERE unique_code = :code';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $this->uniquecode);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                //game session found, now check for existing user else create user
                if ($user->findUser($this->uniquecode)) {
                    //user already exists in this game
                    return "user-exists";

                } else if ($user->addUser($fbaccesstoken, $fbuserid, $picture)) {
                    //added user successfully
                    return true;
                }
            }
        } else {
            throw new Exception ("Cannot join a game without a code");
        }

        //something went wrong if this function returns false
        return false;
    }

    public function setGameName($game = '') {
        if (!empty($game)) {
            $this->game = $game;
            return true;
        }
        return false;
    }

    public function getGameName($code = 0) {

        global $db;

        if (!empty($code)) {
            $sql = "SELECT game_name FROM game_connections WHERE unique_code = :code";

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $result = $result->fetch(PDO::FETCH_ASSOC);
                $gameName = $result['game_name'];
                $this->game = $gameName;
            }
        }

        if (!empty($this->game)) {
            return $this->game;
        }
        return false;
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

    /**
     * Remove user from their current game
     * @return bool
     */
    public function leave() {
        global $db;

        $sql = "UPDATE users SET game_id = 0, is_host = 0, is_display = 0 WHERE session_id = :session_id";
        $result = $db->prepare($sql);
        $result->bindValue(":session_id", $this->sessionid);

        if ($result->execute() && $result->errorCode() == 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $code
     * @return bool
     */
    public function switchGame($code = 0) {
        global $db;

        if (!empty($code) && !empty($this->sessionid)) {

            $sql = "UPDATE users SET game_id = :code WHERE session_id = :session_id";
            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);
            $result->bindValue(":session_id", $this->sessionid);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
        }
        return false;
    }

    public function addChatMessage($message, $owner) {
        global $db, $user;

        $sql = "INSERT INTO messages (game_id, message, owner, name) VALUES(:code, :message, :owner, :name)";
        $result = $db->prepare($sql);
        $result->bindValue(":code", $_SESSION['current_game_code']);
        $result->bindValue(":message", $message);
        $result->bindValue(":owner", $owner);
        $result->bindValue(":name", $user->getName($owner));

        if ($result->execute() && $result->errorCode() == 0) {
            return true;
        }
        return false;
    }

    public function loadChatMessages() {
        global $db;

        $sql = "SELECT * FROM messages WHERE game_id = :code";
        $result = $db->prepare($sql);
        $result->bindValue(":code", $_SESSION['current_game_code']);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * Load a game session based on a game code
     * @param $code
     * @param $orderByPoints
     * @return bool|mixed
     */
    public function loadUsers($code, $orderByPoints = false) {

        global $db, $user;

        if (!empty($code)) {
            //query the game details
            $sql = 'SELECT * FROM game_connections WHERE unique_code = :code LIMIT 1';

            $result = $db->prepare($sql);
            $result->bindValue(":code", $code);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $game = $result->fetch(PDO::FETCH_ASSOC);

                if ($users = $user->getAll($code, $orderByPoints)) {
                    $game['users'] = $users;
                    //return associative array for the game
                    return $game;
                }
            }
        } else {
            throw new Exception ("Cannot have empty game code");
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
    public function getRandomCode() {
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