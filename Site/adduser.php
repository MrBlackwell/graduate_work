<?php
session_start();
require_once("include/connection.php");
if (isset($_SESSION['hash']) and isset($_SESSION['admin']) and ($_SESSION['admin'] == 1) and isset($_POST['submit'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `autorization_hash` = '" . $_SESSION['hash'] . "'";
    $query = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($query);
    $sql = "SELECT `id` FROM `people` WHERE `login` = '".mb_strtolower($_POST['login'], "UTF-8")."' OR `number_card` = '".md5($_POST['number_card'])."'";
    $query = mysqli_query($link, $sql);
    if(mysqli_num_rows($query) == 0 ){
        $values = "'".$user['id_athena']."', ";
        $values .= "'".$_POST['FIO']."', ";
        $values .= "'".$_POST['email']."', ";
        $values .= "'".mb_strtolower($_POST['login'], "UTF-8")."', ";
        $values .= "'".md5(md5($_POST['password']))."', ";
        $values .= ((isset($_POST['admin'])) ? "1" : "0").", ";
        $values .= "'".md5($_POST['number_card'])."', ";
        $values .= "0, ";
        $values .= "-1";
        $sql = "INSERT INTO `people`(`id_athena`, `FIO`, `email`, `login`, `password`, `is_admin`, `number_card`, `add_in_athena`, `delete_from_athena`) VALUES (".$values.")";
        $query = mysqli_query($link, $sql);
        header("Location: admin_monitor.php");
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Добавление пользователя</title>
    <link href="Untitled1.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="jquery-1.12.4.min.js"></script>
    <script src="wb.rotate.min.js"></script>
    <script src="wwb12.min.js"></script>
</head>
<body>
<div class="form1" id="add_Form1" <? if(!isset($_POST['submit'])){ echo 'style="width:444px;height:380px;z-index:5;"';} else {echo 'style="width:444px;height:410px;z-index:5;"';}?>>
    <form name="addUser" method="post" action="adduser.php" id="Form1">
        <label for="" id="Label1"
               style="position:absolute;left:0px;top:15px;width:444px;height:32px;line-height:32px;z-index:3;">Добавление пользователя</label>
        <input type="text" id="FIO"
               style="position:absolute;left:99px;top:75px;width:232px;height:20px;line-height:20px;z-index:0;"
               name="FIO" value="" tabindex="1" spellcheck="false" placeholder="Фамилия Имя Отчество" required>
        <input type="email" id="email"
               style="position:absolute;left:99px;top:115px;width:232px;height:20px;line-height:20px;z-index:0;"
               name="email" value="" tabindex="2" spellcheck="false" placeholder="E-mail" required>
        <input type="text" id="login"
               style="position:absolute;left:99px;top:155px;width:232px;height:20px;line-height:20px;z-index:0;"
               name="login" value="" tabindex="3" spellcheck="false" placeholder="Логин (от 8 до 16 символов)" pattern="[A-Za-z0-9]{8,16}" required>
        <input type="password" id="password"
               style="position:absolute;left:99px;top:195px;width:232px;height:20px;line-height:20px;z-index:1;"
               name="password" value="" tabindex="4" spellcheck="false"
               placeholder="Пароль (от 8 символов)" pattern="[A-Za-zА-Яа-яЁё0-9]{6,}" required>
        <input type="password" id="number_card"
               style="position:absolute;left:99px;top:235px;width:232px;height:20px;line-height:20px;z-index:1;"
               name="number_card" value="" tabindex="5" spellcheck="false"
               placeholder="Номер карты без пробелов" required>
        <label for="admin"style="position:absolute;left:80px;top:275px;width:232px;height:20px;line-height:18px;z-index:1;">Выдать права Admin</label><input
                style="position:absolute;left:175px;top:275px;width:232px;height:20px;line-height:18px;z-index:1;"
                type="checkbox" id="admin" name="admin" tabindex="6">
        <input type="submit" id="Button1" name="submit" value="Добавить"
               style="position:absolute;left:120px;top:305px;width:96px;height:46px;z-index:2;" tabindex="7">
        <input type="button" id="Button2" value="Назад" onclick="location.href = 'admin_monitor.php'";
               style="position:absolute;left:220px;top:305px;width:96px;height:46px;z-index:2;" tabindex="8">
        <?if(isset($_POST['submit'])) { echo '<div align="center" style="position:absolute;left:10px;top:360px;width:430px;height:32px;z-index:3;">Такой логин или карта уже сушествуют</div>';}?>
    </form>
</div>
</body>
</html>