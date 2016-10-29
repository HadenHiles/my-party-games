$(function(){
    var hasNotifiedUserOfAllVotesCasted = false;
    var isMyTurn = false;
    var gameCheckInterval = 1000; // 1 second

    var updateIneterval = setInterval(function() {
        $.ajax({
            url:"get-update-game.php",
            method:"POST"
        }).done(function(result) {
            //console.log(result);

            if (result = JSON.parse(result)) {
                //console.log(result);

                //check for error messages
                if (result.error != "" && typeof result.error != "undefined") {
                    msg("dialog", false, result.error, "Game Error", "danger");
                    clearInterval(updateIneterval);
                }

                $('#currentRound').html("Round " + result.currentRound + "/" + result.totalRounds);

                //get state and hide all elements not required by current state
                var state = parseInt(result.state);
                hideAllExcept(state);

                //otherwise switch based on game state
                switch (state) {

                    case 1:
                        //users are creating dares
                        if (!result.waiting) {
                            document.getElementById('game-stage-1').style.display = "block";
                            document.getElementById('game-stage-1-waiting').style.display = "none";
                        } else {
                            document.getElementById('game-stage-1').style.display = "none";
                            document.getElementById('game-stage-1-waiting').style.display = "block";
                        }
                        hasNotifiedUserOfAllVotesCasted = false;
                        break;

                    case 2:
                        //shuffling and assigning dares
                        document.getElementById('game-stage-2').style.display = "block";

                        if (result.cardInfo.length > 0) {

                            //loop through each html card
                            $('.pickCard').each(function(idx, val) {
                                var $targetCard = $(this)[idx];

                                //check if current iteration at name or picture != null meaning a user has picked this card
                                if(result.cardInfo[idx].display_name != null && result.cardInfo[idx].picture != null) {

                                    //only update if html is empty so we dont flash
                                    if ($(this).html() != '') {
                                        var name = "<h5 class='owner-name'>" + result.cardInfo[idx].display_name + "</h5>";
                                        var picture = "<img class='owner-picture' src='" + result.cardInfo[idx].picture + "' />";
                                        var ownerHtml = name + picture;
                                        $(this).html(ownerHtml);
                                    }
                                }
                            });
                        }
                        break;

                    case 3:
                        //SHOW STAGE 3 DIV
                        document.getElementById('game-stage-3').style.display = "block";
                        var useElement;
                        console.log(result);

                        if (result.turn) {
                            useElement =  document.getElementById('myCard');

                            //TOGGLE BETWEEN PLAYER VIEW AND ACTIVE PLAYER VIEW
                            document.getElementById('game-stage-3-player').style.display = "block";
                            document.getElementById('game-stage-3-viewer').style.display = "none";

                            //IF JAVASCRIPT VARIABLE isMyTurn = false, set to TRUE HERE
                            if (!isMyTurn) {
                                isMyTurn = true;
                                hasNotifiedUserOfAllVotesCasted = false;
                            }
                        } else {
                            useElement =  document.getElementById('activeDare');

                            document.getElementById('game-stage-3-player').style.display = "none";
                            document.getElementById('game-stage-3-viewer').style.display = "block";
                        }

                        //CHECK IF THEY HAVE ALREADY PEEKED AT THEIR DARE
                        if(result.hasPeeked) {
                            useElement.innerHTML = "<h2 class='activeDrinksWorth'>" + result.dare.drinks_worth + "</h2>\
                                                    <div class='activeDrinksWorthPic'>\
                                                        <img src='/join/pictures/party/pint.png' />\
                                                    </div>\
                                                    <h5 class='dareText'>" + result.dare.dare + "</h5>";
                        } else if (result.turn) {
                            useElement.innerHTML = "<h5 class='dareText' style='margin-top: 37%;'>It's Your Turn!<br />Click me to reveal your dare!</h5>";
                        } else {
                            useElement.innerHTML = "<h5 class='dareText' style='margin-top: 37%;'>Waiting for " + result.activePlayer.display_name + "...</h5>";
                        }

                        //check for voting dialog to show results
                        if (result.allVotesCast && !hasNotifiedUserOfAllVotesCasted) {
                            hasNotifiedUserOfAllVotesCasted = true;

                            var votingResult = "<div class='votingResult'>";

                            if (result.verdict == "good") {
                                votingResult = "<h4>People voted <span class='mdl-color-text--green'>"+result.verdict+"</span>!</h4><Br />";
                                votingResult += result.activePlayer.display_name + " ";
                                votingResult += "can give out "+result.drinksWorth+" drink(s).";
                            } else if (result.verdict == "skip") {
                                votingResult = "<h4>People voted <span class='mdl-color-text--primary'>"+result.verdict+"</span>!</h4><Br />";
                                votingResult += result.activePlayer.display_name + " ";
                                votingResult += "gets to skip this dare.";
                            } else if (result.verdict == "bad") {
                                votingResult = "<h4>People voted <span class='mdl-color-text--red'>"+result.verdict+"</span>!</h4><Br />";
                                votingResult += result.activePlayer.display_name + " ";
                                votingResult += "must drink "+result.drinksWorth+" drink(s).";
                            }
                            votingResult += "</div>";

                            msg("dialog", false, votingResult, "Voting Results", false, false, false, false);
                        }

                        //get voting stats
                        if (result.votes.length > 0) {
                            document.getElementById('num-votes').innerHTML = result.votes.length.toString();
                        } else {
                            document.getElementById('num-votes').innerHTML = "0";
                        }
                        break;

                    case 4:
                        //incrementing rounds and check for game completion
                        break;

                    case 5:
                        //game completed show stats
                        document.getElementById('game-stage-5').style.display = "block";

                        if (result.reset) {
                            window.location.reload();
                        }
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
    }, gameCheckInterval); //end of interval

    
    
    $('.pickCard').unbind('click').click(function() {
        var num = $(this).data("cardnum");
        var $self = $(this);

        pickCard(num, function(result) {
            if(result == true) {
                getOwner(num, function(ownerResult) {
                    // console.log("ownerResult pic: " + ownerResult.picture);
                    if(ownerResult != false) {
                        var name = "<h5 class='owner-name'>" + ownerResult.display_name + "</h5>";
                        var picture = "<img class='owner-picture' src='" + ownerResult.picture + "' />";
                        var ownerHtml = name + picture;
                        //console.log(this);
                        $self.html(ownerHtml);
                    }
                });
            }
        });
    }); //end of click function

    $('.showCard').on('click', showCard);

}); //end of document load

function freePass() {

    $.ajax({
        url:"free-pass.php",
        method:"POST"
    }).done(function(result) {
        console.log(result);

        if (result = JSON.parse(result)) {

            //hideAll(result.state);

            if (result.status == true) {
                msg(false, false, "game-drink-or-dare-free-pass-success", "Free Pass", "success");
            } else {
                msg(false, false, "game-drink-or-dare-free-pass-failure", "Free Pass", "warning");
            }
        }
    });
    
}

/*
 * called on stage 1 to submit users dare
 */
function setDare() {
    var textContainer = document.getElementById('dare-text');
    var allDrinksWorth = document.getElementsByName('drinksWorth');
    var drinksWorthSet = false;
    var drinksWorth;

    //check user typed a dare
    if (textContainer.value != "") {

        //check they have picked the drinks worth
        for (var i = 0; i < allDrinksWorth.length; i++) {
            if (allDrinksWorth[i].checked) {
                drinksWorth = allDrinksWorth[i];
                drinksWorthSet = true;
            }
        }

        if(drinksWorthSet) {
            //ajax call to set dare
            $.ajax({
                url:"set-dare.php",
                method:"POST",
                data:{
                    "text":textContainer.value,
                    "drinksWorth":drinksWorth.value
                }
            }).done(function(result) {
                console.log(result);

                if (result = JSON.parse(result)) {
                    //hideAll(result.state);

                    if (result.status == true) {
                        msg(false, false, "game-drink-or-dare-submitted-dare", "Create Dare", "success");
                        document.getElementById('game-stage-1').style.display = "none";
                        document.getElementById('game-stage-1-waiting').style.display = "block";
                    }
                }
            });
        } else {
            msg(false, false, 'game-drink-or-dare-empty-drinks-worth', "Create Dare", "danger");
        }
    } else {
        msg(false, false, 'game-drink-or-dare-empty-dare', "Create Dare", "danger");
    }
}

function pickCard(number, cb) {
    //console.log("number: " + number);
    if (number > 0) {

        //ajax call to set dare
        $.ajax({
            url:"pick-card.php",
            method:"POST",
            data:{
                "number":number
            }
        }).done(function(result) {
            console.log(result);

            if (result = JSON.parse(result)) {

                if (result.status == true) {
                    msg(false, false, "game-drink-or-dare-chosen-dare", "Pick Card", "success");
                    cb(true);
                } else if (result.status == "already-picked") {
                    msg(false, false, "game-drink-or-dare-already-picked-card", "Pick Card", "info");
                    cb(false);
                } else {
                    msg(false, false, 'game-drink-or-dare-stolen', "Pick Card", "danger");
                    cb(false);
                }
            }
        });
    } else {
        msg(false, false, 'game-drink-or-dare-empty-dare', "Error", "danger");
    }
}

function restartGame() {

    //ajax call to set dare
    $.ajax({
        url:"restart-game.php",
        method:"POST"
    }).done(function(result) {
        console.log(result);

        if (result = JSON.parse(result)) {

            if (result.reset) {
                window.location.reload();
            } else {
                msg(false, false, "game-drink-or-dare-reset-success", "Game Reset", "info");
            }
        }
    });
}

function showCard() {
    //ajax call to set dare
    $.ajax({
        url:"get-dare.php",
        method:"POST"
    }).done(function(result) {
        console.log(result);

        if (result = JSON.parse(result)) {

            if (typeof result.dare != "undefined" && result.dare != "hidden") {
                //msg(false, false, "game-drink-or-dare-chosen-dare");
                document.getElementById('myCard').innerHTML = '<h5 class="dareText">'+ result.dare.dare + '</h5>';
            }
        }
    });
}

function getOwner(cardNum, cb) {
    //ajax call to set dare
    $.ajax({
        url:"get-owner.php",
        method:"POST",
        data:{"card_num":cardNum}
    }).done(function(result) {
        console.log(result);
        if (result = JSON.parse(result)) {
            if (typeof result.display_name != "undefined" && typeof result.picture != "undefined") {
                cb(result);
            } else {
                cb(false);
            }
        }
    });
}

function castVote(vote) {

    if (vote > 0) {
        //ajax call to set dare
        $.ajax({
            url: "cast-vote.php",
            method: "POST",
            data:{"vote":vote}
        }).done(function (result) {
            console.log(result);

            if (result = JSON.parse(result)) {

                if (result.status == true) {
                    msg(false, false, "game-drink-or-dare-vote-cast-success", "Cast Vote", "success");
                } else if (result.status == "changed") {
                    msg(false, false, "game-drink-or-dare-vote-cast-change", "Cast Vote", "info");
                } else {
                    msg(false, false, "game-drink-or-dare-vote-cast-failure", "Cast Vote", "danger");
                }
            }
        });
    }

}

function finishDare() {

    //ajax call to set dare
    $.ajax({
        url: "finish-dare.php",
        method: "POST"
    }).done(function (result) {
        console.log(result);

        if (result = JSON.parse(result)) {

            if (result.status == true) {
                if (result.verdict == "skip") {
                    msg(false, false, "game-drink-or-dare-skip");
                } else if (result.verdict == "good") {
                    msg(false, false, "You can give out " + result.drinksWorth + " drinks");
                } else if (result.verdict == "bad") {
                    msg(false, false, "game-drink-or-dare-bad");
                }
            } else {
                msg(false, false, "game-drink-or-dare-finish-dare-failure");
            }
        }
    });
}

/**
 *
 * @param except
 */
function hideAllExcept(except) {
    if(typeof except == "undefined") {
        except = 0;
    }
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
    if (except != 4) {
        document.getElementById('game-stage-4').style.display = "none";
    }
    if (except != 5) {
        document.getElementById('game-stage-5').style.display = "none";
    }
}


function reload() {
    window.location.reload();
}