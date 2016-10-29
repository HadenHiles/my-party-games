<?php
require_once($_SERVER['DOCUMENT_ROOT']."/includes/common.php");
require_once(ROOT."/games/drink-or-dare/class.DrinkOrDare.php");

$drinkOrDare = new DrinkOrDare($_SESSION['game']['code'], $_SESSION['user']['id']);
$drinkOrDare->start(false);
$totalRounds = $drinkOrDare->getTotalRounds();
$drinksToWin = $drinkOrDare->getDrinksToWin();

?>
<!-- Slider with Starting Value -->
<label for="roundSlider">Rounds</label>
<input type="number" class="stealth" name="rounds" id="roundsInput" value="<?php echo $totalRounds ?>" style="float: right; width: 60px; text-align: right;" readonly />
<div class="clear"></div>
<input class="mdl-slider mdl-js-slider" id="roundSlider" type="range" min="1" max="5" value="<?php echo $totalRounds ?>" step="1" />

<input type="hidden" name="fields" value="rounds" />

<script type="text/javascript">
    var roundSlider = document.querySelector('#roundSlider');
    var $roundsInput = $('#roundsInput');

    roundSlider.addEventListener('input', function() {
        $roundsInput.val(this.value);
    });
</script>