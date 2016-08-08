<?php

class DrinkOrDare {

    private $state;
    private $gameid;
    private $total_rounds;
    private $current_round;

    public function __construct($game_id = 0, $total_rounds = 5, $current_round = 1) {

        $this->gameid = $game_id;
        $this->state = 1;
        $this->total_rounds = $total_rounds;
        $this->current_round = $current_round;
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

                    return true;
                }
            }
        } else {
            throw new Exception("Cannot load game without game id.");
        }

        return false;
    }

    public function getState() {

        return $this->state;
    }
}
?>