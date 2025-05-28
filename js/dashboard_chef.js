// dashboard_chef.js

document.addEventListener('DOMContentLoaded', function() {
    // Animation des lignes du tableau
    const tableRows = document.querySelectorAll('table tr');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        row.style.transition = `all 0.3s ease ${index * 0.05}s`;
        
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 100);
    });

    // Confirmation avant suppression
    const deleteButtons = document.querySelectorAll('.btn-supprimer');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });

    // Highlight de l'élément actif dans la sidebar
    const currentLevel = new URL(window.location.href).searchParams.get('niveau') || 'Licence I';
    const sidebarLinks = document.querySelectorAll('.sidebar nav ul li a');
    
    sidebarLinks.forEach(link => {
        if (link.textContent.trim() === currentLevel) {
            link.style.background = 'rgba(255, 255, 255, 0.15)';
            link.style.borderLeft = '3px solid var(--accent-color)';
            link.style.color = 'white';
        }
    });

    // Tooltip pour les photos de profil
    const profilePhotos = document.querySelectorAll('.photo-profil');
    profilePhotos.forEach(photo => {
        photo.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'profile-tooltip';
            tooltip.textContent = 'Voir le profil complet';
            document.body.appendChild(tooltip);
            
            const rect = photo.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width/2 - tooltip.offsetWidth/2}px`;
            tooltip.style.top = `${rect.top - 40}px`;
            
            photo.addEventListener('mouseleave', function() {
                tooltip.remove();
            });
        });
    });
});

// Style dynamique pour le tooltip
const style = document.createElement('style');
style.textContent = `
    .profile-tooltip {
        position: fixed;
        background: var(--primary-dark);
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        pointer-events: none;
        z-index: 1000;
        opacity: 0;
        transform: translateY(5px);
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .photo-profil:hover + .profile-tooltip, 
    .profile-tooltip:hover {
        opacity: 1;
        transform: translateY(0);
    }
`;
document.head.appendChild(style);

// Fonction pour gérer le texte tronqué
function setupTableTextOverflow() {
    const cells = document.querySelectorAll('td');
    
    cells.forEach(cell => {
        if (cell.scrollWidth > cell.clientWidth) {
            cell.setAttribute('data-fulltext', cell.textContent);
            cell.style.cursor = 'help';
        }
    });
}

// Mise à jour lors du redimensionnement
window.addEventListener('resize', setupTableTextOverflow);

// Appel initial
document.addEventListener('DOMContentLoaded', function() {
    setupTableTextOverflow();
    
    // Version alternative avec tooltip persistant
    const tooltip = document.createElement('div');
    tooltip.className = 'table-tooltip';
    document.body.appendChild(tooltip);
    
    document.querySelectorAll('td').forEach(td => {
        td.addEventListener('mouseenter', function(e) {
            if (this.scrollWidth > this.clientWidth) {
                tooltip.textContent = this.textContent;
                tooltip.style.display = 'block';
                tooltip.style.left = `${e.pageX + 100}px`;
                tooltip.style.top = `${e.pageY + 100}px`;
            }
        });
        
        td.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    });
    
    // Faire suivre le tooltip avec la souris
    document.addEventListener('mousemove', function(e) {
        const tooltip = document.querySelector('.table-tooltip');
        if (tooltip.style.display === 'block') {
            tooltip.style.left = `${e.pageX + 20}px`;
            tooltip.style.top = `${e.pageY + 20}px`;
        }
    });
});

// Style pour le tooltip persistant
const tooltipStyle = document.createElement('style');
tooltipStyle.textContent = `
    .table-tooltip {
        position: absolute;
        background: var(--primary-dark);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.9rem;
        z-index: 1000;
        display: none;
        max-width: 300px;
        word-wrap: break-word;
        white-space: normal;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
`;
document.head.appendChild(tooltipStyle);




document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    // Empêche le scroll du sidebar d'affecter le main et vice versa
    sidebar.addEventListener('wheel', function(e) {
        const delta = Math.sign(e.deltaY);
        const isScrollUp = delta < 0;
        const isScrollDown = delta > 0;
        
        const isAtTop = this.scrollTop === 0;
        const isAtBottom = this.scrollHeight - this.scrollTop === this.clientHeight;
        
        if ((isScrollUp && isAtTop) || (isScrollDown && isAtBottom)) {
            e.preventDefault();
        }
    });

    mainContent.addEventListener('wheel', function(e) {
        const delta = Math.sign(e.deltaY);
        const isScrollUp = delta < 0;
        const isScrollDown = delta > 0;
        
        const isAtTop = this.scrollTop === 0;
        const isAtBottom = this.scrollHeight - this.scrollTop === this.clientHeight;
        
        if ((isScrollUp && isAtTop) || (isScrollDown && isAtBottom)) {
            e.preventDefault();
        }
    });
});


