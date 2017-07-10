<?php
require_once("include/connection.php");
if (isset($_REQUEST['hash'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `mobile_hash` = '".$_REQUEST['hash']."'";
    $query = mysqli_query($link, $sql);
    $id = mysqli_fetch_assoc($query);
    if(isset($_REQUEST['date'])){
        $logFail = @fopen("log_" . $_REQUEST['date'] . "_" . $id['id_athena'], "r");
    } else {
        $logFail = @fopen("log_" . date("d.m.Y") . "_" . $id['id_athena'], "r");
    }
    if($logFail != false) {
        $str = fgets($logFail);
        $log = "";
        while ($str != false) {
            $log = $str . $log;
            //$log .= "<br>";
            $str = fgets($logFail);
        }
        //$log = file_get_contents("log_".date("d.m.Y")."_".$id['id_athena']);
        print $log;
    } else {
        print "Файл логов за указанную дату отсутствует";
    }
} else{
    print "Что-то пошло не так";
}
?>