<?php
require_once("include/connection.php");
session_start();
$sql = "UPDATE `people` SET `autorization_hash` = '' WHERE `autorization_hash` = '".$_SESSION['hash']."'";
mysqli_query($link, $sql);
unset($_SESSION['hash']);
unset($_SESSION['admin']);
session_destroy();
header("Location: index.php");