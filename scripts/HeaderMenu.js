function lockScroll() {
  document.body.classList.add('no__scroll')
}
function unlockScroll() {
  document.body.classList.remove('no__scroll');
}

function HeaderMenu() {
  const burger = document.getElementById('burger');
  const sideMenu = document.querySelector('.side__menu');
  const blurBackground = document.querySelector('.blur__background');

  const FiltersMenu = document.getElementById('mobile__filters');
  const isOpened = FiltersMenu ? FiltersMenu.classList.contains('open') : false;

  if (!isOpened) {
    const isMenuOpen = sideMenu.classList.toggle('open');

    burger.classList.toggle('open');
    blurBackground.classList.toggle('open');

    if (isMenuOpen) {
      lockScroll();
    }
    else {
      unlockScroll();
    }

  }
  
}
