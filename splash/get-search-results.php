<?php
require_once('../includes/common.php');
require_once('../includes/class.GameSession.php');
require_once('../includes/class.User.php');
require_once('../includes/database.php');

$searchText = $_POST['text'];

//get all games in directory
$results = array();
$dir = new DirectoryIterator('../games');

foreach ($dir as $game) {
    if ($game->isDir() && !$game->isDot() && stristr($game->getFilename(), $searchText)) {

        $found = str_replace("-", " ", $game->getFilename());
        $results[$game->getFilename()] = ucfirst($found);
    }
}

//format output
$output = '<ul class="search-list-item mdl-list">';

if (!empty($results)) {

    foreach ($results as $key => $result) {
        $output .= '<li class="mdl-list__item">
                   <span class="mdl-list__item-primary-content">
                   <a href="/games/' . $key . '" class="fade">' . $result . '</a>
                   </span>
                   </li>';
    }
} else {
    $output .= '<li class="mdl-list__item">
                   <span class="mdl-list__item-primary-content" style="opacity: .4">
                   Nothing found.
                   </span>
                   </li>';
}
$output .= '</ul>';

//echo response
echo $output;
?>
