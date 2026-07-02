<?php
function isNameOk(string $name) : bool{
    return !empty($name);
};

function isAgeOk(string $age) : bool {
    return (!empty($age) && is_numeric($age) && $age >= 0 && $age <= 130);
};

echo "Введите имя: \n";
$name = trim(fgets(STDIN));

while (!isNameOk($name)) {
    echo "Некорректный ввод имени \n";

    echo "Введите имя: \n";

    $name = trim(fgets(STDIN));
}

echo "Введите возраст: \n";

$age = trim(fgets(STDIN));

while (!isAgeOk($age)) {
    echo "Некорректный ввод возраста \n";

    echo "Введите возраст: \n";

    $age = trim(fgets(STDIN));
}

$ageGroup = $age > 18 ? "adult" : "child";

$result = "Привет, {$name}. Ты - {$ageGroup}";
fwrite(STDOUT, $result);
