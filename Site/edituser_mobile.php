<?php
require_once("include/connection.php");
if (isset($_REQUEST['hash'])) {
    if (isset($_REQUEST['edit'])) {
        if(isset($_REQUEST['login'])) {
            $sql = "SELECT `id` FROM `people` WHERE `mobile_hash` != '" . $_REQUEST['hash'] . "' AND `login` = '" . $_REQUEST['login'] . "'";
            $query = mysqli_query($link, $sql);
            if (mysqli_num_rows($query) == 0) {
                $sql = "UPDATE `people` SET ";
                if ($_REQUEST['name'] != "") {
                    $sql .= "`FIO` = '" . $_REQUEST['name'] . "', ";
                }
                if ($_REQUEST['email']) {
                    $sql .= "`email` = '" . $_REQUEST['email'] . "', ";
                }
                if ($_REQUEST['login']) {
                    $sql .= "`login` = '" . $_REQUEST['login'] . "', ";
                }
                if ($_REQUEST['password']) {
                    $sql .= "`password` = '" . $_REQUEST['password'] . "', ";
                }
                $sql = mb_substr($sql, 0, -2, "UTF-8");
                $sql .= " WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
                mysqli_query($link, $sql);
            } else {
                echo json_encode(array('result' => 0));
            }
        } else {
            $sql = "UPDATE `people` SET ";
            if ($_REQUEST['name'] != "") {
                $sql .= "`FIO` = '" . $_REQUEST['name'] . "', ";
            }
            if ($_REQUEST['email']) {
                $sql .= "`email` = '" . $_REQUEST['email'] . "', ";
            }
            if ($_REQUEST['password']) {
                $sql .= "`password` = '" . $_REQUEST['password'] . "', ";
            }
            $sql = mb_substr($sql, 0, -2, "UTF-8");
            $sql .= " WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
            mysqli_query($link, $sql);
        }
        $sql = "SELECT `FIO`, `email`, `login` FROM `people` WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
        $query = mysqli_query($link, $sql);
        if (mysqli_num_rows($query) == 1) {
            $user = mysqli_fetch_assoc($query);
            echo json_encode(array('result' => '1', 'name' => $user['FIO'], 'login' => $user['login'], 'email' => $user['email']));
        } else {
            echo json_encode(array('result' => 0));
        }
        if(isset($_REQUEST['password'])){
            $sql = "UPDATE `people` SET `autorization_hash`='' WHERE `mobile_hash` = ".$_REQUEST['hash'];
        }
    } else {
        $sql = "SELECT `FIO`, `email`, `login` FROM `people` WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
        $query = mysqli_query($link, $sql);
        if (mysqli_num_rows($query) == 1) {
            $user = mysqli_fetch_assoc($query);
            echo json_encode(array('name' => $user['FIO'], 'login' => $user['login'], 'email' => $user['email']));
        } else {
            echo json_encode(array('result' => 0));
        }
    }
} else {
    echo json_encode(array('result' => 0));
}