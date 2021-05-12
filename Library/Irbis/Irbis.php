<?php

namespace Library\Irbis;

class Irbis
{
    //Локальные переменные для переноса класса
    private $ip = 'library1', $port = '6666', $sock;
    private $login = '1', $pass = '1';
    private $id = '554289', $seq = 0;

    /*АРМ ы
     *  АДМИНИСТРАТОР – ‘A‘
     *  КАТАЛОГИЗАТОР – ‘C’
     *  КОМПЛЕКТАТОР – ‘M’
     *  ЧИТАТЕЛЬ – ‘R’
     *  КНИГОВЫДАЧА – ‘B’
     */

    // Распространённые форматы

    const ALL_FORMAT       = "&uf('+0')";  // Полные данные по полям
    const UNIFOR_FORMAT    = "&unifor('+0')";  // Полные данные по полям
    const BRIEF_FORMAT     = '@brief';     // Краткое библиографическое описание
    const IBIS_FORMAT      = '@ibiskw_h';  // Формат IBIS (старый)
    const INFO_FORMAT      = '@info_w';    // Информационный формат
    const OPTIMIZED_FORMAT = '@';          // Оптимизированный формат

    // Распространённые поиски

    const KEYWORD_PREFIX    = 'K=';  // Ключевые слова
    const AUTHOR_PREFIX     = 'A=';  // Индивидуальный автор, редактор, составитель
    const COLLECTIVE_PREFIX = 'M=';  // Коллектив или мероприятие
    const TITLE_PREFIX      = 'T=';  // Заглавие
    const INVENTORY_PREFIX  = 'IN='; // Инвентарный номер, штрих-код или радиометка
    const INDEX_PREFIX      = 'I=';  // Шифр документа в базе

    public $arm = 'C'; // Каталогизатор
    public $DataBase = [
        '0' => 'FOND',
        '1' => 'ZNANIUM',
        '2' => 'URAIT',
        '3' => 'LAN',
        '4' => 'KOLLYUGU', //Полнотестовая коллекция учебно-методических изданий ЮГУ
        '5' => 'IPRBOOKS'
    ]; //
    public $Server_Timeout = 30;
    public $server_ver = '';
    public $Error_Code = 0;

    function __construct()
    {
    }

    //Разлогирование при уничтожении класса
    function __destruct()
    {
        $this->logout();
    }

    //Методы замены входных параметров
    function set_server($ip, $port = '6666')
    {
        $this->ip = $ip;
        $this->port = (int) $port;
    }

    function set_user($login, $pass)
    {
        $this->login = $login;
        $this->pass = $pass;
    }

    function set_arm($arm)
    {
        $this->arm = $arm;
    }

    function set_db($DataBase)
    {
        $this->DataBase = $DataBase;
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    //Коды возврата сервера
    function error($code = '')
    {
        if ($code == '') $code = $this->Error_Code;

        switch ($code) {
            case '0':
                return "Ошибки нет (Нормальное завершение)";
            case '1':
                return "Сервер не ответил";
            case '-100':
                return '-1 - заданный MFN вне пределов БД';
            case '-140':
                return 'MFN за пределами базы';
            case '-202':
                return 'Термин не существует';
            case '-203':
                return 'TERM_LAST_IN_LIST';
            case '-204':
                return 'TERM_FIRST_IN_LIST';
            case '-300':
                return 'Монопольная блокировка БД';
            case '-400':
                return 'Ошибка при открытии файла mst или xrf';
            case '-401':
                return 'Ошибка при открытии trm файлов';
            case '-402':
                return 'Ошибка при записи';
            case '-403':
                return 'Ошибка при актуализации';
            case '-600':
                return '1-запись логически удалена';
            case '-601':
                return 'Запись удалена';
            case '-602':
                return 'Запись заблокированна на ввод';
            case '-603':
                return 'Запись логически удалена';
            case '-607':
                return 'Ошибка autoin.gbl';
            case '-608':
                return 'Не совпадает номер версии у сохраняемой записи';
            case '-1111':
                return 'Ошибка выполнения сервера';
            case '-2222':
                return 'WRONG PROTOCOL';
            case '-3333':
                return 'Пользователь не существует';
            case '-3334':
                return 'Незарегестрированный пользователь не сделал ibis-reg';
            case '-3335':
                return 'Неверный уникальный идентификатор';
            case '-3336':
                return 'Нет доступа к командам АРМа';
            case '-3337':
                return 'Пользователь уже авторизован в системе';
            case '-4444':
                return 'Пароль не подходит';
            case '-5555':
                return 'Запрашиваемая база не существует';
            case '-6666':
                return 'Сервер перегружен, достигнуто максимальное число потоков обработки';
            case '-7777':
                return 'Не удалось запустить/прервать поток администратора';
        }
        return '>Неизвестный код возврата: ' . $code . '';
    }

    //Собираем строку поискового запроса
    function getQueryNew(string $author = "")
    {
        $query = "";
        //Добавляем в поисковый запрос автора
        if (!empty($author) && (strlen($author) > 0)) {
            $query_part = "";
            $author = ucwords(htmlspecialchars(strtolower(trim($author))));
            $query_part .= '("A=' . $author . '$" + "A=$' . $author . '")';
            $query .= $query_part;
        }
        return $query;
    }

    //Подключение к серверу
    function connect()
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->sock === false) {
            return false;
        }
        if (@socket_connect($this->sock, $this->ip, $this->port)) {
            return true;
        } else {
            return false;
        }
    }

    //Подтверждение авторизации
    function loginVerification()
    {
        $Packet = implode("\n", array('N', $this->arm, 'N', $this->id, $this->seq, '', '', '', '', ''));
        $Packet = strlen($Packet) . "\n" . $Packet;
        $Answer = $this->sendPacket($Packet);
        if ($Answer[10] == 0) {
            return true;
        } else {
            $this->error_code = $Answer[10];
            return false;
        }
    }

    // Авторизация
    function login()
    {
        $Packet = implode("\n", array('A', $this->arm, 'A', $this->id, $this->seq, '', '', '', '', '', $this->login, $this->pass));
        $Packet = strlen($Packet) . "\n" . $Packet;
        $Answer = $this->sendPacket($Packet);
        //Если подключение не удалось, выводим ошибки
        if (($Answer === false)) {
            $this->Error_Code = 1;
            return false;
        }
        //Если обнаружн код ошибки, выводим его на экран
        if (!empty($Answer[10])) {
            $this->Error_Code = $Answer[10];
        }
        if ($this->Error_Code != 0) return false;
        $this->server_timeout = $Answer[11];
        $this->server_ver = $Answer[4];
        return true;
    }



    // Завершение сессии
    function logout()
    {
        $Packet = implode("\n", array('B', $this->arm, 'B', $this->id, $this->seq, '', '', '', '', '', $this->login));
        $Packet = strlen($Packet) . "\n" . $Packet;
        $Answer = $this->sendPacket($Packet);
        if ($Answer === false) {
            return false;
        }
        if (isset($Answer[10])) {
            $this->Error_Code = $Answer[10];
            if ($this->Error_Code != 0) {
                return false;
            }
        }
        return true;
    }

    // Поиск записей по запросу
    function recordsSearch($Query, $num_records = 1, $first_record = 0, $format = "@", $min = null, $max = null, $expression = null)
    {
        $timeStart = (float) microtime();
        $searchNumber = 0;
        for ($i = 0; $i < count($this->DataBase); $i++) {
            $Packet = implode("\n", array('K',  $this->arm, 'K', $this->id, $this->seq++, $this->pass, $this->login, '', '', '', $this->DataBase[$i], $Query, 1000000, $first_record, $format));
            $Packet = strlen($Packet) . "\n" . $Packet;
            $Answer = $this->sendPacket($Packet);
            if ($Answer === false) {
                return false;
            }
            if (isset($Answer[10])) {
                if ($Answer[10] != 0) {
                    $this->Error_Code = $Answer[10];
                    $result[$this->DataBase[$i]]['error_code'] = $this->Error_Code;
                    return $result;
                }
            }
            if (!empty($Answer[11])) {
                $searchNumber += $Answer[11]; // количество найденных записей
            } 
            $c = count($Answer) - 1;
            for ($j = 11; $j < $c; $j++) {
                $result[$this->DataBase[$i]]['records'][] = $Answer[$j];
            }
        }
        $result['searchNumber'] = $searchNumber;
        $searchTime = (float) microtime() - $timeStart;
        $searchTime = substr($searchTime, 0, -3);
        $result['searchTime'] = $searchTime * 10;
        $result['error_code'] = $this->Error_Code;
        return $result;
    }

    function sendPacket($Packet)
    {
        if ($this->sock === false) {
            return false;
        }
        if (!$this->connect()) {
            return false;
        }
        $this->seq++;
        $Answer = '';
        $get_answer = 0;
        while ($buf = @socket_read($this->sock, 2048, PHP_NORMAL_READ)) {
            $Answer .= $buf;
            $get_answer = 1;
        }
        if ($get_answer == 1) {
            $result['code'] = $Answer[10];
        }
        socket_close($this->sock);
        if ($get_answer !== 1) {
            return;
        }
        return explode("\r\n", $Answer);
    }

    // Раскодировать строку поля на ассоциированный массив с подполями
    function parse_field(&$field)
    {
        $ret = array();
        $matches = explode('^', $field);
        if (count($matches) == 1) {
            $matches = explode("\x1f", $field);
        }
        foreach ($matches as $match) {
            $ret[(string) substr($match, 0, 1)] = substr($match, 1);
        }
        return $ret;
    }

    // Раскодировать бинарную строку
    function blob_decode($blob)
    {
        return preg_replace_callback('/%([A-Fa-f0-9]{2})/', function ($matches) {
            return pack('H2', $matches[1]);
        }, $blob);
    }
}
