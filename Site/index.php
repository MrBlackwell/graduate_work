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

if (isset($_SESSION['hash']) and isset($_SESSION['admin']))
{
    $query = mysqli_query($link, "SELECT `login` FROM `people` WHERE `autorization_hash` = '".$_SESSION['hash']."' LIMIT 1");
    $userdata = mysqli_fetch_assoc($query);

    if(mysqli_num_rows($query) != 1)
    {
        unset($_SESSION['hash']);
        unset($_SESSION['admin']);
        session_destroy();
        header("Location: index.php");
    }
    else
    {
        if($_SESSION['admin'] == 1) {
            header("Location: admin_monitor.php");
        } else {
            header("Location: monitor.php");
        }
    }
} else {
    if (isset($_POST['submit'])) {
        // Вытаскиваем из БД запись, у которой логин равняеться введенному
        $query = mysqli_query($link, "SELECT `id`, `password`, `is_admin` FROM `people` WHERE `login`='" . mysqli_real_escape_string($link, mb_strtolower($_POST['login'], "UTF-8")) . "' LIMIT 1");
        $data = mysqli_fetch_assoc($query);

        // Сравниваем пароли
        if ($data['password'] === md5(md5($_POST['password']))) {
            // Генерируем случайное число и шифруем его
            $hash = md5(generateCode(10));

            // Записываем в БД новый хеш авторизации
            mysqli_query($link, "UPDATE `people` SET `autorization_hash`='" . $hash . "' WHERE id=" . $data['id']);

            session_start();
            $_SESSION['hash'] = $hash;
            $_SESSION['admin'] = $data['is_admin'];

            $sql = "UPDATE `people` SET `recovery` = '' WHERE `id`='".$data['id']."'";
            mysqli_query($link, $sql);

            if($data['is_admin'] == 1) {
                // Переадресовываем браузер на страницу проверки нашего скрипта
                header("Location: admin_monitor.php");
            } else {
                header("Location: monitor.php");
            }
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Авторизация</title>
    <link href="Untitled1.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
    <script src="jquery-1.12.4.min.js"></script>
    <script src="wb.rotate.min.js"></script>
    <script src="wwb12.min.js"></script>
</head>
<body>
<div class="form" id="wb_Form1" style="width:444px;height:265px;z-index:5;">
    <form name="enter" method="post" action="index.php" id="Form1"
          onmouseenter="Animate('wb_Form1', '', '', '', '', '100', 100, '');return false;">
        <input type="text" id="Editbox1"
               style="position:absolute;left:99px;top:75px;width:232px;height:20px;line-height:18px;z-index:0;"
               name="login" value="" tabindex="1" spellcheck="false" placeholder="&#1051;&#1086;&#1075;&#1080;&#1085;">
        <input type="password" id="Editbox2"
               style="position:absolute;left:99px;top:116px;width:232px;height:20px;line-height:18px;z-index:1;"
               name="password" value="" tabindex="2" spellcheck="false"
               placeholder="&#1055;&#1072;&#1088;&#1086;&#1083;&#1100;">
        <input type="submit" id="Button1" name="submit" value="Войти"
               style="position:absolute;left:120px;top:162px;width:96px;height:46px;z-index:2;" tabindex="3">
        <input type="button" id="Button1" name="submit" value="Восстановить пароль"
               style="position:absolute;left:220px;top:162px;width:96px;height:46px;z-index:2;" tabindex="4"
               onclick="location.href = 'recovery.php'">
        <label for="" id="Label1"
               style="position:absolute;left:173px;top:18px;width:88px;height:32px;line-height:32px;z-index:3;">Вход</label>
        <div align="center" style="position:absolute;left:10px;top:220px;width:430px;height:32px;z-index:3;"><?php if(isset($_POST['submit'])){echo 'Неверный логин или пароль';}?></div>
    </form>
</div>
</body>
</html>