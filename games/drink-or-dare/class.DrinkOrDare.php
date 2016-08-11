<?php

class DrinkOrDare {

    private $state;
    private $gameid;
    private $total_rounds;
    private $current_round;
    private $userid;
    private $hasCurrentDare;
    private $drinksToWin;
    private $numPlayers;

    public function __construct($game_id = 0, $userid = 0, $total_rounds = 3, $current_round = 1, $drinksToWin = 10) {

        $this->gameid = $game_id;
        $this->state = 1;
        $this->total_rounds = $total_rounds;
        $this->current_round = $current_round;
        $this->userid = $userid;
        $this->hasCurrentDare = false;
        $this->drinksToWin = $drinksToWin;
        $this->numPlayers = 0;
    }

    /**
     * @return array|bool
     */
    public function getDrinkOrDare() {
        global $db;

        $sql = 'SELECT * FROM drink_or_dare WHERE game_id = :gameid';

        $result = $db->prepare($sql);
        $result->bindParam(":gameid", $this->gameid);
        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
            $result = $result->fetch(PDO::FETCH_ASSOC);

            $this->total_rounds = $result['total_rounds'];
            $this->current_round = $result['current_round'];
            $this->drinksToWin = $result['drinks_to_win'];
            $this->state = $result['state'];
            $this->gameid = $result['game_id'];

            return $result;
        }
        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function start() {

        global $db, $game;

        $this->numPlayers = count($game['users']);

        if (!empty($this->gameid)) {

            if (!$this->isStarted($this->gameid)) {
                //game doesnt exist
                $sql = 'INSERT INTO drink_or_dare
                        (game_id, state, total_rounds, current_round, drinks_to_win) 
                        VALUES
                        (:gameid, :state, :total_rounds, :current_round, :drinks_to_win)';

                $result = $db->prepare($sql);
                $result->bindParam(":gameid", $this->gameid);
                $result->bindParam(":state", $this->state);
                $result->bindParam(":total_rounds", $this->total_rounds);
                $result->bindParam(":current_round", $this->current_round);
                $result->bindParam(":drinks_to_win", $this->drinksToWin);

                if ($result->execute() && $result->errorCode() == 0) {
                    return true;
                }
            } else {
                //game exists
                $sql = 'SELECT * FROM drink_or_dare WHERE game_id = :gameid';

                $result = $db->prepare($sql);
                $result->bindParam(":gameid", $this->gameid);

                if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                    $result = $result->fetch(PDO::FETCH_ASSOC);

                    //var_dump($result);
                    $this->state = $result['state'];
                    $this->total_rounds = $result['total_rounds'];
                    $this->current_round = $result['current_round'];
                    $this->drinksToWin = $result['drinks_to_win'];

                    //get user dare state
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
            }
        } else {
            throw new Exception("Cannot load game without game id.");
        }

        return false;
    }

    /**
     * @param $gameId
     * @return bool
     */
    public function isStarted($gameId) {
        global $db;

        $sql = 'SELECT * FROM drink_or_dare WHERE game_id = :gameid';

        $result = $db->prepare($sql);
        $result->bindParam(":gameid", $gameId);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $gameId
     * @param $state
     * @param $totalRounds
     * @param $currentRound
     * @param $drinksToWin
     * @return bool
     */
    public function update($gameId, $state, $totalRounds, $currentRound, $drinksToWin) {
        global $db;

        $sql = 'UPDATE drink_or_dare SET 
                  state = :state, 
                  total_rounds = :total_rounds, 
                  current_round = :current_round, 
                  drinks_to_win = :drinks_to_win
                WHERE game_id = :gameid';

        $result = $db->prepare($sql);
        $result->bindParam(":gameid", $gameId);
        $result->bindParam(":state", $state);
        $result->bindParam(":total_rounds", $totalRounds);
        $result->bindParam(":current_round", $currentRound);
        $result->bindParam(":drinks_to_win", $drinksToWin);

        if ($result->execute() && $result->errorCode() == 0) {
            return true;
        }
        return false;
    }

    public function setDare($text) {

        global $db;

        if (!empty($this->gameid)) {

            $sql = 'SELECT * FROM drink_or_dare_user_dares 
                    WHERE user_id = :userid 
                    AND round_number = :round_number
                    AND game_id = :game_id';

            $result = $db->prepare($sql);
            $result->bindParam(":userid", $this->userid);
            $result->bindParam(":round_number", $this->current_round);
            $result->bindParam(":game_id", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            } else {

                //dare doesnt exist for this user and round
                $sql = 'INSERT INTO drink_or_dare_user_dares
                        (user_id, dare, round_number, game_id) 
                        VALUES
                        (:userid, :dare, :round_number, :game_id)';

                $result = $db->prepare($sql);
                $result->bindParam(":userid", $this->userid);
                $result->bindParam(":dare", $text);
                $result->bindParam(":round_number", $this->current_round);
                $result->bindParam(":game_id", $this->gameid);

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
        global $db, $game;

        if ($this->numPlayers > 0) {

            $userids = array();

            //get an array of the userids playing in the current game
            for ($i = 0; $i < $this->numPlayers; $i++) {

                $userids[] = $game['users'][$i]['id'];
            }

            $sql = 'SELECT * FROM drink_or_dare_user_dares 
                    WHERE user_id IN ('.implode(",", $userids).') 
                    AND round_number = :round_number';

            $result = $db->prepare($sql);
            $result->bindValue(":round_number", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() >= $this->numPlayers) {
                
                return true;
            }
        }

        return false;
    }

    public function checkCardsPickedComplete() {

        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE game_id = :game_id AND assign_to_id != 0 AND round_number = :roundnumber';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() >= $this->numPlayers) {

            return true;
        }

        return false;
    }

    public function getWhoseTurn() {
        global $db;
        
        $sql = 'SELECT * FROM drink_or_dare_order AS dodo
                LEFT JOIN drink_or_dare_user_dares AS dodud ON dodo.user_id = dodud.user_id
                WHERE dodo.game_id = :gameid
                AND dodud.completed = 0
                AND dodud.round_number = :roundnumber
                ORDER BY dodo.id
                LIMIT 1';

        $result = $db->prepare($sql);
        $result->bindValue(":gameid", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $result = $result->fetch(PDO::FETCH_ASSOC);

            if ($result['user_id'] == $this->userid) {

                return true;
            }
        }

        return false;
    }

    public function getDare() {
        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE assign_to_id = :userid';

        $result = $db->prepare($sql);
        $result->bindValue(":userid", $this->userid);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $dare = $result->fetch(PDO::FETCH_ASSOC);

            $sql = 'UPDATE drink_or_dare_user_dares 
                    SET has_peeked = 1 WHERE id = :dareid';

            $result = $db->prepare($sql);
            $result->bindValue(":dareid", $dare['id']);

            if ($result->execute() && $result->errorCode() == 0) {

            }

            return $dare['dare'];
        }

        return false;
    }

    public function getOwner($getInformation = false, $cardId = 0) {
        global $db;

        if (!empty($cardId)) {
            $sql = 'SET @id=0; 
                   SELECT @id := @id+1 AS "id", dodud.*, users.* 
                   FROM drink_or_dare_user_dares AS dodud 
                   LEFT JOIN users ON dodud.user_id = users.id 
                   WHERE dodud.game_id = :gameid
                   AND id = :cardid';

            $result = $db->prepare($sql);
            $result->bindValue(":cardid", $cardId);
            $result->bindValue(":gameid", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                if ($getInformation) {
                    $result = $result->fetch(PDO::FETCH_ASSOC);
                    return $result;
                }

                return true;
            }
        }

        return false;
    }

    public function checkHasPickedCard() {
        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE game_id = :game_id 
                AND assign_to_id != 0 
                AND round_number = :roundnumber
                AND user_id = :userid';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);
        $result->bindValue(":userid", $this->userid);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function checkHasPeeked() {

        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE game_id = :game_id 
                AND assign_to_id != 0 
                AND round_number = :roundnumber
                AND has_peeked = 1
                AND user_id = :userid';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);
        $result->bindValue(":userid", $this->userid);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function pickCard($number) {

        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE game_id = :game_id AND assign_to_id = 0';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $results = $result->fetchAll(PDO::FETCH_ASSOC);

            $sql = 'UPDATE drink_or_dare_user_dares 
                    SET assign_to_id = :userid,
                    card_picked = :cardpicked
                    WHERE id = :randomid';

            $result = $db->prepare($sql);
            $result->bindValue(":userid", $this->userid);
            $result->bindValue(":randomid", $results[$number-1]['id']);
            $result->bindValue(":cardpicked", $number);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $sql = 'SELECT * FROM drink_or_dare_order WHERE game_id = :gameid AND user_id = :userid';

                $result = $db->prepare($sql);
                $result->bindValue(":gameid", $this->gameid);
                $result->bindValue(":userid", $this->userid);

                if ($result->execute() && $result->errorCode() == 0) {

                    if ($result->rowCount() == 0) {

                        $sql = 'INSERT INTO drink_or_dare_order (game_id, user_id)
                                VALUES (:gameid, :userid)';

                        $result = $db->prepare($sql);
                        $result->bindValue(":gameid", $this->gameid);
                        $result->bindValue(":userid", $this->userid);

                        if ($result->execute() && $result->errorCode() == 0) {

                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    public function checkNextState() {

        global $db;

        if (!empty($this->state)) {

            $previous = $this->state;

            if ($this->state == 1 && self::checkDaresComplete()) {
                $this->state = 2;
            } else if ($this->state == 2 && self::checkCardsPickedComplete()) {
                $this->state = 3;
            }

            if ($previous != $this->state) {

                $sql = 'UPDATE drink_or_dare SET state = :state WHERE game_id = :game_id';

                $result = $db->prepare($sql);
                $result->bindParam(":game_id", $this->gameid);
                $result->bindParam(":state", $this->state);

                if ($result->execute() && $result->errorCode() == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getState() {

        return $this->state;
    }

    /**
     * @return bool
     */
    public function getHasCurrentDare() {

        return $this->hasCurrentDare;
    }

    /**
     * @return int
     */
    public function getTotalRounds() {

        return $this->total_rounds;
    }

    /**
     * @return int
     */
    public function getDrinksToWin() {

        return $this->drinksToWin;
    }
}
?>