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
        "https://img.fireden.net/v/image/1450/88/1450884080160.png",
        "http://67.media.tumblr.com/4d92609b430dc10cc1dbe648b35aa7ad/tumblr_nmxi5gL0jL1sfb87ho1_500.png",
        "https://www.shareicon.net/horror-knife-terror-spooky-head-halloween-scary-costume-fear-828598/data/128x128/2016/10/18/845341_avatar_512x512.png",
        "https://www.shareicon.net/horror-knife-terror-spooky-head-halloween-scary-costume-fear-828598/data/128x128/2016/10/18/845346_avatar_512x512.png",
        "http://pre15.deviantart.net/a283/th/pre/i/2012/305/e/6/erza_halloween_by_pervyangel-d5jnv2z.png",
        "https://s-media-cache-ak0.pinimg.com/originals/34/d5/62/34d5624c216ba9ac9c2510eb6f5923de.jpg",
        "https://holybeeofephesus.files.wordpress.com/2015/10/the_raven1.png",
        "http://i.imgur.com/lz7lLow.png",
        "https://a2ua.com/ghost/ghost-002.jpg",
        "http://gallery.yopriceville.com/var/resizes/Free-Clipart-Pictures/Halloween-PNG-Pictures/Halloween_Funny_Witch_PNG_Clipart.png?m=1376604000",
        "http://vignette2.wikia.nocookie.net/trollpasta/images/3/3d/Bloody_Mickey_Mouse_Render.png/revision/latest?cb=20150103154257",
        "https://lh4.googleusercontent.com/-ZBTa0UnaHnM/AAAAAAAAAAI/AAAAAAAAFzY/7YFxUDh2eaQ/photo.jpg",
        "http://vignette3.wikia.nocookie.net/clubpenguin/images/0/0a/Pumpkin_Head_clothing_icon_ID_1095.png/revision/latest?cb=20131006145935",
        "http://images.clipartpanda.com/halloween-skeleton-clipart-117-Happy-Skeleton-Free-Halloween-Vector-Clipart-Illustration.png",
        "https://www.clipartsgram.com/image/1562746355-zombie-halloween-clip-art-2.png",
        "http://www.halloweenasylum.com/assets/images/GhoulishProductions/26446.png",
        "https://a2ua.com/ghost/ghost-002.jpg",
        "http://vignette3.wikia.nocookie.net/clubpenguin/images/0/0a/Pumpkin_Head_clothing_icon_ID_1095.png/revision/latest?cb=20131006145935",
        "http://vignette1.wikia.nocookie.net/lapis/images/3/32/Spooky_spookyhouse.png/revision/latest?cb=20150716120311",
        "http://stockpictures.io/wp-content/uploads/2016/06/48053-masked-man-evil-scary-spooky-mystery-demon-creepy-darkness-mysterious-terrify-gothic-frightening-terrified-satanic-demonic-free-vector-graphics-free-illustrations-free-images-royalty-free-768x601.png",
        "http://cdn.akamai.steamstatic.com/steam/apps/372210/extras/bat.png?t=1460138974",
        "https://staticdelivery.nexusmods.com/mods/1151/images/1917-0-1448130696.png",
        "http://images.vectorhq.com/images/previews/b42/gravestone-psd-436814.png",
        "http://vignette3.wikia.nocookie.net/torchlight/images/f/f5/Werewolf.png/revision/latest?cb=20130122032955",
        "http://gallery.yopriceville.com/var/albums/Free-Clipart-Pictures/Halloween-PNG-Pictures/Halloween_Spooky_Tree_PNG_Clipart_Image.png?m=1444774897",
        "http://images.clipartpanda.com/bat-clipart-bat-gallery-free-clipart-pictures.png",
        "http://www.pngall.com/wp-content/uploads/2016/03/Spider-PNG.png",
        "http://www.clipartlord.com/wp-content/uploads/2013/06/mummy4.png",
        "https://openclipart.org/image/2400px/svg_to_png/222287/Realistic-Human-Skull.png",
        "http://hypnoticowl.com/wordpress/wp-content/uploads/2014/03/Wizard.png",
        "https://img.joke.co.uk/images/products/jmw-v3/large/67508.png",
        "https://cdn4.iconfinder.com/data/icons/desktop-halloween/256/Hat.png",
        "http://www.lovethisgif.com/uploaded_images/104222-3d-Gifs-Blinking-Eyes.gif"
    ];

    var index = Math.floor(Math.random() * images.length);
    $(ghost).attr('src', images[index]);
    var wait = Math.floor(Math.random() * 160) + 60;
    var current = 0;

    var inte = setInterval(function() {

        if (current < wait) {
            current++;
            return;
        }

        if (!toggle) {
            op+=.02;
        } else {
            op-=.03;
        }

        if (op >= .9) {
            toggle = true;
        } else if ( op <= 0) {
            toggle = false;
            index = Math.floor(Math.random() * images.length);
            $(ghost).attr('src', images[index]);
            $(ghost).css("top", Math.floor((Math.random() * 65) + 10)+"%");
            $(ghost).css("left", Math.floor((Math.random() * 80) + 10)+"%");
            current = 0;
            wait = Math.floor(Math.random() * 250) + 1;
        }
        $(ghost).css("opacity", op);
    }, 50);
});
</script>

<?php
require_once(ROOT.'/includes/message.php');
?>