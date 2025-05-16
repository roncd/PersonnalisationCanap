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

    closeBtn.addEventListener('click', function () {
      aside.classList.toggle('collapsed');
    });
  });