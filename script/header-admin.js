//Changement icone white/black
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

//Menu déroulant catalogue
document.addEventListener('DOMContentLoaded', function () {
    const catalogueLink = document.querySelector('.menu-link.catalogue');
    const menuGroup = catalogueLink.closest('.menu-group');
  
    const aside = document.querySelector('aside');
    const main = document.querySelector('main');
    const footer = document.querySelector('.footer');
  
    catalogueLink.addEventListener('click', function (e) {
      e.preventDefault();
  
      const isCollapsed = aside.classList.contains('collapsed');
  
      if (isCollapsed) {
        aside.classList.remove('collapsed');
        main.classList.remove('collapsed-menu');
        footer.classList.remove('collapsed-menu');
        localStorage.setItem('menuCollapsed', 'false');
      }
  
      // Ouvre/ferme le sous-menu catalogue
      menuGroup.classList.toggle('open');
    });
  });
  


//Header admin close/open 
document.addEventListener('DOMContentLoaded', function () {
    const aside = document.querySelector('aside');
    const closeBtn = document.querySelector('.close-icone');
    const main = document.querySelector('main');
    const footer = document.querySelector('.footer');

  
    // Vérifie l'état stocké au chargement de la page
    const isCollapsed = localStorage.getItem('menuCollapsed') === 'true';
    if (isCollapsed) {
      aside.classList.add('collapsed');
      main.classList.add('collapsed-menu');
      footer.classList.add('collapsed-menu');

    }
  
    // Gestion du clic sur le bouton close
    closeBtn.addEventListener('click', function () {
      aside.classList.toggle('collapsed');
      main.classList.toggle('collapsed-menu');
      footer.classList.toggle('collapsed-menu');
  
      // Met à jour l'état dans le localStorage
      const isNowCollapsed = aside.classList.contains('collapsed');
      localStorage.setItem('menuCollapsed', isNowCollapsed);
    });
  });
  
  