var loadFile = function (event) {
    var output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = function () {
        URL.revokeObjectURL(output.src) // free memory
    }
};

function previewExistingImage(fileName) {
    if (fileName) {
        document.getElementById('existing_output').src = '../../medias/' + fileName;
    } else {
        document.getElementById('existing_output').src = '';
    }
}

function previewExistingAssets(fileName) {
    if (fileName) {
        document.getElementById('existing_output').src = '../../assets/' + fileName;
    } else {
        document.getElementById('existing_output').src = '';
    }
}

function handleImageUpload(event) {
    const select = document.getElementById('existing_img');
    if (event.target.files.length > 0) {
        // Réinitialise la liste déroulante à l'option vide
        select.selectedIndex = 0;

        // Réinitialise aussi l’aperçu éventuel
        document.getElementById('existing_output').src = '';
    }

    // Aperçu de l’image téléversée (optionnel)
    const output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = () => URL.revokeObjectURL(output.src);
}

function handleBothImageEvents(event) {
    handleImageUpload(event);
    loadFile(event);
}

function clearFileInput(inputId) {
    const input = document.getElementById(inputId);
    input.value = "";
    const preview = document.getElementById('output');
    if (preview) preview.src = "";
}

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
