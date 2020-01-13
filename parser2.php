<?php
$start = microtime(true);
$rawLog = fopen("logs/logs.txt", "r");
$visits = fopen("logs/visits.txt", "w");
$categories = fopen("logs/categories.txt", "w");
$pagesCat = fopen("logs/pages_cats.txt", "w");
$pagesCarts = fopen("logs/pages_carts.txt", "w");
$pagesPayments = fopen("logs/pages_payments.txt", "w");
$goods = fopen("logs/goods.txt", "w");
$pagesGoods = fopen("logs/pages_goods.txt", "w");
$parsedUrls = [];
$parsedQueries = [];
$categoriesList = [];
$arr1 = [];
$pagesCatList = [];
$i = 0; //counter
function getTypeId($path)
{
    switch ($path) {
        case ('/'):
            return 1;
            break;
        case (preg_match('/(?!.*success_pay)\/(.{1,}?)\//', $path) ? true : false):
            return 2;
            break;
        case (preg_match('/(?<=[a-z]\/)(.*?)(?=\/)/', $path) ? true : false):
            return 3;
            break;
        case ('/cart'):
            return 4;
            break;
        case ('/pay'):
            return 5;
            break;
        case (preg_match('/(?<=\/)success_pay_\d{4,}(?=\/)/', $path) ? true : false):
            return 6;
            break;
    };
}
function getCatId($catName)
{
    switch ($catName) {
        case 'fresh_fish':
            return 1;
            break;
        case 'canned_food':
            return 2;
            break;
        case 'semi_manufactures':
            return 3;
            break;
        case 'caviar':
            return 4;
            break;
        case 'frozen_fish':
            return 5;
            break;
    };
};

//общая обработка лога
if ($rawLog) {
    while (($buffer = fgets($rawLog, 4096)) !== false) {
        //while ($i < 100) {
        //$buffer = fgets($rawLog, 4096);
        preg_match('/\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|0[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-5][0-9])/', $buffer, $times);
        preg_match('/(?<=\[)(.*?)(?=\])/', $buffer, $keys);
        preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $buffer, $ips);
        preg_match("/(https?):\/\/[^\s$.?#].[^\s]*/", $buffer, $urls);

        //парсинг URL
        $parsedUrl = parse_url($urls[0]);
        $parsedUrls[] = $parsedUrl;
        if (array_key_exists('query', $parsedUrl)) {
            parse_str($parsedUrl['query'], $parsedQuery);
            $parsedQueries[] = $parsedQuery;
        }

        //определение страны, используется модуль GeoIP, а также база GeoIP.dat
        //$country = geoip_country_name_by_name($ips[0]);

        //создаем данные для таблицы visits
        $visits_row = "$i;shop_api;$times[0];$keys[0];$ips[0];$urls[0];" . getTypeId($parsedUrl['path']) . PHP_EOL;
        fwrite($visits, $visits_row);

        //добавление элементов в массив для парсинга категорий и товаров
        $arr1[$i]['ip'] = $ips[0];
        $arr1[$i]['time'] = $times[0];
        $arr1[$i]['parsedUrl'] = $parsedUrl;
        if (array_key_exists('query', $parsedUrl)) {
            $arr1[$i]['parsedQuery'] = $parsedQuery;
        };
        $arr1[$i]['url'] = $urls[0];


        $i++;
    }
    if (!feof($rawLog)) {
        echo "Ошибка, файл не был распаршен <br>";
    }
    //парсинг категорий
    function catParse($parsedUrls)
    {
        foreach ($parsedUrls as $value) {
            if (preg_match('/(?!.*success_pay)\/(.*?)\//', $value['path'], $matches)) {
                $result[] = $matches[1];
                $result = array_unique($result);
                $result = array_filter($result, 'strlen');
            }
        };
        return $result;
    };
    $categoriesList = catParse($parsedUrls);

    //подготавливаем данные для таблицы categories
    foreach ($categoriesList as $key => $value) {
        $cats_row = ($key + 1) . ";$value" . PHP_EOL;
        fwrite($categories, $cats_row);
    }

    //подготавливаем данные для таблицы pages_cats
    $j = 0; //counter
    foreach ($arr1 as $key => $val) {
        preg_match('/(?!.*success_pay|.*\.com)\/(.{1,}?)\//', $val['url'], $matches1);
        if ($matches1[1]) {
            $pagesCatList[$j][] = $key;
            $pagesCatList[$j][] = getCatId($matches1[1]);
            $j++;
        }
    };
    foreach ($pagesCatList as $key => $value) {
        $pagesCat_row =  "$value[0];$value[1]" . PHP_EOL;
        fwrite($pagesCat, $pagesCat_row);
    }

    //подготавливаем данные для таблицы pages_carts
    foreach ($arr1 as $key => $value) {
        if ($value['parsedUrl']['path'] != NULL && $value['parsedUrl']['path'] === '/cart') {
            $carts_row = $key . ';' . $value['parsedQuery']['cart_id'] . ';' . $value['parsedQuery']['goods_id'] . ';' . $value['parsedQuery']['amount']  . PHP_EOL;
            fwrite($pagesCarts, $carts_row);;
        }
    }

    //подготавливаем данные для таблицы pages_payments
    foreach ($arr1 as $key => $value) {
        if ($value['parsedUrl']['path'] === '/pay') {
            $tempId = $value['parsedQuery']['cart_id'];
            $filtredArr = array_filter($arr1, function ($k) {
                return $k['parsedUrl']['path'] === "/success_pay_{$GLOBALS['tempId']}/";
            });
            $payments_row = $key . ';' . $value['parsedQuery']['user_id'] . ';' . $value['parsedQuery']['cart_id'] . ';' . (bool) $filtredArr  . PHP_EOL;
            fwrite($pagesPayments, $payments_row);;
        }
    }

    //парсинг товаров в таблицу goods
    $filtredCarts = array_filter($arr1, function ($k) {
        return $k['parsedUrl']['path'] === "/cart";
    });
    $z = 0; //counter
    foreach ($filtredCarts as $key => $value) {
        $parsedGoods[$z]['goodId'] = $value['parsedQuery']['goods_id'];
        $filtredByIp = array_filter($arr1, function ($a) {
            return $a['ip'] === $GLOBALS['value']['ip'];
        });
        $keys = array_keys($filtredByIp);
        $prevElem = $filtredByIp[$keys[array_search($key, $keys) - 1]];
        preg_match('/(?<=[a-z]\/)(.*?)(?=\/)/', $prevElem['parsedUrl']['path'], $matches2);
        $parsedGoods[$z]['goodName'] = $matches2[0];
        preg_match('/(?<=\/)(.*?)(?=\/)/', $prevElem['parsedUrl']['path'], $matches3);
        $parsedGoods[$z]['catName'] = $matches3[0];
        $parsedGoods[$z]['catId'] = getCatId($matches3[0]);
        $z++;
    }
    $parsedGoods = array_unique($parsedGoods, SORT_REGULAR);
    usort($parsedGoods, function ($a, $b) {
        return ($a['goodId'] - $b['goodId']);
    });
    foreach ($parsedGoods as $value) {
        $goods_row = $value['goodId'] . ';' . $value['goodName'] . ';' . $value['catId'] . PHP_EOL;
        fwrite($goods, $goods_row);
    }

    //подготавливаем данные для таблицы pages_goods
    $n = 0; //counter
    foreach ($arr1 as $key1 => $value) {
        preg_match('/(?<=[a-z]\/)(.*?)(?=\/)/', $value['parsedUrl']['path'], $matches4);
        if ($matches4[0]) {
            foreach ($parsedGoods as $key => $value) {
                if ($matches4[0] === $value['goodName']) {
                    $pagesGoodsList[$n]['goodId'] = $value['goodId'];
                    $pagesGoodsList[$n]['id'] = $key1;
                    $n++;
                }
            }
        }
    }
    foreach ($pagesGoodsList as $key => $value) {
        $pagesGoods_row =  $value['id'] . ";" . $value['goodId'] . PHP_EOL;
        fwrite($pagesGoods, $pagesGoods_row);
    }
    fclose($rawLog);
    fclose($visits);
    fclose($categories);
    fclose($pagesCat);
    fclose($pagesCarts);
    fclose($pagesPayments);
    fclose($goods);
    fclose($pagesGoods);
    echo 'Успешно. Время выполнения скрипта: ' . round(microtime(true) - $start, 3) . ' сек.';
}
