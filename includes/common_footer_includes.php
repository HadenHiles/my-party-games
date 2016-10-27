<div>
    <img src="https://a2ua.com/ghost/ghost-002.jpg" style="
        pointer-events: none;
        position: fixed;
        top: 50%;
        left: 50%;
        /* right: 0; */
        width: 20%;
        /* bottom: 0; */
        z-index: 10;
        opacity: 0;
    " id="testerino">
</div>


<?php
/**
 * Created by IntelliJ IDEA.
 * User: Justin
 * Date: 9/23/2016
 * Time: 9:27 PM
 */
?>

<script src="/bower_components/material-design-lite/material.min.js"></script>

<!--<script defer src="https://code.getmdl.io/1.1.3/material.min.js"></script>-->

<script src="/3rd-party/imagefill/imagesLoaded.js"></script>
<script src="/3rd-party/imagefill/jquery-imagefill.js"></script>

<script>
$(function() {
    var ghost = $('#testerino');
    var toggle = false;
    var op = 0;
    var images = [
        "https://a2ua.com/ghost/ghost-002.jpg",
        "http://vignette3.wikia.nocookie.net/clubpenguin/images/0/0a/Pumpkin_Head_clothing_icon_ID_1095.png/revision/latest?cb=20131006145935",
        "http://images.clipartpanda.com/halloween-skeleton-clipart-117-Happy-Skeleton-Free-Halloween-Vector-Clipart-Illustration.png",
        "https://www.clipartsgram.com/image/1562746355-zombie-halloween-clip-art-2.png",
        "http://www.halloweenasylum.com/assets/images/GhoulishProductions/26446.png",
        "https://a2ua.com/ghost/ghost-002.jpg",
        "http://vignette3.wikia.nocookie.net/clubpenguin/images/0/0a/Pumpkin_Head_clothing_icon_ID_1095.png/revision/latest?cb=20131006145935"
    ];

    var index = Math.floor(Math.random() * images.length);
    $(ghost).attr('src', images[index]);

    console.log(images.length);
    var inte = setInterval(function() {
        if (!toggle) {
            op+=.075
        } else {
            op-=.1;
        }
        if (op >= 1) {
            toggle = true;
        } else if ( op <= 0) {
            toggle = false;

            index = Math.floor(Math.random() * images.length);
            $(ghost).attr('src', images[index]);
            $(ghost).css("top", Math.floor((Math.random() * 65) + 10)+"%");
            $(ghost).css("left", Math.floor((Math.random() * 80) + 10)+"%");
        }
        $(ghost).css("opacity", op);
    }, 100);
});
</script>

<?php
require_once(ROOT.'/includes/message.php');
?>