<?php
require_once("include/connection.php");
session_start();
if (isset($_SESSION['hash']) and isset($_SESSION['admin'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `autorization_hash` = '".$_SESSION['hash']."'";
    $query = mysqli_query($link, $sql);
    $id = mysqli_fetch_assoc($query);
    $logFail = fopen("log_".date("d.m.Y")."_".$id['id_athena'], "r");
    $str = fgets($logFail);
    $log = "";
    while ($str != false){
        $log = $str.$log;
        //$log .= "<br>";
        $str = fgets($logFail);
    }
    //$log = file_get_contents("log_".date("d.m.Y")."_".$id['id_athena']);
    print $log;
} else{
    print "Что-то пошло не так";
}
?>