<?php
require_once("include/connection.php");
function generateCode($length=6)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789 ";
    $code = "";
    $clen = strlen($chars) - 1;
    while (strlen($code) < $length) {
        $code .= $chars[mt_rand(0, $clen)];
    }
    return $code;
}
if(isset($_REQUEST['login']) and isset($_REQUEST['password'])) {
    $query = mysqli_query($link, "SELECT `id`, `password`, `is_admin` FROM `people` WHERE `login`='" . mysqli_real_escape_string($link, mb_strtolower($_REQUEST['login'], "UTF-8")) . "' LIMIT 1");
    $data = mysqli_fetch_assoc($query);
// Сравниваем пароли
    if ($data['password'] === $_REQUEST['password']) {
        // Генерируем случайное число и шифруем его
        $hash = md5(generateCode(10));
        $datte = "";
        $dateday = date("d");
        $datemouth = date("m");
        if (($datemouth == "01") and ($dateday == "31")){
            $datemouth = "02";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif ((($datemouth == "02") and ($dateday == "28") and (date("L") == "0")) or (($datemouth == "02") and ($dateday == "29") and (date("L") == "1"))) {
            $datemouth = "03";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "03") and ($dateday == "31")) {
            $datemouth = "04";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "04") and ($dateday == "30")) {
            $datemouth = "05";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "05") and ($dateday == "31")) {
            $datemouth = "06";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "06") and ($dateday == "30")) {
            $datemouth = "07";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "07") and ($dateday == "31")) {
            $datemouth = "08";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "08") and ($dateday == "31")) {
            $datemouth = "09";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "09") and ($dateday == "30")) {
            $datemouth = "10";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "10") and ($dateday == "31")) {
            $datemouth = "11";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "11") and ($dateday == "30")) {
            $datemouth = "12";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } elseif (($datemouth == "12") and ($dateday == "31")) {
            $datemouth = "01";
            $dateday = "01";
            $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
        } else{
            $dateday = sprintf("%02d", ($dateday + 1));
            $datte = date("Y-m") . "-" . $dateday . " " . date("H:i:s");
        }

        // Записываем в БД новый хеш авторизации
        mysqli_query($link, "UPDATE `people` SET `mobile_hash`='" . $hash . "', `mobile_time`='" . $datte . "' WHERE id=" . $data['id']);

        $arr = array('result' => 1, 'hash' => $hash, 'root' => $data['is_admin']);

        echo json_encode($arr);
    } else {
        echo json_encode(array('result' => 0));
    }
} elseif (isset($_REQUEST['hash'])){
    $query = mysqli_query($link, "SELECT `id`, `mobile_time`, `is_admin` FROM `people` WHERE `mobile_hash`='" . $_REQUEST['hash'] . "' LIMIT 1");
    if(mysqli_num_rows($query) != 1){
        echo json_encode(array('result' => 0));
    } else {
        $data = mysqli_fetch_assoc($query);
        if($data['mobile_time'] > date("Y-m-d H:i:s")){
            $hash = md5(generateCode(10));
            $datte = "";
            $dateday = date("d");
            $datemouth = date("m");
            if (($datemouth == "01") and ($dateday == "31")){
                $datemouth = "02";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif ((($datemouth == "02") and ($dateday == "28") and (date("L") == "0")) or (($datemouth == "02") and ($dateday == "29") and (date("L") == "1"))) {
                $datemouth = "03";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "03") and ($dateday == "31")) {
                $datemouth = "04";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "04") and ($dateday == "30")) {
                $datemouth = "05";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "05") and ($dateday == "31")) {
                $datemouth = "06";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "06") and ($dateday == "30")) {
                $datemouth = "07";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "07") and ($dateday == "31")) {
                $datemouth = "08";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "08") and ($dateday == "31")) {
                $datemouth = "09";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "09") and ($dateday == "30")) {
                $datemouth = "10";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "10") and ($dateday == "31")) {
                $datemouth = "11";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "11") and ($dateday == "30")) {
                $datemouth = "12";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } elseif (($datemouth == "12") and ($dateday == "31")) {
                $datemouth = "01";
                $dateday = "01";
                $datte = date("Y")."-".$datemouth."-".$dateday." ".date("H:i:s");
            } else{
                $dateday = sprintf("%02d", ($dateday + 1));
                $datte = date("Y-m") . "-" . $dateday . " " . date("H:i:s");
            }

            // Записываем в БД новый хеш авторизации
            mysqli_query($link, "UPDATE `people` SET `mobile_hash`='" . $hash . "', `mobile_time`='" . $datte . "' WHERE id=" . $data['id']);


            echo json_encode(array('result' => 1, 'hash' => $hash, 'root' => $data['is_admin']));
        } else {
            echo json_encode(array('result' => 0));
        }
    }
}
