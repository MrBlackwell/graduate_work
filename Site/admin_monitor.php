<?php
// Скрипт проверки
require_once("include/connection.php");
session_start();

if (isset($_SESSION['hash']) and isset($_SESSION['admin'])) {
    $query = mysqli_query($link, "SELECT `login` FROM `people` WHERE `autorization_hash` = '" . $_SESSION['hash'] . "' LIMIT 1");
    $userdata = mysqli_fetch_assoc($query);

    if ((mysqli_num_rows($query) != 1)) {
        unset($_SESSION['hash']);
        unset($_SESSION['admin']);
        session_destroy();
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}
?>
<html>
<head>
    <title>Athena</title>
    <link href="Untitled1.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="http_ajax.googleapis.com_ajax_libs_jquery_1.6.1_jquery.js"></script>
    <script type="text/javascript" src="function.js"></script>
    <script src="jquery-1.12.4.min.js"></script>
    <script src="wb.rotate.min.js"></script>
    <script src="wwb12.min.js"></script>
</head>
<body>
<div style="float: right">
    <div style="position:absolute;right: 25px">
        <div style="float:right"><button class="button" id = "exit" onmouseenter="Animate('exit', '', '', '', '', '200', 200, '');return false;"
                                         onmouseleave="Animate('exit', '', '', '', '', '40', 40, '');return false;" onclick="exit()">Выход</button></div>
        <div style="float:right"><button class="button" id = "settings" onmouseenter="Animate('settings', '', '', '', '', '200', 200, '');return false;"
                                         onmouseleave="Animate('settings', '', '', '', '', '40', 40, '');return false;" onclick="setting()">Настройки</button></div>
    </div>
    <div style="position:absolute;right: 20px;top: 40px" id="user" title="Пользователи"></div>
</div>
<div style="float: left">
    <div style="position:absolute;top: 40px;left: 20px" id="sensors" title="Датчики"></div>
    <div id="chart" style="position:absolute;top: 220px;left: 20px;padding:5px 10px;width: 800px; height: 350px" title="График температура/влажность за 24 часа"></div>
    <div style="float: left;padding:5px 10px;position:absolute;top: 570px;left: 20px;">
        <textarea style="width:444px;height:265px;" title="Логи" id="logs" readonly ></textarea>
    </div>
</div>
<script type="text/javascript">
    sensors();
    mode();
    drawGraph();
    //outputGraph();
    outputUser();
    setInterval(sensors, 5000);
    //setInterval(outputGraph, 120000);
    setInterval(mode ,10000);
    setInterval(drawGraph, 120000);
    setInterval(outputUser, 600000);
</script>
</body>
</html>