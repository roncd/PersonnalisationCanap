// afficher les champs du formulaire bois /tissu en fonction du type de banquette séléctionné
document.addEventListener("DOMContentLoaded", function () {
    const banquetteSelect = document.getElementById("banquette");
    const bois = document.querySelector(".bois");
    const tissu = document.querySelector(".tissu");


    function afficherChampsCorrespondants() {
        const selectedOption = banquetteSelect.options[banquetteSelect.selectedIndex];
        const type = selectedOption.getAttribute("data-type");

        if (type === "Bois") {
            bois.style.display = "block";
            tissu.style.display = "none";
        } else if (type === "Tissu") {
            bois.style.display = "none";
            tissu.style.display = "block";
        } else {
            bois.style.display = "none";
            tissu.style.display = "none";
        }
    }

    banquetteSelect.addEventListener("change", afficherChampsCorrespondants);
    afficherChampsCorrespondants(); 
});