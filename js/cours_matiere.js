document.addEventListener('DOMContentLoaded', function() {
    // Animation du bouton retour
    const backButton = document.querySelector('.back-button');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.add('animate__animated', 'animate__fadeOutLeft');
            setTimeout(() => {
                window.history.back();
            }, 500);
        });
    }

    // Effet de hover sur les cartes de cours
    const courseItems = document.querySelectorAll('.course-item');
    courseItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.08)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });

    // Confirmation de téléchargement
    const downloadLinks = document.querySelectorAll('.download-link');
    downloadLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const fileName = this.parentElement.querySelector('.file-name').textContent;
            if (!confirm(`Télécharger le fichier : ${fileName} ?`)) {
                e.preventDefault();
            }
        });
    });

    // Animation au scroll
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    courseItems.forEach(item => {
        observer.observe(item);
    });
});