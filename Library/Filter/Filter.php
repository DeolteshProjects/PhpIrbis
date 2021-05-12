<?php

namespace Library\Filter;

class Filter
{

    //Фильтруем литературу по автору
    public function filterByAuthor($Author, $Literature)
    {
        $Result = [];
        $Author = trim($Author);
        $j = 0;
        for ($i = 0; $i < count($Literature); $i++) {
            if (isset($Literature[$i]['Author'])) {
                if (!empty($Author)) {
                    $pos = strpos(mb_strtoupper($Literature[$i]['Author']), mb_strtoupper($Author));
                    if ($pos === false) {
                    } else {
                        $Result[$j] = $Literature[$i];
                        $j++;
                    }
                } else {
                    $Result = $Literature;
                }
            }
        }
        return $Result;
    }

    //Фильтруем литературу по стоп словам
    public function filterByStopWord($StopWords, $Literature)
    {
        $StopWords = explode(",", $StopWords);
        for ($i = 0; $i <= count($StopWords) - 1; $i++) {
            $StopWords[$i] = mb_strtoupper(trim($StopWords[$i]));
        }
        $Result = [];
        $j = 0;
        for ($i = 0; $i < count($Literature); $i++) {
            if (isset($Literature[$i]['SmallDescription'])) {
                $col = 0;
                for ($d = 0; $d <= (count($StopWords)) - 1; $d++) {
                    $pos = (strpos(mb_strtoupper($Literature[$i]['SmallDescription']), (mb_strtoupper($StopWords[$d]))));
                    if ($pos !== false) {
                        $col++;
                    }
                }
                if ($col == 0) {
                    $Result[$j] = $Literature[$i];
                    $j++;
                }
            }
        }
        return $Result;
    }

    //Фильтр литературы по возрасту не более 20 лет
    public function filterByOldYearOfPublication($Literature)
    {
        $LastYear = date('Y') - 19;
        $Result = [];
        $j = 0;
        for ($i = 0; $i < count($Literature); $i++) {
            if (isset($Literature[$i]['YearOfPublication'])) {
                if ((int) ($Literature[$i]['YearOfPublication']) >= $LastYear) {
                    $Result[$j] = $Literature[$i];
                    $j++;
                }
            }
        }
        return $Result;
    }

    //Фильтр печатной литературы имеющейся в наличии менее 3 штук
    public function filterByStock($Literature)
    {
        $MinStock = 3;
        $Result = [];
        $j = 0;
        for ($i = 0; $i < count($Literature); $i++) {
            if (!isset($Literature[$i]['NumberOfCopies'])) $Literature[$i]['NumberOfCopies'] = 1;
            if (isset($Literature[$i]['Link'])) {
                if ($Literature[$i]["Link"] === "(=^.^=)") {
                    $pos = (strpos($Literature[$i]['SmallDescription'], "[Электронный ресурс]"));
                    if ($pos === false) {
                        if (isset($Literature[$i]['NumberOfCopies'])) {
                            if ($Literature[$i]['NumberOfCopies'] >= $MinStock) {
                                $Result[$j] = $Literature[$i];
                                $j++;
                            }
                        } else {
                            $Result[$j] = $Literature[$i];
                            $j++;
                        }
                    } else {
                        $Result[$j] = $Literature[$i];
                        $j++;
                    }
                } else {
                    $Result[$j] = $Literature[$i];
                    $j++;
                }
            }
        }
        return $Result;
    }

    //Фильтр печатной литературы имеющейся в наличии по количеству студентов
    public function filterByCountStudents($Literature, $CountStudents, $forma)
    {
        $Result = [];
        $j = 0;
        for ($i = 0; $i < count($Literature); $i++) {
            if ($Literature[$i]["Link"] === "(=^.^=)") {
                $pos = (strpos(mb_strtoupper($Literature[$i]['SmallDescription']), (mb_strtoupper("[Электронный ресурс]"))));
                if ($pos === false) {
                    if (isset($Literature[$i]['NumberOfCopies'])) {
                        if ($Literature[$i]['NumberOfCopies'] !== "Неограниченно") {
                            $needCount = round((($Literature[$i]['NumberOfCopies']) / $CountStudents), 2);
                            if ($forma == "Очная") {
                                if (($needCount >= 0.25)) {
                                    $Result[$j] = $Literature[$i];
                                    $Result[$j]["Provision"] = ($needCount >= 1) ? 1 : $needCount;
                                    $j++;
                                }
                            } else {
                                if (($needCount >= 0.5)) {
                                    $Result[$j] = $Literature[$i];
                                    $Result[$j]["Provision"] = ($needCount >= 1) ? 1 : $needCount;
                                    $j++;
                                }
                            }
                        } else {
                            $Result[$j] = $Literature[$i];
                            $Result[$j]["Provision"] = 1;
                            $j++;
                        }
                    } else {
                        // $Result[$j] = $Literature[$i];
                        // $Result[$j]["Provision"] = 1;
                        // $j++;
                    }
                } else {
                    $Result[$j] = $Literature[$i];
                    $Result[$j]["Provision"] = 1;
                    $j++;
                }
            } else {
                $Result[$j] = $Literature[$i];
                $Result[$j]["Provision"] = 1;
                $j++;
            }
        }
        return $Result;
    }

    //Фильтр дублирующейся литературы 
    public function filterByDouble($Literature)
    {
        $Result = [];
        $j = 0;
        $Amikus = $Literature;
        for ($i = 0; $i < count($Literature); $i++) {
            $set = 0;
            for ($k = 0; $k < count($Amikus); $k++) {
                if (($i != $k) && (md5(serialize($Literature[$i])) === md5(serialize($Literature[$k])))) {
                    $set = 1;
                }
            }
            if ($set == 0) {
                $Result[$j] = $Literature[$i];
                $j++;
            }
        }
        return $Result;
    }

    //Фильтр дублирующейся литературы 
    public function getEBookNoFond($Literature)
    {
        $j = 0;
        $Result = [];
        for ($i = 0; $i < count($Literature); $i++) {
            $pos = strpos($Literature[$i]["SmallDescription"], '[Электронный ресурс]');
            if ((($pos > 0) || ($Literature[$i]['Link'] != "(=^.^=)")) && (!empty($Literature[$i]['metka']))) {
            } else {
                $Result[$j] = $Literature[$i];
                $j++;
            }
        }
        return $Result;
    }
}
