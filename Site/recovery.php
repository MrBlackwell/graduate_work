<?php
require_once("include/connection.php");
if(isset($_GET['rec'])){
    $result = "";
    if(isset($_POST['submit'])){
        if($_POST['password'] == $_POST['password1']){
            $sql = "UPDATE `people` SET `password` = '".md5(md5($_POST['password']))."', `recovery` = '' WHERE `recovery`='".$_GET['rec']."'";
            mysqli_query($link, $sql);
            $result = "Новый пароль успешно сохранен";
        } else {
            $result = "Пароли не совпадают";
        }
    }
    $html = "<div class=\"form1\" style=\"width:444px;height:245px;z-index:5;\">
            <form name=\"recovery\" method=\"post\" action=\"recovery.php?rec=".$_GET['rec']."\">
            <input type=\"password\" id=\"Editbox1\"
               style=\"position:absolute;left:99px;top:75px;width:232px;height:25px;line-height:20px;z-index:0;\"
               name=\"password\" value=\"\" tabindex=\"1\" spellcheck=\"false\" placeholder=\"Введите новый пароль\">
            <input type=\"password\" id=\"Editbox2\"
               style=\"position:absolute;left:99px;top:116px;width:232px;height:25px;line-height:20px;z-index:1;\"
               name=\"password1\" value=\"\" tabindex=\"2\" spellcheck=\"false\"
               placeholder=\"Повторите пароль\">
            <input type=\"submit\" id=\"Button1\" name=\"submit\" value=\"Подвердить\"
               style=\"position:absolute;left:120px;top:162px;width:96px;height:46px;z-index:2;\" tabindex=\"3\">
            <input type=\"button\" id=\"Button1\" name=\"submit\" value=\"Назад\"
               style=\"position:absolute;left:220px;top:162px;width:96px;height:46px;z-index:2;\" tabindex=\"4\"
               onclick=\"location.href = 'index.php'\">
            <label for=\"\" id=\"Label1\"
               style=\"position:absolute;left:20px;top:18px;width:400px;height:32px;line-height:32px;z-index:3;\">Новый пароль</label>";
    $html .= "<div align=\"center\" style=\"position:absolute;left:10px;top:220px;width:430px;height:32px;z-index:3;\">".$result."</div>";
    $html .= "</form></div>";
} else {
    $result = "";
    if(isset($_POST['submit'])){
        $sql = "SELECT `id`, `email` FROM `people` WHERE `login` = '".mb_strtolower($_POST['login'], "UTF-8")."'";
        $query = mysqli_query($link, $sql);
        if(mysqli_num_rows($query)==1){
            $user = mysqli_fetch_assoc($query);
            $recovery = md5($_POST['login'].date("H.i.s"));
            $sql = "UPDATE `people` SET `recovery`='".$recovery."' WHERE `id` =".$user['id'];
            mysqli_query($link, $sql);
            $message = "Для восстановления доступа к сайту SecurityAthena.ru перейдите по ссылке ниже:\r\n\r\n";
            $message .= "Если Вы не запрашивали восстановление пароля, проигнорируйте это письмо.\r\n";
            $message .= "Письмо сформированно автоматически, не отвечайте на него.\r\n\r\n";
            $message .= "https://";
            $message .= $_SERVER['SERVER_NAME'];
            $message .= "/recovery.php?rec=";
            $message .= $recovery."\r\n";
            if(mail($user['email'], "Восстановление пароля", $message, "From: SecurityAthena <noreply@securityathena.ru>"."\r\n")){
                $result = "Данные для восстановления отправлены на Ваш e-mail";
            } else {
                $result = "Ошибка, попытайтесь позже";
            }
        } else {
            $result = "Совпадений не найдено, проверьте правильность написания";
        }
    }
    $html = "<div class=\"form1\" style=\"width:444px;height:210px;z-index:5;\">
            <form name=\"recovery\" method=\"post\" action=\"recovery.php\">
            <input type=\"text\" id=\"login\"
               style=\"position:absolute;left:99px;top:75px;width:232px;height:25px;line-height:20px;z-index:0;\"
               name=\"login\" value=\"\" tabindex=\"1\" spellcheck=\"false\" placeholder=\"Введите логин\">
            <input type=\"submit\" id=\"Button1\" name=\"submit\" value=\"Восстановить\"
               style=\"position:absolute;left:120px;top:115px;width:96px;height:46px;z-index:2;\" tabindex=\"3\">
            <input type=\"button\" id=\"Button1\" name=\"submit\" value=\"Назад\"
               style=\"position:absolute;left:220px;top:115px;width:96px;height:46px;z-index:2;\" tabindex=\"4\"
               onclick=\"location.href = 'index.php'\">
            <label for=\"\" id=\"Label1\"
               style=\"position:absolute;left:15px;top:18px;width:400px;height:32px;line-height:32px;z-index:3;\">Восстановление доступа</label>";
    $html .= "<div align=\"center\" style=\"position:absolute;left:10px;top:170px;width:430px;height:32px;z-index:3;\">".$result."</div>";
    $html .= "</form>
            </div>";

}
?>
<html>
<head>
    <meta charset="utf-8">
    <title>Восстановление пароля</title>
    <link href="Untitled1.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
    <script src="jquery-1.12.4.min.js"></script>
    <script src="wb.rotate.min.js"></script>
    <script src="wwb12.min.js"></script>
</head>
<body>
<?php echo $html; ?>
</body>
</html>
