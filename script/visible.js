document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.toggle-visible');
    if (!toggles.length) return;

    toggles.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const id = this.dataset.id;
            const visible = this.checked ? 1 : 0;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}&visible=${visible}`
            })
            .then(res => res.text())
            .then(response => {
                console.log('Mise à jour visibilité :', response);
            })
            .catch(err => {
                console.error('Erreur lors de la mise à jour de visibilité :', err);
            });
        });
    });
});
