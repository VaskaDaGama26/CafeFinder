// Находим все карточки
document.querySelectorAll('.list__card').forEach(card => {
    // Добавляем обработчик события "click" для каждой карточки
    card.addEventListener('click', event => {
        // Находим ссылку внутри карточки
        const link = card.querySelector('.list__card__button');
        if (link) {
            // Переходим по ссылке
            window.location.href = link.href;
        }
    });
});
