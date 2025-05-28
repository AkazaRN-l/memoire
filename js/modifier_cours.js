// Gestion de l'affichage du fichier sélectionné
document.getElementById('fichier').addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const file = this.files[0];
        const fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        
        // Créer ou mettre à jour l'affichage
        let fileDisplay = document.querySelector('.current-file-display');
        if (!fileDisplay) {
            fileDisplay = document.createElement('div');
            fileDisplay.className = 'current-file-display';
            fileDisplay.innerHTML = `
                <i class="fas fa-file-alt file-icon"></i>
                <div class="file-details">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${fileSize}</span>
                </div>
                <button type="button" class="file-clear-btn">
                    <i class="fas fa-times"></i>
                </button>
            `;
            document.querySelector('.file-upload-wrapper').after(fileDisplay);
        } else {
            fileDisplay.querySelector('.file-name').textContent = file.name;
            fileDisplay.querySelector('.file-size').textContent = fileSize;
        }
        
        // Ajouter l'animation
        this.classList.add('has-file');
    }
});

// Gestion du bouton de suppression
document.addEventListener('click', function(e) {
    if (e.target.closest('.file-clear-btn')) {
        const fileInput = document.getElementById('fichier');
        fileInput.value = '';
        fileInput.classList.remove('has-file');
        document.querySelector('.current-file-display')?.remove();
    }
});