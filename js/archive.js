document.addEventListener('DOMContentLoaded', function() {
    // Gestion des confirmations
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Adaptation responsive
    function adaptLayout() {
        const table = document.querySelector('.archive-table');
        if (!table) return;

        if (window.innerWidth < 768) {
            // Masquer colonne enseignant sur mobile
            table.querySelectorAll('th:nth-child(3), td:nth-child(3)').forEach(cell => {
                cell.style.display = 'none';
            });
        } else {
            // Tout afficher sur desktop
            table.querySelectorAll('th, td').forEach(cell => {
                cell.style.display = '';
            });
        }
    }

    // Tooltips
    document.querySelectorAll('[title]').forEach(el => {
        el.addEventListener('mouseenter', showTooltip);
        el.addEventListener('mouseleave', hideTooltip);
    });

    function showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = this.title;
        document.body.appendChild(tooltip);
        
        const rect = this.getBoundingClientRect();
        tooltip.style.left = `${rect.left + window.scrollX}px`;
        tooltip.style.top = `${rect.top + window.scrollY - 35}px`;
        
        this._tooltip = tooltip;
    }

    function hideTooltip() {
        if (this._tooltip) {
            this._tooltip.remove();
        }
    }

    // Initialisation
    window.addEventListener('resize', adaptLayout);
    adaptLayout();
});