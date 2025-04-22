document.querySelectorAll('.bx.bxs-file-pdf').forEach(button => {
    button.addEventListener('click', function() {
        // Récupérer l'ID avec data-id
        let idCommande = this.getAttribute('data-id'); 
        console.log("ID de la commande :", idCommande);

        if (idCommande) {
            const currentPath = window.location.origin; 
            const basePath = '/PersonnalisationCanap/front/generate-pdf/'
            // vérification du type de commande dans la database
            fetch(`${currentPath}${basePath}type-commande.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idCommande })
            })
            
            .then(response => response.json())
            .then(data => {
                if (data.success && data.type) {
                    let url = '';
                    // Choisir le fichier PHP en fonction du type
                    if (data.type === 'Bois') {
                        const currentPath = window.location.origin;
                        const basePath = '/PersonnalisationCanap/front/generate-pdf/';
                        url = `${currentPath}${basePath}generer-devis-bois.php`;
                    } else if (data.type === 'Tissu') {
                        const currentPath = window.location.origin; 
                        const basePath = '/PersonnalisationCanap/front/generate-pdf/';
                        url = `${currentPath}${basePath}generer-devis-tissu.php`;
                    }

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
                            a.download = `devis-${data.type}.pdf`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(pdfUrl); // Libérer la mémoire
                            document.body.removeChild(a); // Supprimer l'élément temporaire
                        })
                        .catch(error => console.error('Erreur lors de la génération du PDF :', error));
                    } else {
                        console.error('Erreur : type de commande non valide.');
                    }
                } else {
                    console.error('Erreur : données de commande non valides.');
                }
            })
            .catch(error => console.error('Erreur lors de la récupération du type de commande :', error));
        } else {
            console.error('Erreur : ID de commande non défini.');
        }
    });
});