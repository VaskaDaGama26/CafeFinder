<?php
require_once 'vendor/connect.php';
require_once 'vendor/crypt.php';

if (isset($_GET['category'])) {
  $placeCategory = decryptData($_GET['category']);
}

function getPlacesWord($number)
{
  $number = abs($number) % 100;
  $lastDigit = $number % 10;

  if ($number > 10 && $number < 20) {
    return "мест";
  }
  if ($lastDigit > 1 && $lastDigit < 5) {
    return "места";
  }
  if ($lastDigit == 1) {
    return "место";
  }
  return "мест";
}

$categoryNameQuery = mysqli_query($connect, "SELECT `Places_Category_Name` FROM `Places_Categories` WHERE `Places_Category_ID` = '$placeCategory'");
$categoryName = mysqli_fetch_assoc($categoryNameQuery)['Places_Category_Name'] ?? 'Категория не найдена';

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

// Получаем фильтры из базы данных
$kitchensQuery = mysqli_query($connect, "SELECT `Kitchen_Filters_ID`, `Kitchen_Filters_Name` FROM `Kitchen_Filters`");
$kitchens = [];
while ($kitchen = mysqli_fetch_assoc($kitchensQuery)) {
  $kitchens[] = $kitchen;
}

$popularQuery = mysqli_query($connect, "SELECT `Popular_Filters_ID`, `Popular_Filters_Name` FROM `Popular_Filters` WHERE `Popular_Filters_Displayed` = 1");
$popularFilters = mysqli_fetch_all($popularQuery, MYSQLI_ASSOC);

$paymentQuery = mysqli_query($connect, "SELECT `Payment_Filters_ID`, `Payment_Filters_Name` FROM `Payment_Filters`");
$paymentFilters = mysqli_fetch_all($paymentQuery, MYSQLI_ASSOC);

// Обработаем фильтры из GET-запроса и добавим их в URL
$filters = [];
$selectedKitchens = isset($_GET['kitchen']) ? $_GET['kitchen'] : [];
$selectedPopular = isset($_GET['popular']) ? $_GET['popular'] : [];
$selectedPayments = isset($_GET['payment']) ? $_GET['payment'] : [];
$selectedStartTime = isset($_GET['start-time']) ? intval($_GET['start-time']) : 0;
$selectedEndTime = isset($_GET['end-time']) ? intval($_GET['end-time']) : 1440;
$selectedBill = isset($_GET['bill']) ? intval($_GET['bill']) : 0;

if ($selectedBill > 0) {
  $filters[] = "`Places_Bill` <= $selectedBill";
}

if (!empty($selectedKitchens)) {
  $selectedKitchens = is_array($selectedKitchens) ? $selectedKitchens : explode(',', $selectedKitchens);
  $selectedKitchens = array_map(function ($value) use ($connect) {
    return mysqli_real_escape_string($connect, trim($value));
  }, $selectedKitchens);
  $filters[] = "`Kitchen_Filters_ID` IN ('" . implode("','", $selectedKitchens) . "')";
}

if (!empty($selectedPopular)) {
  $selectedPopular = is_array($selectedPopular) ? $selectedPopular : explode(',', $selectedPopular);
  $selectedPopular = array_map(function ($value) use ($connect) {
    return mysqli_real_escape_string($connect, trim($value));
  }, $selectedPopular);
  $filters[] = "`Places_ID` IN (SELECT `Places_ID` FROM `Places_PopularFilters` WHERE `Popular_Filters_ID` IN ('" . implode("','", $selectedPopular) . "'))";
}

if (!empty($selectedPayments)) {
  $selectedPayments = is_array($selectedPayments) ? $selectedPayments : explode(',', $selectedPayments);
  $selectedPayments = array_map(function ($value) use ($connect) {
    return mysqli_real_escape_string($connect, trim($value));
  }, $selectedPayments);
  $filters[] = "`Places_ID` IN (SELECT `Places_ID` FROM `Places_PaymentFilters` WHERE `Payment_Filters_ID` IN ('" . implode("','", $selectedPayments) . "'))";
}

// Разбор времени в формате "HH:MM" и преобразование в минуты с начала дня
function timeToMinutes($time)
{
  $parts = explode(':', $time);
  return $parts[0] * 60 + $parts[1];
}

if ($selectedStartTime || $selectedEndTime < 1440) {
  $timeFilter = [];
  $placesQuery = "SELECT * FROM `Places` WHERE `Places_Category_ID` = '$placeCategory'";
  $placesResult = mysqli_query($connect, $placesQuery);

  while ($place = mysqli_fetch_assoc($placesResult)) {
    $start = timeToMinutes($place['Places_StartTime']);
    $end = timeToMinutes($place['Places_EndTime']);

    if (($selectedStartTime >= $start && $selectedStartTime <= $end) || ($selectedEndTime >= $start && $selectedEndTime <= $end)) {
      $timeFilter[] = $place['Places_ID'];
    }
  }

  if (!empty($timeFilter)) {
    $filters[] = "`Places_ID` IN ('" . implode("','", $timeFilter) . "')";
  }
}

$filterQuery = !empty($filters) ? " AND " . implode(" AND ", $filters) : "";

$query = "SELECT * FROM `Places` WHERE `Places_Category_ID` = '$placeCategory' $filterQuery";
$places = mysqli_query($connect, $query);

if (!$places) {
  die('Ошибка выполнения запроса: ' . mysqli_error($connect));
}

$placesCount = $places ? mysqli_num_rows($places) : 0;
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

  <div class="side__menu">
    <div class="side__menu__content">
      <?php foreach ($categories as $id => $name) { ?>
        <?php echo "<a href=\"category.php?category=" . encryptData($id) . "\" class=\"side__menu__item\">$name</a>"; ?>
      <?php } ?>
    </div>
  </div>
  <div class="blur__background" id="blurBackground"></div>

  <section class="category" id="top">
    <div class="category__container">
      <div class="category__filters">
        <div class="filters__header">Все фильтры</div>

        <!-- Фильтры для кухни -->
        <div class="filters__kitchen">
          <div class="filters__kitchen__header">Кухня</div>
          <div class="filters__kitchen__toggle">
            <button class="filters__toggler" id="ToggleKitchen">
              <div class="filters__toggler__circle" id="toggle__kitchen"></div>
            </button>
            <div class="filters__label">Применить все</div>
          </div>
          <div class="filters__kitchen__checkbox">
            <?php foreach ($kitchens as $kitchen) {
              $checked = in_array($kitchen['Kitchen_Filters_ID'], $selectedKitchens) ? 'checked' : '';
              echo "<label class='filters__checkbox'>
                            <input type='checkbox' name='kitchen[]' value='{$kitchen['Kitchen_Filters_ID']}' $checked />
                            {$kitchen['Kitchen_Filters_Name']}
                            </label>";
            } ?>
          </div>
        </div>

        <!-- Фильтры для популярных -->
        <div class="filters__popular">
          <div class="filters__popular__header">Популярные</div>
          <div class="filters__popular__toggle">
            <button class="filters__toggler" id="TogglePopular">
              <div class="filters__toggler__circle" id="toggle__popular"></div>
            </button>
            <div class="filters__label">Применить все</div>
          </div>
          <div class="filters__popular__checkbox">
            <?php foreach ($popularFilters as $popular) {
              $checked = in_array($popular['Popular_Filters_ID'], $selectedPopular) ? 'checked' : '';
              echo "<label class='filters__checkbox'>
                            <input type='checkbox' name='popular[]' value='{$popular['Popular_Filters_ID']}' $checked />
                            {$popular['Popular_Filters_Name']}
                            </label>";
            } ?>
          </div>
        </div>

        <!-- Фильтры для оплаты -->
        <div class="filters__payment">
          <div class="filters__payment__header">Оплата</div>
          <div class="filters__payment__toggle">
            <button class="filters__toggler" id="TogglePayment">
              <div class="filters__toggler__circle" id="toggle__payment"></div>
            </button>
            <div class="filters__label">Применить все</div>
          </div>
          <div class="filters__payment__checkbox">
            <?php foreach ($paymentFilters as $payment) {
              $checked = in_array($payment['Payment_Filters_ID'], $selectedPayments) ? 'checked' : '';
              echo "<label class='filters__checkbox'>
                            <input type='checkbox' name='payment[]' value='{$payment['Payment_Filters_ID']}' $checked />
                            {$payment['Payment_Filters_Name']}
                            </label>";
            } ?>
          </div>
        </div>

        <!-- Фильтры для среднего чека -->
        <div class="filters__bill">
          <div class="filters__bill__header">Средний чек</div>
          <div class="filters__bill__range">
            <label for="bill-range-desktop">Средний чек (₽):</label>
            <input type="range" id="bill-range-desktop" name="bill-range" min="0" max="5000" step="100" value="0">
            <span id="bill-range-value-desktop">0</span>
          </div>
        </div>

        <!-- Фильтр по времени работы -->
        <div class="filters__time">
          <div class="filters__time__header">Время работы</div>
          <div class="filters__time__range">
            <label for="start-time-desktop">Начало:</label>
            <input type="range" id="start-time-desktop" name="start-time" min="0" max="1440" step="60" value="0">
            <span id="start-time-value-desktop">00:00</span>
          </div>
          <div class="filters__time__range">
            <label for="end-time-desktop">Конец:</label>
            <input type="range" id="end-time-desktop" name="end-time" min="0" max="1440" step="60" value="1440">
            <span id="end-time-value-desktop">24:00</span>
          </div>
        </div>
        <button id="applyButton" class="filters__apply" onclick="applyFiltersDesktop()">Применить</button>
        <button id="resetButton" class="filters__reset" onclick="resetFiltersDesktop()">Сбросить фильтры</button>
      </div>

      <div class="category__list">
        <div class="category__header__container">
          <h1 class="list__header"><?php echo "$categoryName: найдено $placesCount " . getPlacesWord($placesCount); ?></h1>
          <div class="list__mobile__filters" id="mobile__filters" onclick="FilterMenu()">
            <img class="list__mobile__filters__icon" src="static/filter-icon.svg" alt="Filter Icon" />
          </div>
        </div>

        <?php while ($result = mysqli_fetch_assoc($places)) { ?>
          <div class="list__card">
            <?php echo '<img class="list__card__img"' . 'src="' . $result['Places_Preview'] . '" alt="">'; ?>
            <div class="list__card__info">
              <?php echo '<h4 class="list__card__header">' . $result['Places_Name'] . '</h4>'; ?>
              <?php echo '<p class="list__card__about">' . $result['Places_Description'] . '</p>'; ?>
              <a href="place.php?place=<?php echo encryptData($result['Places_ID']); ?>" class="list__card__button">Подробнее</a>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>


  <!-- Мобильный фильтр -->
  <div class="filters__menu">
    <div class="filters__header">Все фильтры</div>

    <!-- Фильтры для кухни -->
    <div class="filters__kitchen">
      <div class="filters__kitchen__header">Кухня</div>
      <div class="filters__kitchen__toggle">
        <button class="filters__toggler" id="ToggleKitchenMobile">
          <div class="filters__toggler__circle" id="toggle__kitchen__mobile"></div>
        </button>
        <div class="filters__label">Применить все</div>
      </div>
      <div class="filters__kitchen__checkbox">
        <?php foreach ($kitchens as $kitchen) {
          $checked = in_array($kitchen['Kitchen_Filters_ID'], $selectedKitchens) ? 'checked' : '';
          echo "<label class='filters__checkbox'>
                <input type='checkbox' name='kitchen[]' value='{$kitchen['Kitchen_Filters_ID']}' $checked />
                {$kitchen['Kitchen_Filters_Name']}
                </label>";
        } ?>
      </div>
    </div>

    <!-- Фильтры для популярных -->
    <div class="filters__popular">
      <div class="filters__popular__header">Популярные</div>
      <div class="filters__popular__toggle">
        <button class="filters__toggler" id="TogglePopularMobile">
          <div class="filters__toggler__circle" id="toggle__popular__mobile"></div>
        </button>
        <div class="filters__label">Применить все</div>
      </div>
      <div class="filters__popular__checkbox">
        <?php foreach ($popularFilters as $popular) {
          $checked = in_array($popular['Popular_Filters_ID'], $selectedPopular) ? 'checked' : '';
          echo "<label class='filters__checkbox'>
                <input type='checkbox' name='popular[]' value='{$popular['Popular_Filters_ID']}' $checked />
                {$popular['Popular_Filters_Name']}
                </label>";
        } ?>
      </div>
    </div>

    <!-- Фильтры для оплаты -->
    <div class="filters__payment">
      <div class="filters__payment__header">Оплата</div>
      <div class="filters__payment__toggle">
        <button class="filters__toggler" id="TogglePaymentMobile">
          <div class="filters__toggler__circle" id="toggle__payment__mobile"></div>
        </button>
        <div class="filters__label">Применить все</div>
      </div>
      <div class="filters__payment__checkbox">
        <?php foreach ($paymentFilters as $payment) {
          $checked = in_array($payment['Payment_Filters_ID'], $selectedPayments) ? 'checked' : '';
          echo "<label class='filters__checkbox'>
                <input type='checkbox' name='payment[]' value='{$payment['Payment_Filters_ID']}' $checked />
                {$payment['Payment_Filters_Name']}
                </label>";
        } ?>
      </div>
    </div>

    <!-- Фильтр по времени работы -->
    <div class="filters__time">
      <div class="filters__time__header">Время работы</div>
      <div class="filters__time__range">
        <label for="start-time-mobile">Начало:</label>
        <input type="range" id="start-time-mobile" name="start-time" min="0" max="1440" step="60" value="0">
        <span id="start-time-value-mobile">00:00</span>
      </div>
      <div class="filters__time__range">
        <label for="end-time-mobile">Конец:</label>
        <input type="range" id="end-time-mobile" name="end-time" min="0" max="1440" step="60" value="1440">
        <span id="end-time-value-mobile">24:00</span>
      </div>
    </div>

    <!-- Фильтры для среднего чека -->
    <div class="filters__bill">
      <div class="filters__bill__header">Средний чек</div>
      <div class="filters__bill__range">
        <label for="bill-range-mobile">Средний чек (₽):</label>
        <input type="range" id="bill-range-mobile" name="bill-range" min="0" max="5000" step="100" value="0">
        <span id="bill-range-value-mobile">0</span>
      </div>
    </div>

    <button id="applyButtonMobile" class="filters__apply" onclick="applyFiltersMobile()">Применить</button>
    <button id="resetButtonMobile" class="filters__reset" onclick="resetFiltersMobile()">Сбросить фильтры</button>
  </div>
  <div class="filters__blur__background"></div>
  
  <footer class="footer">
        <div class="footer__container">
            <div class="footer__row">
                <a class="footer__item">Copyright © 2024–2024 CafeFinder Lipetsk. Все права защищены.</a>
                <a href="#top" class="footer__button">Наверх</a>
            </div>
        </div>
    </footer>


  <!-- Подключение скриптов -->
  <script src="./scripts/CardClicked.js"></script>
  <script src="./scripts/FilterMenu.js"></script>
  <script src="./scripts/HeaderMenu.js"></script>
  <script src="./scripts/FilterFunctions.js"></script>
  <script src="./scripts/FilterFunctionsMobile.js"></script>

</body>

</html>