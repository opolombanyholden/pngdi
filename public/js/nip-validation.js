/**
 * =============================================
 * 🔧 VALIDATION NIP FORMAT XX-QQQQ-YYYYMMDD
 * Fichier: public/js/nip-validation.js
 * =============================================
 */

// Configuration globale
const NIP_CONFIG = {
    format: 'XX-QQQQ-YYYYMMDD',
    regex: /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/,
    maxLength: 16,
    example: 'A1-2345-19901225',
    apiUrl: '/api/v1/validate-nip'
};

/**
 * ✅ VALIDATION FORMAT NIP CÔTÉ CLIENT
 * @param {string} nip 
 * @returns {object} Résultat de validation
 */
function validateNipFormat(nip) {
    if (!nip || typeof nip !== 'string') {
        return {
            valid: false,
            message: 'NIP requis'
        };
    }

    // Nettoyer le NIP (supprimer espaces)
    nip = nip.trim().toUpperCase();

    // Vérification regex de base
    if (!NIP_CONFIG.regex.test(nip)) {
        return {
            valid: false,
            message: `Format invalide. Attendu: ${NIP_CONFIG.format}`,
            example: NIP_CONFIG.example,
            help: 'Format: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
        };
    }

    // Extraction des parties
    const parts = nip.split('-');
    const prefix = parts[0]; // XX
    const sequence = parts[1]; // QQQQ
    const dateStr = parts[2]; // YYYYMMDD

    // Validation date
    const year = parseInt(dateStr.substring(0, 4));
    const month = parseInt(dateStr.substring(4, 6));
    const day = parseInt(dateStr.substring(6, 8));

    // Vérifier si la date est valide
    const date = new Date(year, month - 1, day);
    if (date.getFullYear() !== year ||
        date.getMonth() !== month - 1 ||
        date.getDate() !== day) {
        return {
            valid: false,
            message: 'Date de naissance invalide dans le NIP',
            extracted_date: `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`
        };
    }

    // Validation plage d'années
    if (year < 1900 || year > 2100) {
        return {
            valid: false,
            message: 'Année de naissance invalide (doit être entre 1900 et 2100)'
        };
    }

    // Calculer l'âge
    const today = new Date();
    const birthDate = new Date(year, month - 1, day);
    const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));

    // Validation âge
    if (age < 18) {
        return {
            valid: false,
            message: `Personne mineure détectée (âge: ${age} ans). Seules les personnes majeures sont autorisées.`,
            age: age,
            errorCode: 'UNDERAGE'
        };
    }

    if (age > 100) {
        return {
            valid: true,
            message: `Âge suspect détecté (${age} ans). Veuillez vérifier.`,
            warning: true,
            age: age
        };
    }

    return {
        valid: true,
        message: 'NIP valide',
        extracted_info: {
            prefix: prefix,
            sequence: sequence,
            birth_date: `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`,
            age: age
        }
    };
}

/**
 * ✅ FORMATAGE AUTOMATIQUE DU NIP PENDANT LA SAISIE
 * @param {string} input 
 * @returns {string} NIP formaté
 */
function formatNipInput(input) {
    // Supprimer tout ce qui n'est pas alphanumérique
    let value = input.toUpperCase().replace(/[^A-Z0-9]/g, '');

    // Appliquer le format XX-QQQQ-YYYYMMDD
    if (value.length > 2) {
        value = value.substring(0, 2) + '-' + value.substring(2);
    }

    if (value.length > 7) {
        value = value.substring(0, 7) + '-' + value.substring(7);
    }

    if (value.length > 16) {
        value = value.substring(0, 16);
    }

    return value;
}

/**
 * ✅ INITIALISER LA VALIDATION NIP SUR LES CHAMPS
 */
function initNipValidation() {
    const nipInputs = document.querySelectorAll('input[data-validate="nip"], input[name*="nip"]');

    nipInputs.forEach(input => {
        // Formatage automatique pendant la saisie
        input.addEventListener('input', function(e) {
            const formatted = formatNipInput(e.target.value);
            e.target.value = formatted;

            // Validation en temps réel si format complet
            if (formatted.length === 16) { // XX-QQQQ-YYYYMMDD = 16 caractères
                validateNipRealTime(e.target, formatted);
            } else {
                clearValidationFeedback(e.target);
            }
        });

        // Validation finale à la perte de focus
        input.addEventListener('blur', function(e) {
            if (e.target.value.length > 0) {
                validateNipRealTime(e.target, e.target.value);
            }
        });

        // Configuration des attributs
        input.setAttribute('placeholder', NIP_CONFIG.example);
        input.setAttribute('maxlength', NIP_CONFIG.maxLength.toString());
        input.setAttribute('pattern', '[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}');

        // Ajouter aide contextuelle
        addNipHelp(input);
    });

    console.log('✅ Validation NIP initialisée - Format:', NIP_CONFIG.format);
}

/**
 * ✅ VALIDATION TEMPS RÉEL AVEC FEEDBACK VISUEL
 * @param {HTMLElement} input 
 * @param {string} nip 
 */
function validateNipRealTime(input, nip) {
    const validation = validateNipFormat(nip);
    const container = input.closest('.form-group, .mb-3, .mb-4, .input-group') || input.parentElement;

    // Nettoyer ancien feedback
    clearValidationFeedback(input);

    if (validation.valid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');

        if (validation.warning) {
            showValidationMessage(container, validation.message, 'warning');
        } else {
            const message = validation.extracted_info ? 
                `${validation.message} (Âge: ${validation.extracted_info.age} ans)` : 
                validation.message;
            showValidationMessage(container, message, 'success');
        }

        // Validation serveur optionnelle pour vérifier les doublons
        validateNipWithServer(input, nip);

    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        showValidationMessage(container, validation.message, 'error');
    }
}

/**
 * ✅ VALIDATION SERVEUR POUR VÉRIFIER LES DOUBLONS
 * @param {HTMLElement} input 
 * @param {string} nip 
 */
async function validateNipWithServer(input, nip) {
    try {
        const response = await fetch(NIP_CONFIG.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nip: nip })
        });

        const result = await response.json();
        const container = input.closest('.form-group, .mb-3, .mb-4, .input-group') || input.parentElement;

        if (result.success && result.valid) {
            if (!result.available) {
                // NIP déjà utilisé
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                showValidationMessage(container, result.message, 'warning');
            } else {
                // NIP valide et disponible
                const message = `NIP valide et disponible (Âge: ${result.age} ans)`;
                showValidationMessage(container, message, 'success');
            }
        } else if (!result.valid) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            showValidationMessage(container, result.message, 'error');
        }

    } catch (error) {
        console.warn('Erreur validation serveur NIP:', error);
        // Ne pas bloquer l'interface en cas d'erreur serveur
    }
}

/**
 * ✅ AFFICHER MESSAGE DE VALIDATION
 * @param {HTMLElement} container 
 * @param {string} message 
 * @param {string} type 
 */
function showValidationMessage(container, message, type) {
    const existingFeedback = container.querySelector('.nip-validation-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    const feedback = document.createElement('div');
    feedback.className = 'nip-validation-feedback small mt-1';
    feedback.textContent = message;

    switch (type) {
        case 'success':
            feedback.className += ' text-success';
            feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + message;
            break;
        case 'warning':
            feedback.className += ' text-warning';
            feedback.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + message;
            break;
        case 'error':
            feedback.className += ' text-danger';
            feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + message;
            break;
    }

    container.appendChild(feedback);
}

/**
 * ✅ NETTOYER FEEDBACK DE VALIDATION
 * @param {HTMLElement} input 
 */
function clearValidationFeedback(input) {
    input.classList.remove('is-valid', 'is-invalid');
    const container = input.closest('.form-group, .mb-3, .mb-4, .input-group') || input.parentElement;
    const feedback = container.querySelector('.nip-validation-feedback');
    if (feedback) {
        feedback.remove();
    }
}

/**
 * ✅ AJOUTER AIDE CONTEXTUELLE POUR LE NIP
 * @param {HTMLElement} input 
 */
function addNipHelp(input) {
    const container = input.closest('.form-group, .mb-3, .mb-4') || input.parentElement;
    
    if (!container.querySelector('.nip-help')) {
        const help = document.createElement('small');
        help.className = 'nip-help text-muted form-text';
        help.innerHTML = ``;
        container.appendChild(help);
    }
}

/**
 * ✅ VALIDATION COMPLÈTE NIP (CLIENT + SERVEUR)
 * @param {HTMLElement} input 
 * @param {string} nip 
 * @returns {Promise} Résultat de validation
 */
async function validateNipComplete(input, nip) {
    // 1. Validation côté client
    const clientValidation = validateNipFormat(nip);
    if (!clientValidation.valid) {
        validateNipRealTime(input, nip);
        return clientValidation;
    }

    // 2. Validation côté serveur
    const container = input.closest('.form-group, .mb-3, .mb-4, .input-group') || input.parentElement;
    showValidationMessage(container, 'Vérification en cours...', 'info');

    try {
        const response = await fetch(NIP_CONFIG.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nip: nip })
        });

        const serverValidation = await response.json();

        if (serverValidation.success && serverValidation.valid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');

            const message = serverValidation.available ?
                `NIP valide et disponible (Âge: ${serverValidation.age} ans)` :
                'NIP valide mais déjà utilisé';

            showValidationMessage(container, message,
                serverValidation.available ? 'success' : 'warning');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            showValidationMessage(container, serverValidation.message, 'error');
        }

        return serverValidation;

    } catch (error) {
        console.error('Erreur validation serveur:', error);
        showValidationMessage(container, 'Erreur de connexion au serveur', 'error');
        return { success: false, valid: false, message: 'Erreur serveur' };
    }
}

/**
 * ✅ GÉNÉRER EXEMPLE DE NIP VALIDE
 * @returns {string} Exemple de NIP
 */
function generateNipExample() {
    const prefixes = ['A1', 'B2', 'C3', '1A', '2B', '3C'];
    const sequences = ['0001', '1234', '5678', '9999'];
    
    const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const sequence = sequences[Math.floor(Math.random() * sequences.length)];
    
    // Date aléatoire entre 1960 et 2005
    const year = 1960 + Math.floor(Math.random() * 45);
    const month = 1 + Math.floor(Math.random() * 12);
    const day = 1 + Math.floor(Math.random() * 28);
    
    const dateStr = year.toString() + 
                   month.toString().padStart(2, '0') + 
                   day.toString().padStart(2, '0');
    
    return prefix + '-' + sequence + '-' + dateStr;
}

/**
 * ✅ RÉINITIALISER VALIDATION NIP (pour contenu dynamique)
 */
function reinitNipValidation() {
    initNipValidation();
}

// =============================================
// 🚀 INITIALISATION AUTOMATIQUE
// =============================================

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initNipValidation();
});

// Exporter les fonctions pour usage global
window.NipValidation = {
    validateFormat: validateNipFormat,
    formatInput: formatNipInput,
    validateComplete: validateNipComplete,
    generateExample: generateNipExample,
    reinit: reinitNipValidation
};