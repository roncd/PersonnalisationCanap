document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault(); // empêche le rechargement de la page
    document.querySelector('.btn-noir')?.click();
  }
});