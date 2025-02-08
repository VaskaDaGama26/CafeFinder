// scripts/FilterMenu.js

function lockScroll() {
    document.body.classList.add('no__scroll');
}

function unlockScroll() {
    document.body.classList.remove('no__scroll');
}

function FilterMenu() {
    const filterButton = document.getElementById('mobile__filters');
    const filterMenu = document.querySelector('.filters__menu');
    const blurBackground = document.querySelector('.filters__blur__background');

    // Убираем проверку HeaderMenu, так как он не используется в этой функции
    if (!filterButton || !filterMenu || !blurBackground) {
        console.error('One or more elements not found');
        return;
    }

    const isMenuOpen = filterMenu.classList.toggle('open');

    filterButton.classList.toggle('open');
    blurBackground.classList.toggle('open');

    if (isMenuOpen) {
        lockScroll();
    } else {
        unlockScroll();
    }
}
