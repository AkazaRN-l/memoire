// envoyer_information.js

document.addEventListener('DOMContentLoaded', function() {
  // Sélection des éléments
  const form = document.querySelector('form');
  const inputs = document.querySelectorAll('.form-control');
  const fileInput = document.querySelector('input[type="file"]');
  const successMessage = document.querySelector('.success-message');
  const btnGold = document.querySelector('.btn-gold');

  // Animation au chargement
  setTimeout(() => {
      document.body.style.opacity = '1';
  }, 100);

  // Effet de vague sur le bouton doré
  if (btnGold) {
      btnGold.addEventListener('click', function(e) {
          if (!form.checkValidity()) return;
          
          const x = e.clientX - e.target.getBoundingClientRect().left;
          const y = e.clientY - e.target.getBoundingClientRect().top;
          
          const ripple = document.createElement('span');
          ripple.classList.add('ripple');
          ripple.style.left = `${x}px`;
          ripple.style.top = `${y}px`;
          
          this.appendChild(ripple);
          
          setTimeout(() => {
              ripple.remove();
          }, 1000);
      });
  }

  // Animation des inputs au focus
  inputs.forEach(input => {
      const label = input.previousElementSibling;
      
      if (!label || label.tagName !== 'LABEL') return;
      
      input.addEventListener('focus', () => {
          label.style.transform = 'translateY(-5px)';
          label.style.color = '#d4af37';
          label.style.fontSize = '0.85rem';
      });
      
      input.addEventListener('blur', () => {
          if (!input.value) {
              label.style.transform = '';
              label.style.color = '';
              label.style.fontSize = '';
          }
      });
  });

  // Affichage du nom du fichier sélectionné
  if (fileInput) {
      fileInput.addEventListener('change', function() {
          const fileName = this.files[0]?.name || 'Aucun fichier sélectionné';
          const fileLabel = this.previousElementSibling;
          
          if (fileLabel && fileLabel.tagName === 'LABEL') {
              const fileInfo = document.createElement('span');
              fileInfo.className = 'file-info';
              fileInfo.textContent = fileName;
              fileInfo.style.display = 'block';
              fileInfo.style.marginTop = '0.5rem';
              fileInfo.style.fontSize = '0.85rem';
              fileInfo.style.color = '#d4af37';
              
              // Supprime l'ancienne info si elle existe
              const oldFileInfo = fileLabel.nextElementSibling;
              if (oldFileInfo && oldFileInfo.classList.contains('file-info')) {
                  oldFileInfo.remove();
              }
              
              fileLabel.insertAdjacentElement('afterend', fileInfo);
          }
      });
  }

  // Disparition élégante du message de succès
  if (successMessage) {
      setTimeout(() => {
          successMessage.style.transition = 'opacity 0.5s ease-out';
          successMessage.style.opacity = '0';
          
          setTimeout(() => {
              successMessage.remove();
          }, 500);
      }, 3000);
  }

  // Validation en temps réel
  form.addEventListener('submit', function(e) {
      let isValid = true;
      
      inputs.forEach(input => {
          if (!input.value.trim()) {
              input.style.borderColor = '#e74c3c';
              isValid = false;
          } else {
              input.style.borderColor = '#ddd';
          }
      });
      
      if (!isValid) {
          e.preventDefault();
          
          // Animation d'erreur
          form.style.animation = 'shake 0.5s';
          setTimeout(() => {
              form.style.animation = '';
          }, 500);
      }
  });

  // Effet de parallaxe subtil
  window.addEventListener('mousemove', function(e) {
      const x = e.clientX / window.innerWidth;
      const y = e.clientY / window.innerHeight;
      
      const header = document.querySelector('.header-animation');
      if (header) {
          header.style.transform = `translate(-${x * 10}px, -${y * 10}px)`;
      }
  });

  // Ajout du style pour l'effet ripple
  const style = document.createElement('style');
  style.textContent = `
      @keyframes shake {
          0%, 100% { transform: translateX(0); }
          10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
          20%, 40%, 60%, 80% { transform: translateX(5px); }
      }
      
      .ripple {
          position: absolute;
          background: rgba(255, 255, 255, 0.5);
          border-radius: 50%;
          transform: scale(0);
          animation: ripple 0.6s linear;
          pointer-events: none;
      }
      
      @keyframes ripple {
          to {
              transform: scale(4);
              opacity: 0;
          }
      }
  `;
  document.head.appendChild(style);
});

// Transition d'entrée pour le body
document.body.style.opacity = '0';
document.body.style.transition = 'opacity 0.5s ease-in';