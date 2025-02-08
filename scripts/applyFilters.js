function applyFilters() {
    let url = new URL(window.location.href);
    let filters = {
        kitchen: [],
        popular: [],
        payment: []
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

    // Перезагружаем страницу с обновленными параметрами
    window.location.href = url.toString();
}