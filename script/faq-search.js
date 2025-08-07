const searchInput = document.getElementById('faq-search');
  const accordeonItems = document.querySelectorAll('.accordeon-item');

  searchInput.addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();

    accordeonItems.forEach(item => {
      const question = item.querySelector('.accordeon-title').textContent.toLowerCase();
      const answer = item.querySelector('.accordeon-content').textContent.toLowerCase();

      if (question.includes(searchTerm) || answer.includes(searchTerm)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  });

