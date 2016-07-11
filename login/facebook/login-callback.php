<?php
/**
 * Created by handshiles on 2016-07-05.
 */
require_once("../../includes/common.php");
require_once("../../vendor/autoload.php");
require_once("config.php");

use Facebook\FacebookRequest;
use Facebook\Authentication\OAuth2Client;

$fb = new Facebook\Facebook([
    'app_id' => APP_ID,
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.6'
]);

$helper = $fb->getRedirectLoginHelper();
try {
    $accessToken = $helper->getAccessToken();

    $client = $fb->getOAuth2Client();
    // Returns a long-lived access token
    $accessToken = $client->getLongLivedAccessToken($accessToken);
    //Debug the access token
    $metaData = $client->debugToken($accessToken);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (isset($accessToken)) {
    // Logged in!
    $_SESSION['fb_access_token'] = (string) $accessToken;

    // Now you can redirect to another page and use the
    // access token from $_SESSION['facebook_access_token']
    header("Location: ../../lobby/join.php?fb-login=true");
}