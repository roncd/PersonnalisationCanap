document.addEventListener('DOMContentLoaded', function () {
  const toggles = document.querySelectorAll('.toggle-password-text');

  toggles.forEach(toggleText => {
    // On remonte jusqu'à l'élément parent qui contient à la fois le champ et le bouton
    const input = toggleText.closest('.input-section')?.querySelector('input[type="password"], input[type="text"]');
    
    if (!input) return;

    toggleText.addEventListener('click', () => {
      if (input.type === 'password') {
        input.type = 'text';
        toggleText.textContent = 'Masquer';
      } else {
        input.type = 'password';
        toggleText.textContent = 'Afficher';
      }
    });
  });
});
