document.getElementById('image').addEventListener('change', function (e) {
    const maxFileSize = 8 * 1024 * 1024; // 8 Mo
    for (const file of e.target.files) {
        if (file.size > maxFileSize) {
            alert(`Le fichier "${file.name}" dépasse la taille maximale autorisée de 8 Mo.`);
            e.target.value = ""; // Réinitialise le champ
            break;
        }
    }
});
const dropzone = document.getElementById('dropzone');
const input = document.getElementById('image');
const fileList = document.getElementById('file-list');
let files = [];

// Nettoyer le nom du fichier côté JS (retirer accents et caractères spéciaux)
function cleanFileNameJS(filename) {
    filename = filename.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    filename = filename.replace(/[^A-Za-z0-9.\-_]/g, "_");
    return filename;
}


dropzone.addEventListener('click', () => input.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

input.addEventListener('change', () => {
    handleFiles(input.files);
});

function handleFiles(selectedFiles) {
    for (let file of selectedFiles) {
        files.push(file);
    }
    input.files = createFileList(files);
    updateFileList();
}

function updateFileList() {
    fileList.innerHTML = '';
    files.forEach((file, index) => {
        const cleanName = cleanFileNameJS(file.name);
        const li = document.createElement('li');

        const span = document.createElement('span');
        span.textContent = cleanName;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '×';
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            removeFile(index);
        });

        li.appendChild(span);
        li.appendChild(btn);
        fileList.appendChild(li);
    });
}


function removeFile(index) {
    files.splice(index, 1);
    input.files = createFileList(files);
    updateFileList();
}


function createFileList(filesArray) {
    const dataTransfer = new DataTransfer();
    filesArray.forEach(file => dataTransfer.items.add(file));
    return dataTransfer.files;
}