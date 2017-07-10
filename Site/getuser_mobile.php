<?php
require_once("include/connection.php");
if (isset($_REQUEST['hash'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
    $query = mysqli_query($link, $sql);
    if(mysqli_num_rows($query) == 1) {
        if(isset($_REQUEST['id'])){
            $sql = "UPDATE `people` SET `delete_from_athena` = 0 WHERE `id` =".$_REQUEST['id'];
            mysqli_query($link, $sql);
        }
        $id = mysqli_fetch_assoc($query);
        $sql = "SELECT `id`, `FIO`, `is_admin`, `delete_from_athena` FROM `people` WHERE `id_athena` = ".$id['id_athena'];
        $query = mysqli_query($link, $sql);
        $arr[] = array('result' => 1);
        while ($users = mysqli_fetch_assoc($query)){
            if($users['delete_from_athena'] == -1) {
                $arr[] = array('id' => $users['id'], 'FIO' => $users['FIO'], 'root' => $users['is_admin']);
            }
        }
        echo json_encode($arr);
    }
} else {
    echo $jarray[] = json_encode(array('result' => 0));
}