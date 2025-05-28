// Animation d'entrée
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.univ-header');
    const form = document.querySelector('.login-form');
    
    // Animation du header
    header.style.opacity = '0';
    header.style.transform = 'translateY(-20px)';
    header.style.transition = 'all 0.8s ease-out';
    
    // Animation du formulaire
    form.style.opacity = '0';
    form.style.transform = 'scale(0.95)';
    form.style.transition = 'all 0.6s ease 0.3s';
    
    // Déclenchement après un léger délai
    setTimeout(() => {
        header.style.opacity = '1';
        header.style.transform = 'translateY(0)';
        form.style.opacity = '1';
        form.style.transform = 'scale(1)';
    }, 100);
});