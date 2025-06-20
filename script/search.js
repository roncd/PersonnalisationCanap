const searchInput = document.getElementById('faq-search');
const faqItems = document.querySelectorAll('#faq-list .accordeon-item');
const noResults = document.getElementById('no-results');

// Fonction pour enlever les accents
const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

searchInput.addEventListener('input', function () {
  const query = normalize(this.value.trim());
  let matchCount = 0;

  faqItems.forEach(item => {
    const question = normalize(item.querySelector('.accordeon-header').textContent);
    
    if (question.includes(query)) {
      item.style.display = "block";
      matchCount++;
    } else {
      item.style.display = "none";
    }
  });

  noResults.style.display = matchCount === 0 ? "block" : "none";
});
