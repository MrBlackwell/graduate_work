<?php
require_once("include/connection.php");
session_start();
if (isset($_SESSION['hash']) and isset($_SESSION['admin']) and ($_SESSION['admin'] == 1)) {
    if(isset($_REQUEST['id']) and isset($_REQUEST['add'])){
        if($_REQUEST['add'] == 0){
            $sql = "UPDATE `people` SET `delete_from_athena` = 0 WHERE `id` =".$_REQUEST['id'];
            mysqli_query($link, $sql);
        }
    }
    $sql = "SELECT `id_athena` FROM `people` WHERE `autorization_hash` = '" . $_SESSION['hash'] . "'";
    $query = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($query);
    $sql = "SELECT `id`, `FIO`, `is_admin`, `delete_from_athena` FROM `people` WHERE `id_athena` = " . $user['id_athena'];
    $query = mysqli_query($link, $sql);
    $str = "<table class='table_dark'><tr><th>ФИО</th><th>Права</th><th></th></tr>";
    while ($data = mysqli_fetch_assoc($query)) {
        if($data['delete_from_athena'] == -1) {
            $str .= "<tr><td style='width: 260px'>" . $data['FIO'] . "</td><td style='width: 50px'>" . (($data['is_admin'] == 1) ? "Admin" : "User") . "</td><td style='width: 50px'><button onclick='deleteUser(event, ".$data['id'].")'>Удалить</button></td></tr>";
        }
    }
    $str .= "<tr><td colspan='3'><button onclick='toAddUser()'>Добавить</button></td></tr></table>";
    echo $str;
} else{
    print "Что-то пошло не так";
}
?>