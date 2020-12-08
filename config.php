<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'hughsonb_ULibs3');
define('DB_PASSWORD', 'motherfucker');
define('DB_NAME', 'hughsonb_ULibs3');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>