<?php

namespace Library\FGOSRules;

class FGOSRules
{
    private $fgosRulesDescription = (object)[   //Требования ФГОС
        "FGOS" => 'Наименование ФГОС',
        "HAVE_DAA_BOOKS_REQUIRED" => 'Флаг обязательного разбиения на основную и дополнительную литературу (dafault_and_advance)',
        "HAVE_DEF_AND_ADV" => 'Флаг разбиения на основную и дополнительную литературу (dafault_and_advance)',
        "HAVE_ELECT_ADV_REQUIRED" => 'Обязательное присутствие дополнительной электронной литературы',
        "HAVE_ELECT_BOOKS_REQUIRED" => 'Флаг обязательного наличия электронной литературы',
        "HAVE_ELECT_DEF_REQUIRED" => 'Обязательное присутствие основной электронной литературы',
        "HAVE_PRINT_ADV_REQUIRED" => 'Обязательное присутствие дополнительной печатной литературы',
        "HAVE_PRINT_AND_ELECT" => 'Присутствие электронной и печатной литературы',
        "HAVE_PRINT_BOOKS_REQUIRED" => 'Флаг обязательного наличия печатной литературы',
        "MIN_PERCENT_ELECT" => 'Минимальный процент электронной литературы',
        "MIN_PERCENT_ELECT_ADVANCE" => 'Минимальный процент электронной дополнительной литературы',
        "MIN_PERCENT_ELECT_DEFAULT" => 'Минимальный процент электронной основной литературы',
        "MIN_PERCENT_PRINT" => 'Минимальный процент печатной литературы',
        "MIN_PERCENT_PRINT_ADVANCE" => 'Минимальный процент печатной дополнительной литературы',
        "MIN_PERCENT_PRINT_DEFAULT" => 'Минимальный процент печатной основной литературы',
        "MUST_ELECT_BOOKS" => 'Может содержать электронную литературу',
        "MUST_PRINT_BOOKS" => 'Может содержать печатную литературу',
    ];

    private $fgos3PPRules = (object)[   //Требования ФГОС 3++
        "FGOS" => '3++',
        "HAVE_DAA_BOOKS_REQUIRED" => 0,
        "HAVE_DEF_AND_ADV" => 0,
        "HAVE_ELECT_ADV_REQUIRED" => 0,
        "HAVE_ELECT_BOOKS_REQUIRED" => 0,
        "HAVE_ELECT_DEF_REQUIRED" => 0,
        "HAVE_PRINT_ADV_REQUIRED" => 0,
        "HAVE_PRINT_AND_ELECT" => 0,
        "HAVE_PRINT_BOOKS_REQUIRED" => 0,
        "MIN_PERCENT_ELECT" => 0,
        "MIN_PERCENT_ELECT_ADVANCE" => 0.001,
        "MIN_PERCENT_ELECT_DEFAULT" => 0.001,
        "MIN_PERCENT_PRINT" => 0.25,
        "MIN_PERCENT_PRINT_ADVANCE" => 0,
        "MIN_PERCENT_PRINT_DEFAULT" => 0,
        "MUST_ELECT_BOOKS" => 1,
        "MUST_PRINT_BOOKS" => 1,
    ];

    private $fgos3PRules = (object)[   //Требования ФГОС 3+
        "FGOS" => '3++',
        "HAVE_DAA_BOOKS_REQUIRED" => 1,
        "HAVE_DEF_AND_ADV" => 1,
        "HAVE_ELECT_ADV_REQUIRED" => 0,
        "HAVE_ELECT_BOOKS_REQUIRED" => 0,
        "HAVE_ELECT_DEF_REQUIRED" => 0,
        "HAVE_PRINT_ADV_REQUIRED" => 0,
        "HAVE_PRINT_AND_ELECT" => 0,
        "HAVE_PRINT_BOOKS_REQUIRED" => 0,
        "MIN_PERCENT_ELECT" => 0,
        "MIN_PERCENT_ELECT_ADVANCE" => 0.001,
        "MIN_PERCENT_ELECT_DEFAULT" => 0.001,
        "MIN_PERCENT_PRINT" => 0,
        "MIN_PERCENT_PRINT_ADVANCE" => 0,5,
        "MIN_PERCENT_PRINT_DEFAULT" => 0,5,
        "MUST_ELECT_BOOKS" => 1,
        "MUST_PRINT_BOOKS" => 1,
    ];
}
