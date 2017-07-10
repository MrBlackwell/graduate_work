<?php
require_once("include/connection.php");
if (isset($_REQUEST['hash']) and isset($_REQUEST['login'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
    $query = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($query);
    $sql = "SELECT `id` FROM `people` WHERE `login` = '".mb_strtolower($_REQUEST['login'], "UTF-8")."' OR `number_card` = '".$_REQUEST['card']."'";
    $query = mysqli_query($link, $sql);
    if(mysqli_num_rows($query) != 0 ){
        echo json_encode(array('result' => 0));
    } else {
        $values = "'".$user['id_athena']."', ";
        $values .= "'".$_REQUEST['name']."', ";
        $values .= "'".$_REQUEST['email']."', ";
        $values .= "'".mb_strtolower($_REQUEST['login'], "UTF-8")."', ";
        $values .= "'".$_REQUEST['password']."', ";
        $values .= $_REQUEST['admin'].", ";
        $values .= "'".$_REQUEST['card']."', ";
        $values .= "0, ";
        $values .= "-1";
        $sql = "INSERT INTO `people`(`id_athena`, `FIO`, `email`, `login`, `password`, `is_admin`, `number_card`, `add_in_athena`, `delete_from_athena`) VALUES (".$values.")";
        $query = mysqli_query($link, $sql);
        echo json_encode(array('result' => 1));
    }
}