document.querySelectorAll('.accordeon-header').forEach(header => {
  const item = header.parentElement;
  const content = item.querySelector('.accordeon-content');
  const icon = header.querySelector('.accordeon-icon');

  header.addEventListener('click', () => {
    const isOpen = item.classList.contains('active');

    if (isOpen) {
      content.style.maxHeight = content.scrollHeight + 'px';
      requestAnimationFrame(() => {
        content.style.maxHeight = '0';
      });
      item.classList.remove('active');
      icon.textContent = '+';
    } else {
      content.style.maxHeight = '0';
      item.classList.add('active');
      requestAnimationFrame(() => {
        content.style.maxHeight = content.scrollHeight + 'px';
      });
      icon.textContent = '−';
    }
  });

  content.addEventListener('transitionend', () => {
    if (item.classList.contains('active')) {
      content.style.maxHeight = 'none';
    }
  });
});