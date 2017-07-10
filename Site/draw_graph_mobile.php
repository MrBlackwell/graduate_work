<?php
require_once("include/connection.php");
include("pChart/class/pDraw.class.php");
include("pChart/class/pImage.class.php");
include("pChart/class/pData.class.php");
if (isset($_REQUEST['hash'])) {
    $sql = "SELECT `id_athena` FROM `people` WHERE `mobile_hash` = '" . $_REQUEST['hash'] . "'";
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

    $myPicture = new pImage(790, 540, $data);

    $Settings = array("R" => 255, "G" => 255, "B" => 255);
    $myPicture->drawFilledRectangle(0, 0, 790, 540, $Settings);

    $Settings = array("StartR" => 101, "StartG" => 72, "StartB" => 184, "EndR" => 104, "EndG" => 160, "EndB" => 232, "Alpha" => 50);
    $myPicture->drawGradientArea(0, 0, 790, 540, DIRECTION_VERTICAL, $Settings);

    $myPicture->setFontProperties(array("FontName" => "pChart/fonts/verdana.ttf", "FontSize" => 6));
    $myPicture->drawText(380, 25, "Температура/Влажность", array("FontSize" => 16, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

    $myPicture->setGraphArea(25, 40, 785, 500);
    $myPicture->drawScale(array("DrawSubTicks" => TRUE));
    $myPicture->setFontProperties(array("FontName" => "pChart/fonts/verdana.ttf", "FontSize" => 8));
    $myPicture->drawLineChart();
    $myPicture->setShadow(FALSE);

    $myPicture->drawLegend(300, 525, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

    $name = $id['id_athena'] . "-graphic_mobile.png";

    $myPicture->Render($name);

    if (isset($_REQUEST['hash'])) {
        echo json_encode(array('result' => 1, 'name' => $name));
    }

} else{
    print "Что-то пошло не так";
}