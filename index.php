<?php 
require_once 'vendor/connect.php';
require_once 'vendor/crypt.php';

$categories = [
    1 => 'Кофейни',
    2 => 'Пиццерии',
    3 => 'Бары',
    4 => 'Пабы',
    5 => 'Рестораны',
    6 => 'Гастробары',
    7 => 'Кафе',
    8 => 'Быстрое'
];

// Получаем список карточек для отображения
$query = "SELECT Lists_ID, Lists_Name, Lists_Img FROM Lists WHERE Lists_Displayed = 1";
$result = mysqli_query($connect, $query);

if (!$result) {
    die("Ошибка выполнения запроса: " . mysqli_error($connect));
}

$lists = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Функция для получения кафе по списку
function getPlacesByList($list_id) {
    global $connect;
    $query = "SELECT PLACES.Places_ID, PLACES.Places_Name FROM PLACES
              JOIN Lists_Have_Places ON PLACES.Places_ID = Lists_Have_Places.Places_ID
              WHERE Lists_Have_Places.Lists_ID = $list_id";
    $result = mysqli_query($connect, $query);
    if (!$result) {
        die("Ошибка выполнения запроса: " . mysqli_error($connect));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" />
    <title>CafeFinder</title>
</head>
<body>
<header class="header">
    <nav class="nav">
        <div class="header__container">
            <a class="nav__logo" href="#"><img class="nav__img" src="/static/logo.svg" /></a>
            <div class="nav__burger" id="burger" onclick="HeaderMenu()">
                <div class="nav__bar"></div>
                <div class="nav__bar"></div>
                <div class="nav__bar"></div>
            </div>
            <div class="nav__left">
                <?php foreach ($categories as $id => $name) { ?> 
                    <?php echo "<a href=\"category.php?category=" . encryptData($id) . "\" class=\"nav__item\">$name</a>"; ?>
                <?php } ?>
            </div>
        </div>
    </nav>
</header>

<div class="side__menu">
    <div class="side__menu__content">
        <?php foreach ($categories as $id => $name) { ?> 
            <?php echo "<a href=\"category.php?category=" . encryptData($id) . "\" class=\"side__menu__item\">$name</a>"; ?>
        <?php } ?>
    </div>
</div>
<div class="blur__background" id="blurBackground"></div>

<section class="main" id="top">
    <div class="main__container">
        <h1 class="main__h">Поиск заведений в Липецке</h1>
        <img src="static/main-page.png" alt="CafeFinder" class="main__img">
    </div>
</section>
<section class="usual">
    <h1 class="usual__h">Часто ищут</h1>
    <div class="usual__container">
        <?php foreach ($lists as $list) { 
            $places = getPlacesByList($list['Lists_ID']);
            if (!empty($places)) { ?>
                <div class="card">
                    <a href="place.php?place=<?php echo encryptData($places[0]['Places_ID']); ?>">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($list['Lists_Img']); ?>" alt="<?php echo $list['Lists_Name']; ?>" class="card__img">
                        <h4 class="card__header"><?php echo $list['Lists_Name']; ?></h4>
                    </a>
                </div>
            <?php }
        } ?>
    </div>
</section>

<footer class="footer">
    <div class="footer__container">
        <div class="footer__row">
            <a class="footer__item">Copyright © 2024–2024 CafeFinder Lipetsk. Все права защищены.</a>
            <a href="#top" class="footer__button">Наверх</a>
        </div>
    </div>
</footer>

<script src="scripts/HeaderMenu.js"></script>
</body>
</html>
