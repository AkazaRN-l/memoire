document.addEventListener("DOMContentLoaded", function () {
    // Sélection des éléments HTML
    let password = document.getElementById("mot_de_passe");
    let confirmPassword = document.getElementById("confirmer_mot_de_passe");
    let errorMsg = document.getElementById("password-error");
    let submitBtn = document.querySelector(".btn");
    let dateNaissanceInput = document.getElementById("date_naissance");
    let ageInput = document.getElementById("age");

    // Vérification en temps réel des mots de passe
    confirmPassword.addEventListener("input", function () {
        if (password.value !== confirmPassword.value) {
            errorMsg.textContent = "❌ Les mots de passe ne correspondent pas.";
            errorMsg.style.color = "red";
            submitBtn.disabled = true; // Désactiver le bouton
        } else {
            errorMsg.textContent = "✔ Mot de passe confirmé.";
            errorMsg.style.color = "green";
            submitBtn.disabled = false; // Activer le bouton
        }
    });

    // Calcul automatique de l'âge
    dateNaissanceInput.addEventListener("change", function () {
        let dateNaissance = new Date(this.value);
        let today = new Date();
        let age = today.getFullYear() - dateNaissance.getFullYear();
        let monthDiff = today.getMonth() - dateNaissance.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dateNaissance.getDate())) {
            age--;
        }

        ageInput.value = age;
    });

    // Prévisualisation de l'image de profil
    let photoInput = document.getElementById("photo");
    let photoPreview = document.getElementById("photo-preview");

    photoInput.addEventListener("change", function () {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                photoPreview.innerHTML = `<img src="${e.target.result}" alt="Photo de profil">`;
            };
            reader.readAsDataURL(file);
        }
    });
});




document.addEventListener("DOMContentLoaded", function () {
    const motDePasse = document.getElementById("mot_de_passe");
    const erreurLongueur = document.getElementById("length-error");

    motDePasse.addEventListener("input", function () {
        if (motDePasse.value.length < 8) {
            erreurLongueur.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            erreurLongueur.textContent = "";
        }
    });
});
