document.addEventListener('DOMContentLoaded', function() {
    // Password strength configuration
    const strengthLevels = [
        { 
            name: 'weak', 
            color: '#ff5252', 
            text: 'Faible - Au moins 8 caractères', 
            minLength: 8 
        },
        { 
            name: 'medium', 
            color: '#ffb74d', 
            text: 'Moyen - Ajoutez des majuscules et des chiffres', 
            minLength: 10,
            requirements: [/[A-Z]/, /\d/] 
        },
        { 
            name: 'strong', 
            color: '#4caf50', 
            text: 'Fort - Ajoutez des caractères spéciaux', 
            minLength: 12,
            requirements: [/[A-Z]/, /\d/, /[^A-Za-z0-9]/] 
        }
    ];

    // Initialize password strength meter
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthMeter = document.createElement('div');
    strengthMeter.className = 'password-meter';
    
    // Create meter sections
    for (let i = 0; i < 4; i++) {
        const section = document.createElement('div');
        section.className = 'password-meter-section';
        strengthMeter.appendChild(section);
    }
    
    // Create feedback element
    const feedback = document.createElement('div');
    feedback.className = 'password-feedback';
    
    // Insert elements after password input
    if (passwordInput) {
        passwordInput.insertAdjacentElement('afterend', feedback);
        passwordInput.insertAdjacentElement('afterend', strengthMeter);
        
        // Add tooltip for requirements
        const tooltip = document.createElement('span');
        tooltip.className = 'tooltip';
        tooltip.innerHTML = '<i class="fas fa-info-circle"></i>';
        tooltip.innerHTML += '<span class="tooltiptext">Le mot de passe doit contenir au moins 8 caractères, inclure des majuscules, des chiffres et des caractères spéciaux pour une meilleure sécurité.</span>';
        passwordInput.parentNode.insertBefore(tooltip, passwordInput.nextSibling);
        
        // Add password toggle
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.innerHTML = '<i class="fas fa-eye"></i>';
        toggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        const container = document.createElement('div');
        container.className = 'password-input-container';
        passwordInput.parentNode.insertBefore(container, passwordInput);
        container.appendChild(passwordInput);
        container.appendChild(toggle);
    }

    // Password strength calculation
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const sections = strengthMeter.querySelectorAll('.password-meter-section');
            let strength = 0;
            
            // Reset meter
            sections.forEach(section => {
                section.style.backgroundColor = '#e0e0e0';
            });
            
            // Check length
            if (password.length >= strengthLevels[0].minLength) strength++;
            if (password.length >= strengthLevels[1].minLength) strength++;
            
            // Check requirements
            strengthLevels.forEach(level => {
                if (level.requirements) {
                    const meetsRequirements = level.requirements.every(regex => regex.test(password));
                    if (meetsRequirements) strength++;
                }
            });
            
            // Update meter and feedback
            let currentLevel = strengthLevels[0];
            
            for (let i = 0; i < sections.length; i++) {
                if (i < strength) {
                    let levelColor;
                    
                    if (strength <= 2) {
                        levelColor = strengthLevels[0].color;
                        currentLevel = strengthLevels[0];
                    } else if (strength === 3) {
                        levelColor = strengthLevels[1].color;
                        currentLevel = strengthLevels[1];
                    } else {
                        levelColor = strengthLevels[2].color;
                        currentLevel = strengthLevels[2];
                    }
                    
                    sections[i].style.backgroundColor = levelColor;
                }
            }
            
            // Update feedback text
            if (password.length === 0) {
                feedback.textContent = '';
                feedback.className = 'password-feedback';
            } else {
                feedback.textContent = currentLevel.text;
                feedback.className = `password-feedback ${currentLevel.name}`;
            }
        });
    }
    
    // Password match verification
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirm = this.value;
            const matchFeedback = document.createElement('div');
            matchFeedback.className = 'password-feedback';
            
            if (confirm.length > 0) {
                if (password === confirm) {
                    matchFeedback.textContent = 'Les mots de passe correspondent';
                    matchFeedback.style.color = '#4caf50';
                } else {
                    matchFeedback.textContent = 'Les mots de passe ne correspondent pas';
                    matchFeedback.style.color = '#ff5252';
                }
                
                // Insert or update feedback
                const existingFeedback = this.nextElementSibling;
                if (existingFeedback && existingFeedback.className === 'password-feedback') {
                    existingFeedback.textContent = matchFeedback.textContent;
                    existingFeedback.style.color = matchFeedback.style.color;
                } else {
                    this.insertAdjacentElement('afterend', matchFeedback);
                }
            } else {
                // Remove feedback if empty
                const existingFeedback = this.nextElementSibling;
                if (existingFeedback && existingFeedback.className === 'password-feedback') {
                    existingFeedback.remove();
                }
            }
        });
        
        // Add toggle for confirm password
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.innerHTML = '<i class="fas fa-eye"></i>';
        toggle.addEventListener('click', function() {
            const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        const container = document.createElement('div');
        container.className = 'password-input-container';
        confirmInput.parentNode.insertBefore(container, confirmInput);
        container.appendChild(confirmInput);
        container.appendChild(toggle);
    }
    
    // Form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (passwordInput && confirmInput) {
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas. Veuillez vérifier votre saisie.');
                }
            }
        });
    });
});



// Validation des noms
const nameForm = document.querySelector('form[name="update_names"]');
if (nameForm) {
    nameForm.addEventListener('submit', function(e) {
        const nom = this.querySelector('#new_nom').value.trim();
        const prenom = this.querySelector('#new_prenom').value.trim();
        
        if (nom.length < 2 || prenom.length < 2) {
            e.preventDefault();
            alert('Le nom et le prénom doivent contenir au moins 2 caractères');
            return false;
        }
        
        if (!/^[a-zA-ZÀ-ÿ\- ]+$/.test(nom) || !/^[a-zA-ZÀ-ÿ\- ]+$/.test(prenom)) {
            e.preventDefault();
            alert('Seules les lettres, espaces et traits d\'union sont autorisés');
            return false;
        }
    });
}