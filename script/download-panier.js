//Download Commande front-back
document.querySelectorAll('.bx.bxs-file-pdf').forEach(button => {
    button.addEventListener('click', function () {
        // Récupérer l'ID avec data-id
        let idCommande = this.getAttribute('data-id');
        console.log("ID de la commande :", idCommande);

        if (idCommande) {
            const currentPath = window.location.origin;
            const basePath = '/front/generate-pdf/'
            let url = '';
            url = `${currentPath}${basePath}generer-devis-panier.php`;
            if (url) {
                // Télécharger directement le devis en PDF
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: idCommande })
                })
                    .then(response => response.blob())
                    .then(blob => {
                        const pdfUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = pdfUrl;
                        a.download = `devis-${idCommande}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(pdfUrl); // Libérer la mémoire
                        document.body.removeChild(a); // Supprimer l'élément temporaire
                    })
                    .catch(error => console.error('Erreur lors de la génération du PDF :', error));
            } else {
                console.error('Erreur : données de commande non valides.');
            }
        } else {
            console.error('Erreur : ID de commande non défini.');
        }
    }
    )
});