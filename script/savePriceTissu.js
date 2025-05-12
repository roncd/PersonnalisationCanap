document.addEventListener('DOMContentLoaded', () => {
    const suivantBtn = document.querySelector('.btn-suivant');
    const userId = document.body.getAttribute('data-user-id');
    const currentStep = document.body.getAttribute('data-current-step');

    if (!suivantBtn || !userId || !currentStep) {
        console.error("Bouton ou données manquants :", { suivantBtn, userId, currentStep });
        return;
    }

    const sessionKey = `allSelectedOptions_${userId}`;
    let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];

    if (!Array.isArray(allSelectedOptions)) {
        console.warn("Les options sélectionnées ne sont pas valides. Réinitialisation.");
        allSelectedOptions = [];
    }

    const tissuEtapes = ['1-dimensions','3-modele-tissu','4-1couleur-tissu','4-2-couleur-tissu','5-dossier-tissu','7-mousse-tissu'];

    const totalTissuEtapes = allSelectedOptions.reduce((sum, option) => {
        const id = option.id || '';
        const price = option.price || 0;
        const quantity = option.quantity || 1;

        if (tissuEtapes.some(step => id.startsWith(step))) {
            return sum + (price * quantity);
        }
        return sum;
    }, 0);

    console.log("Total tissu à envoyer :", totalTissuEtapes);

    suivantBtn.addEventListener('click', (e) => {
        e.preventDefault();

        fetch('save_total_tissu.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                total_price: totalTissuEtapes
            }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('Prix tissu enregistré !');
                window.location.href = "recapitulatif-commande-tissu.php";
            } else {
                console.error('Erreur côté serveur :', data.message);
            }
        })
        .catch(error => console.error("Erreur de requête :", error));
    });
});
