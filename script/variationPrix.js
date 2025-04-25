// VARIATION PRIX EN FONCTION DU CHEMIN BOIS OU TISSU

document.addEventListener('DOMContentLoaded', () => {
let totalPrice = 0;

const currentStep = document.body.getAttribute('data-current-step');
const userId = document.body.getAttribute('data-user-id');
if (!userId || !currentStep) return;

const isTissu = currentStep.includes('tissu');
const isBois = currentStep.includes('bois');
const stepKey = currentStep.split('-')[0];

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

  if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
    option.parentElement.classList.add('selected');
  }

  option.addEventListener('click', () => {
    console.log(`🖱️ Option cliquée : ID=${optionId}, Prix=${price} €`);

    imgElements.forEach(opt => opt.parentElement.classList.remove('selected'));
    allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));
    clearOtherPathOptions();

    allSelectedOptions.push({ id: uniqueId, price: price });
    option.parentElement.classList.add('selected');

    console.log(`➕ Option ajoutée : ${uniqueId} (${price} €)`);

    sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
    updateTotal();
  });
});

clearOtherPathOptions();
updateTotal();
});


