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
    private $activePlayer;
    private $freePass;
    private $isStarted;

    /**
     * DrinkOrDare constructor.
     * @param int $game_id
     * @param int $userid
     * @param int $total_rounds
     * @param int $current_round
     * @param int $drinksToWin
     */
    public function __construct($game_id = 0, $userid = 0, $total_rounds = 3, $current_round = 1, $drinksToWin = 10, $freePass = 1) {

        $this->gameid = $game_id;
        $this->state = 1;
        $this->total_rounds = $total_rounds;
        $this->current_round = $current_round;
        $this->userid = $userid;
        $this->hasCurrentDare = false;
        $this->drinksToWin = $drinksToWin;
        $this->numPlayers = 0;
        $this->activePlayer = 0;
        $this->freePass = $freePass;
        $this->isStarted = false;
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

    public function destroy($gameId = 0) {
        global $db;

        if (!empty($gameId)) {
            //delete game
//            $sql = 'DELETE FROM drink_or_dare AS dod
//                    LEFT JOIN drink_or_dare_order AS dodo ON dod.game_id = dodo.game_id
//                    LEFT JOIN drink_or_dare_user_dares AS dodud ON dod.game_id = dodud.game_id
//                    WHERE game_id = :game_id';
            $sql = 'DELETE FROM drink_or_dare                
                    WHERE game_id = :game_id';

            $result = $db->prepare($sql);
            $result->bindValue(":game_id", $gameId);

            if ($result->execute() && $result->errorCode() == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $isStarted
     * @return bool
     * @throws Exception
     */
    public function start($play = true) {

        global $db, $game, $user;

        //make sure we have players
        if (empty($game['users'])) {
            throw new Exception("Game cannot start without players");
            return false;
        }

        //find amount of players excluding all displays
        foreach ($game['users'] as $tempUser) {
            if ($tempUser['is_display'] == 0) {
                $this->numPlayers++;
            }
        }

        if ($play && $this->numPlayers == 0) {
            throw new Exception("Cannot start game with at least 1 player.");
            return false;
        }

        //make sure we have a game id to work with
        if (!empty($this->gameid)) {

            //check if game has already started
            if (!self::isStarted($this->gameid, false)) {
                /*
                 * game doesnt exist, create it here
                 */
                $sql = 'INSERT INTO drink_or_dare
                        (game_id, state, total_rounds, current_round, drinks_to_win, is_started) 
                        VALUES
                        (:gameid, :state, :total_rounds, :current_round, :drinks_to_win, :is_started)';

                $result = $db->prepare($sql);
                $result->bindParam(":gameid", $this->gameid);
                $result->bindParam(":state", $this->state);
                $result->bindParam(":total_rounds", $this->total_rounds);
                $result->bindParam(":current_round", $this->current_round);
                $result->bindParam(":drinks_to_win", $this->drinksToWin);

                $isStarted = ($play ? 1 : 0);
                $result->bindParam(":is_started", $isStarted);

                if ($result->execute() && $result->errorCode() == 0) {
                    return true;
                }
            } else {
                /*
                 * game exists already
                 */
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

                    //check if not started but should be
                    if (!$result['is_started'] && $play) {

                        $sql = 'UPDATE drink_or_dare
                                SET is_started = 1
                                WHERE game_id = :gameid';

                        $result = $db->prepare($sql);
                        $result->bindParam(":gameid", $this->gameid);

                        if ($result->execute() && $result->errorCode() == 0) {
                            //yay
                        }
                    }

                    //set active players
                    if ($this->state == 3) {

                        $sql = 'SELECT dodo.*, dodo.free_pass, dodud.* FROM drink_or_dare_order AS dodo
                                LEFT JOIN drink_or_dare_user_dares AS dodud ON dodo.user_id = dodud.assign_to_id
                                WHERE dodo.game_id = :gameid
                                AND dodud.completed = 0
                                AND dodud.round_number = :roundnumber                                
                                ORDER BY dodo.id
                                LIMIT 1';

                        $order = $db->prepare($sql);
                        $order->bindValue(":gameid", $this->gameid);
                        $order->bindValue(":roundnumber", $this->current_round);

                        if ($order->execute() && $order->errorCode() == 0 && $order->rowCount() > 0) {

                            $order = $order->fetch(PDO::FETCH_ASSOC);
                            $this->activePlayer = $user->getUser($order['assign_to_id']);
                            $this->freePass = $order['free_pass'];
                        }
                    }

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

                    return true;
                }
            }
        } else {
            throw new Exception("Cannot load game without game id.");
        }

        return false;
    }

    /**
     * @return bool
     */
    public function freePass() {

        global $db;

        if ($this->freePass > 0) {

            $this->freePass--;

            $sql = 'UPDATE drink_or_dare_order SET free_pass = :freepass
                    WHERE user_id = :userid';

            $result = $db->prepare($sql);
            $result->bindValue(":freepass", $this->freePass);
            $result->bindValue(":userid", $this->userid);

            if ($result->execute() && $result->errorCode() == 0) {

                $sql = 'UPDATE drink_or_dare_user_dares SET completed = 1 
                        WHERE game_id = :gameid 
                        AND assign_to_id = :assigntoid
                        AND round_number = :roundnumber';

                $result = $db->prepare($sql);
                $result->bindValue(":gameid", $this->gameid);
                $result->bindValue(":assigntoid", $this->userid);
                $result->bindValue(":roundnumber", $this->current_round);

                if ($result->execute() && $result->errorCode() == 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getEndResults() {

        global $db;

        return false;
    }

    /**
     * @return bool
     */
    public function resetGame() {

        global $db, $user;

        $sql = 'DELETE dodo, dodud, dodv FROM drink_or_dare AS dod
                LEFT JOIN drink_or_dare_order AS dodo ON dodo.game_id = dod.game_id
                LEFT JOIN drink_or_dare_user_dares AS dodud ON dodud.game_id = dod.game_id
                LEFT JOIN drink_or_dare_votes AS dodv ON dodv.dare_id = dodud.id 
                WHERE dod.game_id = :gameid';

        $result = $db->prepare($sql);
        $result->bindValue(":gameid", $this->gameid);

        if ($result->execute() && $result->errorCode() == 0) {

            // Update the user's score
            $user->resetScore($this->gameid);

            $sql = 'UPDATE drink_or_dare SET state = 1, current_round = 1 WHERE game_id = :gameid';

            $result = $db->prepare($sql);
            $result->bindValue(":gameid", $this->gameid);

            if ($result->execute() && $result->errorCode() == 0) {
                //make sure to reset the scores as well
                $sql = 'UPDATE drink_or_dare_order SET score = 0 WHERE game_id = :gameid';

                $result = $db->prepare($sql);
                $result->bindValue(":gameid", $this->gameid);
                $result->execute();

                return true;
            }
        }

        return false;
    }

    /**
     * @param $gameId
     * @param int $checkIsStarted
     * @return bool
     */
    public function isStarted($gameId = 0, $checkGameInProgress = false) {
        global $db;

        if (!empty($gameId)) {
            $sql = 'SELECT id FROM drink_or_dare 
                    WHERE game_id = :gameid';

            if ($checkGameInProgress) {
                $sql .= ' AND is_started = 1';
            }

            $result = $db->prepare($sql);
            $result->bindParam(":gameid", $gameId);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                return true;
            }
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

        if (!empty($gameId)) {
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
        } else {
            throw new Exception("Cannot update game without game id");
        }
        return false;
    }

    /**
     * @param $text
     * @return bool
     * @throws Exception
     */
    public function setDare($text = '', $drinksWorth = 1) {

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

            if ($result->execute() && $result->errorCode() == 0) {

                if ($result->rowCount() == 0) {

                    //dare doesnt exist for this user and round
                    $sql = 'INSERT INTO drink_or_dare_user_dares
                            (user_id, dare, round_number, game_id, drinks_worth) 
                            VALUES
                            (:userid, :dare, :round_number, :game_id, :drinksworth)';

                    $result = $db->prepare($sql);
                    $result->bindParam(":userid", $this->userid);
                    $result->bindParam(":dare", $text);
                    $result->bindParam(":round_number", $this->current_round);
                    $result->bindParam(":game_id", $this->gameid);
                    $result->bindParam(":drinksworth", $drinksWorth);

                    if ($result->execute() && $result->errorCode() == 0) {
                        return true;
                    }
                }
            }
        } else {
            throw new Exception("Cannot set dare without game id.");
        }

        return false;
    }

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function setDarePeeked() {

        global $db;

        $sql = 'UPDATE drink_or_dare_user_dares
                SET has_peeked = 1 
                WHERE assign_to_id = :userid
                AND round_number = :roundnumber
                AND game_id = :gameid';

        $result = $db->prepare($sql);
        $result->bindValue(":userid", $this->userid);
        $result->bindValue(":gameid", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);

        if ($result->execute() && $result->errorCode() == 0) {

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getIsMyTurn() {
        if ($this->activePlayer['id'] == $this->userid) {
            return true;
        }

        return false;
//        global $db;
//
//        $sql = 'SELECT * FROM drink_or_dare_user_dares AS dodud
//                WHERE dodud.game_id = :gameid
//                AND dodud.completed = 0
//                AND dodud.round_number = :roundnumber
//                LIMIT 1';
//
//        $result = $db->prepare($sql);
//        $result->bindValue(":gameid", $this->gameid);
//        $result->bindValue(":roundnumber", $this->current_round);
//
//        //var_dump($this->activePlayer);
//
//        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
//
//            $result = $result->fetch(PDO::FETCH_ASSOC);
//
//            if ($result['assign_to_id'] == $this->activePlayer) {
//                return true;
//            }
//        }
//
//        return false;
    }

    /**
     * @param bool $activePlayer
     * @return bool
     */
    public function getDare($activePlayer = false, $fullDetails = false) {
        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE assign_to_id = :userid
                AND round_number = :roundnumber';

        $result = $db->prepare($sql);
        $result->bindValue(":roundnumber", $this->current_round);

        if ($activePlayer) {

            $result->bindValue(":userid", $this->activePlayer['id']);

        } else {

            $result->bindValue(":userid", $this->userid);
        }

        //query
        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $dare = $result->fetch(PDO::FETCH_ASSOC);

            if ($fullDetails) {
                return $dare;
            }
            return $dare['dare'];
        }
        return false;
    }

    /**
     * @return array|bool
     */
    public function getCardsInfo() {
        global $db;

        if (!empty($this->gameid)) {
            $sql = 'SELECT dodud.id AS dareid, dodud.user_id, users.display_name, users.picture, dodud.dare, dodud.completed,
                    dodud.assign_to_id, card_picked, has_peeked, drinks_worth
                   FROM drink_or_dare_user_dares AS dodud
                   LEFT JOIN users ON dodud.assign_to_id = users.id
                   WHERE dodud.game_id = :gameid
                   AND dodud.round_number = :round_number';

            $result = $db->prepare($sql);
            $result->bindValue(":gameid", $this->gameid);
            $result->bindValue(":round_number", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                return $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return false;
    }

    /**
     * @param bool $getInformation
     * @param int $cardId
     * @return bool|mixed|PDOStatement
     */
    public function getOwner($getInformation = false, $cardId = 0) {
        global $db;

        if (!empty($cardId)) {
            $sql = 'SET @id=0;';
            $normalResult = $db->prepare($sql);
            $normalResult->execute();

            $fuckingSql = 'SELECT @id := @id+1 AS "id", dodud.*, users.display_name, users.picture 
                           FROM drink_or_dare_user_dares AS dodud 
                           LEFT JOIN users ON dodud.assign_to_id = users.id 
                           WHERE dodud.game_id = :gameid
                           AND dodud.round_number = :round_number';

            $result = $db->prepare($fuckingSql);
            $result->bindValue(":gameid", $this->gameid);
            $result->bindValue(":round_number", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                if ($getInformation) {
                    $result = $result->fetchAll(PDO::FETCH_ASSOC);
                    return $result[$cardId-1];
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function checkHasPickedCard() {
        global $db;

        if (!empty($this->gameid)) {

            $sql = 'SELECT * FROM drink_or_dare_user_dares 
                    WHERE game_id = :game_id 
                    AND assign_to_id = :userid
                    AND round_number = :roundnumber';

            $result = $db->prepare($sql);
            $result->bindValue(":game_id", $this->gameid);
            $result->bindValue(":roundnumber", $this->current_round);
            $result->bindValue(":userid", $this->userid);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                return true;
            }
        } else {
            throw new Exception ("Cannot check database without id");
        }

        return false;
    }

    /**
     * @param bool $activePlayer
     * @return bool
     */
    public function checkHasPeeked($activePlayer = false) {

        global $db;

        if (!empty($this->gameid)) {
            $sql = 'SELECT * FROM drink_or_dare_user_dares 
                    WHERE game_id = :game_id 
                    AND assign_to_id = :userid 
                    AND round_number = :roundnumber
                    AND has_peeked = 1';

            $result = $db->prepare($sql);
            $result->bindValue(":roundnumber", $this->current_round);
            $result->bindValue(":game_id", $this->gameid);

            if ($activePlayer) {
                $result->bindValue(":userid", $this->activePlayer['id']);
                //echo $this->activePlayer;
            } else {
                $result->bindValue(":userid", $this->userid);
            }

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {
                return true;
            }
        } else {
            throw new Exception ("cannot check database without gameid");
        }
        return false;
    }

    /**
     * @param $vote
     * @return bool
     */
    public function castVote($vote) {

        global $db;

        $dare = self::getDare(true, true);

        $sql = 'SELECT * FROM drink_or_dare_votes AS dodv
                LEFT JOIN drink_or_dare_user_dares AS dodud ON dodud.id = dodv.dare_id
                WHERE dodud.game_id = :game_id
                AND dodud.round_number = :roundnumber
                AND dodv.user_id = :userid
                AND dodud.id = :dareid';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);
        $result->bindValue(":userid", $this->userid);
        $result->bindValue(":dareid", $dare['id']);
        //var_dump($this->userid);

        if ($result->execute() && $result->errorCode() == 0) {

            if ($result->rowCount() > 0) {

                //changing dare
                $sql = 'UPDATE drink_or_dare_votes 
                        SET vote = :vote
                        WHERE dare_id = :dareid
                        AND user_id = :userid';

                $result = $db->prepare($sql);
                $result->bindValue(":dareid", $dare['id']);
                $result->bindValue(":vote", $vote);
                $result->bindValue(":userid", $this->userid);

                if ($result->execute() && $result->errorCode() == 0) {

                    return "changed";
                }

            } else {

                //inserting dare
                $sql = 'INSERT INTO drink_or_dare_votes (dare_id, vote, user_id)
                        VALUES (:dareid, :vote, :userid)';

                $result = $db->prepare($sql);
                $result->bindValue(":dareid", $dare['id']);
                $result->bindValue(":vote", $vote);
                $result->bindValue(":userid", $this->userid);

                if ($result->execute() && $result->errorCode() == 0) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $activeUser
     * @return bool
     */
    public function getDrinksWorth($activeUser = false) {

        global $db;

        $sql = 'SELECT drinks_worth
                FROM drink_or_dare_user_dares
                WHERE assign_to_id = :activeplayer
                AND game_id = :gameid
                AND round_number = :roundnumber';
        
        $result = $db->prepare($sql);
        $result->bindValue(":activeplayer", $this->activePlayer['id']);
        $result->bindValue(":gameid", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $result = $result->fetch(PDO::FETCH_ASSOC);

            return $result['drinks_worth'];
        }

        return false;
    }

    /**
     * @return bool
     */
    public function finishCurrentDare()  {

        global $db;

        if (self::checkAllVotesCast()) {

            $sql = 'UPDATE drink_or_dare_user_dares 
                    SET completed = 1 
                    WHERE game_id = :gameid
                    AND assign_to_id = :userid
                    AND round_number = :currentround';

            $result = $db->prepare($sql);
            $result->bindValue(":gameid", $this->gameid);
            $result->bindValue(":userid", $this->userid);
            $result->bindValue(":currentround", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0) {

                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkAllVotesCast() {
        global $db;

        $dare = self::getDare(true, true);

        $sql = 'SELECT * FROM drink_or_dare_votes AS dodv               
                WHERE dare_id = :dareid';

        $result = $db->prepare($sql);
        $result->bindValue(":dareid", $dare['id']);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() >= ($this->numPlayers - 1)) {

           return true;
        }

        return false;
    }

    /**
     * @return array|bool|PDOStatement
     */
    public function getVotes() {

        global $db;

        $dare = self::getDare(true, true);

        $sql = 'SELECT vote FROM drink_or_dare_votes AS dodv
                LEFT JOIN drink_or_dare_user_dares AS dodud ON dodud.id = dodv.dare_id
                WHERE dodud.game_id = :game_id
                AND dodud.round_number = :roundnumber
                AND dare_id = :dareid';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);
        $result->bindValue(":dareid", $dare['id']);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

            $result = $result->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        }

        return false;
    }

    /**
     * @param $number
     * @return bool
     */
    public function pickCard($number = 0) {

        global $db;

        if (!empty($number)) {
            $sql = 'SELECT * FROM drink_or_dare_user_dares 
                    WHERE game_id = :game_id 
                    AND round_number = :roundnumber';

            $result = $db->prepare($sql);
            $result->bindValue(":game_id", $this->gameid);
            $result->bindValue(":roundnumber", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                $results = $result->fetchAll(PDO::FETCH_ASSOC);

                if ($results[$number - 1]['assign_to_id'] == 0) {

                    $sql = 'UPDATE drink_or_dare_user_dares 
                            SET assign_to_id = :userid,
                            card_picked = :cardpicked
                            WHERE id = :randomid';

                    $result = $db->prepare($sql);
                    $result->bindValue(":userid", $this->userid);
                    $result->bindValue(":randomid", $results[$number - 1]['id']);
                    $result->bindValue(":cardpicked", $number);

                    if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() > 0) {

                        $sql = 'SELECT * FROM drink_or_dare_order WHERE game_id = :gameid AND user_id = :userid';

                        $result = $db->prepare($sql);
                        $result->bindValue(":gameid", $this->gameid);
                        $result->bindValue(":userid", $this->userid);

                        if ($result->execute() && $result->errorCode() == 0) {

                            if ($result->rowCount() == 0) {

                                $sql = 'INSERT INTO drink_or_dare_order (game_id, user_id, free_pass)
                                        VALUES (:gameid, :userid, :freepass)';

                                $result = $db->prepare($sql);
                                $result->bindValue(":gameid", $this->gameid);
                                $result->bindValue(":userid", $this->userid);
                                $result->bindValue(":freepass", $this->freePass);

                                if ($result->execute() && $result->errorCode() == 0) {

                                }
                            }
                        }

                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function checkAllDaresPlayed() {

        global $db;

        $sql = 'SELECT * FROM drink_or_dare_user_dares 
                WHERE game_id = :game_id 
                AND round_number = :roundnumber
                AND completed = 0';

        $result = $db->prepare($sql);
        $result->bindValue(":game_id", $this->gameid);
        $result->bindValue(":roundnumber", $this->current_round);

        if ($result->execute() && $result->errorCode() == 0 && $result->rowCount() == 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $isHost
     * @return bool
     */
    public function checkNextRound() {

        global $db;

        if ($this->current_round < $this->total_rounds) {

            $this->current_round++;

            $sql = 'UPDATE drink_or_dare 
                    SET current_round = :currentround 
                    WHERE game_id = :gameid';

            $result = $db->prepare($sql);
            $result->bindValue(":gameid", $this->gameid);
            $result->bindValue(":currentround", $this->current_round);

            if ($result->execute() && $result->errorCode() == 0) {
                $sql = "DELETE FROM drink_or_dare_user_dares WHERE game_id = :gameid AND round_number = :currentround";
                $result = $db->prepare($sql);
                $result->bindValue(":gameid", $this->gameid);
                $result->bindValue(":currentround", $this->current_round - 1);

                if ($result->execute() && $result->errorCode() == 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkAllRoundsComplete() {

        if ($this->current_round >= $this->total_rounds) {

            return true;
        }

        return false;
    }

    /**
     * @param $isHost
     * @return bool
     */
    public function checkNextState($isHost) {

        global $db;

        if (!empty($this->state) && $isHost) {
            //get current state and compare to see if state should be updated
            $previous = $this->state;

            if ($this->state == 1 && self::checkDaresComplete()) {
                //change to state 2
                $this->state = 2;
            } else if ($this->state == 2 && self::checkCardsPickedComplete()) {
                //change to state 3
                $this->state = 3;
            } else if ($this->state == 3 && self::checkAllDaresPlayed()) {
                //check to state 4
                $this->state = 4;
            } else if ($this->state == 4) {
                //check to reset to first round or complete game
                if (self::checkAllRoundsComplete()) {
                    $this->state = 5;
                } else if (self::checkNextRound()) {
                    $this->state = 1;
                }
            }

            //if state change cccured, update database
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
     * @param $userId
     * @return bool|mixed
     */
    public function getScore($userId) {
        global $db;

        $sql = 'SELECT score FROM drink_or_dare_order WHERE game_id = :gameid AND user_id = :userid';

        $result = $db->prepare($sql);
        $result->bindParam(":gameid", $this->gameid);
        $result->bindParam(":userid", $userId);

        if ($result->execute() && $result->errorCode() == 0) {
            $result = $result->fetch(PDO::FETCH_ASSOC);
            return $result['score'];
        }

        return false;
    }

    /**
     * @param $userId
     * @param $score
     * @return bool
     */
    public function updateScore($userId, $score) {
        global $db;

        $sql = 'UPDATE drink_or_dare_order SET score = :score WHERE game_id = :gameid AND user_id = :userid';

        $result = $db->prepare($sql);
        $result->bindParam(":gameid", $this->gameid);
        $result->bindParam(":userid", $userId);
        $result->bindParam(":score", $score);

        if ($result->execute() && $result->errorCode() == 0) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getUserId() {

        return $this->userid;
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
    public function getCurrentRound() {

        return $this->current_round;
    }

    /**
     * @return int
     */
    public function getDrinksToWin() {

        return $this->drinksToWin;
    }

    /**
     * @return int
     */
    public function getActivePlayer() {

        return $this->activePlayer;
    }

    /**
     * @return int
     */
    public function getNumPlayers() {

        return $this->numPlayers;
    }
}
?>