<?php
//init a new game session
$mySession = new GameSession(SESSION_ID, DEVICE_IP);

foreach($mySession->getCurrentGames() as $game) {
    $gameName = str_replace("-", " ", $game['game_name']);
    ?>
    <div class="mdl-card mdl-shadow--6dp player" style="min-height: 0;">
        <a href="/join/index.php?unique-code=<?php echo $game['unique_code']; ?>">
            <div class="mdl-card__supporting-text">
                <h5 style="margin: 0; text-transform: capitalize;"><?php echo $game['unique_code']; ?> - <?php echo $gameName; ?></h5>
            </div>
        </a>
    </div>
    <?php
}
?>