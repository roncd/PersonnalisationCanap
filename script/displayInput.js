// afficher les champs du formulaire bois /tissu en fonction du type de banquette séléctionné
document.addEventListener("DOMContentLoaded", function () {
    const banquetteSelect = document.getElementById("banquette");
    const bois = document.querySelector(".bois");
    const tissu = document.querySelector(".tissu");

    const boisInputs = bois.querySelectorAll("select");
    const tissuInputs = tissu.querySelectorAll("select");

    function afficherChampsCorrespondants() {
        const selectedOption = banquetteSelect.options[banquetteSelect.selectedIndex];
        const type = selectedOption.getAttribute("data-type");

        if (type === "Bois") {
            bois.style.display = "block";
            tissu.style.display = "none";

            boisInputs.forEach(input => input.required = true);
            tissuInputs.forEach(input => {
                input.required = false;
                input.value = "";
            });

        } else if (type === "Tissu") {
            bois.style.display = "none";
            tissu.style.display = "block";

            tissuInputs.forEach(input => input.required = true);
            boisInputs.forEach(input => {
                input.required = false;
                input.value = "";
            });
        } else {
            bois.style.display = "none";
            tissu.style.display = "none";
            boisInputs.forEach(input => {
                input.required = false;
                input.value = "";
            });

            tissuInputs.forEach(input => {
                input.required = false;
                input.value = "";
            });
        }
    }

    banquetteSelect.addEventListener("change", afficherChampsCorrespondants);
    afficherChampsCorrespondants();

    const structureSelect = document.getElementById("structure");
    const inputA = document.getElementById("longueurA");
    const inputB = document.getElementById("longueurB");
    const inputC = document.getElementById("longueurC");

    const groupeA = inputA.closest('.form-group');
    const groupeB = inputB.closest('.form-group');
    const groupeC = inputC.closest('.form-group');

    function updateVisibleInputs() {
        const selectedOption = structureSelect.options[structureSelect.selectedIndex];
        const nbLongueurs = parseInt(selectedOption.getAttribute("data-nb-longueurs")) || 0;

        if (nbLongueurs >= 1) {
            groupeA.style.display = 'flex';
            inputA.required = true;
        } else {
            groupeA.style.display = 'none';
            inputA.value = '';
            inputA.required = false;
        }

        if (nbLongueurs >= 2) {
            groupeB.style.display = 'flex';
            inputB.required = true;
        } else {
            groupeB.style.display = 'none';
            inputB.value = '';
            inputB.required = false;
        }

        if (nbLongueurs >= 3) {
            groupeC.style.display = 'flex';
            inputC.required = true;
        } else {
            groupeC.style.display = 'none';
            inputC.value = '';
            inputC.required = false;
        }
    }

    structureSelect.addEventListener("change", updateVisibleInputs);
    updateVisibleInputs();

    function toggleAccoudoirs() {
        const nb = document.getElementById("nb_accoudoir_bois").value;
        const accoudoir1 = document.getElementById("accoudoir1").closest('.form-group');
        const accoudoir2Group = document.getElementById("accoudoir2-group");
        const accoudoir1Select = document.getElementById("accoudoir1");
        const accoudoir2Select = document.getElementById("accoudoir2");
    
        if (nb == "2") {
            accoudoir1.style.display = "flex";
            accoudoir2Group.style.display = "flex";
            accoudoir1Select.required = true;
            accoudoir2Select.required = true;
        } else if (nb == "1") {
            accoudoir1.style.display = "flex";
            accoudoir2Group.style.display = "none";
            accoudoir1Select.required = true;
            accoudoir2Select.required = false;
            accoudoir2Select.value = ""; 
        } else { // nb == "0"
            accoudoir1.style.display = "none";
            accoudoir2Group.style.display = "none";
            accoudoir1Select.required = false;
            accoudoir2Select.required = false;
            accoudoir1Select.value = ""; 
            accoudoir2Select.value = "";
        }
    }
    
    document.getElementById("nb_accoudoir_bois").addEventListener("change", toggleAccoudoirs);
    toggleAccoudoirs(); 
    
    
});    