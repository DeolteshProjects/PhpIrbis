<?php

namespace Library\QueryConstructor;

class QueryConstructor
{
    //Основные поля
    public $author = null;  ///Автор
    public $title = null;   //Название

    //Публикация
    public $year = null;    //Год публикации
    public $city = null;    //Город публикации
    public $publisher = null; //Издательство

    //Дополнительные поля
    public $rubrika = null; //Рубрика
    public $isbn = null; //ISBN
    public $bbk = null; //BBK
    public $word_keys = null; //Ключевые слова
    //Признак фильтра
    public $filter = false;
    public $auto_gen_authors = false;   //Использование альтернативных вариантов поиска автора

    //Даты поступления
    public $giving_date_begin = null;   //Начальная дата диапозона даты поступления
    public $giving_date_end = null; //Крайняя дата поступления

    //Итоговый запрос
    private $query = "";    

    //Вид издания
    public $view_of_publication = "";

    //Тематика
    public $theme = "";

    /*
    *   Символьные операторы
    *   + - Логическое ИЛИ
    *   * - Логическое И
    *   $ - Любой набор символов
    *
    */

    //Конструктор запроса
    public function getConstructQuery()
    {
        $this->constructAuthorPart();   //Автор
        $this->constructTitlePart();    //Заглавие
        $this->constructYearPart();     //Год издания
        // $this->constructGivingDatePart();   //Даты поступления
        $this->constructRubrikaPart();  //Рубрика
        $this->constructPublisherPart();    //Издательство
        $this->constructCityPart();    //Город издания
        $this->constructISBNPart();    //ISBN
        $this->constructBBKPart();    //BBK
        $this->constructWordKeysPart();    //Ключевые слова
        $this->constructViewOfPublicationPart();    //Вид издания
        $this->constructThemePart();    //Тематика
        return $this->query;
    }

    private function constructThemePart()
    {
        $query = "";
        if (($this->theme !== null) && (!empty($this->theme)) && (strlen($this->theme) > 0)) {
            $theme = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->theme)))));
            $theme = strtolower($theme);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * (R=' . $theme . ')';
                } else {
                    $query .= ' + (R=' . $theme . ')';
                }
            } else {
                if ($this->filter) {
                    $query .= '(R=' . $theme . ')';
                } else {
                    $query .= '(R=' . $theme . ')';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructViewOfPublicationPart()
    {
        $query = "";
        if (($this->view_of_publication !== null) && (!empty($this->view_of_publication)) && (strlen($this->view_of_publication) > 0)) {
            $view_of_publication = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->view_of_publication)))));
            $view_of_publication = strtolower($view_of_publication);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("V=' . $view_of_publication . '")';
                } else {
                    $query .= ' + ("V=' . $view_of_publication . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("V=' . $view_of_publication . '")';
                } else {
                    $query .= '("V=' . $view_of_publication . '")';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructAuthorPart()
    {
        $query = "";
        if (($this->author !== null) && (!empty($this->author)) && (strlen($this->author) > 0)) {
            $author = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->author)))));
            $author = strtolower($author);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("A=' . $author . '$" + "A=$' . $author . '")';
                } else {
                    $query .= ' + ("A=' . $author . '$" + "A=$' . $author . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("A=' . $author . '$" + "A=$' . $author . '")';
                } else {
                    $query .= '("A=' . $author . '$" + "A=$' . $author . '")';
                }
            }
            if ($this->auto_gen_authors) {
                $author_var = [];
                $author = str_ireplace(",", "", $author);
                $author_var = explode(" ", $author);
                if (count($author_var) > 0) {
                    foreach ($author_var as $value) {
                        $value = str_ireplace([".", ",", ":", "?", "{", "}", "(", ")", "!", "<", ">", "+", "-", "_", "=", "$", "%", "^", "@", "'", ";", '"', "#", "&", "|", "/"], "", $value);
                        $value = trim($value);
                    }
                    //Иванов Иван Иванович
                    //Иванов, 
                    $author1 = $author_var[0] . ", ";
                    $query = $query . ' + ("A=' . $author1 . '$" + "A=$' . $author1 . '")';
                    //Иванов, И
                    if (count($author_var) > 0) {
                        $author2 = $author_var[0] . ", " . mb_substr($author_var[1], 0, 1);
                        $query = $query . ' + ("A=' . $author2 . '$" + "A=$' . $author2 . '")';
                        //Иванов, И.
                        $author3 = $author_var[0] . ", " . mb_substr($author_var[1], 0, 1) . ".";
                        $query = $query . ' + ("A=' . $author3 . '$" + "A=$' . $author3 . '")';
                        if (count($author_var) > 2) {
                            //     //Иванов, И. Иванованович
                            $author4 = $author_var[0] . ", " . mb_substr($author_var[1], 0, 1) . ". " . $author_var[2];
                            $query = $query . ' + ("A=' . $author4 . '$" + "A=$' . $author4 . '")';
                            //     //Иванов, И. И
                            $author5 = $author_var[0] . ", " . mb_substr($author_var[1], 0, 1) . ". " . mb_substr($author_var[2], 0, 1);
                            $query = $query . ' + ("A=' . $author5 . '$" + "A=$' . $author5 . '")';
                            //     //Иванов, И. И.
                            $author6 = $author_var[0] . ", " . mb_substr($author_var[1], 0, 1) . ". " . mb_substr($author_var[2], 0, 1) . ".";
                            $query = $query . ' + ("A=' . $author6 . '$" + "A=$' . $author6 . '")';
                            //     //Иванов, Иван И.
                            $author7 = $author_var[0] . ", " . $author_var[1] . " " . mb_substr($author_var[2], 0, 1) . ".";
                            $query = $query . ' + ("A=' . $author7 . '$" + "A=$' . $author7 . '")';
                            //     //Иванов, Иван И.
                            $author8 = $author_var[0] . ", " . $author_var[1] . " " . $author_var[2];
                            $query = $query . ' + ("A=' . $author8 . '$" + "A=$' . $author8 . '")';
                        }
                    }
                    $query = "(" . $query . ")";
                }
            }
        }
        $this->query .= $query;
    }

    private function constructTitlePart()
    {
        $query = "";
        if (($this->title !== null) && (!empty($this->title)) && (strlen($this->title) > 0)) {
            $title = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->title)))));
            $title = strtolower($title);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("T=' . $title . '$" + "T=$' . $title . '")';
                } else {
                    $query .= ' + ("T=' . $title . '$" + "T=$' . $title . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("T=' . $title . '$" + "T=$' . $title . '")';
                } else {
                    $query .= '("T=' . $title . '$" + "T=$' . $title . '")';
                }
            }
            // }
        }
        $this->query .= $query;
    }

    private function constructYearPart()
    {
        $query = "";
        if (($this->year !== null) && (!empty($this->year)) && (strlen($this->year) > 0)) {
            $year = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->year)))));
            $year = strtolower($year);
            $years = explode(",", $year);
            if (count($years) > 0) {
                foreach ($years as $year) {
                    if (strlen($year) > 0) {
                        if (strpos((string)$year, '-') !== false) {
                            $years_p = explode("-", $year);
                            if (count($years_p) > 0) {
                                if (count($years_p) > 1) {
                                    if (is_numeric(trim($years_p[0])) && is_numeric(trim($years_p[1]))) {
                                        if ((int)$years_p[0] > (int)$years_p[1]) {
                                            $s = (int)$years_p[1];
                                            $years_p[1] = (int)$years_p[0];
                                            $years_p[0] = (int)$s;
                                        }
                                        if (strlen($this->query) > 0) {
                                            if ($this->filter) {
                                                $query .= ' * ("G=' . trim($years_p[0]) . '$"[...]"G=' . trim($years_p[1]) . '$")';
                                            } else {
                                                $query .= ' + ("G=' . trim($years_p[0]) . '$"[...]"G=' . trim($years_p[1]) . '$")';
                                            }
                                        } else {
                                            if ($this->filter) {
                                                $query .= '("G=' . trim($years_p[0]) . '$"[...]"G=' . trim($years_p[1]) . '$")';
                                            } else {
                                                $query .= '("G=' . trim($years_p[0]) . '$"[...]"G=' . trim($years_p[1]) . '$")';
                                            }
                                        }
                                    }
                                } else {
                                    if (is_numeric(trim($years_p[0]))) {
                                        if (strlen($this->query) > 0) {
                                            if ($this->filter) {
                                                $query .= ' * ("G=' . trim($years_p[0]) . '")';
                                            } else {
                                                $query .= ' + ("G=' . trim($years_p[0]) . '")';
                                            }
                                        } else {
                                            if ($this->filter) {
                                                $query .= '("G=' . trim($years_p[0]) . '")';
                                            } else {
                                                $query .= '("G=' . trim($years_p[0]) . '")';
                                            }
                                        }
                                    }
                                }
                            } else {
                            }
                        } else {
                            if (is_numeric(trim($year))) {
                                if (strlen($this->query) > 0) {
                                    if ($this->filter) {
                                        $query .= ' * ("G=' . $year . '$")';
                                    } else {
                                        $query .= ' + ("G=' . $year . '$")';
                                    }
                                } else {
                                    if ($this->filter) {
                                        $query .= '("G=' . $year . '$")';
                                    } else {
                                        $query .= '("G=' . $year . '$")';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->query .= $query;
    }

    private function constructGivingDatePart()
    {
        $query = "";
        $giving_date_begin = implode("", array_reverse(explode("-", $this->giving_date_begin)));
        $giving_date_end = implode("", array_reverse(explode("-", $this->giving_date_end)));
        if (($giving_date_begin !== null) && (!empty($giving_date_begin)) && (strlen($giving_date_begin) > 0) && ((empty($giving_date_end)))) {
            if (is_numeric(trim($giving_date_begin))) {
                if (strlen($this->query) > 0) {
                    if ($this->filter) {
                        $query .= ' * ("DP=' . $giving_date_begin . '")';
                    } else {
                        $query .= ' + ("DP=' . $giving_date_begin . '")';
                    }
                } else {
                    if ($this->filter) {
                        $query .= '("DP=' . $giving_date_begin . '")';
                    } else {
                        $query .= '("DP=' . $giving_date_begin . '")';
                    }
                }
            }
        } else if (($giving_date_end !== null) && (!empty($giving_date_end)) && (strlen($giving_date_end) > 0) && ((empty($giving_date_begin)))) {
            if (is_numeric(trim($giving_date_end))) {
                if (strlen($this->query) > 0) {
                    if ($this->filter) {
                        $query .= ' * ("DP=' . $giving_date_end . '")';
                    } else {
                        $query .= ' + ("DP=' . $giving_date_end . '")';
                    }
                } else {
                    if ($this->filter) {
                        $query .= '("DP=' . $giving_date_end . '")';
                    } else {
                        $query .= '("DP=' . $giving_date_end . '")';
                    }
                }
            }
        } else if (($giving_date_end !== null) && (!empty($giving_date_end)) && (strlen($giving_date_end) > 0) && ($giving_date_begin !== null) && (!empty($giving_date_begin)) && (strlen($giving_date_begin) > 0)) {
            if (is_numeric(trim($giving_date_begin)) && is_numeric(trim($giving_date_end))) {
                if ((int)$giving_date_begin > (int)$giving_date_end) {
                    $s = (int)$giving_date_end[1];
                    $giving_date_end = (int)$giving_date_begin;
                    $giving_date_begin = (int)$s;
                }
                if (strlen($this->query) > 0) {
                    if ($this->filter) {
                        $query .= ' * ("DP=' . trim($giving_date_begin) . '$"[...]"DP=' . trim($giving_date_end) . '$")';
                    } else {
                        $query .= ' + ("DP=' . trim($giving_date_begin) . '$"[...]"DP=' . trim($giving_date_end) . '$")';
                    }
                } else {
                    if ($this->filter) {
                        $query .= '("DP=' . trim($giving_date_begin) . '$"[...]"DP=' . trim($giving_date_end) . '$")';
                    } else {
                        $query .= '("DP=' . trim($giving_date_begin) . '$"[...]"DP=' . trim($giving_date_end) . '$")';
                    }
                }
            }
        }
        $this->query .= $query;
    }

    private function constructRubrikaPart() //Рубрика
    {
        $query = "";
        if (($this->rubrika !== null) && (!empty($this->rubrika)) && (strlen($this->rubrika) > 0)) {
            $rubrika = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->rubrika)))));
            $rubrika = strtolower($rubrika);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("S=' . $rubrika . '$" + "S=$' . $rubrika . '")';
                } else {
                    $query .= ' + ("S=' . $rubrika . '$" + "S=$' . $rubrika . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("S=' . $rubrika . '$" + "S=$' . $rubrika . '")';
                } else {
                    $query .= '("S=' . $rubrika . '$" + "S=$' . $rubrika . '")';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructPublisherPart() //Издательство
    {
        $query = "";
        if (($this->publisher !== null) && (!empty($this->publisher)) && (strlen($this->publisher) > 0)) {
            $publisher = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->publisher)))));
            $publisher = strtolower($publisher);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("O=' . $publisher . '$" + "O=$' . $publisher . '")';
                } else {
                    $query .= ' + ("O=' . $publisher . '$" + "O=$' . $publisher . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("O=' . $publisher . '$" + "O=$' . $publisher . '")';
                } else {
                    $query .= '("O=' . $publisher . '$" + "O=$' . $publisher . '")';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructCityPart() //Город издания
    {
        $query = "";
        if (($this->city !== null) && (!empty($this->city)) && (strlen($this->city) > 0)) {
            $city = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->city)))));
            $city = strtolower($city);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("MI=' . $city . '$" + "MI=$' . $city . '")';
                } else {
                    $query .= ' + ("MI=' . $city . '$" + "MI=$' . $city . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("MI=' . $city . '$" + "MI=$' . $city . '")';
                } else {
                    $query .= '("MI=' . $city . '$" + "MI=$' . $city . '")';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructISBNPart()    //ISBN
    {
        $query = "";
        if (($this->isbn !== null) && (!empty($this->isbn)) && (strlen($this->isbn) > 0)) {
            $isbn = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->isbn)))));
            $isbn = strtolower($isbn);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("I=' . $isbn . '$" + "I=$' . $isbn . '")';
                } else {
                    $query .= ' + ("I=' . $isbn . '$" + "I=$' . $isbn . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("I=' . $isbn . '$" + "I=$' . $isbn . '")';
                } else {
                    $query .= '("I=' . $isbn . '$" + "I=$' . $isbn . '")';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructBBKPart() //BBK
    {
        $query = "";
        if (($this->bbk !== null) && (!empty($this->bbk)) && (strlen($this->bbk) > 0)) {
            $bbk = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->bbk)))));
            $bbk = strtolower($bbk);
            if (strlen($this->query) > 0) {
                if ($this->filter) {
                    $query .= ' * ("bbk=' . $bbk . '$" + "bbk=$' . $bbk . '")';
                } else {
                    $query .= ' + ("bbk=' . $bbk . '$" + "bbk=$' . $bbk . '")';
                }
            } else {
                if ($this->filter) {
                    $query .= '("bbk=' . $bbk . '$" + "bbk=$' . $bbk . '")';
                } else {
                    $query .= '("bbk=' . $bbk . '$" + "bbk=$' . $bbk . '")';
                }
            }
        }
        $this->query .= $query;
    }

    private function constructWordKeysPart()    //Ключевые слова
    {
        $query = "";
        if (($this->word_keys !== null) && (!empty($this->word_keys)) && (strlen($this->word_keys) > 0)) {
            $word_keys = strtolower(ucwords(htmlspecialchars(strtolower(trim($this->word_keys)))));
            $word_keys = strtolower($word_keys);
            $word_keys = explode(",", $this->word_keys);
            foreach ($word_keys as $work_key) {
                $work_key = trim($work_key);
                if (strlen($this->query) > 0 || strlen($query) > 0) {
                    if ($this->filter) {
                        $query .= ' * ("K=' . $work_key . '$" + "K=$' . $work_key . '")';
                    } else {
                        $query .= ' + ("K=' . $work_key . '$" + "K=$' . $work_key . '")';
                    }
                } else {
                    if ($this->filter) {
                        $query .= '("K=' . $work_key . '$" + "K=$' . $work_key . '")';
                    } else {
                        $query .= '("K=' . $work_key . '$" + "K=$' . $work_key . '")';
                    }
                }
            }
        }
        $this->query .= $query;
    }
}
