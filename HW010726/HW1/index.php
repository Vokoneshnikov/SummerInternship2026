<?php
function isNameOk(string $name) : bool{
    return !empty($name);
};

function isAgeOk(string $age) : bool {
    return (!empty($age) && is_numeric($age) && $age >= 0 && $age <= 130);
};

echo "Введите имя: " . PHP_EOL;
$name = trim(fgets(STDIN));

while (!isNameOk($name)) {
    echo "Некорректный ввод имени " . PHP_EOL;

    echo "Введите имя: " . PHP_EOL;

    $name = trim(fgets(STDIN));
}

echo "Введите возраст: " . PHP_EOL;

$age = trim(fgets(STDIN));

while (!isAgeOk($age)) {
    echo "Некорректный ввод возраста " . PHP_EOL;

    echo "Введите возраст: " . PHP_EOL;

    $age = trim(fgets(STDIN));
}

$ageGroup = $age > 18 ? "adult" : "child";

$result = "Привет, {$name}. Ты - {$ageGroup}";
fwrite(STDOUT, $result);
