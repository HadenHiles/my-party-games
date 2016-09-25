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
<label for="drinksToWinSlider">Drinks To Win</label>
<input type="number" class="stealth" name="drinks_to_win" id="drinksToWinInput" value="<?php echo $drinksToWin ?>" style="float: right; width: 60px; text-align: right;" readonly />
<div class="clear"></div>
<input class="mdl-slider mdl-js-slider" id="drinksToWinSlider" type="range" min="5" max="25" value="<?php echo $drinksToWin ?>" step="5" />

<input type="hidden" name="fields" value="rounds,drinks_to_win" />

<script type="text/javascript">
    var roundSlider = document.querySelector('#roundSlider');
    var $roundsInput = $('#roundsInput');
    var drinksToWinSlider = document.querySelector('#drinksToWinSlider');
    var $drinksToWinInput = $('#drinksToWinInput');

    roundSlider.addEventListener('input', function() {
        $roundsInput.val(this.value);
    });

    drinksToWinSlider.addEventListener('input', function() {
        $drinksToWinInput.val(this.value);
    });
</script>