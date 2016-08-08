<!-- Slider with Starting Value -->
<label for="roundSlider">Rounds</label>
<input type="text" class="stealth" name="rounds" id="roundsInput" value="3" style="float: right; width: 30px; text-align: right;" readonly />
<div class="clear"></div>
<input class="mdl-slider mdl-js-slider" id="roundSlider" type="range" min="1" max="5" value="3" step="1" />
<label for="drinksToWinSlider">Drinks To Win</label>
<input type="text" class="stealth" name="drinks_to_win" id="drinksToWinInput" value="10" style="float: right; width: 30px; text-align: right;" readonly />
<div class="clear"></div>
<input class="mdl-slider mdl-js-slider" id="drinksToWinSlider" type="range" min="5" max="25" value="10" step="5" />

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