<script src="../../../bower_components/jquery/dist/jquery.min.js"></script>
<script defer src="../../../bower_components/material-design-lite/material.min.js"></script>
<script>
    (function() {
        var dialog = document.querySelector('dialog.error');
        if(dialog != null) {
            if (!dialog.showModal) {
                dialogPolyfill.registerDialog(dialog);
            }
            dialog.showModal();
            dialog.querySelector('.close').addEventListener('click', function() {
                dialog.close();
            });
        }
    })();
</script>


<script>
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

                    switch (result) {

                        case 1:
                            //users are picking dares

                            break;

                        case 2:
                            //shuffling and assigning dares

                            break;

                        case 3:
                            //looping users carrying out dares

                            break;

                        case 4:
                            //incrementing rounds and check for game completion

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
                    }
                }
            });
        }, 1000);
    });

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

                    if (result.status == true) {

                        document.getElementById('game-stage-1').style.display = "none";
                        document.getElementById('game-stage-1-waiting').style.display = "block";
                    }
                }
            });
        } else {
            alert("enter a dare!");
        }
    }
</script>

</body>
</html>