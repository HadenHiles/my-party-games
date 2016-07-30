<?php
/**
 * Created by IntelliJ IDEA.
 * User: Justin
 * Date: 7/20/2016
 * Time: 9:44 PM
 * Description: Place all scripts in the cron folder then include them here.
 *              Cron script will run everyday at midnight.
 */

echo '<h2>Cleaning up database</h2>';
require_once('cleanup.php');
echo '<br /><Br />';

//echo '<h2>Action being taken</h2>';
//require_once('your_file_here.php');
//echo '<br /><Br />';

?>