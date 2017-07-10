<?
require("constants.php");
$link = mysqli_connect(DB_SERVER,DB_USER, DB_PASS) or die("Cannot connect");
mysqli_select_db($link, DB_NAME) or die("Cannot select DB");
mysqli_query ($link, "set character_set_client='utf8'");
mysqli_query ($link, "set character_set_results='utf8'");
mysqli_query ($link, "set collation_connection='utf8_general_ci'");
?>