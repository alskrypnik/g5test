<?php
const MAX_DIGIT = 9;  // максимальная цифра в десятичной системе счисления. всегда "9" ;)

const TIKET_LENGTH = 6;  // Длина билетика, обязательно чётное
const HALF_TIKET_LENGTH = TIKET_LENGTH/2;  // половина длины билетика. сколько чисел слева и сколько справа
const FIRST_LUCKY_TIKECT = 1001;

const FROM_QUERY_PARAM = 'start';
const TO_QUERY_PARAM = 'end';
const MIN_TIKET_NUMBER = 1;
const MAX_TIKET_NUMBER = 999999;


/**
 * РЕШЕНИЕ
 * Считает количество счастливых шестизначных билетов.
 * 
 * В этой реализации приходится руками просмотреть первые N билетов, доведя до нового тысячного билета
 * И аналогично с конца до вести до меньшей тысячи. То есть имеется в виду, что
 * если был, например, такой диапазон: 132497 - 961424
 * мы руками просмотрим первые 503 билета (до 132500) и последние 424.
 * А диапазон 132500 - 96100 считается формулой. Возможно, можно и полностью формулой,
 * но что-то я до этого не дошел. То есть руками просчитается <2000 билетов
 * 
 * Ниже есть несколько альтернативных функций, которые считают с погрешностью 1-2 билета
 *
 * @param integer $from Нижняя граница диапазона
 * @param integer $to Верхняя граница диапазона
 * 
 * @return integer Количество "счастливых" билетов в диапазоне от $from до $to
 */ 
function luckyTickets($from, $to) {
    // билеты с номером меньше 0001001 всегда можно игнорировать
    // т.к. они не бывают счастливыми
    if ($from < FIRST_LUCKY_TIKECT) {
        $from = FIRST_LUCKY_TIKECT;
    }
    // обрабатываем крайние случаи
    if ($to < FIRST_LUCKY_TIKECT || $from > $to) {
        return 0;
    }

    $found = 0;
    // сколько отступим от начала диапахона
    $firstThousand = 1000 - ($from - floor($from/1000)*1000);
    // и столько (<1000) билетиков просчитываем вручную
    $found += bruteForceLuckyTickets($from, $from+$firstThousand);

    // сколько надо отступить от конца диапазона
    $lastThousand = $to - floor($to/1000)*1000;
    // тоже просчитываем их вручную
    $found += bruteForceLuckyTickets($to-$lastThousand, $to);

    // делаем отступы от диапазона
    $from += $firstThousand;
    $to -= $lastThousand;

    $diff = $to - $from;
    // $diff/9 был бы ответ,
    // но при переходе через каждую 1_000 мы учли лишний билет
    // а при переходе через каждые 10_000 мы его наоборот не учли
    // поэтому вычитаем разницы каждых 9_000
    $except = floor($diff / (1000 * 9));
    $found += floor($diff/9 - $except);
    return $found;
}


/**
 * Для примера решение с приближенным результатом. Погрешность 1-2
 * Считает количество счастливых шестизначных билетов.
 * 
 * В этой реализации приходится руками просмотреть первые N билетов, доведя до нового тысячного билета
 * И аналогично с конца до вести до меньшей тысячи. То есть имеется в виду, что
 * если был, например, такой диапазон: 132497 - 961424
 * мы руками просмотрим первые 503 билета (до 132500) и последние 424.
 * А диапазон 132500 - 96100 считается формулой. Возможно, можно и полностью формулой,
 * но что-то я до этого не дошел. То есть руками просчитается <2000 билетов
 * 
 * Ниже есть несколько альтернативных функций, которые считают с погрешностью 1-2 билета
 *
 * @param integer $from Нижняя граница диапазона
 * @param integer $to Верхняя граница диапазона
 * 
 * @return integer Количество "счастливых" билетов в диапазоне от $from до $to
 */ 
function naiveLuckyTickets($from, $to) {
    // билеты с номером меньше 0001001 всегда можно игнорировать
    // т.к. они не бывают счастливыми
    if ($from < FIRST_LUCKY_TIKECT) {
        $from = FIRST_LUCKY_TIKECT;
    }
    // обрабатываем крайние случаи
    if ($to < FIRST_LUCKY_TIKECT || $from > $to) {
        return 0;
    }

    $diff = $to - $from;
    $average = $diff / 9;
    $except = ($diff / (1001 * 9)) - 1;
    return floor($average - $except);
}


/**
 * Считает количество счастливых шестизначных билетов полным перебором.
 * ТОЛЬКО ДЛЯ ТЕСТОВ
 * 
 * @param integer $from Нижняя граница диапазона
 * @param integer $to Верхняя граница диапазона
 * 
 * @return integer Количество "счастливых" билетов в диапазоне от $from до $to
 */ 
function bruteForceLuckyTickets($from, $to) {
    $found = 0;
    for($i=$from; $i<=$to; $i++) {
        // заполняем наше число ведущими нулями
        $number = str_pad(''.$i, TIKET_LENGTH, '0', STR_PAD_LEFT);
        $left = substr($number, 0, HALF_TIKET_LENGTH);  // левая половина "счастливого" билета
        do {
            $left = array_sum(str_split(''.$left));
            // по нашим правилам, если получилось двухзначное число, то повторяем
        } while ($left > MAX_DIGIT);
        
        // аналогично с правой половиной
        $right = substr($number, HALF_TIKET_LENGTH, HALF_TIKET_LENGTH);
        do {
            $right = array_sum(str_split(''.$right));
        } while ($right > MAX_DIGIT);
        
        // если суммы цифр совпали, то "счастливый билетик"
        if ($left == $right) {
            $found++;
        }
    }
    return $found;
}


function getIntParam($key) {
    $val = $_GET[$key];
    if(empty($val) || !is_numeric($val)) {
        return NULL;
    }
    return (int)$val;
}


$from = getIntParam(FROM_QUERY_PARAM);
$to = getIntParam(TO_QUERY_PARAM);
if ($from === NULL || $to === NULL) {
    die('Необходимо передать два числовых параметра: ' . FROM_QUERY_PARAM . ' и ' . TO_QUERY_PARAM);
}
if ($from < MIN_TIKET_NUMBER) {
    die(FROM_QUERY_PARAM . ' должен быть не меньше чем ' . MIN_TIKET_NUMBER);
}
if ($to > MAX_TIKET_NUMBER) {
    die(TO_QUERY_PARAM . ' должен быть не больше чем ' . MAX_TIKET_NUMBER);
}
if ($to < $from) {
    die(FROM_QUERY_PARAM . ' должен быть не больше чем ' . TO_QUERY_PARAM);
}

$count = luckyTickets($from, $to);
print($count);
