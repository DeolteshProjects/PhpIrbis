<?php

namespace Library\SintAnal;

//Сласс парсер
class SintAnalNew
{
    //ID
    public $Id = "";
    public $code_1 = "";
    public $code_2 = "";
    //Автор
    public $Author = "";
    //Заглавие
    public $Title = "";
    //Вид издания
    public $ViewOfPublication = "";
    //Тип издания
    public $TypeOfPublication = "";
    //Количество экземпляров
    public $NumberOfCopies = "";
    //Год издания
    public $YearOfPublication = "(=^.^=)";
    //Краткое описание
    public $SmallDescription = "";
    //Ссылка на книгу
    public $Link = "(=^.^=)";
    //Город издания
    public $CityOfPublication = "";
    //Шапка
    public $Head = "";
    //Прочее
    public $Other = "";
    //Краткое описание
    // public $Other = "";

    public $parsingString = ""; //Строка для парсинга

    //Финальный массив
    public $ResultParseArray = [];

    //Метод очистки
    public function getCleanString($line)
    {
        $line  =  str_replace("<b>",  "",  $line);
        $line  =  str_replace("</b>",  "",  $line);
        $line  =  str_replace("<br>",  "",  $line);
        $line  =  str_replace("</br>",  "",  $line);
        $line  =  str_replace("<tr>",  "",  $line);
        $line  =  str_replace("</tr>",  "",  $line);
        $line  =  str_replace("<dd>",  "",  $line);
        $line  =  str_replace("</dd>",  "",  $line);
        $line  =  str_replace("<table width=\"100%\">",  "",  $line);
        $line  =  str_replace("</table>",  "",  $line);
        $line  =  str_replace("<td>", "", $line);
        $line  =  str_replace("</td>", "", $line);
        $line  =  str_replace("<td width=\"50%\">", "", $line);
        $line  =  str_replace("", "", $line);
        $line  =  trim($line);
        $line  =  trim($line);
        $line  =  trim($line);
        return $line;
    }

    //Парсинг строки
    public function parseItem(string $item)
    {
        $this->parsingString = $item;
        $result = [];
        $result["small_description"] = $this->getSmallDescription($item);
        $this->SmallDescription = $result["small_description"];
        $result["id"] = (int)$this->getItemId($item);
        $this->id = $result["id"];
        $result["code_1"] = $this->getItemCode_1($item);
        $this->code_1 = $result["code_1"];
        $result["code_2"] = $this->getItemCode_2($item);
        $this->code_2 = $result["code_2"];
        $result["author"] = $this->getItemAuthor($item);
        $this->author = $result["author"];
        $result["title"] = $this->getItemTitle($item);
        $this->title = $result["title"];
        $result["description"] = $this->getItemDescription($item);
        $this->description = $result["description"];
        $result["isbn"] = $this->getItemISBN($item);
        $this->isbn = $result["isbn"];
        $result["view"] = (int)$this->getItemView($item);
        $this->view = $result["view"];
        $result["view_print"] = $this->getItemViewPrint($item);
        $this->view_print = $result["view_print"];
        $result["year"] = (int)$this->getItemYear($item);
        $this->year = $result["year"];
        $result["small_description"] = $this->clearSmallDescriptionToGost($this->getSmallDescription($item));
        $this->SmallDescription = $result["small_description"];
        if (strpos($item, "<b> ББК </b>")) {
            $result["bbk"] = $this->getItemBBK($item);
            $this->bbk = $result["bbk"];
        }
        if (strpos($item, "<b> Рубрики: </b>")) {
            $result["rubriks"] = $this->getItemRubriks($item);
            $this->rubriks = $result["rubriks"];
        }
        if (strpos($item, "<b> Аннотация: </b>")) {
            $result["annotation"] = $this->getItemAnnotation($item);
            $this->annotation = $result["annotation"];
        }
        if (strpos($item, "<b> Доп. точки доступа: </b>")) {
            $result["advance_links"] = $this->getItemAdvanceLinks($item);
            $this->advance_links = $result["advance_links"];
        }
        if (strpos($item, "<b> Кл.слова")) {
            $result["key_words"] = $this->getItemKeyWords($item);
            $this->key_words = $result["key_words"];
        }
        if (strpos($item, "<b>Имеются экземпляры в отделах: </b>")) {
            $result["count_in_lib"] = (int)$this->getItemCountInLib($item);
            $this->count_in_lib = $result["count_in_lib"];
        } else if (strpos($item, "<b>Экз-ры: всего: ")) {
            $result["count_in_lib"] = (int)$this->getItemCountInLibAdvance($item);
            $this->count_in_lib = $result["count_in_lib"];
        } else {
            $result["count_in_lib"] = 1;
        }
        if (strpos($item, 'HREF') || strpos($item, 'href')) {
            $result["electronik_link"] = $this->getItemElectronikLink($item);
            $this->electronik_link = $result["electronik_link"];
        }
        $result["full_data"] = $item;
        return $result;
    }

    //Приводим библиографическое описание к госту
    public function clearSmallDescriptionToGost(string $small_description)
    {
        //Удаляем двойные символы
        $hase_double = stripos($small_description, "  ");
        while ($hase_double) {
            $small_description = str_replace("  ", " ", $small_description);
            $hase_double = stripos($small_description, "  ");
        }
        $hase_double = stripos($small_description, "--");
        while ($hase_double) {
            $small_description = str_replace("--", " ", $small_description);
            $hase_double = stripos($small_description, "--");
        }
        $hase_double = stripos($small_description, "::");
        while ($hase_double) {
            $small_description = str_replace("::", " ", $small_description);
            $hase_double = stripos($small_description, "::");
        }

        //Удаляем "URL:"
        $small_description = str_replace(["URL:"], "", $small_description);
        //Удаляем из описания литературы вид литературы
        $small_description = str_replace(["Текст (визуальный) : непосредственный.", "Текст : электронный."], "", $small_description);


        //Удаляем из описания ISBN
        $isbn = [];
        preg_match("/(\s[-]\s[ISBN]{4}\s([0-9A-Z]{1,}+[-]){1,}+[0-9A-Z]{1,})/u", $small_description, $isbn);
        while (count($isbn) > 0) {
            $small_description = str_replace($isbn[0], "", $small_description);
            preg_match("/(\s[-]\s[ISBN]{4}\s([0-9A-Z]{1,}+[-]){1,}+[0-9A-Z]{1,})/u", $small_description, $isbn);
        }
        $isbn = [];
        preg_match("/(\s[-]\s[ISBN]{4}\s([0-9A-Z]{1,}+[–]){1,}+[0-9A-Z]{1,})/u", $small_description, $isbn);
        while (count($isbn) > 0) {
            $small_description = str_replace($isbn[0], "", $small_description);
            preg_match("/(\s[-]\s[ISBN]{4}\s([0-9A-Z]{1,}+[–]){1,}+[0-9A-Z]{1,})/u", $small_description, $isbn);
        }


        //Удаляем из описания стоимость литературы, если она имеется
        $prices = [];
        preg_match("/(\s[0-9]{1,}+[.]+[0-9]{1,}\s[р]{1}[.]{1}){1,}/u", $small_description, $prices);
        while (count($prices) > 0) {
            $small_description = str_replace($prices[0], "", $small_description);
            preg_match("/(\s[0-9]{1,}+[.]+[0-9]{1,}\s[р]{1}[.]{1}){1,}/u", $small_description, $prices);
        }
        $prices2 = [];
        preg_match("/(\s[0-9]+[0-9]{1,}\s[р]{1}[.]{1}){1,}/u", $small_description, $prices2);
        while (count($prices2) > 0) {
            $small_description = str_replace($prices2[0], "", $small_description);
            preg_match("/(\s[0-9]+[0-9]{1,}\s[р]{1}[.]{1}){1,}/u", $small_description, $prices2);
        }


        //Удаляем из описания различные ссылки, если они имеется
        $urls = [];
        preg_match("/([https]{4,5}+[:\/]{3}+([a-zA-Z0-9а-яА-Я.\/-]{1,})){1,}/u", $small_description, $urls);
        while (count($urls) > 0) {
            $small_description = str_replace($urls[0], "", $small_description);
            preg_match("/([https]{4,5}+[:\/]{3}+([a-zA-Z0-9а-яА-Я.\/-]{1,})){1,}/u", $small_description, $urls);
        }
        $urls2 = [];
        preg_match("/([:\/]{3}+([a-zA-Z0-9а-яА-Я.\/-]{1,})){1,}/u", $small_description, $urls2);
        while (count($urls2) > 0) {
            $small_description = str_replace($urls2[0], "", $small_description);
            preg_match("/([:\/]{3}+([a-zA-Z0-9а-яА-Я.\/-]{1,})){1,}/u", $small_description, $urls2);
        }


        //Удаляем из описания различные ссылки, если они имеется
        $ids = [];
        preg_match("/(\s[?]{1}+[id=]{1,}+[0-9]{0,}){1,}/u", $small_description, $ids);
        while (count($ids) > 0) {
            $small_description = str_replace($ids[0], "", $small_description);
            preg_match("/(\s[?]{1}+[id=]{1,}+[0-9]{0,}){1,}/u", $small_description, $ids);
        }


        //Снова удаляем двойные символы
        for ($i = 0; $i < 6; $i++) {
            $hase_double = stripos($small_description, "  ");
            while ($hase_double) {
                $small_description = str_replace("  ", " ", $small_description);
                $hase_double = stripos($small_description, "  ");
            }
            $hase_double = stripos($small_description, "--");
            while ($hase_double) {
                $small_description = str_replace("--", " ", $small_description);
                $hase_double = stripos($small_description, "--");
            }
            $hase_double = stripos($small_description, "::");
            while ($hase_double) {
                $small_description = str_replace("::", " ", $small_description);
                $hase_double = stripos($small_description, "::");
            }
            $hase_double = stripos($small_description, "- -");
            while ($hase_double) {
                $small_description = str_replace("- -", " -", $small_description);
                $hase_double = stripos($small_description, "- -");
            }
            $hase_double = stripos($small_description, ". ;");
            while ($hase_double) {
                $small_description = str_replace(". ;", ".;", $small_description);
                $hase_double = stripos($small_description, ". ;");
            }
        }
        //Чистим конец строки
        while (in_array(substr($small_description, -1), [" ", ".", ":", "-", ","])) {
            $small_description = trim(substr($small_description, 0, -1));
        }
        $small_description .= ".";
        return $small_description;
    }

    //Очистка строки от мусора
    private function clearLiterature($line)
    {
        $line  =  str_replace("<b>",  "",  $line);
        $line  =  str_replace("</b>",  "",  $line);
        $line  =  str_replace("<br>",  "",  $line);
        $line  =  str_replace("</br>",  "",  $line);
        $line  =  str_replace("<tr>",  "",  $line);
        $line  =  str_replace("</tr>",  "",  $line);
        $line  =  str_replace("<dd>",  "",  $line);
        $line  =  str_replace("</dd>",  "",  $line);
        $line  =  str_replace("<table width=\"100%\">",  "",  $line);
        $line  =  str_replace("</table>",  "",  $line);
        $line  =  str_replace("<td>", "", $line);
        $line  =  str_replace("</td>", "", $line);
        $line  =  str_replace("<td width=\"50%\">", "", $line);
        $line  =  str_replace("", "", $line);
        $line  =  trim($line);
        $line  =  trim($line);
        $line  =  trim($line);
        $line = str_replace("[", "", $line);
        $line = str_replace("]", "", $line);
        $line  =  trim($line);
        $line  =  trim($line);
        $line  =  trim($line);
        if (strlen($line) == 0 || $line == "") return null;
        return $line;
    }


    //Метод получения id записи в базе
    private function getItemId($line)
    {
        $line = explode("#", $line);
        $Id = $line[0];
        return $this->clearLiterature($Id);
    }

    //Метод получения кода 1 записи в базе
    private function getItemCode_1($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("code_1");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<br>";
            $lines = explode($str_for_search_1, $line_for_search);
            $code_1 = $lines[0];
            return $this->clearLiterature($code_1);
        }
    }

    //Метод получения кода 2 записи в базе
    private function getItemCode_2($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("code_2");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<br>";
            $lines = explode($str_for_search_1, $line_for_search);
            $code_2 = $lines[0];
            return $this->clearLiterature($code_2);
        }
    }

    //Метод получения автора записи в базе
    private function getItemAuthor($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("author");
        $author = null;
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "</b>";
            $lines = explode($str_for_search_1, $line_for_search);
            $author = $lines[0];
            return $this->clearLiterature($author);
        }
        //Примерные маски авторов
        //Вариант 1 
        //[Фимилия]+[,]+[[пробел]]+[Имя]+[[пробел]]+[Отчество]+[.]
        //Маска
        //      ^\b[а-яА-Я]{1,}\b.\s\b[а-яА-Я]{1,}\b\s\b[а-яА-Я]{1,}\b.
        $mask1 = "/^\b[а-яА-Я]{1,}\b.\s\b[а-яА-Я]{1,}\b\s\b[а-яА-Я]{1,}\b./u";

        //Вариант 1_2 
        //[Фимилия]+[,]+[[пробел]]+[{2,}Имя]+[[пробел]]+[Отчество]+[.]
        //Маска
        //      ^\b[а-яА-Я]{1,}\b.\s\b[а-яА-Я]{1,}\b\s\b[а-яА-Я]{1,}\b.
        $mask1_2 = "/^\b[а-яА-Я]{1,}\b.\s\b[а-яА-Я]{2,}\b\s\b[а-яА-Я]{1,}\b./u";

        //Вариант 2 
        //[Фимилия]+[,]+[[пробел]]+[{1}Имя]+[.]+[[пробел]]+[{1}Отчество]+[.]
        //Маска
        //      ^\b[а-яА-Я]{1,}\b.\s\b[а-яА-Я]{1}\b.\s\b[а-яА-Я]{1}\b.
        $mask2 = "/^\b[а-яА-Я]{1,}\b.\s\b[а-яА-Я]{1}\b.\s\b[а-яА-Я]{1}\b./u";

        if (preg_match($mask1, $author) || preg_match($mask1_2, $author) || preg_match($mask2, $author)) {
            $author = $this->clearLiterature($author);
        } else {
            $author = null;
        }
        return $author;
    }

    //Метод получения названия литературы
    private function getItemTitle($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("title");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = ". -";
            $lines = explode($str_for_search_1, $line_for_search);
            $title = $lines[0];
            return $this->clearLiterature($title);
        }
    }

    //Метод получения доп информации литературы
    private function getItemDescription($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("description");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = ". - <b>";
            $lines = explode($str_for_search_1, $line_for_search);
            $description = $lines[0];
            if (strpos($description, "<")) {
                $description = explode("<", $description)[0];
            }
            return $this->clearLiterature($description);
        }
    }

    //Метод получения isbn литературы
    private function getItemISBN($line)
    {
        $isbn = "";
        $str_for_search = $this->getStringForSearchRunParsing("isbn");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = " ";
            $lines = explode($str_for_search_1, $line_for_search);
            $isbn = $lines[0];
        }
        return $this->clearLiterature($isbn);
    }

    //Метод получения краткого описания
    private function getSmallDescription($line)
    {
        $small_description = "";
        $str_for_search = $this->getStringForSearchRunParsing("small_description");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<br><br>";
            $lines = explode($str_for_search_1, $line_for_search);
            $small_description = $lines[0];
            $small_description = (explode("<A HREF", $small_description))[0];
        }
        return $this->clearLiterature($small_description);
    }

    //Метод получения bbk литературы
    private function getItemBBK($line)
    {
        $bbk = "";
        $str_for_search = $this->getStringForSearchRunParsing("bbk");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<";
            $lines = explode($str_for_search_1, $line_for_search);
            $bbk = $lines[0];
        }
        return $this->clearLiterature($bbk);
    }

    //Метод получения рубрик литературы
    private function getItemRubriks($line)
    {
        $rubriks = "";
        $str_for_search = $this->getStringForSearchRunParsing("rubriks");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<";
            $lines = explode($str_for_search_1, $line_for_search);
            $rubriks = $lines[0];
        }
        return $this->clearLiterature($rubriks);
    }

    //Метод получения аннотации
    public function getItemAnnotation($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("annotation");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<";
            $lines = explode($str_for_search_1, $line_for_search);
            $annotation = $lines[0];
        }
        return $this->clearLiterature($annotation);
    }

    //Метод получения дополнительных точек доступа
    public function getItemAdvanceLinks($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("advance_links");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = "<";
            $lines = explode($str_for_search_1, $line_for_search);
            $advance_links = $lines[0];
        }
        return $this->clearLiterature($advance_links);
    }

    //Получение ключевых слов
    public function getItemKeyWords($line)
    {
        $str_for_search = $this->getStringForSearchRunParsing("key_words");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $lines = explode("</b>", $line_for_search);
            if (count($lines) > 1) {
                $line_for_search = $lines[1];
                $str_for_search_1 = "<";
                $lines = explode($str_for_search_1, $line_for_search);
                $key_words = $lines[0];
            }
        }
        return $this->clearLiterature($key_words);
    }

    //Получение электронной ссылки
    public function getItemElectronikLink($line)
    {
        $electronik_link = null;
        $str_for_search = $this->getStringForSearchRunParsing("electronik_link");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $str_for_search_1 = '"';
            $lines = explode($str_for_search_1, $line_for_search);
            $electronik_link = $lines[0];
        }
        $pos = strpos($electronik_link, "IRBIS");
        if ($pos !== false && $pos == 0) {
            $electronik_link = null;
        }
        return $this->clearLiterature($electronik_link);
    }


    //Получение количества экземпляров в библиотеке
    public function getItemCountInLib($line)
    {
        $count_in_lib = 1;
        $str_for_search = $this->getStringForSearchRunParsing("count_in_lib");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $lines = explode("сего ", $line_for_search);
            if (count($lines) > 1) {
                $line_for_search = $lines[1];
                $str_for_search_1 = " ";
                $lines = explode($str_for_search_1, $line_for_search);
                $count_in_lib = $lines[0];
            }
        }
        return $this->clearLiterature($count_in_lib);
    }

    //Получение количества экземпляров в библиотеке, дополнительный метод
    public function getItemCountInLibAdvance($line)
    {
        $count_in_lib = 1;
        $str_for_search = $this->getStringForSearchRunParsing("count_in_lib_advance");
        $lines = explode($str_for_search, $line);
        if (count($lines) > 1) {
            $line_for_search = $lines[1];
            $lines = explode("сего: ", $line_for_search);
            if (count($lines) > 1) {
                $line_for_search = $lines[1];
                $str_for_search_1 = " ";
                $lines = explode($str_for_search_1, $line_for_search);
                $count_in_lib = $lines[0];
            }
        }
        return $this->clearLiterature($count_in_lib);
    }

    //Получение вида литературы (печатная/Электронная)
    public function getItemView($line)
    {
        // [ 0 - "Текст (визуальный) : непосредственный", 1 -  "Текст : электронный"]
        if (strpos($line, "Текст (визуальный) : непосредственный")) return 0;
        else if (strpos($line, "Текст : электронный")) return 1;
        return 0;
    }

    //Получение вида литературы текстом (печатная/Электронная)
    public function getItemViewPrint($line)
    {
        // [ 0 - "Текст (визуальный) : непосредственный", 1 -  "Текст : электронный"]
        if (strpos($line, "Текст (визуальный) : непосредственный")) return "Текст (визуальный) : непосредственный";
        else if (strpos($line, "Текст : электронный")) return "Текст : электронный";
        return "Текст (визуальный) : непосредственный";
    }

    //Получение года издания
    function getItemYear($line)
    {
        $year = preg_replace("!|, ([0-9]{4}+[. -]{3})|. -!", "\\1 ", $line);
        //Получение последнего года издания
        $year = preg_replace("!|([0-9]{4})|.!", "\\1 ", $year);
        $year = $this->clearLiterature($year);
        if ($year === null) $year = 2020;
        return $year;
    }

    //Получение строки для парсинга
    private function getStringForSearchRunParsing(string $param)
    {
        if ($param == "id") {
            return $this->parsingString;
        } else if ($param == "code_1") {
            return $this->Id . '#</><table><tr align="right"><td width="95%"></td></tr></table><b>';
        } else if ($param == "code_2") {
            return $this->Id . '#</><table><tr align="right"><td width="95%"></td></tr></table><b>' . $this->code_1 . "<br>";
        } else if ($param == "author") {
            return '<br> </b> <b>';
        } else if ($param == "title") {
            return '<dd>';
        } else if ($param == "description") {
            return '<dd>' . $this->title . '. - ';
        } else if ($param == "isbn") {
            return '<b>ISBN </b>';
        } else if ($param == "bbk") {
            return '<b> ББК </b></td> </tr><tr><td width="50%"><td>';
        } else if ($param == "rubriks") {
            return '<b> Рубрики: </b> <br>';
        } else if ($param == "annotation") {
            return '<b> Аннотация: </b>';
        } else if ($param == "advance_links") {
            return '<b> Доп. точки доступа: </b> <br>';
        } else if ($param == "key_words") {
            return '<b> Кл.слова';
        } else if ($param == "count_in_lib") {
            return '<b>Имеются экземпляры в отделах: </b>';
        } else if ($param == "count_in_lib_advance") {
            return '<b>Экз-ры: в';
        } else if ($param == "electronik_link") {
            return '<A HREF="';
        } else if ($param == "small_description") {
            return '<br> </b> <b>';
        }
    }
}
