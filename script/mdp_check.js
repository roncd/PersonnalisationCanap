const mdpInput = document.getElementById("mdp");
const checklist = document.querySelector(".password-requirements");

// Affiche la checklist au focus
mdpInput.addEventListener("focus", () => {
    checklist.classList.add("show");
});

// Cache la checklist si on sort du champ ET qu’on ne clique pas dessus
mdpInput.addEventListener("blur", () => {
    setTimeout(() => {
        // Ne pas masquer si on clique dans la checklist
        if (!document.activeElement.closest(".password-requirements")) {
            checklist.classList.remove("show");
        }
    }, 100);
});



const passwordInput = document.getElementById("mdp");
const strengthText = document.getElementById("password-strength-text");

passwordInput.addEventListener("input", function () {
    const value = passwordInput.value;

    const hasLength = value.length >= 8;
    const hasUppercase = /[A-Z]/.test(value);
    const hasLowercase = /[a-z]/.test(value);
    const hasNumber = /[0-9]/.test(value);
    const hasSpecialChar = /[^A-Za-z0-9]/.test(value);

    const criteriaCount = [hasLength, hasUppercase, hasLowercase, hasNumber, hasSpecialChar].filter(Boolean).length;

    // Met à jour visuellement la force du mot de passe
    passwordInput.classList.remove("input-weak", "input-medium", "input-strong");
    if (criteriaCount <= 2) {
        passwordInput.classList.add("input-weak");
        strengthText.textContent = "Mot de passe faible";
        strengthText.style.color = "red";
    } else if (criteriaCount <= 4) {
        passwordInput.classList.add("input-medium");
        strengthText.textContent = "Mot de passe moyen";
        strengthText.style.color = "orange";
    } else {
        passwordInput.classList.add("input-strong");
        strengthText.textContent = "Mot de passe fort";
        strengthText.style.color = "green";
    }

    // Met à jour la checklist
    toggleClass("check-length", hasLength);
    toggleClass("check-uppercase", hasUppercase);
    toggleClass("check-lowercase", hasLowercase);
    toggleClass("check-number", hasNumber);
    toggleClass("check-special", hasSpecialChar);
});

// Fonction pour appliquer ou retirer la classe "valid" sur les éléments de checklist
function toggleClass(id, isValid) {
    const item = document.getElementById(id);
    if (isValid) {
        item.classList.add("valid");
    } else {
        item.classList.remove("valid");
    }
}




const password = document.getElementById('mdp');
const confirmPassword = document.getElementById('confirm-password');
const matchMessage = document.getElementById('match-message');

function checkPasswordMatch() {
    const pwd = password.value;
    const confirmPwd = confirmPassword.value;

    if (confirmPwd.length === 0) {
        matchMessage.style.display = 'none';
        confirmPassword.classList.remove('input-error', 'input-valid');
        confirmPassword.setCustomValidity('');
        return;
    }

    if (pwd === confirmPwd) {
        matchMessage.style.display = 'none';
        confirmPassword.classList.remove('input-error');
        confirmPassword.classList.add('input-valid');
        confirmPassword.setCustomValidity('');
    } else {
        matchMessage.style.display = 'block';
        confirmPassword.classList.remove('input-valid');
        confirmPassword.classList.add('input-error');
        confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas.');
    }
}

password.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);

