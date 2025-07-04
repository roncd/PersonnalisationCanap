document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('mdp');
  const capsLockWarning = document.getElementById('caps-lock-warning');
  const shiftWarning = document.getElementById('shift-warning');

  passwordInput.addEventListener('keydown', function (event) {
    if (event.getModifierState && event.getModifierState('CapsLock')) {
      capsLockWarning.style.display = 'block';
    } else {
      capsLockWarning.style.display = 'none';
    }

    if (event.shiftKey) {
      shiftWarning.style.display = 'block';
    } else {
      shiftWarning.style.display = 'none';
    }
  });

  passwordInput.addEventListener('keyup', function (event) {
    if (!(event.getModifierState && event.getModifierState('CapsLock'))) {
      capsLockWarning.style.display = 'none';
    }

    if (!event.shiftKey) {
      shiftWarning.style.display = 'none';
    }
  });
});