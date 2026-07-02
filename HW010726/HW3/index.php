<?php

$pattern = '/^([-]?[\d.]+)\s*([-+*\/])\s*([-]?[\d.]+)$/';

$str = trim(fgets(STDIN));

if (preg_match($pattern, $str, $matches)) {
    $number1 = (float)$matches[1];
    $operator = $matches[2];
    $number2 = (float)$matches[3];

    if ($operator === "/" && $number2 == 0) {
        echo "Ошибка: деление на ноль";
        exit;
    }

    $result = calculate($number1, $number2, $operator);
    echo $result;

} elseif (is_numeric($str) && floor($str) == $str) {
    echo isPrime((int) $str) ? "Это простое число" : "Это составное число";
} else {
    echo "Некорректный ввод";
}

function calculate(float $number1, float $number2, string $operator) : float {

    return match ($operator) {
        "+" => $number1 + $number2,
        "-" => $number1 - $number2,
        "*" => $number1 * $number2,
        "/" => $number1 / $number2,
    };
};

function isPrime(int $n) : bool {
    if ($n <= 2) {
        return true;
    }

    $limit = (int)sqrt($n);
    for ($i = 2; $i <= $limit; $i++) {
        if ($n % $i == 0) {
            return false;
        }
    }

    return true;
}