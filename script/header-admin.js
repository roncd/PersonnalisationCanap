document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('.menu-link');

    links.forEach(link => {
        const img = link.querySelector('img');
        const iconName = link.getAttribute('data-icon');

        // Actif
        if (link.classList.contains('active')) {
            img.src = `../../assets/menu/black/${iconName}.svg`;
        }

        // Hover 
        link.addEventListener('mouseenter', () => {
            img.src = `../../assets/menu/black/${iconName}.svg`;
        });

        link.addEventListener('mouseleave', () => {
            if (!link.classList.contains('active')) {
                img.src = `../../assets/menu/${iconName}.svg`;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const catalogueLink = document.querySelector('.menu-link.catalogue');
    const menuGroup = catalogueLink.closest('.menu-group');

    catalogueLink.addEventListener('click', function (e) {
        e.preventDefault();
        menuGroup.classList.toggle('open');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const aside = document.querySelector('aside');
    const closeBtn = document.querySelector('.close-icone');
    const main = document.querySelector('main');
  
    // Vérifie l'état stocké au chargement de la page
    const isCollapsed = localStorage.getItem('menuCollapsed') === 'true';
    if (isCollapsed) {
      aside.classList.add('collapsed');
      main.classList.add('collapsed-menu');
    }
  
    // Gestion du clic sur le bouton
    closeBtn.addEventListener('click', function () {
      aside.classList.toggle('collapsed');
      main.classList.toggle('collapsed-menu');
  
      // Met à jour l'état dans le localStorage
      const isNowCollapsed = aside.classList.contains('collapsed');
      localStorage.setItem('menuCollapsed', isNowCollapsed);
    });
  });
  
  