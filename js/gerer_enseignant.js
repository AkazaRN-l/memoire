// gerer_enseignants.js

document.addEventListener('DOMContentLoaded', function() {
    // Animation des lignes du tableau
    const tableRows = document.querySelectorAll('.teacher-table tbody tr');
    tableRows.forEach((row, index) => {
      row.style.opacity = '0';
      row.style.transform = 'translateY(10px)';
      row.style.transition = `all 0.4s ease ${index * 0.05}s`;
      
      setTimeout(() => {
        row.style.opacity = '1';
        row.style.transform = 'translateY(0)';
      }, 50);
    });
  
    // Confirmation avant suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        if (!confirm('Confirmer la suppression de cet enseignant ?')) {
          e.preventDefault();
        }
      });
    });
  
    // Effet de focus sur les champs du formulaire
    const formInputs = document.querySelectorAll('.form-control');
    formInputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.querySelector('label').style.color = 'var(--accent-gold)';
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.querySelector('label').style.color = 'var(--primary-light)';
      });
    });
  });