// scripts/FilterFunctions.js

document.addEventListener('DOMContentLoaded', function () {
    // Обработчики для чекбоксов
    document.querySelectorAll('.filters__checkbox input[type="checkbox"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            document.getElementById('applyButton').style.display = 'block'; // Показываем кнопку "Применить"
        });
    });

    // Обработчики для тогглеров
    document.getElementById('ToggleKitchen').addEventListener('click', function () {
        toggleFilterSection('filters__kitchen__checkbox', this);
    });

    document.getElementById('TogglePopular').addEventListener('click', function () {
        toggleFilterSection('filters__popular__checkbox', this);
    });

    document.getElementById('TogglePayment').addEventListener('click', function () {
        toggleFilterSection('filters__payment__checkbox', this);
    });

    // Обработчики для шкалы времени
    document.querySelectorAll('input[id="start-time-desktop"]').forEach(function (range) {
        range.addEventListener('input', function () {
            updateTimeDisplay(range, 'start-time-value-desktop');
            document.getElementById('applyButton').style.display = 'block'; // Показываем кнопку "Применить"
        });
    });

    document.querySelectorAll('input[id="end-time-desktop"]').forEach(function (range) {
        range.addEventListener('input', function () {
            updateTimeDisplay(range, 'end-time-value-desktop');
            document.getElementById('applyButton').style.display = 'block'; // Показываем кнопку "Применить"
        });
    });

    // Обработчик для шкалы среднего чека
    document.querySelectorAll('input[id="bill-range-desktop"]').forEach(function (range) {
        range.addEventListener('input', function () {
            updateBillDisplay(range, 'bill-range-value-desktop');
            document.getElementById('applyButton').style.display = 'block'; // Показываем кнопку "Применить"
        });
    });
});

function applyFiltersDesktop() {
    let url = new URL(window.location.href);
    let filters = {
        kitchen: [],
        popular: [],
        payment: [],
        time: {},
        bill: 0
    };

    // Собираем выбранные фильтры
    document.querySelectorAll("input[name='kitchen[]']:checked").forEach((checkbox) => {
        filters.kitchen.push(checkbox.value);
    });
    document.querySelectorAll("input[name='popular[]']:checked").forEach((checkbox) => {
        filters.popular.push(checkbox.value);
    });
    document.querySelectorAll("input[name='payment[]']:checked").forEach((checkbox) => {
        filters.payment.push(checkbox.value);
    });

    // Собираем выбранные значения времени
    filters.time.start = document.getElementById('start-time-desktop').value;
    filters.time.end = document.getElementById('end-time-desktop').value;

    // Собираем выбранное значение среднего чека
    filters.bill = document.getElementById('bill-range-desktop').value;

    // Обновляем URL
    if (filters.kitchen.length > 0) {
        url.searchParams.set('kitchen', filters.kitchen.join(','));
    } else {
        url.searchParams.delete('kitchen');
    }

    if (filters.popular.length > 0) {
        url.searchParams.set('popular', filters.popular.join(','));
    } else {
        url.searchParams.delete('popular');
    }

    if (filters.payment.length > 0) {
        url.searchParams.set('payment', filters.payment.join(','));
    } else {
        url.searchParams.delete('payment');
    }

    // Обновляем URL с параметрами времени
    url.searchParams.set('start-time', filters.time.start);
    url.searchParams.set('end-time', filters.time.end);

    // Обновляем URL с параметром среднего чека
    url.searchParams.set('bill', filters.bill);

    // Перезагружаем страницу с обновленными параметрами
    window.location.href = url.toString();
}

function updateTimeDisplay(rangeElement, displayId) {
    let value = rangeElement.value;
    let hours = Math.floor(value / 60);
    let minutes = value % 60;
    let formattedTime = ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2);
    document.getElementById(displayId).innerText = formattedTime;
}

function updateBillDisplay(rangeElement, displayId) {
    let value = rangeElement.value;
    document.getElementById(displayId).innerText = value;
}

function resetFiltersDesktop() {
    let url = new URL(window.location.href);

    // Удаляем параметры фильтров из URL
    url.searchParams.delete('kitchen');
    url.searchParams.delete('popular');
    url.searchParams.delete('payment');
    url.searchParams.delete('start-time');
    url.searchParams.delete('end-time');
    url.searchParams.delete('bill');

    // Перезагружаем страницу без параметров фильтров
    window.location.href = url.toString();
}

function toggleFilterSection(sectionClass, toggler) {
    let section = document.querySelector(`.${sectionClass}`);
    let checkboxes = section.querySelectorAll('input[type="checkbox"]');
    let toggleState = checkboxes.length > 0 && !checkboxes[0].checked; // Проверяем первый чекбокс для определения текущего состояния

    checkboxes.forEach((checkbox) => {
        checkbox.checked = toggleState; // Устанавливаем все чекбоксы в одно состояние
    });

    // Анимация и изменение стилей для тогглера
    toggler.classList.toggle('active');
    toggler.querySelector('.filters__toggler__circle').classList.toggle('active');

    document.getElementById('applyButton').style.display = 'block'; // Показываем кнопку "Применить"
}
