//Получение логов
function mode() {
    $.ajax({
        type: 'POST',
        url: "getlog.php",
        success: function (data) {
            $('#logs').html(data);
        }
    });
}

//Создание графика
function drawGraph() {
    $.ajax({
        type: 'POST',
        url: "draw_graph.php",
        success: function (data) {
            var str = '<img src="'+ data +'?'+Math.random()+'">';
            $('#chart').html(str);
        }
    });
}

//Вывод графика
function outputGraph(str1) {
    var str = '<img src='+ str +'"graphic.png?'+Math.random()+'">';
    $('#chart').html(str);
}

//Получение таблицы датчиков
function sensors() {
    $.ajax({
        type: 'POST',
        url: "getsensors.php",
        success: function (data) {
            $('#sensors').html(data);
        }
    });
}

//Включение/отключение датчиков
function config(event, ch) {
    var value;
    value = event.currentTarget.value;
    console.log(value);
    $.ajax({
        type: 'POST',
        url: "getsensors.php",
        data: {'sensor':ch, 'add':value},
        success: function (data) {
            $('#sensors').html(data);
        }
    });
}

//Вывод пользователей
function outputUser() {
    $.ajax({
        type: 'POST',
        url: "getuser.php",
        success: function (data) {
            $('#user').html(data);
        }
    });
}

function deleteUser(event, id) {
    console.log(id);
    $.ajax({
        type: 'POST',
        url: "getuser.php",
        data: {'add':0, 'id':id},
        success: function (data) {
            $('#user').html(data);
        }
    });
}

function toAddUser() {
    location.href = 'adduser.php';
}

function exit() {
    location.href = 'logout.php';
}

function setting() {
    location.href = 'settings.php';
}
