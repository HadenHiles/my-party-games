<?php
/**
 * Created by handshiles on 2016-07-05.
 */
require_once("../../vendor/facebook/php-sdk-v4/src/Facebook/autoload.php");
require_once("config.php");

$fb = new Facebook\Facebook([
    'app_id' => APP_ID,
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.6'
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl('http://www.partygame.ca/tests/facebook/login-callback.php', $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';