//list of all messages and icons for website
var messages = {
    "account-passwords-notmet":"Password requirements have not been met!",
    "account-passwords-check1":"Passwords Match",
    "account-passwords-check2":"8 Or More Characters",
    "account-passwords-check3":"Contains Numbers And Letters",
    "account-passwords-dontmatch":"Passwords Don't Match",
    "account-email-notmet":"An email is required!",
    "account-creation-failed":"Account creation failed. Please try again later.",
    "account-login-failed":"Username and Password do not match.",
    "account-limited-level":"Your account is currently limited until approved by an admin.",
    "account-not-verified":"Your account is currently limited until your email has been verified.",
    "account-reset":"You're account has been reset back to the defaults.",
    "account-update":"You're account has been successfully updated.",
    "account-already-exists":"Accout with that username already exists.",
    "movie-needs-title":"This movie needs a title!",
    "control-case-need-name":"You need a case name to create a case!",
    "control-case-need-range":"You need to enter a range to create a case!",
    "control-case-created":"Case created successfully",
    "control-case-failed":"Case could not be created",
    "control-case-exists":"A case with that name already exists.",
    "control-case-edited":"The case has been updated!",
    "control-case-edited-failed":"The case could not be updated!",
    "control-case-edited-notexists":"No case with that name exists.",
    "wishlist-need-priority":"You need to select a priority",
    "wishlist-add-failed":"The movie could not be added to your wish list at this time.",
    "general-notmet":"Please fill out all the required fields.",
    "direct-known-error-images":"I know images and some other information is missing / broken on this page. Fixing soon. Dont worry."
};
var icons = {
    "warning":'<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>',
    "danger":'<i class="fa fa-exclamation-circle" aria-hidden="true"></i>',
    "success":'<i class="fa fa-smile-o" aria-hidden="true"></i>',
    "info":'<i class="fa fa-info-circle" aria-hidden="true"></i>',
    "times":'<i class="fa fa-times" style="color:red" aria-hidden="true"></i>',
    "check":'<i class="fa fa-check" style="color:green" aria-hidden="true"></i>',
    "spinner":'<i class="fa fa-refresh fa-spin fa-fw"></i>',
}

//on document load
$(function() {

    //check for server messages
    if (srvmsg != "") {
        console.log("Checking for messages...");

        srvmsg = JSON.parse(srvmsg);
        var maxTries = 20;

        var waitForMDL = setTimeout(function () {

            console.log(typeof MaterialSnackbar);
            //set a timeout to wait for MDL to load
            if (typeof MaterialSnackbar == "function") {
                console.log(typeof MaterialSnackbar);
                console.log("hit");
                //loop through each server message
                for (var message of srvmsg) {

                    //send the message request
                    if (message.msg != "") {
                        msg(message.popup, message.ele, message.msg, message.type, false, (message.hide == false ? false : true), message.delay);
                    }
                }

                //clear the timeout
                clearTimeout(MaterialSnackbar);
            }

            //if MDL hasnt loaded in 20 tries, clean interval
            maxTries--;
            if (maxTries <= 0) {
                clearTimeout(MaterialSnackbar);
            }
        }, 100);
    }
});

//display an error on the webpage
function msg(popup, ele, msgid, type, icon, hide, delay) {

    //check for type
    if (type != "warning" && type != "info" && type != "danger" && type != "success") {
        type = "info";
    }

    //check for undefined parameters
    delay = (parseInt(delay) ? delay : 5000);
    hide = !(hide == false);
    popup = (popup == "snackbar" || popup == "dialog" ? popup : "snackbar");

    //default element example: #info-msg
    ele = (ele == false || ele === "undefined" ? type+'-msg' : ele);

    console.log("Message request - Popup: " + popup + ", Ele: " + ele + ", Msgid: " + msgid + ", Type: " + type + ", Icon: " + icon + ", Hide: " + hide);

    //if (ele = document.getElementById(ele)) {
        //check for empty message
        if (msgid != "" && messages[msgid]) {

            if (popup == "snackbar") {

                //MDL
                //'use strict';
                var snackbarContainer = document.querySelector('#'+ele);
                // var showSnackbarButton = document.querySelector('#demo-show-snackbar');
                // var handler = function (event) {
                //     showSnackbarButton.style.backgroundColor = '';
                // };
                //showSnackbarButton.addEventListener('click', function () {
                    //'use strict';
                    // showSnackbarButton.style.backgroundColor = '#' +
                    //     Math.floor(Math.random() * 0xFFFFFF).toString(16);
                    var data = {
                        message: messages[msgid],
                        timeout: delay,
                        //actionHandler: handler,
                        //actionText: 'Undo'
                    };
                    snackbarContainer.MaterialSnackbar.showSnackbar(data);
                //});

            } else if (popup == "dialog") {

                var dialog = document.querySelector(ele);
                //var showDialogButton = document.querySelector('#show-dialog');
                if (! dialog.showModal) {
                    dialogPolyfill.registerDialog(dialog);
                }
                //showDialogButton.addEventListener('click', function() {
                    dialog.showModal();
               // });
                dialog.querySelector('.close').addEventListener('click', function() {
                    dialog.close();
                });
            }

            //show message
            // $(ele).attr('class', 'alert alert-'+type);
            // if (icons[icon]) {
            //     $(ele).html(icons[icon] + " " + messages[msgid]);
            // } else {
            //     $(ele).html(icons[type] + " " + messages[msgid]);
            // }
            //$(ele).slideDown();

            //check for hiding of message
            // if (hide) {
            //     switch (type) {
            //         case "info":
            //             var infoclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(infoclear);
            //             }, delay);
            //             break;
            //         case "danger":
            //             var dangerclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(dangerclear);
            //             }, delay);
            //             break;
            //         case "warning":
            //             var warningclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(warningclear);
            //             }, delay);
            //             break;
            //         case "success":
            //             var successclear = setTimeout(function () {
            //                 $(ele).slideUp();
            //                 clearTimeout(successclear);
            //             }, delay);
            //             break;
            //     }
            // } // end of check for hide
        } else {
            console.log("Message could not be found or message empty");
        }
    // } else {
    //     console.log("Element could not be found");
    // }
}

//hide a msg element
function msgHide(ele) {
    var isDefault = true;

    //check for default message
    if (ele == false || ele == "undefined") {
        ele = 'msg';
    } else {
        isDefault = false;
    }

    if (ele = document.getElementById(ele)) {
        $(ele).slideUp();
        //if default, hide all children elements of parent 'msg'
        if (isDefault) {
            $(ele).children().each(function(index) {
                $(this).hide();
            });
        }
    }
}
