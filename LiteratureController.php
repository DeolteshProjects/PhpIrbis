<?php
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RPD\Entities\LibraryReport\ReportDisciplines;
use Library\Irbis\Irbis;
use Library\QueryConstructor\QueryConstructor;
use Library\SintAnal\SintAnalNew;
use Modules\RPD\Entities\LibraryReport\Fgos as LibraryFgosRules;


class LiteratureController extends Controller
{
    private $block_library_list = [
        'колледжей',
        'техникумов',
        'училищ',
        'для гимназий, лицеев и школ с гуманитарным профилем',
        'для использования в учебном процессе образовательных учреждений,',
        'реализующих программы среднего профессионального образования',
        'для образовательных учреждений, реализующих программы среднего профессионального образования',
        'для подготовки специалистов среднего проф. образования',
        'для среднего профессионального образования',
        'для среднего профобразования',
        'для средних специальных учебных заведений',
        'для студентов образовательных учреждений среднего проф. образования',
        'для студентов образовательных учреждений среднего профессионального образования',
        'для студентов среднего проф. образования',
        'для студентов среднего профессионального образования',
        'для студентов средних педагогических учебных заведений',
        'для студентов средних профессиональных учебных заведений',
        'для студентов средних специальных учебных заведений',
        'для студентов средних учебных заведений',
        'для студентов учреждений среднего профессионального образования',
        'для студентов экономических колледжей и средних специальных учебных заведений',
        'для учащихся 8-9 кл. средних сельских общеобразовательных школ',
        'для учащихся 9-10 классов средней школы',
        'для учащихся средних школ, гимназий, лицеев',
        'для учащихся старших классов общеобразовательных учреждений',
        'для учреждений начального профессионального образования, подготовки и',
        'переподготовки рабочих на производстве и в центрах занятости, профессионального',
        'обучения учащихся средних общеобразовательных школ',
        'для учреждений среднего профессионального образования',
        'для специальности 100401 "Туризм" среднего профессионального образования (базовый уровень)',
        'для студентов и учащихся среднего и нач. проф. образования для использования в учебном процессе образовательных учреждений, реализующих программы начального профессионального образования',
        'для начального профессионального образования',
        'для образовательных учреждений, реализующих программы начального проф. образования',
        'для учреждений начального профессионального образования, подготовки и переподготовки рабочих на производстве и в центрах занятости, профессионального обучения учащихся средних общеобразовательных школ',
    ];

    //Поиск литературы
    public function searchLiterature(Request $request)
    {
        set_time_limit(800); //Просим клиент подождать нас максимум 800 секунд
        $author = $request->input("author");    //Автор
        $title = $request->input("title");  //Заглавие
        $year = $request->input("year");    //Год публикации
        $giving_date_begin = $request->input("giving_date_begin");  //Дата поступления min
        $giving_date_end = $request->input("giving_date_end");  //Дата поступления max
        $rubrika = $request->input("rubrika");  //Рубрика
        $city = $request->input("city");    //Город издания
        $publisher = $request->input("publisher");  //Издательство
        $isbn = $request->input("isbn");    //ISBN
        $bbk = $request->input("bbk");  //BBK
        $word_keys = $request->input("word_keys");  //Ключевые слова
        $stop_keys = ($request->input("stop_keys") !== null) ? strtolower(trim($request->input("stop_keys"))) : ""; //Стоп-слова
        $stop_keys = (strlen($stop_keys) > 0) ? explode(",", $stop_keys) : [];
        $auto_gen_authors = $request->input("auto_gen_authors");    //Флаг использования альтернативных записей авторов
        $filters = $request->input("filters");  //Флаг использования фильтров. (Логическое И на весь поисковый запрос)
        $view_danger = $request->input("view_danger");  //Флаг отображения недоступной литературы
        $view_of_publication = $request->input("view_of_publication");  //Вид публикации
        $theme = $request->input("theme");  //Тематика литературы
        $report_discipline_id = (int)($request->input("report_discipline_id"));
        $rules = (new LibraryFgosRules())->getFgosRulesByReportDisciplineId($report_discipline_id);
        $fgos = $rules[0]->fgos;
        $QueryConstructor = new QueryConstructor();
        $QueryConstructor->author = $author;
        $QueryConstructor->title = $title;
        $QueryConstructor->year = $year;
        $QueryConstructor->giving_date_begin = $giving_date_begin;
        $QueryConstructor->giving_date_end = $giving_date_end;
        $QueryConstructor->rubrika = $rubrika;
        $QueryConstructor->city = $city;
        $QueryConstructor->publisher = $publisher;
        $QueryConstructor->isbn = $isbn;
        $QueryConstructor->bbk = $bbk;
        $QueryConstructor->word_keys = $word_keys;
        $QueryConstructor->view_of_publication = $view_of_publication;
        $QueryConstructor->theme = $theme;
        $QueryConstructor->auto_gen_authors = $auto_gen_authors;
        $QueryConstructor->filter = $filters;
        $query = $QueryConstructor->getConstructQuery();
        $Irbis = new Irbis();

        $Irbis->login();
        $records = collect($Irbis->recordsSearch($query, null, 1, "@"));
        $result = collect();
        $SintAnal = new SintAnalNew();
        $repositories = [];
        foreach ($Irbis->DataBase as $key => $value) {
            array_push($repositories, $value);
        }
        // $repositories = ["FOND", "ZNANIUM", "URAIT", "LAN", "KOLLYUGU", "IPRBOOKS"];
        $counts = $this->getNeedCountBooks($report_discipline_id);
        $countstud = 1;
        $needbooks = 1;
        if (count($counts) > 0) {
            $countstud = $counts[0]->studcount;
            $needbooks = $counts[0]->need_books;
        }
        foreach ($repositories as $reposit) {
            if (isset($records[$reposit])) {
                $param = strtolower($reposit);
                $package = $records[$reposit];
                if (isset($package["records"])) {
                    if (count($package["records"]) > 0) {
                        if (((int)($package["records"][0])) > 0) {
                            $items = $package["records"];
                            for ($i = 0; $i < (int)($package["records"][0]); $i++) {
                                $item = $items[$i + 1];
                                if (!isset($result->$param)) $result->$param = [];
                                array_push($result->$param, $SintAnal->parseItem($item));
                            }
                        }
                    }
                }
            }
        }
        $data = [];
        $uniq_data = [];
        foreach ($repositories as $reposit) {
            if (isset($records[$reposit])) {
                $param = strtolower($reposit);
                $package = $records[$reposit];
                if (isset($package["records"])) {
                    if (count($package["records"]) > 0) {
                        if (((int)($package["records"][0])) > 0) {
                            $items = $package["records"];
                            for ($i = 0; $i < (int)($package["records"][0]); $i++) {
                                foreach ($result->$param as $item) {
                                    $item["base"] = $param;
                                    $item["search_query"] = $query;
                                    //Отсеиваем дубликаты
                                    if (!in_array(serialize($item), $uniq_data)) {
                                        array_push($uniq_data, serialize($item));
                                        $access = 1;
                                        $item_danger = [];
                                        //Отсеиваем литературу, в недостаточном количестве для обеспечения направления
                                        //Если литература печатная, проверяем количество экземпляров, и обеспеченность
                                        if ($fgos === '3++') {
                                            $literature = $this->checkLiteratureForFgos3PPFromIrbis($item, $rules, $countstud);
                                        } else if ($fgos === '3+' || $fgos === '3') {
                                            $literature = $this->checkLiteratureForFgos3PFromIrbis($item, $rules, $countstud);
                                        }
                                        if (count($stop_keys) > 0) $literature = $this->checkStopKeys($literature, $stop_keys);
                                        if (count($literature["danger"]) > 0 && $view_danger) {
                                        } else {
                                            array_push($data, $literature);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    //Получение формы обучения и количества студентов
    public function getNeedCountBooks(int $report_discipline_id)
    {
        return (new ReportDisciplines())->getNeedCountBooksByReportDisciplineId($report_discipline_id);
    }

    //Проверка литературы на содержание стоп слов
    public function checkStopKeys($literature, $stop_keys)
    {
        if (count($stop_keys) > 0) {
            foreach ($stop_keys as $stop_key) {
                $pos = stripos($literature["full_data"], $stop_key);
                if ($pos) {
                    $literature["access"] = 0;
                    array_push($literature["danger"], "В литературе найдено стоп-слово: >>" . $stop_key . "<<");
                    $new_srt = "<b style='color:red;'>" . $stop_key . "</b>";
                    $literature["full_data"] = str_ireplace($stop_key, $new_srt, $literature["full_data"]);
                    // $literature["small_description"] = str_ireplace($stop_key, $new_srt, $literature["small_description"]);
                }
            }
        }
        return $literature;
    }

    //Проверка литературы на соответствие требованию фгос 3++ к учебному плану при получении литературы
    public function checkLiteratureForFgos3PPFromIrbis($literature, $rules, int $countstud)
    {
        $literature["info"] = [];
        $literature["danger"] = [];
        $literature["access"] = 1;
        if ($literature["base"] === "fond") {
            $block_list = false;
            foreach ($this->block_library_list as $black_list) {
                $pos = strpos(strtolower($literature["small_description"]), strtolower($black_list));
                if ($pos) $block_list = true;
            }
            if ($block_list) {
                $literature["access"] = 0;
                array_push($literature["danger"], "Литература попадавет в разряд литературы для среднего, среднего проф., начального и др. образования!");
            }
        }
        if (count($literature["danger"]) === 0) {
            if (count($rules) > 0) {
                $rules = $rules[0];
                //В первую очередь проверяем, что литература не электронная, с ней проще
                if (((int)($literature["view"])) === 1) {
                    //Проверяем можем ли мы использовать электронную литературу в составляемой справке
                    if (((int)($rules->must_elect_books)) === 1) {
                        $literature["provision"] = 1;
                        $literature["count_book"] = 1;
                        $literature["access"] = 1;
                    } else {
                        //Значит справка не может содержать электронную литературу
                        $literature["access"] = 0;
                        $literature["provision"] = 0;
                        $literature["count_book"] = 0;
                        array_push($literature["danger"], "Электронный экземпляр литературы");
                    }
                } else {
                    //Значит литература печатная
                    //С ней сложнее
                    //Считаем количество экземпляров
                    //если экземпляров литературы больше или равно количеству студентов, берем литературу равную количеству студентов
                    if ((int)$literature["count_in_lib"] >= (int)$countstud) {
                        $literature["provision"] = 1;
                        $literature["count_book"] = (int)$countstud;
                        $literature["access"] = 1;
                    }
                    //если экземпляров литературы меньше чем количество студентов, берем литературу равную количеству студентов
                    if ((int)$literature["count_in_lib"] < (int)$countstud) {
                        $minPercent = (float)$rules->min_percent_print;
                        $needbooks = ceil($countstud * $minPercent);
                        //Если количество экземпляров литературы недостаточно, для того, чтобы обеспечить направление, блокиреуем такую литературу
                        if ((int)$literature["count_in_lib"] < (int)$needbooks) {
                            $literature["provision"] = 0;
                            $literature["count_book"] = 0;
                            $literature["access"] = 0;
                            array_push($literature["danger"], "Количество экземпляров недостаточно для обеспеченности студентов учебной литературой. Необходимое количество - не менее " . (int)$needbooks . " экз. Из расчета не менее 25% на число студентов в " . (int)$countstud . " чел. по плану набора.");
                        }
                        //Если количество экземпляров литературы достаточно, для того, чтобы обеспечить направление
                        if ((int)$literature["count_in_lib"] >= (int)$countstud) {
                            //Вычисляем необходимое количество экземпляров и обеспеченность
                            $literature["count_book"] = $countstud;
                            $literature["provision"] = 100;
                            $literature["access"] = 1;
                        } else if ((int)$literature["count_in_lib"] >= (int)$needbooks) {
                            //Вычисляем необходимое количество экземпляров и обеспеченность
                            $literature["count_book"] = $literature["count_in_lib"];
                            $literature["provision"] = ceil(($literature["count_in_lib"] / $countstud) * 100) / 100;
                            $literature["access"] = 1;
                        }
                    }
                }
            } else {
                $literature["access"] = 0;
                array_push($literature["danger"], "Не удалось найти требования ФГОС к составляемой справке!");
            }
        }
        $literature = $this->checkAgeBooks($literature);
        if (count($literature["danger"]) > 0) $literature["info"] = [];
        if (count($literature["danger"]) > 0) $literature["access"] = 0;
        $literature = $this->checkMinStockBooks($literature);
        return $literature;
    }

    //Проверка литературы на соответствие требованию фгос 3+ к учебному плану при получении литературы
    public function checkLiteratureForFgos3PFromIrbis($literature, $rules, int $countstud)
    {
        $literature["info"] = [];
        $literature["danger"] = [];
        $literature["access"] = 1;
        if ($literature["base"] === "fond") {
            $block_list = false;
            foreach ($this->block_library_list as $black_list) {
                $pos = strpos(strtolower($literature["small_description"]), strtolower($black_list));
                if ($pos) $block_list = true;
            }
            if ($block_list) {
                $literature["access"] = 0;
                array_push($literature["danger"], "Литература попадавет в разряд литературы для среднего, среднего проф., начального и др. образования!");
            }
        }
        if (count($literature["danger"]) === 0) {
            if (count($rules) > 0) {
                $rules = $rules[0];
                //В первую очередь проверяем, что литература не электронная, с ней проще
                if (((int)($literature["view"])) === 1) {
                    //Проверяем можем ли мы использовать электронную литературу в составляемой справке
                    if (((int)($rules->must_elect_books)) === 1) {
                        $literature["access"] = 1;
                        $literature["provision"] = 1;
                        $literature["count_book"] = 1;
                        $literature["elect_default_access"] = 1;
                        $literature["elect_advance_access"] = 1;
                    } else {
                        //Значит справка не может содержать электронную литературу
                        $literature["access"] = 0;
                        $literature["provision"] = 0;
                        $literature["count_book"] = 0;
                        $literature["elect_default_access"] = 0;
                        $literature["elect_advance_access"] = 0;
                        array_push($literature["danger"], "Электронный экземпляр литературы");
                    }
                } else {
                    //Значит литература печатная
                    //С ней сложнее
                    //Считаем количество экземпляров
                    //если экземпляров литературы больше или равно количеству студентов, берем литературу равную количеству студентов
                    if ((int)$literature["count_in_lib"] >= (int)$countstud) {
                        $literature["provision"] = 1;
                        $literature["count_book"] = (int)$countstud;
                        $literature["access"] = 1;
                        //В данном случае, литература может быть как основной, так и дополнительной
                        $literature["print_default_access"] = 1;
                        $literature["print_advance_access"] = 1;
                    }
                    //если экземпляров литературы меньше чем количество студентов, берем литературу равную количеству студентов
                    if ((int)$literature["count_in_lib"] < (int)$countstud) {
                        $minPercentDefault = (float)$rules->min_percent_print_default;
                        $minPercentAdvance = (float)$rules->min_percent_print_advance;
                        $needbooksDefault = ceil($countstud * $minPercentDefault);
                        $needbooksAdvance = ceil($countstud * $minPercentAdvance);
                        //Проверяем можем ли мы добавить литературу как основную
                        //Если количество экземпляров литературы недостаточно, для того, чтобы обеспечить направление, блокиреуем такую литературу
                        if ((int)$literature["count_in_lib"] < (int)$needbooksDefault) {
                            $literature["provision"] = 0;
                            $literature["count_book"] = 0;
                            $literature["print_default_access"] = 0;
                            // array_push($literature["danger"], "Количество экземпляров недостаточно для обеспеченности студентов учебной литературой. Необходимое количество - не менее " . (int)$needbooksDefault . " экз. Из расчета не менее 25% на число студентов в " . (int)$countstud . " чел. по плану набора.");
                        }
                        //Если количество экземпляров литературы достаточно, для того, чтобы обеспечить направление
                        if ((int)$literature["count_in_lib"] >= (int)$countstud) {
                            //Вычисляем необходимое количество экземпляров и обеспеченность
                            $literature["count_book"] = $countstud;
                            $literature["provision"] = 100;
                            $literature["print_default_access"] = 1;
                        } else if ((int)$literature["count_in_lib"] >= (int)$needbooksDefault) {
                            //Вычисляем необходимое количество экземпляров и обеспеченность
                            $literature["count_book"] = $literature["count_in_lib"];
                            $literature["provision"] = ceil(($literature["count_in_lib"] / $countstud) * 100) / 100;
                            $literature["print_default_access"] = 1;
                        }

                        //Проверяем можем ли мы добавить литературу как дополнительную
                        //Если количество экземпляров литературы недостаточно, для того, чтобы обеспечить направление, блокиреуем такую литературу
                        if ((int)$literature["count_in_lib"] < (int)$needbooksAdvance) {
                            $literature["provision"] = 0;
                            $literature["count_book"] = 0;
                            $literature["print_advance_access"] = 0;
                            // array_push($literature["danger"], "Количество экземпляров недостаточно для обеспеченности студентов учебной литературой. Необходимое количество - не менее " . (int)$needbooksAdvance . " экз. Из расчета не менее 25% на число студентов в " . (int)$countstud . " чел. по плану набора.");
                        }
                        //Если количество экземпляров литературы достаточно, для того, чтобы обеспечить направление
                        if ((int)$literature["count_in_lib"] >= (int)$countstud) {
                            //Вычисляем необходимое количество экземпляров и обеспеченность
                            $literature["count_book"] = $countstud;
                            $literature["provision"] = 100;
                            $literature["print_advance_access"] = 1;
                        } else if ((int)$literature["count_in_lib"] >= (int)$needbooksAdvance) {
                            //Вычисляем необходимое количество экземпляров и обеспеченность
                            $literature["count_book"] = $literature["count_in_lib"];
                            $literature["provision"] = ceil(($literature["count_in_lib"] / $countstud) * 100) / 100;
                            $literature["print_advance_access"] = 1;
                        }

                        //Проверяем как итог, можем ли мы вообще использовать данную литература
                        if ($literature["print_default_access"] === 0 && $literature["print_advance_access"] === 0) {
                            array_push($literature["danger"], "Количество имеющихся экземпляров недостаточно для внесения ее в справку");
                            $literature["access"] = 0;
                        } else if ($literature["print_default_access"] === 1 && $literature["print_advance_access"] === 1) {
                            $literature["access"] = 1;
                        } else if ($literature["print_default_access"] === 1 && $literature["print_advance_access"] === 0) {
                            array_push($literature["info"], "По количеству имеющихся в фонде Научной библиотеки экземпляров данное издание может использоваться только в качестве основной литературы");
                            $literature["access"] = 1;
                        } else if ($literature["print_default_access"] === 0 && $literature["print_advance_access"] === 1) {
                            array_push($literature["info"], "По количеству имеющихся в фонде Научной библиотеки экземпляров данное издание может использоваться только в качестве дополнительной литературы");
                            $literature["access"] = 1;
                        } else {
                            array_push($literature["danger"], "Неизвестная ошибка! Обратитесь к администратору системы.");
                            $literature["access"] = 0;
                        }
                    }
                }
            } else {
                $literature["access"] = 0;
                array_push($literature["danger"], "Не удалось найти требования ФГОС к составляемой справке!");
            }
        }
        $literature = $this->checkAgeBooks($literature);
        if (count($literature["danger"]) > 0) $literature["info"] = [];
        if (count($literature["danger"]) > 0) $literature["access"] = 0;
        $literature = $this->checkMinStockBooks($literature);
        return $literature;
    }

    //Отсеивание старой литературы
    public function checkAgeBooks($literature)
    {
        $maxAge = 20;   //Максимальный возраст книги
        //Отсеиваем литературу которая имеет возраст более 20 лет
        if ((date("Y") - $maxAge) > $literature["year"]) {
            $literature["access"] = 0;
            array_push($literature["danger"], "Литература старше 20 лет.");
        } else if ((date("Y") - $maxAge) == $literature["year"]) {
            array_push($literature["info"], "Литература будет недоступна в следующем году, в связи с ее возрастом в $maxAge лет.");
        }
        return $literature;
    }

    //Отсеиваивание недостаточной литературы на остатке в библиотеке
    public function checkMinStockBooks($literature)
    {
        //Отсеиваем литературу, количество экземпляров которой не более 3 штук
        if ($literature["count_in_lib"] < 4 && (int)$literature["view"] === 0) {
            $literature["access"] = 0;
            $literature["provision"] = 0;
            $literature["count_book"] = 0;
            array_push($literature["danger"], "На хранении в библиотеке присутствует менее 4 экземпляров.");
        }
        return $literature;
    }
}
