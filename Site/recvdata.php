<?php
require_once("include/connection.php");
$name_file = "log_".date("d.m.Y")."_";

//обработка сообщений
$hash = "00000000000000000000000000000000";
$bit = 0;
$sql = /** @lang text */
    "SELECT `sensorsActivity` FROM `athenas` WHERE `id`=".$_GET['id'];
$query = mysqli_query($link, $sql);
$row = mysqli_fetch_row($query);
if(mysqli_num_rows($query) == 0){
    if(!isset($_GET['close'])) {
        $sql = /** @lang text */
            "INSERT INTO `athenas` (`id`, `wet/temp`, `sensors`, `sensorsActivity`, `open`) VALUES (" .
            $_GET['id'] . ", '" . $_GET['wet'] . "', " . $_GET['sensors'] . ", 127, 1)";
        mysqli_query($link, $sql);
        $password = md5(md5("password".$_GET['id']));
        $sql = "INSERT INTO `people` (`id_athena`, `login`, `password`, `is_admin`) VALUES ('".$_GET['id']."', 'admin".$_GET['id']."', '".$password."', 1)";
        mysqli_query($link, $sql);
        echo $bit . $hash . "127";
    }
} 
else 
{
    if(!isset($_GET['close'])) {
        $sql = /** @lang text */
            "UPDATE `athenas` SET `wet/TEMP`='" . $_GET['wet'] . "', `sensors` = " . $_GET['sensors'] . " WHERE `id`=" . $_GET['id'];
        mysqli_query($link, $sql);

        //$sensor = decbin($_GET['sensors']);
        $sensor = sprintf("%05d", decbin($_GET['sensors']));
        if($sensor[4] == '1'){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Сработал датчик проникновения\n", FILE_APPEND);
        }
        if($sensor[3] == '1'){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Сработал датчик вибрации\n", FILE_APPEND);
        }
        if($sensor[2] == '1'){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Сработал датчик воды\n", FILE_APPEND);
        }
        if($sensor[1] == '1'){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Сработал датчик дыма\n", FILE_APPEND);
        }
        if($sensor[0] == '1'){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Сработал датчик движения\n", FILE_APPEND);
        }
        $wettemp = explode("/", $_GET['wet']);
        $minute = date("i.s");
        if((($minute > "00.00")and($minute < "00.10"))or(($minute > "30.00")and($minute < "30.10"))){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Температура/Влажность в помещении: "
                .$wettemp[0]."/".$wettemp[1]."\n", FILE_APPEND);
        }
        if($wettemp[0]>"30") {
            file_put_contents($name_file . $_GET['id'], date("H:i:s") . " Критично! Температура/Влажность в помещении: "
                . $wettemp[0] . "/" . $wettemp[1] . "\n", FILE_APPEND);
        }
        if($wettemp[1]>"50"){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Критично! Температура/Влажность в помещении: "
                .$wettemp[0]."/".$wettemp[1]."\n", FILE_APPEND);
        }

        $sql = /** @lang text */
            "SELECT `id`, `id_athena` FROM `people` WHERE `add_in_athena`= 1";
        $subquery = mysqli_query($link, $sql);
        $rows = mysqli_fetch_row($subquery);
        if ((mysqli_num_rows($subquery) == 1) && ($rows[1] == $_GET['id'])) {
            $sql = /** @lang text */
                "UPDATE `people` SET `add_in_athena`=2 WHERE `id`=" . $rows[0];
            mysqli_query($link, $sql);
        } else {
            $sql = /** @lang text */
                "SELECT `id`, `id_athena` FROM `people` WHERE `delete_from_athena`= 1";
            $subquery = mysqli_query($link, $sql);
            $rows = mysqli_fetch_row($subquery);
            if ((mysqli_num_rows($subquery)) == 1 && ($rows[1] == $_GET['id'])) {
                //$sql = "DELETE FROM `people` WHERE `id`=".$rows[0];
                $sql = /** @lang text */
                    "UPDATE `people` SET `delete_from_athena`=2 WHERE `id`=" . $rows[0];
                mysqli_query($link, $sql);
            }
        }
        //if ($_GET['flag'] == 0){
        $sql = /** @lang text */
            "SELECT `id`, `number_card`, `id_athena` FROM `people` WHERE `add_in_athena`= 0";
        $subquery = mysqli_query($link, $sql);
        $rows = mysqli_fetch_row($subquery);
        if ((mysqli_num_rows($subquery)) == 1 && ($rows[2] == $_GET['id'])) {
            $sql = /** @lang text */
                "UPDATE `people` SET `add_in_athena`= 1 WHERE `id`=" . $rows[0];
            mysqli_query($link, $sql);
            $hash = $rows[1];
            $bit = 1;
        } else {
            $sql = /** @lang text */
                "SELECT `id`, `number_card`, `id_athena` FROM `people` WHERE `delete_from_athena`= 0";
            $subquery = mysqli_query($link, $sql);
            $rows = mysqli_fetch_row($subquery);
            if ((mysqli_num_rows($subquery)) == 1 && ($rows[2] == $_GET['id'])) {
                $sql = /** @lang text */
                    "UPDATE `people` SET `delete_from_athena`= 1 WHERE `id`=" . $rows[0];
                mysqli_query($link, $sql);
                $hash = $rows[1];
                $bit = 2;
            }
        }
        //}
        if(strlen($row[0])==2){
            echo($bit . $hash . "0". $row[0]);
        } elseif (strlen($row[0])==1){
            echo($bit . $hash . "00". $row[0]);
        } else {
            echo($bit . $hash . $row[0]);
        }

    } else {
        $sql = /** @lang text */
            "UPDATE `athenas` SET `open`=".$_GET['close'].", `hash`='".$_GET['hash']."' WHERE `id`=".$_GET['id'];
        mysqli_query($link, $sql);
        if($_GET['close'] == 1){
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." Помещение закрыто\n", FILE_APPEND);
        } elseif($_GET['close'] == 0) {
            $sql = /** @lang text */
                "SELECT `FIO` FROM `people` WHERE `number_card`='".$_GET['hash']."'";
            $res = mysqli_query($link, $sql);
            $FIO = mysqli_fetch_assoc($res);
            file_put_contents($name_file.$_GET['id'], date("H:i:s")." ".$FIO['FIO']." открыл(а) помещение\n", FILE_APPEND);
        }
    }
}
