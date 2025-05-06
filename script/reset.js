document.addEventListener('DOMContentLoaded', () => {
    console.log("✅ reset.js chargé !");
    const resetBtn = document.getElementById('reset-selection');
    if (!resetBtn) return;
  
    resetBtn.addEventListener('click', () => {
      // 1. Désélectionner tous les éléments avec la classe 'selected'
      document.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
  
      // 2. Réinitialiser toutes les images principales
      document.querySelectorAll('.main-display img').forEach(img => {
        const firstOption = img.closest('.step-wrapper')?.querySelector('.color-options .option img');
        if (firstOption) img.src = firstOption.src;
      });
  
      // 3. Réinitialiser tous les input cachés liés à la sélection
      document.querySelectorAll('input[type="hidden"][id^="selected"]').forEach(input => {
        input.value = '';
      });
  
      // 4. Supprimer toutes les clés sessionStorage liées aux étapes
      Object.keys(sessionStorage).forEach(key => {
        if (key.startsWith('allSelectedOptions_')) {
          sessionStorage.removeItem(key);
        }
      });
  
      // 🔁 4 BIS. Si tu utilises userId pour le stockage (sécurité)
      if (typeof userId !== 'undefined') {
        sessionStorage.setItem('allSelectedOptions_' + userId, JSON.stringify([])); // Vide proprement
      }
  
      // 5. Supprimer la sélection de décoration dans le localStorage
      localStorage.removeItem('selectedDecoration');
  
      // 6. Réinitialiser les totaux de prix (si plusieurs totaux sur plusieurs étapes)
      document.querySelectorAll('.footer p span').forEach(span => {
        span.textContent = '0.00 €';
      });
  
      // ✅ 7. Réinitialiser aussi les variables JS en mémoire
      if (window.allSelectedOptions) {
        window.allSelectedOptions = [];
        console.log("🧠 allSelectedOptions vidé !");
      }
  
      // 8. Recalculer le prix pour afficher 0
      if (typeof updateTotal === 'function') {
        updateTotal();
      }
  
      console.log("🧹 Réinitialisation globale effectuée !");
    });
  });
  