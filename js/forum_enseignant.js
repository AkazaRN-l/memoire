document.addEventListener("DOMContentLoaded", function() {
    const selectNiveau = document.querySelector('select[name="niveau"]');
    const selectMatiere = document.querySelector('select[name="matiere_id"]');
    const inputFichier = document.getElementById('fichier');
    const nomFichierSpan = document.getElementById('nom-fichier');

    if (selectNiveau) {
        selectNiveau.addEventListener('change', () => {
            const niveau = encodeURIComponent(selectNiveau.value);
            window.location.href = 'forum_enseignant.php?niveau=' + niveau;
        });
    }

    if (selectMatiere) {
        selectMatiere.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            const niveau = params.get('niveau');
            const matiere_id = selectMatiere.value;
            if (niveau && matiere_id) {
                window.location.href = `forum_enseignant.php?niveau=${encodeURIComponent(niveau)}&matiere_id=${matiere_id}`;
            }
        });
    }

    if (inputFichier && nomFichierSpan) {
        inputFichier.addEventListener('change', () => {
            const nom = inputFichier.files.length > 0 ? inputFichier.files[0].name : '';
            nomFichierSpan.textContent = nom;
        });
    }
});
