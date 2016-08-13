$(function(){
    var hasNotifiedUserOfAllVotesCasted = false;
    var isMyTurn = false;

    var updateIneterval = setInterval(function() {
        $.ajax({
            url:"get-update-game.php",
            method:"POST"
        }).done(function(result) {
            console.log(result);

            if (result = JSON.parse(result)) {
                //console.log(result);

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
                        hasNotifiedUserOfAllVotesCasted = false;
                        break;

                    case 2:
                        //shuffling and assigning dares
                        document.getElementById('game-stage-2').style.display = "block";
                        break;

                    case 3:
                        //looping users carrying out dares
                        document.getElementById('myCard').innerHTML = result.dare;
                        document.getElementById('activeDare').innerHTML = result.dare;
                        document.getElementById('game-stage-3').style.display = "block";

                        if (result.turn) {
                            document.getElementById('game-stage-3-player').style.display = "block";
                            document.getElementById('game-stage-3-viewer').style.display = "none";

                            if (!isMyTurn) {
                                isMyTurn = true;
                                hasNotifiedUserOfAllVotesCasted = false;
                            }
                        } else {
                            document.getElementById('activeDare').innerHTML = result.dare;
                            document.getElementById('game-stage-3-player').style.display = "none";
                            document.getElementById('game-stage-3-viewer').style.display = "block";
                        }

                        if (result.allVotesCast && !hasNotifiedUserOfAllVotesCasted) {
                            hasNotifiedUserOfAllVotesCasted = true;
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

                            document.getElementById('voted-bad').innerHTML = bad + ' people voted bad.';
                            document.getElementById('voted-skip').innerHTML = skip + ' people voted skip.';
                            document.getElementById('voted-good').innerHTML = good + ' people voted good.';
                            //console.log("Votes: good=" + good + ", bad="+bad + ", skip="+skip);
                        } else {
                            document.getElementById('voted-bad').innerHTML = '0 people voted bad.';
                            document.getElementById('voted-skip').innerHTML = '0 people voted skip.';
                            document.getElementById('voted-good').innerHTML = '0 people voted good.';
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
    }, 1000); //end of interval

    $('.pickCard').unbind('click').click(function() {
        var num = $(this).data("cardnum");
        var $self = $(this);
        pickCard(num, function(result) {
            if(result == true) {
                getOwner(num, function(ownerResult) {
                    console.log("ownerResult pic: " + ownerResult.picture);
                    if(ownerResult != false) {
                        var name = "<h5 class='owner-name'>" + ownerResult.display_name + "</h5>";
                        var picture = "<img class='owner-picture' src='" + ownerResult.picture + "' />";
                        var ownerHtml = name + picture;
                        console.log(this);
                        $self.html(ownerHtml);
                    }
                });
            }
        });

    });

    $('.showCard').click(function() {

        showCard();
    });

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
                msg(false, false, "game-drink-or-dare-free-pass-success");
            } else {
                msg(false, false, "game-drink-or-dare-free-pass-failure");
            }
        }
    });
    
}

function setDare() {

    var textContainer = document.getElementById('dare-text');
    var drinksWorth = document.getElementById('drinksWorth').value;

    if (textContainer.value != "") {
        //ajax call to set dare
        $.ajax({
            url:"set-dare.php",
            method:"POST",
            data:{"text":textContainer.value,"drinksWorth":drinksWorth}
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

function pickCard(number, cb) {
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
                    cb(true);
                } else if (result.status == "already-picked") {
                    msg(false, false, "game-drink-or-dare-already-picked-card");
                    cb(false);
                } else {
                    msg(false, false, 'game-drink-or-dare-stolen');
                    cb(false);
                }
            }
        });
    } else {
        msg(false, false, 'game-drink-or-dare-empty-dare');
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
                msg(false, false, "game-drink-or-dare-reset-success");
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

            if (typeof result.dare != "undefined") {
                //msg(false, false, "game-drink-or-dare-chosen-dare");
               document.getElementById('myCard').innerHTML = result.dare;
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

    }
    if (except != 5) {
        document.getElementById('game-stage-5').style.display = "none";
    }
}