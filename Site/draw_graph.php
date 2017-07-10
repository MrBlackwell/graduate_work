<?php
require_once("include/connection.php");
include("pChart/class/pDraw.class.php");
include("pChart/class/pImage.class.php");
include("pChart/class/pData.class.php");
session_start();
if (isset($_SESSION['hash']) and isset($_SESSION['admin'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `autorization_hash` = '" . $_SESSION['hash'] . "'";
    $query = mysqli_query($link, $sql);
    $id = mysqli_fetch_assoc($query);
    $logFailToday = fopen("log_" . date("d.m.Y") . "_" . $id['id_athena'], "r");
    $yesterday = date('d.m.Y', strtotime('yesterday'));
    $logFailYesteday = fopen("log_" . $yesterday . "_" . $id['id_athena'], "r");
    $str = fgets($logFailYesteday);
    $time = [];
    $temperature = [];
    $humidity = [];
    $i = 1;
    while ($str != false) {
        if (stristr($str, "Температура/Влажность") != null) {
            $element = [];
            $parts = explode(" ", $str);
            if ($parts[0] > date("H:i:s")) {
                if ($i % 2 != 0) {
                    $time[] = mb_substr($parts[0], 0, 5, "UTF-8");
                } else {
                    $time[] = "";
                }
                $wettemp = explode("/", $parts[count($parts) - 1]);
                $temperature[] = $wettemp[0];
                $humidity[] = $wettemp[1];
                $i++;
            }
        }
        $str = fgets($logFailYesteday);
    }
    $logFailToday = fopen("log_" . date("d.m.Y") . "_" . $id['id_athena'], "r");
    $str = fgets($logFailToday);
    while ($str != false) {
        if (stristr($str, "Температура/Влажность") != null) {
            $element = [];
            $parts = explode(" ", $str);
            if ($parts[0] < date("H:i:s")) {
                if ($i % 2 != 0) {
                    $time[] = mb_substr($parts[0], 0, 5, "UTF-8");
                } else {
                    $time[] = "";
                }
                $wettemp = explode("/", $parts[count($parts) - 1]);
                $temperature[] = $wettemp[0];
                $humidity[] = $wettemp[1];
                $i++;
            }
        }
        $str = fgets($logFailToday);
    }
    $data = new pData();
    $data->addPoints($time, "Serie1");
    $data->addPoints($temperature, "Температура");
    $data->addPoints($humidity, "Влажность");
    $data->setSerieWeight("Температура", 2);
    $data->setSerieWeight("Влажность", 2);
    $serieSettings = array("R" => 255, "G" => 13, "B" => 0);
    $data->setPalette("Температура", $serieSettings);
    $serieSettings = array("R" => 73, "G" => 173, "B" => 33);
    $data->setPalette("Влажность", $serieSettings);
    $data->setAbscissa("Serie1");

    $myPicture = new pImage(790, 350, $data);

    $Settings = array("R" => 255, "G" => 255, "B" => 255);
    $myPicture->drawFilledRectangle(0, 0, 790, 350, $Settings);

    $Settings = array("StartR" => 101, "StartG" => 72, "StartB" => 184, "EndR" => 104, "EndG" => 160, "EndB" => 232, "Alpha" => 50);
    $myPicture->drawGradientArea(0, 0, 790, 350, DIRECTION_VERTICAL, $Settings);

    $myPicture->setFontProperties(array("FontName" => "pChart/fonts/verdana.ttf", "FontSize" => 6));
    $myPicture->drawText(380, 25, "Температура/Влажность", array("FontSize" => 16, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

    $myPicture->setGraphArea(25, 40, 785, 310);
    $myPicture->drawScale(array("DrawSubTicks" => TRUE));
    $myPicture->setFontProperties(array("FontName" => "pChart/fonts/verdana.ttf", "FontSize" => 8));
    $myPicture->drawLineChart();
    $myPicture->setShadow(FALSE);

    $myPicture->drawLegend(300, 335, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

    $name = $id['id_athena'] . "-graphic.png";

    echo $name;

    $myPicture->Render($name);

} else{
    print "Что-то пошло не так";
}