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
}
?>