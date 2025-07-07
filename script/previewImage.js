var loadFile = function (event) {
    var output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = function () {
        URL.revokeObjectURL(output.src) // free memory
    }
};

document.addEventListener('DOMContentLoaded', function () {
    const imgInput = document.getElementById('img');
    if (imgInput) {
        imgInput.addEventListener('change', function (e) {
            const maxFileSize = 8 * 1024 * 1024; // 8 Mo
            for (const file of e.target.files) {
                if (file.size > maxFileSize) {
                    alert(`Le fichier "${file.name}" dépasse la taille maximale autorisée de 8 Mo.`);
                    e.target.value = ""; // Réinitialise le champ
                    break;
                }
            }
        });
    }

});
