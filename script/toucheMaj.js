document.querySelector('input[type=password]').addEventListener('keyup', function (keyboardEvent) {
    const capsLockOn = keyboardEvent.getModifierState && keyboardEvent.getModifierState('CapsLock');
    const shiftOn = keyboardEvent.getModifierState && keyboardEvent.getModifierState('Shift');

    const capsWarning = document.getElementById('caps-lock-warning');
    const shiftWarning = document.getElementById('shift-warning');

    // Gère l'affichage des deux avertissements
    capsWarning.style.display = capsLockOn ? 'block' : 'none';
    shiftWarning.style.display = shiftOn ? 'block' : 'none';
});

// Masque les deux avertissements quand le champ perd le focus
document.querySelector('input[type=password]').addEventListener('blur', function () {
    document.getElementById('caps-lock-warning').style.display = 'none';
    document.getElementById('shift-warning').style.display = 'none';
});
