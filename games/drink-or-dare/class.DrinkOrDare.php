<?php

class DrinkOrDare {

    private $state;
    private $gameid;
    private $total_rounds;
    private $current_round;
    private $userid;
    private $hasCurrentDare;

    public function __construct($game_id = 0, $userid = 0, $total_rounds = 5, $current_round = 1) {

        $this->gameid = $game_id;
        $this->state = 1;
        $this->total_rounds = $total_rounds;
        $this->current_round = $current_round;
        $this->userid = $userid;
        $this->hasCurrentDare = false;
    }

    public function start() {

        global $db;

        if (!empty($this->gameid)) {

            $sql = 'SELECT * FROM drink_or_dare WHERE game_id = :gameid';

            $result = $db->prepare($sql);
            $result->bindParam(":gameid", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0) {

                if ($result->rowCount() <= 0) {
                    //game doesnt exist
                    $sql = 'INSERT INTO drink_or_dare
                            (game_id, state, total_rounds, current_round) 
                            VALUES
                            (:gameid, :state, :total_rounds, :current_round)';

                    $result = $db->prepare($sql);
                    $result->bindParam(":gameid", $this->gameid);
                    $result->bindParam(":state", $this->state);
                    $result->bindParam(":total_rounds", $this->total_rounds);
                    $result->bindParam(":current_round", $this->current_round);

                    if ($result->execute() && $result->errorCode() == 0) {
                        return true;
                    }
                } else {
                    //game exists
                    $result = $result->fetch(PDO::FETCH_ASSOC);
                    
                    $this->state = $result['state'];
                    $this->total_rounds = $result['total_rounds'];
                    $this->current_round = $result['current_round'];

                    //get user dare state
                    if (!empty($this->gameid)) {

                        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                                WHERE user_id = :userid 
                                AND round_number = :round_number';

                        $result = $db->prepare($sql);
                        $result->bindParam(":userid", $this->userid);
                        $result->bindParam(":round_number", $this->current_round);

                        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                            $this->hasCurrentDare = true;
                        }
                    }

                    return true;
                }
            }
        } else {
            throw new Exception("Cannot load game without game id.");
        }

        return false;
    }

    public function setDare($text) {

        global $db;

        if (!empty($this->gameid)) {

            $sql = 'SELECT * FROM drink_or_dare_user_dares 
                    WHERE user_id = :userid 
                    AND round_number = :round_number';

            $result = $db->prepare($sql);
            $result->bindParam(":userid", $this->userid);
            $result->bindParam(":round_number", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            } else {

                //dare doesnt exist for this user and round
                $sql = 'INSERT INTO drink_or_dare_user_dares
                        (user_id, dare, round_number) 
                        VALUES
                        (:userid, :dare, :round_number)';

                $result = $db->prepare($sql);
                $result->bindParam(":userid", $this->userid);
                $result->bindParam(":dare", $text);
                $result->bindParam(":round_number", $this->current_round);

                if ($result->execute() && $result->errorCode() == 0) {
                    return true;
                }
            }
        } else {
            throw new Exception("Cannot set dare without game id.");
        }

        return false;
    }

    public function checkDaresComplete() {
        return false;
    }

    public function nextState() {

        global $db;

        if (!empty($this->state)) {

            if ($this->state == 1) {
                $this->state = 2;
            }

            $sql = 'UPDATE drink_or_date SET state = :state WHERE game_id = :game_id';

            $result = $db->prepare($sql);
            $result->bindParam(":game_id", $this->gameid);
            $result->bindParam(":state", $this->state);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
        }
        return false;
    }

    public function getState() {

        return $this->state;
    }

    public function getHasCurrentDare() {

        return $this->hasCurrentDare;
    }
}
?>