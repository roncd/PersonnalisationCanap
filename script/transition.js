document.addEventListener("DOMContentLoaded", () => {
  const elements = document.querySelectorAll('.transition-all, .transition-boom');

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
        observer.unobserve(entry.target); // L'élément ne s'anime qu'une seule fois
      }
    });
  }, {
    threshold: 0.3
  });

  elements.forEach(el => observer.observe(el));
});
