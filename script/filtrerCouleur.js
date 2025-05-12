document.getElementById('couleur').addEventListener('change', function () {
    const selectedCouleurId = this.value;
    const options = document.querySelectorAll('.color-options .option');

    options.forEach(option => {
      const optionCouleurId = option.getAttribute('data-couleur-id');

      if (selectedCouleurId === "" || optionCouleurId === selectedCouleurId) {
        option.style.display = 'block';
      } else {
        option.style.display = 'none';
      }
    });
  });