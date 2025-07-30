function animateValue(element, target, duration, isDecimal = false, showPlus = false, isPercent = false, isNotation = false) {
  const startTime = performance.now();

  function update(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const value = isDecimal
      ? (progress * target).toFixed(1)
      : Math.floor(progress * target);

    const prefix = showPlus && !isDecimal && !isNotation ? '+' : '';
    const suffix = isNotation ? '/5' : isPercent ? '%' : '';

    element.textContent = `${prefix}${value}${suffix}`;

    if (progress < 1) {
      requestAnimationFrame(update);
    }
  }

  requestAnimationFrame(update);
}

// Observer pour déclencher l’animation au scroll
const observer = new IntersectionObserver((entries, obs) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const elements = document.querySelectorAll('.stats-list strong');
      elements.forEach(el => {
        const target = parseFloat(el.getAttribute('data-target'));
        const isDecimal = el.hasAttribute('data-decimal');
        const showPlus = el.hasAttribute('data-plus');
        const isPercent = el.hasAttribute('data-percent');
        const isNotation = el.hasAttribute('data-notation');

        animateValue(el, target, 1500, isDecimal, showPlus, isPercent, isNotation);
      });
      obs.disconnect(); // Une seule fois
    }
  });
}, { threshold: 0.6 });

observer.observe(document.querySelector('.stats-section'));
