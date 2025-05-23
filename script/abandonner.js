document.addEventListener('DOMContentLoaded', () => {
  const btnAbandonner = document.querySelector('.btn-abandonner');
  const popup = document.getElementById('abandonner-popup');
  const btnNo = document.querySelector('.no-btn');
  const btnYes = document.querySelector('.yes-btn');

  if (btnAbandonner && popup && btnNo && btnYes) {
    btnAbandonner.addEventListener('click', () => {
      popup.style.display = 'flex';
    });

    btnNo.addEventListener('click', () => {
      popup.style.display = 'none';
    });

    btnYes.addEventListener('click', () => {
      fetch('abandonner_commande.php', {
        method: 'POST'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          sessionStorage.clear();
          localStorage.clear();
          window.location.href = '../pages'; 
        } else {
          alert("Erreur : " + data.message);
        }
      })
      .catch(error => {
        console.error('Erreur lors de l’abandon :', error);
      });
    });
  }
});
