/* ─── globalPathCleaner.js ─────────────────────────────────────────
   À charger AVANT tous les scripts d’étape.
-------------------------------------------------------------------*/

document.addEventListener('DOMContentLoaded', () => {
  const currentStep = document.body.getAttribute('data-current-step') || '';
  const userId      = document.body.getAttribute('data-user-id');
  if (!userId || !currentStep) return;

  /* Chemin (« bois » ou « tissu ») détecté dans l’URL/slug */
  const currentPath = currentStep.includes('tissu') ? 'tissu'
                   : currentStep.includes('bois')  ? 'bois'
                   : null;
  if (!currentPath) return;                              // inert si step atypique

  /* Dernier chemin utilisé par ce user dans la session */
  const lastPathKey = `lastPath_${userId}`;
  const lastPath    = sessionStorage.getItem(lastPathKey);

  /* Si on vient de changer de chemin → on réinitialise */
  if (lastPath && lastPath !== currentPath) {
    const wordOpposite   = currentPath === 'tissu' ? 'bois'  : 'tissu';
    const suffixOpposite = `-${wordOpposite}`;

    /* 1. Enlève .selected partout (visuel) */
    document.querySelectorAll('.selected').forEach(el =>
      el.classList.remove('selected')
    );

    /* 2. allSelectedOptions_<user> → garde les entrées du chemin courant */
    const allKey = `allSelectedOptions_${userId}`;
    const kept   = (JSON.parse(sessionStorage.getItem(allKey) || '[]'))
                     .filter(opt => !opt.id.includes(suffixOpposite));
    sessionStorage.setItem(allKey, JSON.stringify(kept));

    /* 3. selectedOptions_<user> (accoudoirs, etc.) → reset complet */
    sessionStorage.removeItem(`selectedOptions_${userId}`);

    /* 4. Clés génériques en localStorage (décoration, etc.) */
    const genericKeysToClear = [
      'selectedDecoration',
      'selectedAccoudoir',
      'selectedMousse',     
    ];
    genericKeysToClear.forEach(k => localStorage.removeItem(k));

    /* 5. Clés localStorage contenant l’autre chemin */
    Object.keys(localStorage).forEach(k => {
      if (k.toLowerCase().includes(wordOpposite)) localStorage.removeItem(k);
    });

    console.log(`🧹 Changement ${lastPath} → ${currentPath} : nettoyage effectué`);
  }

  /* Met à jour la mémoire du chemin courant */
  sessionStorage.setItem(lastPathKey, currentPath);
});
