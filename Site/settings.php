<?php
session_start();
require_once("include/connection.php");
if (isset($_SESSION['hash']) and isset($_SESSION['admin'])) {
    $sql = "SELECT `id`, `FIO`, `email`, `login` FROM `people` WHERE `autorization_hash` = '" . $_SESSION['hash'] . "'";
    $query = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($query);
    if(isset($_POST['submitFIO'])){
        $sql = "UPDATE `people` SET `FIO`='".$_POST['FIO']."' WHERE `id`=".$user['id'];
        mysqli_query($link, $sql);
    }
    if(isset($_POST['submitemail'])){
        $sql = "UPDATE `people` SET `email`='".$_POST['email']."' WHERE `id`=".$user['id'];
        mysqli_query($link, $sql);
    }
    if(isset($_POST['submitlogin'])){
        $sql = "SELECT `id` FROM `people` WHERE `login` ='".mb_strtolower($_POST['login'], "UTF-8")."'";
        $query = mysqli_query($link, $sql);
        if(mysqli_num_rows($query) == 0){
            $sql = "UPDATE `people` SET `login`='".mb_strtolower($_POST['login'], "UTF-8")."' WHERE `id`=".$user['id'];
            mysqli_query($link, $sql);
        } else {
            $flag = 0;
        }
    }
    if(isset($_POST['submitpassword'])){
        if($_POST['password'] == $_POST['password1']){
            $sql = "UPDATE `people` SET `mobile_hash` = '',`password`='".md5(md5($_POST['password']))."' WHERE `id`=".$user['id'];
            mysqli_query($link, $sql);
        } else {
            $flag = 1;
        }
    }
    $sql = "SELECT `id`, `FIO`, `email`, `login` FROM `people` WHERE `autorization_hash` = '" . $_SESSION['hash'] . "'";
    $query = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($query);
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Редактирование</title>
    <link href="Untitled1.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
    <script src="jquery-1.12.4.min.js"></script>
    <script src="wb.rotate.min.js"></script>
    <script src="wwb12.min.js"></script>
</head>
<body>
<div class="form1" id="add_Form1" <?
if((!isset($_POST['submitFIO'])) and (!isset($_POST['submitemail']) and (!isset($_POST['submitlogin']))) and (!isset($_POST['submitpassword']))){
    echo 'style="width:444px;height:410px;z-index:5;"';
} else {
    echo 'style="width:444px;height:430px;z-index:5;"';
} ?>>
    <form name="editUser" method="post" action="settings.php" id="Form1">
        <label for="" id="Label1"
               style="position:absolute;left:0px;top:15px;width:444px;height:32px;line-height:32px;z-index:3;">Настройки</label>
        <input type="text" id="FIO"
               style="position:absolute;left:99px;top:75px;width:232px;height:20px;line-height:20px;z-index:0;"
               name="FIO" value="<?echo $user['FIO']?>" tabindex="1" spellcheck="false" placeholder="Фамилия Имя Отвество">
        <input type="submit" id="Button1" name="submitFIO" value="Изменить"
               style="position:absolute;left:120px;top:115px;width:96px;height:25px;z-index:2;" tabindex="2">

        <input type="email" id="email"
               style="position:absolute;left:99px;top:150px;width:232px;height:20px;line-height:20px;z-index:0;"
               name="email" value="<?echo $user['email']?>" tabindex="3" spellcheck="false" placeholder="E-mail">
        <input type="submit" id="Button1" name="submitemail" value="Изменить"
               style="position:absolute;left:120px;top:190px;width:96px;height:25px;z-index:2;" tabindex="4">

        <input type="text" id="login"
               style="position:absolute;left:99px;top:225px;width:232px;height:20px;line-height:20px;z-index:0;"
               name="login" value="<?echo $user['login']?>" tabindex="5" spellcheck="false" placeholder="Логин (от 8 до 16 символов)" pattern="[A-Za-z0-9]{8,16}">
        <input type="submit" id="Button1" name="submitlogin" value="Изменить"
               style="position:absolute;left:120px;top:265px;width:96px;height:25px;z-index:2;" tabindex="6">

        <input type="password" id="password"
               style="position:absolute;left:99px;top:300px;width:232px;height:20px;line-height:20px;z-index:1;"
               name="password" value="" tabindex="7" spellcheck="false"
               placeholder="Новый пароль (от 8 символов)" pattern="[A-Za-zА-Яа-яЁё0-9]{6,}">
        <input type="password" id="password1"
               style="position:absolute;left:99px;top:340px;width:232px;height:20px;line-height:20px;z-index:1;"
               name="password1" value="" tabindex="8" spellcheck="false"
               placeholder="Повторите пароль" pattern="[A-Za-zА-Яа-яЁё0-9]{6,}">
        <input type="submit" id="Button1" name="submitpassword" value="Изменить"
               style="position:absolute;left:120px;top:380px;width:96px;height:25px;z-index:2;" tabindex="9">
        <input type="button" id="Button2" value="Назад" onclick="location.href = 'admin_monitor.php'";
               style="position:absolute;left:220px;top:380px;width:96px;height:25px;z-index:2;" tabindex="10">
        <? if((isset($flag)) and ($flag==0)) {
            echo '<div align="center" style="position:absolute;left:10px;top:405px;width:430px;height:32px;z-index:3;">Такой логин уже существуют</div>';
        } elseif((isset($flag)) and ($flag==1)) {
            echo '<div align="center" style="position:absolute;left:10px;top:405px;width:430px;height:32px;z-index:3;">Пароли не совпадают</div>';
        } elseif((isset($_POST['submitFIO'])) or (isset($_POST['submitemail']) or (isset($_POST['submitlogin']))) or (isset($_POST['submitpassword']))){
            echo '<div align="center" style="position:absolute;left:10px;top:405px;width:430px;height:32px;z-index:3;">Изменения сохранены</div>';
        }
        ?>
    </form>
</div>
</body>
</html>