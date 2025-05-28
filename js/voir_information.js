/**
 * SYSTEME DE GESTION DES INFORMATIONS - DASHBOARD CHEF DE MENTION
 * Fonctionnalités :
 * - Gestion des formulaires d'édition
 * - Filtrage automatique par niveau
 * - Confirmation avant suppression
 * - Messages flash avec auto-dismiss
 * - Effets visuels avancés
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants
    initSystem();
});

function initSystem() {
    // 1. Initialisation des formulaires d'édition
    initEditForms();
    
    // 2. Configuration du filtre automatique
    initAutoFilter();
    
    // 3. Gestion des confirmations de suppression
    initDeleteConfirmations();
    
    // 4. Gestion des messages flash avec auto-dismiss
    initFlashMessages();
    
    // 5. Effets visuels supplémentaires
    initVisualEffects();
}

// --------------------------------------------------
// 1. GESTION DES FORMULAIRES D'ÉDITION
// --------------------------------------------------
function initEditForms() {
    // Cache tous les formulaires au chargement
    document.querySelectorAll('.edit-form').forEach(form => {
        form.style.display = 'none';
    });
    
    // Ajoute les écouteurs d'événements
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const cardId = this.closest('.info-card').dataset.id;
            toggleEditForm(cardId, true);
        });
    });
    
    document.querySelectorAll('.btn-cancel').forEach(button => {
        button.addEventListener('click', function() {
            const cardId = this.closest('.info-card').dataset.id;
            toggleEditForm(cardId, false);
        });
    });
}

function toggleEditForm(cardId, show = true) {
    const card = document.querySelector(`.info-card[data-id="${cardId}"]`);
    if (!card) return;
    
    const form = card.querySelector('.edit-form');
    const cardBody = card.querySelector('.card-body');
    
    if (show) {
        // Ferme d'abord les autres formulaires ouverts
        closeAllEditForms();
        
        // Affiche le formulaire avec animation
        form.style.display = 'block';
        cardBody.style.display = 'none';
        animateFormAppearance(form);
        
        // Scroll doux vers le formulaire
        smoothScrollToForm(form);
    } else {
        // Cache le formulaire avec animation
        animateFormDisappearance(form, cardBody);
    }
}

function closeAllEditForms() {
    document.querySelectorAll('.edit-form').forEach(form => {
        form.style.display = 'none';
    });
    document.querySelectorAll('.card-body').forEach(body => {
        body.style.display = 'block';
    });
}

function animateFormAppearance(form) {
    form.style.animation = 'none';
    void form.offsetWidth; // Trigger reflow
    form.style.animation = 'fadeIn 0.4s cubic-bezier(0.22, 1, 0.36, 1)';
}

function animateFormDisappearance(form, cardBody) {
    form.style.animation = 'fadeOut 0.3s ease-out';
    setTimeout(() => {
        form.style.display = 'none';
        cardBody.style.display = 'block';
    }, 300);
}

function smoothScrollToForm(form) {
    setTimeout(() => {
        form.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'start'
        });
    }, 50);
}

// --------------------------------------------------
// 2. FILTRE AUTOMATIQUE PAR NIVEAU
// --------------------------------------------------
function initAutoFilter() {
    const niveauFilter = document.getElementById('niveau-filter');
    if (!niveauFilter) return;
    
    niveauFilter.addEventListener('change', function() {
        showLoadingIndicator();
        setTimeout(() => this.form.submit(), 400);
    });
}

function showLoadingIndicator() {
    const existingLoader = document.querySelector('.filter-loading');
    if (existingLoader) return;
    
    const loader = document.createElement('div');
    loader.className = 'filter-loading';
    loader.innerHTML = `
        <div class="spinner"></div>
        <span>Chargement...</span>
    `;
    
    const filterBar = document.querySelector('.filter-bar');
    if (filterBar) {
        filterBar.appendChild(loader);
        
        // Animation d'apparition
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.transition = 'opacity 0.3s ease';
            loader.style.opacity = '1';
        }, 10);
    }
}

// --------------------------------------------------
// 3. CONFIRMATION AVANT SUPPRESSION
// --------------------------------------------------
function initDeleteConfirmations() {
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirmAction('Supprimer cette information ?')) {
                e.preventDefault();
            }
        });
    });
}

function confirmAction(message) {
    return confirm(message);
}

// --------------------------------------------------
// 4. GESTION DES MESSAGES FLASH AVEC AUTO-DISMISS
// --------------------------------------------------
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.alert');
    if (flashMessages.length === 0) return;
    
    flashMessages.forEach(message => {
        // Animation d'entrée
        message.style.animation = 'slideIn 0.5s cubic-bezier(0.22, 1, 0.36, 1)';
        
        // Configuration de l'auto-dismiss
        setupAutoDismiss(message);
        
        // Bouton de fermeture manuelle
        addCloseButton(message);
    });
}

function setupAutoDismiss(message) {
    let remainingTime = 5000; // 5 secondes
    const progressBar = document.createElement('div');
    progressBar.className = 'progress-bar';
    
    message.appendChild(progressBar);
    
    // Animation de la barre de progression
    progressBar.style.animation = `progress ${remainingTime}ms linear`;
    
    // Compte à rebours
    const dismissTimer = setTimeout(() => {
        dismissMessage(message);
    }, remainingTime);
    
    // Arrêt du timer si la souris survole le message
    message.addEventListener('mouseenter', () => {
        clearTimeout(dismissTimer);
        progressBar.style.animationPlayState = 'paused';
    });
    
    // Reprise du timer quand la souris quitte
    message.addEventListener('mouseleave', () => {
        remainingTime = 2000; // Réduit à 2 secondes après hover
        progressBar.style.animation = `progress ${remainingTime}ms linear`;
        
        setTimeout(() => {
            dismissMessage(message);
        }, remainingTime);
    });
}

function dismissMessage(message) {
    message.style.animation = 'fadeOut 0.5s ease';
    message.style.opacity = '0';
    
    setTimeout(() => {
        message.style.maxHeight = '0';
        message.style.margin = '0';
        message.style.padding = '0';
        
        setTimeout(() => {
            message.remove();
        }, 500);
    }, 500);
}

function addCloseButton(message) {
    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn';
    closeBtn.innerHTML = '&times;';
    closeBtn.title = 'Fermer';
    
    closeBtn.addEventListener('click', () => {
        dismissMessage(message);
    });
    
    message.appendChild(closeBtn);
}

// --------------------------------------------------
// 5. EFFETS VISUELS AVANCÉS
// --------------------------------------------------
function initVisualEffects() {
    // Effets sur les cartes
    document.querySelectorAll('.info-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
            
            const badge = card.querySelector('.niveau-badge');
            if (badge) {
                badge.style.transform = 'scale(1.1) rotate(2deg)';
            }
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
            
            const badge = card.querySelector('.niveau-badge');
            if (badge) {
                badge.style.transform = 'scale(1) rotate(0)';
            }
        });
    });
    
    // Effets sur les boutons
    document.querySelectorAll('.btn-edit, .btn-delete, .btn-save, .btn-cancel').forEach(btn => {
        btn.addEventListener('mousedown', () => {
            btn.style.transform = 'scale(0.95)';
        });
        
        btn.addEventListener('mouseup', () => {
            btn.style.transform = 'scale(1)';
        });
        
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'scale(1)';
        });
    });
}

// --------------------------------------------------
// ANIMATIONS CSS DYNAMIQUES
// --------------------------------------------------
function addDynamicAnimations() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
    `;
    document.head.appendChild(style);
}

// Initialise les animations dynamiques
addDynamicAnimations();


/**
 * Affiche une notification stylée
 * @param {string} message - Le message à afficher
 * @param {string} type - 'success' ou 'error'
 * @param {number} duration - Durée en ms (défaut: 5000ms)
 */
function showNotification(message, type = 'success', duration = 5000) {
    const notificationCenter = document.querySelector('.notification-center') || createNotificationCenter();
    
    // Création de la notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Icône selon le type
    const icon = type === 'success' ? 
        '<i class="fas fa-check-circle"></i>' : 
        '<i class="fas fa-exclamation-circle"></i>';
    
    // Structure de la notification
    notification.innerHTML = `
        <div class="notification-icon">${icon}</div>
        <div class="notification-content">${message}</div>
        <button class="notification-close">&times;</button>
        <div class="notification-progress"></div>
    `;
    
    // Animation d'entrée
    notificationCenter.appendChild(notification);
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Barre de progression
    const progressBar = notification.querySelector('.notification-progress');
    progressBar.style.animation = `progress ${duration}ms linear forwards`;
    
    // Fermeture au click
    notification.querySelector('.notification-close').addEventListener('click', () => {
        dismissNotification(notification);
    });
    
    // Auto-dismiss
    if (duration > 0) {
        setTimeout(() => {
            dismissNotification(notification);
        }, duration);
    }
    
    return notification;
}

/**
 * Ferme une notification avec animation
 */
function dismissNotification(notification) {
    notification.style.animation = 'slideOutRight 0.5s forwards';
    notification.addEventListener('animationend', () => {
        notification.remove();
    });
}

/**
 * Crée le container de notifications si inexistant
 */
function createNotificationCenter() {
    const center = document.createElement('div');
    center.className = 'notification-center';
    document.body.appendChild(center);
    return center;
}




function toggleEdit(id) {
    const form = document.getElementById('edit-form-' + id);
    
    if (form.style.display === 'block') {
        form.style.opacity = 1;
        form.style.transition = 'opacity 0.3s ease';
        form.style.opacity = 0;
        setTimeout(() => {
            form.style.display = 'none';
        }, 300);
    } else {
        form.style.display = 'block';
        form.style.opacity = 0;
        setTimeout(() => {
            form.style.transition = 'opacity 0.3s ease';
            form.style.opacity = 1;
        }, 10);
    }
}
