
$(function(){
    var parent = document.getElementById('game-content');

    var updateIneterval = setInterval(function() {
        $.ajax({
            url:"get-update-game.php",
            method:"POST"
        }).done(function(result) {
            //console.log(result);

            if (result = JSON.parse(result)) {
                console.log(result);

                //check for error messages
                if (result.error != "" && typeof result.error != "undefined") {
                    msg("dialog", false, result.error);
                }

                var state = parseInt(result.state);

                //otherwise switch based on game state
                switch (state) {

                    case 1:
                        //users are creating dares

                        break;

                    case 2:
                        //shuffling and assigning dares
                        document.getElementById('game-stage-2').style.display = "block";
                        break;

                    case 3:
                        //looping users carrying out dares
                        document.getElementById('game-stage-3').style.display = "block";
                        break;

                    case 4:
                        //incrementing rounds and check for game completion
                        //hideAll(3);

                        break;

                    case 5:
                        //game completed show stats

                        break;

                    case 6:
                        //special case -- veto dare

                        break;

                    case 6:
                        //special case -- free pass

                        break;
                } //end of switch

            } //end of parse json object if
        }); //end of ajax call
    }, 1000); //end of interval

}); //end of document load

function setDare() {

    var textContainer = document.getElementById('dare-text');

    if (textContainer.value != "") {
        //ajax call to set dare
        $.ajax({
            url:"set-dare.php",
            method:"POST",
            data:{"text":textContainer.value}
        }).done(function(result) {
            console.log(result);

            if (result = JSON.parse(result)) {
                
                hideAll(result.state);

                if (result.status == true) {
                    msg(false, false, "game-drink-or-dare-submitted-dare");
                    document.getElementById('game-stage-1').style.display = "none";
                    document.getElementById('game-stage-1-waiting').style.display = "block";
                }
            }
        });
    } else {
        msg(false, false, 'game-drink-or-dare-empty-dare');
    }
}

function pickCard(number) {
    //console.log("number: " + number);
    if (number > 0) {
        //ajax call to set dare
        $.ajax({
            url:"pick-card.php",
            method:"POST",
            data:{"number":number}
        }).done(function(result) {
            console.log(result);

            if (result = JSON.parse(result)) {

                if (result.status == true) {
                    msg(false, false, "game-drink-or-dare-chosen-dare");
                    document.getElementById('game-stage-2').style.display = "block";
                    return true;
                } else if (result.status == "already-picked") {
                    msg(false, false, "game-drink-or-dare-already-picked-card");
                    return false;
                } else {
                    msg(false, false, 'game-drink-or-dare-stolen');
                    return false;
                }
            }
        });
    } else {
        msg(false, false, 'game-drink-or-dare-empty-dare');
    }
}

function hideAll(except = 0) {

    //console.log("hiding all but : " + except);

    if (except != 1) {
        document.getElementById('game-stage-1').style.display = "none";
        document.getElementById('game-stage-1-waiting').style.display = "none";
    }
    if (except != 2) {
        document.getElementById('game-stage-2').style.display = "none";
    }
    if (except != 3) {
        document.getElementById('game-stage-3').style.display = "none";
    }
}