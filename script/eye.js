
  const input = document.querySelector('#motdepasse');
  const eyeBtn = document.querySelector('#eyeBtn');

  eyeBtn.addEventListener('click', () => {
    if (input.type === 'password') {
      input.type = 'text';
      eyeBtn.setAttribute('src', '../../medias/iconmonstr-eye-off-thin.png');
    } else {
      input.type = 'password';
      eyeBtn.setAttribute('src', '../../medias/eye.svg');
    }
  });
