<?
require $_SERVER['DOCUMENT_ROOT'] . '/sql.php';

function prepareGenerateCall()
{
    $j = 0;
    foreach (selectUsersByCountry() as $key => $value) {
        $visitors[$j]['ip'] = $value[0];
        $visitors[$j]['quantity'] = $value[1];
        $visitors[$j]['country'] = getCountryByIp($value[0]);
        $j++;
    }
    $country0 = [];
    array_walk($visitors, function ($value) use (&$country0) {
        $country0 = array_merge($country0, array_fill(0, $value['quantity'], $value['country']));
    });
    $country_count_values = array_count_values($country0);
    $country = array_flip($country_count_values)[max($country_count_values)];
    $quantity = $country_count_values[$country];
    $time = date("Y-m-d H:i:s");
    return [
        'country' => $country,
        'quantity' => $quantity,
        'time' => $time,
    ];
};

function prepareGenerateCallTwo()
{
    if ($_GET['catId']) {
        if (getTheMostPopularTimeOfDay($_GET['catId']) != 'error') {
            $result = getTheMostPopularTimeOfDay($_GET['catId']);
            $time = date("Y-m-d H:i:s");
            $category = $result[0];
            $timeOfDay = $result[2];
            $quantity = $result[1];
            return [
                'category' => $category,
                'timeOfDay' => $timeOfDay,
                'quantity' => $quantity,
                'time' => $time,
            ];
        } else return 'err';
    } else return '';
};

function prepareGenerateCallThree()
{
    if ($_GET['date'] && $_GET['h1'] && $_GET['h2'] && intval(($_GET['h1']) < intval($_GET['h2']))) {
        if ($result = getCountQueriesPerHour($_GET['date'], $_GET['h1'], $_GET['h2'])[0]) {
            $time = date("Y-m-d H:i:s");
            $date = $_GET['date'];
            $h1 = $_GET['h1'];
            $h2 = $_GET['h2'];
            $quantity = $result;
            return [
                'date' => $date,
                'h1' => $h1,
                'h2' => $h2,
                'quantity' => $quantity,
                'time' => $time,
            ];
        } else return 'err';
    } else return false;
}
?>

<!DOCTYPE html>
<html lang='ru'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Генерация отчетов</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.0/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <link rel="stylesheet" href="/css/main.css">
    <script src='/js/generate.js' ?></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/docxtemplater/3.14.0/docxtemplater.js"></script>
    <script src="https://unpkg.com/pizzip@3.0.6/dist/pizzip.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.js"></script>
    <script src="https://unpkg.com/pizzip@3.0.6/dist/pizzip-utils.js"></script>
    <!--
    Mandatory in IE 6, 7, 8 and 9.
    -->
    <!--[if IE]>
        <script type="text/javascript" src="https://unpkg.com/pizzip@3.0.6/dist/pizzip-utils-ie.js"></script>
    <![endif]-->
    <script>
        function prev(elem) {
            if (elem.parentNode.parentNode.className == 'dropdown is-active') {
                elem.parentNode.parentNode.classList.remove('is-active');
            } else {
                elem.parentNode.parentNode.classList.add('is-active');
            }

        }
    </script>
</head>

<body>
    <section class="hero is-info is-medium is-bold">
        <div class="header">
            <div class="container has-text-centered">
                <a href='/'>
                    <h1 class="title">
                        Генерация отчетов
                    </h1>
                </a>
                <h2 class="subtitle">
                    Сервис генерации отчетов на основании обработанных лог-файлов сервера.
                </h2>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container">

            <div class="dropdown <?= $_GET['report1'] ? 'is-active' : ''; ?>" id="report1">
                <div class="dropdown-trigger">
                    <button class="button hider" aria-haspopup="true" aria-controls="dropdown-menu2" onclick="prev(this)">
                        <span><b>Отчет 1: </b>Страна, пользователи которой наиболее часто посещают сайт</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
                <div class="dropdown-menu" id="dropdown-menu1" role="menu">
                    <div class="dropdown-content">
                        <div class="control">
                            <a href='/?report1=true'>
                                <button class="button is-link">Сформировать</button>
                            </a>
                        </div>

                        <? if ($_GET['report1']) { ?>

                            <hr class="dropdown-divider">
                            <div class="dropdown-item">
                                <p>Ваш отчет <strong> готов!</strong></p>
                            </div>
                            <div class="dropdown-item">
                                <button class="button is-primary" onclick="generate1('<?= prepareGenerateCall()['time'] ?>', '<?= prepareGenerateCall()['country'] ?>', '<?= prepareGenerateCall()['quantity'] ?>')">Скачать</button>
                            </div>
                        <? } ?>

                    </div>
                    <div class='separator'></div>
                </div>
            </div>
            <br>
            <br>
            <div class="dropdown <?= $_GET['catId'] || $_GET['catId'] == '0' ? 'is-active' : ''; ?>" id="report2">
                <div class="dropdown-trigger">
                    <button class="button hider" aria-haspopup="true" aria-controls="dropdown-menu2" onclick="prev(this)">
                        <span><b>Отчет 2: </b>Наиболее популярное время суток для просмотра категории</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>

                <div class="dropdown-menu" id="dropdown-menu2" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            <div class="field">
                                <label class="label">Выберите категорию</label>
                                <form method="GET">
                                    <div class="control">
                                        <div class="select">
                                            <select name='catId'>
                                                <option value='0'>Не выбрана</option>
                                                <option value='1'>fresh_fish</option>
                                                <option value='2'>canned_food</option>
                                                <option value='3'>semi_manufactures</option>
                                                <option value='4'>caviar</option>
                                                <option value='5'>frozen_fish</option>
                                            </select>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="control">
                            <button class="button is-link">Отправить</button>
                        </div>
                        </form>
                        <? if ($_GET['catId'] && $_GET['catId'] != '' && prepareGenerateCallTwo() != 'err') { ?>
                            <hr class="dropdown-divider">
                            <div class="dropdown-item">
                                <p>Ваш отчет <strong>готов!</strong></p>
                            </div>
                            <div class="dropdown-item">
                                <button class="button is-primary" onclick="generate2('<?= prepareGenerateCallTwo()['time'] ?>', '<?= prepareGenerateCallTwo()['category'] ?>', '<?= prepareGenerateCallTwo()['timeOfDay'] ?>', '<?= prepareGenerateCallTwo()['quantity'] ?>')">Скачать</button>
                            </div>
                        <? } elseif ($_GET['catId'] == '0') { ?>
                            <hr class="dropdown-divider">
                            <label class="label"><span class="tag is-danger is-large is-light">Не выбрана категория</span></label>
                        <? } elseif ($_GET['catId']  && prepareGenerateCallTwo() == 'err') { ?>

                            <hr class="dropdown-divider">
                            <label class="label"><span class="tag is-danger is-large is-light">Указана неверная категория.</span></label>
                        <? } ?>
                    </div>
                </div>

            </div>
            <br>
            <br>
            <div class="dropdown <?= ($_GET['date'] && $_GET['h1'] && $_GET['h2']) ? 'is-active' : ''; ?>" id="report3">
                <div class="dropdown-trigger">
                    <button class="button hider" aria-haspopup="true" aria-controls="dropdown-menu2" onclick="prev(this)">
                        <span><b>Отчет 3: </b>Нагрузка на сайт за астрономический час</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>

                <div class="dropdown-menu" id="dropdown-menu3" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            <div class="field">
                                <div class="field">
                                    <label class="label">Выберите параметры для формирования отчета</label>
                                    <p>Обратите внимание, что текущий лог-файл содержит данные о посещениях с <code>2018-08-01 00:01:35</code> по <code>2018-08-14 09:53:56</code></p>
                                </div>
                                <form method="GET">
                                    <div class="field">
                                        <div class="control">
                                            <input type="date" class="input" min="2018-08-01" max="2018-08-14" placeholder="DD/MM/YYYY" style='width:200px;' name="date" required>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <p>Укажите временной диапазон (часы)</p>
                                    </div>
                                    <div class="field">
                                        <div class="control">
                                            <input class="input" type="number" min="0" max="23" style='width:100px;' placeholder="8" name="h1" required />
                                            <span style="line-height: 35px;"><b> — </b></span>
                                            <input class="input" type="number" min="0" max="23" style='width:100px;' placeholder="9" name="h2" required />
                                        </div>
                                    </div>
                            </div>

                            <div class="control">
                                <button class="button is-link">Отправить</button>
                            </div>
                            </form>
                        </div>
                        <? if (prepareGenerateCallThree() && prepareGenerateCallThree() != 'err' && !($_GET['date'] < '2018-08-01' || $_GET['date'] > '2018-08-14')) { ?>
                            <hr class="dropdown-divider">
                            <div class="dropdown-item">
                                <p>Ваш отчет <strong>готов!</strong></p>
                            </div>
                            <div class="dropdown-item">
                                <button class="button is-primary" onclick="generate3('<?= prepareGenerateCallThree()['time'] ?>', '<?= prepareGenerateCallThree()['date'] ?>', '<?= prepareGenerateCallThree()['h1'] ?>', '<?= prepareGenerateCallThree()['h2'] ?>', '<?= prepareGenerateCallThree()['quantity'] ?>')">Скачать</button>
                            </div>
                        <? } elseif (!prepareGenerateCallThree() && ($_GET['date'] && $_GET['h1'] && $_GET['h2'])) { ?>
                            <hr class="dropdown-divider">
                            <label class="label"><span class="tag is-danger is-large is-light">Второй вводимый час должен быть больше первого</span></label>
                        <? } elseif (($_GET['date'] && $_GET['h1'] && $_GET['h2']) && ($_GET['date'] < '2018-08-01' || $_GET['date'] > '2018-08-14')) { ?>

                            <hr class="dropdown-divider">
                            <label class="label"><span class="tag is-danger is-large is-light">Произошла ошибка. Проверьте правильность введенных дат.</span></label>
                        <? } ?>
                    </div>
                </div>

            </div>
        </div>
    </section>
</body>

</html>