let index = 1;

function addProduit() {
    const container = document.getElementById('produits-container');
    const firstRow = container.querySelector('.produit-row');
    const newRow = firstRow.cloneNode(true);

    const select = newRow.querySelector('select');
    const input = newRow.querySelector('input[type="number"]');

    select.name = `produits[${index}][id]`;
    input.name = `produits[${index}][quantite]`;

    select.value = "";
    input.value = "";

    container.appendChild(newRow);
    index++;

    updateOptions();
}

function removeRow(button) {
    const row = button.closest('.produit-row');
    const container = document.getElementById('produits-container');

    if (container.querySelectorAll('.produit-row').length > 1) {
        row.remove();
        updateOptions();
    }
}

function updateOptions() {
    const selects = document.querySelectorAll('.produit-select');
    const selectedValues = Array.from(selects).map(s => s.value).filter(v => v);

    selects.forEach(select => {
        const currentValue = select.value;
        Array.from(select.options).forEach(option => {
            if (option.value === "" || option.value === currentValue) {
                option.disabled = false;
            } else {
                option.disabled = selectedValues.includes(option.value);
            }
        });
    });
}