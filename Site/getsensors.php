<?php
require_once("include/connection.php");
session_start();
if (isset($_SESSION['hash']) and isset($_SESSION['admin'])) {
    $sql = "SELECT `id_athena`, `is_admin` FROM `people` WHERE `autorization_hash` = '".$_SESSION['hash']."'";
    $query = mysqli_query($link, $sql);
    $user = mysqli_fetch_assoc($query);
    $sql = "SELECT `sensorsActivity`, `sensors`, `wet/temp`, `open` FROM `athenas` WHERE `id` = ".$user['id_athena'];
    $query = mysqli_query($link, $sql);
    $data = mysqli_fetch_assoc($query);
    if(isset($_REQUEST['sensor'])){
        if($_REQUEST['add'] == 1){
            $data['sensorsActivity'] -= $_REQUEST['sensor'];
        } else {
            $data['sensorsActivity'] += $_REQUEST['sensor'];
        }
        $sql = "UPDATE `athenas` SET `sensorsActivity`=".$data['sensorsActivity']." WHERE `id` = ".$user['id_athena'];
        mysqli_query($link, $sql);
    }
    $sensors = sprintf("%05d", decbin($data['sensors']));
    $config = sprintf("%07d", decbin($data['sensorsActivity']));
    if($user['is_admin'] == '0') {
        $str = "<table class='table_dark'>" .
            "<tr><th>Датчик<br>проникновения</th><th>Датчик<br>вибрации</th><th>Датчик<br>воды</th><th>Датчик<br>дыма</th><th>Датчик<br>движения</th><th>Датчик<br>влажности и<br>температуры</th><th>Система<br>пропусков</th></tr>" .
            "<tr>" . (($sensors[4] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[3] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[2] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[1] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[0] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . "</td>" .
            "<td>" . $data['wet/temp'] . "</td><td>" . (($data['open'] == '1') ? 'Система<br>закрыта' : 'Система<br>открыта') . "</td></tr>" .
            "</table>";
    } else {
        $wt = explode('/', $data['wet/temp']);
        $str = "<table class='table_dark'>" .
            "<tr><th>Датчик<br>проникновения</th><th>Датчик<br>вибрации</th><th>Датчик<br>воды</th><th>Датчик<br>дыма</th><th>Датчик<br>движения</th><th>Датчик<br>влажности и<br>температуры</th><th>Система<br>пропусков</th></tr>" .
            "<tr>" . (($sensors[4] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[3] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[2] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[1] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . (($sensors[0] == '1') ? '<td class="alarm">Сработал</td>' : '<td>Молчит</td>') . "</td>" .
            (($wt[0] > 30 or $wt[1] > 50) ? "<td  class='alarm'>" : "<td>") . $data['wet/temp'] . "</td>" . (($data['open'] == '1') ? '<td>Система<br>закрыта' : '<td  class="alarm">Система<br>открыта') . "</td></tr>" .
            "<tr><td><button ".(($config[6] == '1') ? 'value = "1"' : 'value = "0"')." onclick=\"config(event, 1)\">" . (($config[6] == '1') ? 'Отключить' : 'Включить') ."</button></td><td><button ".(($config[5] == '1') ? 'value = "1"' : 'value = "0"')." onclick=\"config(event, 2)\">" . (($config[5] == '1') ? 'Отключить' : 'Включить') ."</button></td><td><button ".(($config[4] == '1') ? 'value = "1"' : 'value = "0"')." onclick='config(event, 4)'>" . (($config[4] == '1') ? 'Отключить' : 'Включить') ."</button></td><td><button ".(($config[3] == '1') ? 'value = "1"' : 'value = "0"')." onclick='config(event, 8)'>" . (($config[3] == '1') ? 'Отключить' : 'Включить') ."</button></td><td><button ".(($config[2] == '1') ? 'value = "1"' : 'value = "0"')." onclick='config(event, 16)'>" . (($config[2] == '1') ? 'Отключить' : 'Включить') ."</button></td><td><button ".(($config[1] == '1') ? 'value = "1"' : 'value = "0"')." onclick='config(event, 32)'>" . (($config[1] == '1') ? 'Отключить' : 'Включить') ."</button></td><td><button ".(($config[0] == '1') ? 'value = "1"' : 'value = "0"')." onclick='config(event, 64)'>" . (($config[0] == '1') ? 'Отключить' : 'Включить') ."</button></td></tr>".
            "</table>";
    }
    echo $str;
} else{
    print "Что-то пошло не так";
}
?>