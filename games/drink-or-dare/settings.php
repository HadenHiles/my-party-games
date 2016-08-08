<?php
require_once("class.DrinkOrDare.php");

$drinkOrDare = new DrinkOrDare(intval($_SESSION['user']['code']), intval($_SESSION['user']['userid']), 3, 1, 10);
$drinkOrDare = $drinkOrDare->getDrinkOrDare();
$drinkOrDare = $drinkOrDare[0];
?>
<!-- Slider with Starting Value -->
<label for="roundSlider">Rounds</label>
<input type="number" class="stealth" name="rounds" id="roundsInput" value="<?php echo $drinkOrDare['total_rounds']; ?>" style="float: right; width: 35px; text-align: right;" readonly />
<div class="clear"></div>
<input class="mdl-slider mdl-js-slider" id="roundSlider" type="range" min="1" max="5" value="<?php echo $drinkOrDare['total_rounds']; ?>" step="1" />
<label for="drinksToWinSlider">Drinks To Win</label>
<input type="number" class="stealth" name="drinks_to_win" id="drinksToWinInput" value="<?php echo $drinkOrDare['drinks_to_win']; ?>" style="float: right; width: 35px; text-align: right;" readonly />
<div class="clear"></div>
<input class="mdl-slider mdl-js-slider" id="drinksToWinSlider" type="range" min="5" max="25" value="<?php echo $drinkOrDare['drinks_to_win']; ?>" step="5" />

<input type="hidden" name="fields" value="rounds,drinks_to_win" />

<script>
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