<?

//предотвращаем доступ по http
header('HTTP/1.1 400 Bad Request');
$servername = "localhost";
$database = "shop";
$username = "mysql";
$password = "mysql";
$mysqli = new mysqli($servername, $username, $password, $database);
if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}


function getCountryByIp($ip)
{
    // Преобразуем IP в число
    $int = sprintf("%u", ip2long($ip));
    $sql = "select * from (select * from net_euro where begin_ip<=$int order by begin_ip desc limit 1) as t where end_ip>=$int";
    $result = $GLOBALS['mysqli']->query($sql);
    if ($result->num_rows == 0) {
        $sql = "select * from (select * from net_country_ip where begin_ip<=$int order by begin_ip desc limit 1) as t where end_ip>=$int";
        $result = $GLOBALS['mysqli']->query($sql);
    }
    if ($row = $result->fetch_row()) {
        $country_id = $row['0'];
        $sql = "select * from net_country where id='$country_id'";
        $result = $GLOBALS['mysqli']->query($sql);
        if ($row = $result->fetch_row()) {
            $country_name = $row['1'];
        }
    }
    if ($country_id == 0) {
        return "Страна не определена";
    } else {
        return $country_name;
    }
    $GLOBALS['mysqli']->close(); // закрываем подключение

};

function selectUsersByCountry()
{
    $query = 'SELECT ip, COUNT(v.ip) AS "Количество"
    FROM visits v
    GROUP BY ip
    ORDER BY COUNT(v.ip) DESC
    ';
    $result = $GLOBALS['mysqli']->query($query);
    $i = 0; //counter
    while ($row = $result->fetch_row()) {
        $resArr[$i] = $row;
        $i++;
    }
    return $resArr;
    $GLOBALS['mysqli']->close();
};

function getTheMostPopularTimeOfDay($catId)
{
    $trueArray = [1, 2, 3, 4, 5];
    if (in_array($GLOBALS['mysqli']->real_escape_string($catId), $trueArray)) {
        $query = " SELECT * FROM (
            SELECT c.cat_name, COUNT(*), 'Утро'
            FROM visits v
            JOIN pages_cats pc
            ON v.id=pc.id
            JOIN categories c
            ON pc.cat_id=c.cat_id
            WHERE pc.cat_id= ? AND hour(v.visit_time) >= 6 AND hour(v.visit_time) < 12
            GROUP BY c.cat_name
            UNION SELECT c.cat_name, COUNT(*), 'День'
            FROM visits v
            JOIN pages_cats pc
            ON v.id=pc.id
            JOIN categories c
            ON pc.cat_id=c.cat_id
            WHERE pc.cat_id=? AND hour(v.visit_time) >= 12 AND hour(v.visit_time) < 18
            GROUP BY c.cat_name
            UNION SELECT c.cat_name, COUNT(*), 'Вечер'
            FROM visits v
            JOIN pages_cats pc
            ON v.id=pc.id
            JOIN categories c
            ON pc.cat_id=c.cat_id
            WHERE pc.cat_id=? AND hour(v.visit_time) >= 18 AND hour(v.visit_time) < 22
            GROUP BY c.cat_name
            UNION SELECT c.cat_name, COUNT(*), 'Ночь'
            FROM visits v
            JOIN pages_cats pc
            ON v.id=pc.id
            JOIN categories c
            ON pc.cat_id=c.cat_id
            WHERE pc.cat_id=?  AND ((hour(v.visit_time) >= 22) AND (hour(v.visit_time)<24) OR ((hour(v.visit_time) >=0) AND (hour(v.visit_time) <6)))
            GROUP BY c.cat_name
            ) AS a
            order BY 2 DESC
            LIMIT 1"; // объявляем переменную с запросом
        $stmt = $GLOBALS['mysqli']->prepare($query); // подготавливаем  запрос
        if (!$stmt) {
            exit('SQL Error: ' . $GLOBALS['mysqli']->errno . ' ' . $GLOBALS['mysqli']->error);
        }
        if (!$stmt->bind_param("ssss", $catId, $catId, $catId, $catId)) {
            echo "Не удалось привязать параметры: (" . $stmt->errno . ") " . $stmt->error;
        };
        $stmt->execute(); // выполняем подготовленный запрос
        $result = $stmt->get_result(); // получаем результат из подготовленного запроса
        while ($row = $result->fetch_row()) {
            return $row;
        }
        $result->free(); // очищаем результат
        $stmt->close(); // закрываем подготовленный запрос
        $GLOBALS['mysqli']->close(); // закрываем подключение
    }
    else return 'error';
};

function getCountQueriesPerHour($date, $h1, $h2)
{
    $time1 = "$date $h1:00:00";
    $time2 = "$date $h2:00:00";
    $query = " SELECT COUNT(*)
    FROM visits
    WHERE visit_time BETWEEN ? AND ?
    "; // объявляем переменную с запросом
    $stmt = $GLOBALS['mysqli']->prepare($query); // подготавливаем  запрос
    if (!$stmt) {
        exit('SQL Error: ' . $GLOBALS['mysqli']->errno . ' ' . $GLOBALS['mysqli']->error);
    }
    if (!$stmt->bind_param("ss", $time1, $time2)) {
        echo "Не удалось привязать параметры: (" . $stmt->errno . ") " . $stmt->error;
    };
    $stmt->execute(); // выполняем подготовленный запрос
    $result = $stmt->get_result(); // получаем результат из подготовленного запроса
    while ($row = $result->fetch_row()) {
        return $row;
    }
    $result->free(); // очищаем результат
    $stmt->close(); // закрываем подготовленный запрос
    $GLOBALS['mysqli']->close(); // закрываем подключение
};
