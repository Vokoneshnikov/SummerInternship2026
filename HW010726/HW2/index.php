<?php
function getSumOfArray(array $array) : float {
    $result = 0;
    foreach ($array as $value) {
        $result += $value;
    }
    return $result;
};
function getMaxOfArray(array $array) : float {
    $result = PHP_FLOAT_MIN;

    foreach ($array as $value) {
        $result = max($value, $result);
    }

    return $result;
};
function getMinOfArray(array $array) : float {

    $result = PHP_FLOAT_MAX;

    foreach ($array as $value) {
        $result = min($value, $result);
    }

    return $result;
};

$file = fopen("file.txt", "r");

$arr = [];

$line = fgets($file);

while ($line !== false) {
    $arr[] = (float)$line;

    $line = fgets($file);
}
fclose($file);


echo "Сумма всех чисел: " . getSumOfArray($arr) . "\n";

echo "Максимальное число: " . getMaxOfArray($arr) . "\n";

echo "Минимальное числоа: " . getMinOfArray($arr) . "\n";
