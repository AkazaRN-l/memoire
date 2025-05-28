console.log("Script chargé !");

// Sélection des éléments
const fileInput = document.getElementById('cours-fichier');
const fileDetails = document.getElementById('file-details');
const fileNameDisplay = document.getElementById('display-filename');
const fileSizeDisplay = document.getElementById('display-filesize');
const fileIcon = document.getElementById('file-icon');
const fileClearBtn = document.getElementById('file-clear-btn');

// Masquer par défaut (au cas où)
fileDetails.style.display = 'none';

// Écouteur d'événement pour le changement de fichier
fileInput.addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        
        // Afficher la section de détails
        fileDetails.style.display = 'flex';
        
        // Mettre à jour le nom du fichier
        fileNameDisplay.textContent = file.name;
        
        // Mettre à jour la taille du fichier
        fileSizeDisplay.textContent = formatFileSize(file.size);
        
        // Mettre à jour l'icône
        updateFileIcon(file);
    }
});

// Écouteur pour le bouton de suppression
fileClearBtn.addEventListener('click', function(e) {
    e.preventDefault();
    resetFileInput();
});

// Fonction pour formater la taille du fichier
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const units = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + units[i];
}

// Fonction pour mettre à jour l'icône
function updateFileIcon(file) {
    // Réinitialiser les classes
    fileIcon.className = 'fas file-icon';
    
    // Déterminer le type de fichier
    if (file.type.match('image.*')) {
        fileIcon.classList.add('fa-file-image');
        fileIcon.style.color = '#3498db';
    } 
    else if (file.type.match('audio.*') || file.name.match(/\.(mp3|wav|ogg)$/i)) {
        fileIcon.classList.add('fa-file-audio');
        fileIcon.style.color = '#9b59b6';
    } 
    else if (file.type.match('video.*') || file.name.match(/\.(mp4|avi|mov)$/i)) {
        fileIcon.classList.add('fa-file-video');
        fileIcon.style.color = '#e74c3c';
    } 
    else if (file.type === 'application/pdf' || file.name.match(/\.pdf$/i)) {
        fileIcon.classList.add('fa-file-pdf');
        fileIcon.style.color = '#e74c3c';
    } 
    else if (file.type.match('word') || file.name.match(/\.(doc|docx)$/i)) {
        fileIcon.classList.add('fa-file-word');
        fileIcon.style.color = '#2c3e50';
    }
    else {
        fileIcon.classList.add('fa-file-alt');
        fileIcon.style.color = '#7f8c8d';
    }
}

// Fonction pour réinitialiser le champ fichier
function resetFileInput() {
    fileInput.value = '';
    fileDetails.style.display = 'none';
    fileNameDisplay.textContent = 'Aucun fichier sélectionné';
    fileSizeDisplay.textContent = '';
}

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce cours ? Cette action est irréversible.')) {
            e.preventDefault();
        }
    });
});




document.addEventListener('DOMContentLoaded', function() {
    const selectMatiere = document.getElementById('select-matiere');
    
    if (selectMatiere) {
        // Animation lors du focus
        selectMatiere.addEventListener('focus', function() {
            this.parentNode.classList.add('is-focused');
        });
        
        selectMatiere.addEventListener('blur', function() {
            this.parentNode.classList.remove('is-focused');
        });
        
        // Validation en temps réel
        selectMatiere.addEventListener('change', function() {
            if (this.value === "") {
                this.parentNode.classList.add('is-invalid');
            } else {
                this.parentNode.classList.remove('is-invalid');
            }
        });
        
        // Effet visuel lors de la sélection
        selectMatiere.addEventListener('mouseenter', function() {
            this.style.borderColor = '#a5b4fc';
        });
        
        selectMatiere.addEventListener('mouseleave', function() {
            if (!this.parentNode.classList.contains('is-invalid')) {
                this.style.borderColor = '#e0e6ed';
            }
        });
    }
    
    // Si tu veux transformer le select en select2 (nécessite la librairie Select2)
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#select-matiere').select2({
            placeholder: "-- Sélectionner une matière --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#select-matiere').parent()
        });
    }
});




