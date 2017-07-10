<?php
require_once("include/connection.php");
if ((isset($_REQUEST['hash'])) and (!isset($_REQUEST['config']))) {
    $sql = "SELECT `id`, `id_athena` FROM `people` WHERE `mobile_hash`='" . $_REQUEST['hash'] . "'";
    $query = mysqli_query($link, $sql);
    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);
        $sql = "SELECT `wet/temp`, `sensors`, `sensorsActivity`, `open` FROM `athenas` WHERE `id`='" . $user['id_athena'] . "'";
        $data = mysqli_fetch_assoc(mysqli_query($link, $sql));
        $sensorsConfig = sprintf("%07d", decbin($data['sensorsActivity']));
        $wettemp = explode("/", $data['wet/temp']);
        $sensors = "";
        if ($data['open'] == "0") {
            $sensors = "1";
        } else {
            $sensors = "0";
        }
        if (($wettemp[0] > "30") or $wettemp[1] > "50") {
            $sensors .= "1";
        } else {
            $sensors .= "0";
        }
        $sensors .= sprintf("%05d", decbin($data['sensors']));
        echo json_encode(array('result' => 1, 'sensors' => $sensors, 'config' => $sensorsConfig, 'temperature' => $wettemp[0], 'humidity' => $wettemp[1]));
    } else {
        echo json_encode(array('result' => 0));
    }
} elseif ((isset($_REQUEST['hash'])) and (isset($_REQUEST['config']))){
    $sql = "SELECT `id_athena` FROM `people` WHERE `mobile_hash`='" . $_REQUEST['hash'] . "'";
    $query = mysqli_query($link, $sql);
    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);
        $sql = "UPDATE `athenas` SET `sensorsActivity`=".$_REQUEST['config']." WHERE `id`=".$user['id_athena'];
        mysqli_query($link, $sql);
    }
}