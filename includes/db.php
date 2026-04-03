<?php
$servername = "sqlXXX.infinityfree.com"; // your host
$username = "your_db_username";          // from InfinityFree
$password = "your_db_password";          // from InfinityFree
$dbname = "your_db_name";                // the database you just created

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>