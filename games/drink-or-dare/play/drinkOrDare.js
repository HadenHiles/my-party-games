$(function(){
    var hasNotifiedUserOfAllVotesCated = false;

    var updateIneterval = setInterval(function() {
        $.ajax({
            url:"get-update-game.php",
            method:"POST"
        }).done(function(result) {
            console.log(result);

            if (result = JSON.parse(result)) {
                console.log(result);

                //check for error messages
                if (result.error != "" && typeof result.error != "undefined") {
                    msg("dialog", false, result.error);
                    clearInterval(updateIneterval);
                }

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
                        break;

                    case 2:
                        //shuffling and assigning dares
                        document.getElementById('game-stage-2').style.display = "block";
                        break;

                    case 3:
                        //looping users carrying out dares
                        document.getElementById('activeDare').innerHTML = "hidden";
                        document.getElementById('game-stage-3').style.display = "block";

                        if (result.turn) {
                            document.getElementById('game-stage-3-player').style.display = "block";
                            document.getElementById('game-stage-3-viewer').style.display = "none";
                        } else {
                            document.getElementById('activeDare').innerHTML = result.dare;
                            document.getElementById('game-stage-3-player').style.display = "none";
                            document.getElementById('game-stage-3-viewer').style.display = "block";
                        }

                        if (!result.allVotesCast && !hasNotifiedUserOfAllVotesCated) {
                            hasNotifiedUserOfAllVotesCated = true;
                            msg(false, false, "game-drink-or-dare-all-votes-casted");
                        }

                        //get voting stats
                        if (result.votes.length > 0) {

                            var bad = 0;
                            var good = 0;
                            var skip = 0;

                            for (var i = 0; i < result.votes.length; i++) {
                                if (result.votes[i].vote == 3) {
                                    good++;
                                } else if (result.votes[i].vote == 2) {
                                    skip++;
                                } else if (result.votes[i].vote == 1) {
                                    bad++;
                                }
                            }
                            console.log("Votes: good=" + good + ", bad="+bad + ", skip="+skip);
                        }

                        if (result.allVotesCast) {
                            //$('#').removeAttr('disabled');
                        }
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

    $('.pickCard').click(function() {
        var num = $(this).data("cardnum");
        //console.log("pickCard num: " + num);
        if (pickCard(num)) {
            var owner = getOwner(num);
            console.log("owner: -------------------------------------" + owner);
            var name = "<h5 class='owner-name'>" + owner.display_name + "</h5>";
            var picture = "<img class='owner-picture' src='" + owner.picture + "' />";
            var ownerHtml = name + picture;
            $(this).html(ownerHtml);
        }
    });

    $('.showCard').click(function() {

        showCard();
    });

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
                
                //hideAll(result.state);

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

function showCard() {
    //ajax call to set dare
    $.ajax({
        url:"get-dare.php",
        method:"POST"
    }).done(function(result) {
        console.log(result);

        if (result = JSON.parse(result)) {

            if (typeof result.dare != "undefined") {
                //msg(false, false, "game-drink-or-dare-chosen-dare");
               document.getElementById('myCard').innerHTML = result.dare;
            }
        }
    });
}

function getOwner(cardNum) {
    //ajax call to set dare
    $.ajax({
        url:"get-owner.php",
        method:"POST",
        data:{"card_num":cardNum}
    }).done(function(result) {
        if (result = JSON.parse(result)) {
            console.log(result);

            if (typeof result.display_name != "undefined" && typeof result.picture != "undefined") {
                return result;
            }
        }
        return false;
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
                    msg(false, false, "game-drink-or-dare-vote-cast-success");
                } else if (result.status == "changed") {
                    msg(false, false, "game-drink-or-dare-vote-cast-change");
                } else {
                    msg(false, false, "game-drink-or-dare-vote-cast-failure");
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
                msg(false, false, "game-drink-or-dare-finish-dare-success");
            } else {
                msg(false, false, "game-drink-or-dare-finish-dare-failure");
            }
        }
    });
}

function hideAllExcept(except = 0) {

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