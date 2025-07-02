// VARIATION PRIX EN FONCTION DU CHEMIN BOIS OU TISSU

document.addEventListener("DOMContentLoaded", function () {
  let totalPrice = 0;

  const currentStep = document.body.getAttribute('data-current-step');
  const userId = document.body.getAttribute('data-user-id');
  if (!userId || !currentStep) return;

  const isTissu = currentStep.includes('tissu');
  const isBois = currentStep.includes('bois');

  console.log(`🔍 Étape actuelle : ${currentStep}`);
  console.log(`🔁 Chemin détecté : ${isTissu ? 'TISSU' : isBois ? 'BOIS' : 'INCONNU'}`);

  const sessionKey = `allSelectedOptions_${userId}`;
  let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
  if (!Array.isArray(allSelectedOptions)) allSelectedOptions = [];

  function getBasePrice() {
    const basePriceElement = document.querySelector('.base-price');
    return basePriceElement ? parseFloat(basePriceElement.textContent) || 0 : 0;
  }

  function clearOtherPathOptions() {
    const before = [...allSelectedOptions];
    allSelectedOptions = allSelectedOptions.filter(opt => {
      if (isTissu) return !opt.id.includes('-bois');
      if (isBois) return !opt.id.includes('-tissu');
      return true;
    });
    const removed = before.filter(opt => !allSelectedOptions.includes(opt));
    if (removed.length > 0) {
      console.log(`🧹 Éléments supprimés du chemin opposé :`, removed);
    }
    sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
  }

  function updateTotal() {
    const basePrice = getBasePrice();
    totalPrice = basePrice + allSelectedOptions.reduce((sum, option) => {
      const price = option.price || 0;
      const quantity = option.quantity || 1;
      return sum + (price * quantity);
    }, 0);
    const totalElement = document.querySelector(".footer p span");
    if (totalElement) totalElement.textContent = `${totalPrice.toFixed(2)} €`;

    console.log(`💰 Nouveau total (${isTissu ? 'TISSU' : 'BOIS'}) : ${totalPrice.toFixed(2)} €`);
    console.log(`🧾 Options sélectionnées :`, allSelectedOptions);
  }


  const attributeSuffix = isTissu ? '-tissu' : '-bois';
  const imgElements = document.querySelectorAll('img');

  imgElements.forEach(option => {
    const idAttr = [...option.attributes].find(attr => attr.name.startsWith('data-') && attr.name.endsWith('-id') && attr.name.includes(attributeSuffix));
    const priceAttr = [...option.attributes].find(attr => attr.name.startsWith('data-') && attr.name.endsWith('-prix') && attr.name.includes(attributeSuffix));

    if (!idAttr || !priceAttr) return;

    const optionId = option.getAttribute(idAttr.name);
    const price = parseFloat(option.getAttribute(priceAttr.name)) || 0;
    const uniqueId = `${currentStep}_${optionId}`;

    const canDeselect = option.dataset.canDeselect === 'true';

    if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
      option.parentElement.classList.add('selected');
    }

    option.addEventListener('click', () => {
    const alreadySelected = option.parentElement.classList.contains('selected');

    /* ◼︎ 1. Clic sur la même option déjà sélectionnée */
    if (alreadySelected) {
      // → option obligatoire : on ignore le clic
      if (!canDeselect) return;

      // → option facultative : on la retire
      option.parentElement.classList.remove('selected');
      allSelectedOptions = allSelectedOptions.filter(opt => opt.id !== uniqueId);
      console.log(`➖ Option retirée : ${uniqueId}`);

    /* ◼︎ 2. Clic sur une autre option de la même étape */
    } else {
      // On remet à zéro la sélection de l’étape courante
      document.querySelectorAll("img").forEach(img =>
        img.parentElement.classList.remove('selected')
      );
      allSelectedOptions = allSelectedOptions.filter(opt =>
        !opt.id.startsWith(`${currentStep}_`)
      );
      clearOtherPathOptions();

      // On ajoute la nouvelle option
      allSelectedOptions.push({ id: uniqueId, price });
      option.parentElement.classList.add('selected');
      console.log(`➕ Option ajoutée : ${uniqueId} (${price} €)`);
    }

    sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
    updateTotal();
  });

}); 

  clearOtherPathOptions();
  updateTotal();
});