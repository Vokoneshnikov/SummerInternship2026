<?php
date_default_timezone_set('Europe/Moscow');

$greeting = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $age = intval($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';

    $hour = intval(date('G'));
    if ($hour >= 5 && $hour < 12) {
        $timeOfDay = "Доброе утро";
    } elseif ($hour >= 12 && $hour < 18) {
        $timeOfDay = "Добрый день";
    } elseif ($hour >= 18 && $hour < 23) {
        $timeOfDay = "Добрый вечер";
    } else {
        $timeOfDay = "Доброй ночи";
    }

    $personType = "";
    if ($age < 14) {
        $personType = ($gender === 'male') ? "мальчик" : "девочка";
    } elseif ($age < 60) {
        $personType = ($gender === 'male') ? "молодой человек" : "девушка";
    } else {
        $personType = ($gender === 'male') ? "пожилой человек" : "пожилая женщина";
    }

    $greeting = "{$timeOfDay}, {$personType} {$name}!";

    echo $greeting;
}
?>

<form method="post" action="">
    <label for="username">Введите имя</label>
    <input type="text" id="username" name="name" required>

    <p>Введите ваш возраст</p>
    <input type="number" id="age" name="age" required>

    <p>Выберите пол:</p>
    <div>
        <input type="radio" id="male" name="gender" value="male" checked>
        <label for="novice">Мужской</label>
    </div>

    <div>
        <input type="radio" id="female" name="gender" value="female">
        <label for="middle">Женский</label>
    </div>

    <button type="submit">Сохранить данные</button>
</form>
