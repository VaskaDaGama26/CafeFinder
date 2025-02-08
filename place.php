<?php
require_once 'vendor/connect.php';
require_once 'vendor/crypt.php';

if (isset($_GET['place'])) {
  $placeID = decryptData($_GET['place']);
}

$placeResult = mysqli_query($connect, "SELECT p.*, c.Places_Category_Name, c.Places_Category_ID FROM `Places` p LEFT JOIN `Places_Categories` c ON p.Places_Category_ID = c.Places_Category_ID WHERE p.Places_ID = '$placeID'");

$place = mysqli_fetch_assoc($placeResult);

if ($place) {
  $categoryID = $place['Places_Category_ID'];
  $categoryName = $place['Places_Category_Name'];
} else {
  // Обработка ошибки, если место не найдено
  echo "Место не найдено";
  exit;
}

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
        <a class="nav__logo" href="index.php"><img class="nav__img" src="/static/logo.svg" /></a>
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

  <section class="place">
    <div class="place__container">
      <div class="place__breadcrumbs">
        <ul class="place__breadcrumbs__list">
          <li class="place__breadcrumbs__item">
            <a href="index.php" class="place__breadcrumbs__link">Главная</a>
          </li>
          <li class="place__breadcrumbs__item">
            <a href="category.php?category=<?php echo encryptData($categoryID); ?>" class="place__breadcrumbs__link"><?php echo $categoryName; ?></a>
          </li>
          <li class="place__breadcrumbs__item"><?php echo $place['Places_Name']; ?></li>
        </ul>
      </div>

      <?php echo '<div class="place__header">' . htmlspecialchars($place['Places_Name']) . '</div>' ?>
      <div class="place__info">
        <div class="place__info__left">
          <div class="place__info__block">
            <img src="static/map.svg" alt="Icon" class="place__info__icon" />
            <?php echo htmlspecialchars($place['Places_Address']) ?>
          </div>
          <div class="place__info__block">
            <img src="static/clock.svg" alt="Icon" class="place__info__icon" />
            <?php echo htmlspecialchars($place['Places_StartTime']) ?>-<?php echo htmlspecialchars($place['Places_EndTime']) ?>
          </div>
          <div class="place__info__block">
            <img src="static/phone.svg" alt="Icon" class="place__info__icon" /><?php echo htmlspecialchars($place['Places_Phone']) ?>
          </div>
          <div class="place__info__block">
            <img src="static/receipt.svg" alt="Icon" class="place__info__icon" /><?php echo htmlspecialchars($place['Places_Bill']) ?>₽
          </div>
          <div class="place__info__block">
            <img src="static/website.svg" alt="Icon" class="place__info__icon" /><a style="color: #E09132" target="_blank" href="http://<?php echo htmlspecialchars($place['Places_Site']) ?>"><?php echo htmlspecialchars($place['Places_Site']) ?></a>
          </div>
        </div>
        <div class="place__info__block">
          <button class="place__info__button" onclick="alert('Место забронировано, для подтверждения позвоните по указанному номеру телефона')">Забронировать</button>
        </div>
      </div>
      <div class="place__photogrid">
        <?php 
        $photosJson = $place['Places_Gallery'];
        $photosArray = json_decode($photosJson, true); 
        foreach ($photosArray as $photo) { 
          echo '<img src="img/' . htmlspecialchars($photo) . '" class="place__photogrid__photo" alt="' . htmlspecialchars($photo) . '">';
        }?>
      </div>
      <div class="place__review">
        <p class="place__review__text">
        <?php echo htmlspecialchars($place['Places_Description']) ?>
        </p>
      </div>
    </div>
  </section>

  <div class="side__menu">
    <div class="side__menu__content">
      <?php foreach ($categories as $id => $name) { ?> 
        <?php echo "<a href=\"category.php?category=" . encryptData($id) . "\" class=\"side__menu__item\">$name</a>"; ?>
      <?php } ?>
    </div>
  </div>
  <div class="blur__background" id="blurBackground"></div>

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
