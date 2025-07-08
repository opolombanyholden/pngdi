{{--
============================================================================
ADHERENT-FORM-MANUAL.BLADE.PHP - FORMULAIRE SAISIE MANUELLE
Partial pour confirmation.blade.php - Section import manuel
Version: 2.0 - Interface moderne avec validation temps réel
============================================================================
--}}

<div class="manual-form">
    <div class="form-header mb-4">
        <h5 class="text-primary">
            <i class="fas fa-keyboard me-2"></i>
            Saisie Manuelle d'Adhérents
        </h5>
        <p class="text-muted mb-0">
            Ajoutez vos adhérents un par un via ce formulaire.
            Idéal pour de petits volumes ou des corrections ponctuelles.
        </p>
    </div>

    <!-- Formulaire de saisie -->
    <form id="manual-adherent-form" class="needs-validation" novalidate>
        <div class="row">
            <!-- Civilité -->
            <div class="col-md-2 mb-3">
                <label for="manual-civilite" class="form-label">
                    <i class="fas fa-user me-1"></i>Civilité
                </label>
                <select class="form-select form-control-gabon" id="manual-civilite" name="civilite" required>
                    <option value="">Sélectionner</option>
                    <option value="M">M.</option>
                    <option value="Mme">Mme</option>
                    <option value="Mlle">Mlle</option>
                </select>
                <div class="invalid-feedback">
                    Veuillez sélectionner une civilité.
                </div>
            </div>

            <!-- Nom -->
            <div class="col-md-5 mb-3">
                <label for="manual-nom" class="form-label">
                    <i class="fas fa-user-tag me-1"></i>Nom <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control form-control-gabon" id="manual-nom" name="nom" 
                       placeholder="Ex: MBARGA" maxlength="100" required>
                <div class="invalid-feedback">
                    Le nom est obligatoire.
                </div>
                <div class="valid-feedback">
                    <i class="fas fa-check-circle"></i> Nom valide
                </div>
            </div>

            <!-- Prénom -->
            <div class="col-md-5 mb-3">
                <label for="manual-prenom" class="form-label">
                    <i class="fas fa-user me-1"></i>Prénom <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control form-control-gabon" id="manual-prenom" name="prenom" 
                       placeholder="Ex: Jean-Paul" maxlength="100" required>
                <div class="invalid-feedback">
                    Le prénom est obligatoire.
                </div>
                <div class="valid-feedback">
                    <i class="fas fa-check-circle"></i> Prénom valide
                </div>
            </div>
        </div>

        <div class="row">
            <!-- NIP -->
            <div class="col-md-6 mb-3">
                <label for="manual-nip" class="form-label">
                    <i class="fas fa-id-card me-1"></i>NIP <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control form-control-gabon" id="manual-nip" name="nip" 
                       placeholder="XX-QQQQ-YYYYMMDD" maxlength="15" required 
                       data-bs-toggle="tooltip" data-bs-placement="top" 
                       title="Format: XX-QQQQ-YYYYMMDD (Ex: GA-1234-19851203)">
                <div class="invalid-feedback">
                    Format NIP invalide. Attendu: XX-QQQQ-YYYYMMDD
                </div>
                <div class="valid-feedback">
                    <i class="fas fa-check-circle"></i> NIP valide
                </div>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Numéro d'Identification Personnel gabonais
                </small>
            </div>

            <!-- Téléphone -->
            <div class="col-md-6 mb-3">
                <label for="manual-telephone" class="form-label">
                    <i class="fas fa-phone me-1"></i>Téléphone
                </label>
                <div class="input-group">
                    <span class="input-group-text">+241</span>
                    <input type="tel" class="form-control form-control-gabon" id="manual-telephone" name="telephone" 
                           placeholder="Ex: 06 12 34 56 78" maxlength="15">
                </div>
                <div class="invalid-feedback">
                    Format téléphone invalide.
                </div>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Optionnel - Format international recommandé
                </small>
            </div>
        </div>

        <div class="row">
            <!-- Profession -->
            <div class="col-md-12 mb-3">
                <label for="manual-profession" class="form-label">
                    <i class="fas fa-briefcase me-1"></i>Profession
                </label>
                <input type="text" class="form-control form-control-gabon" id="manual-profession" name="profession" 
                       placeholder="Ex: Ingénieur informatique, Médecin généraliste..." maxlength="150">
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Optionnel - Permet une meilleure classification
                </small>
            </div>
        </div>

        <!-- Zone de prévisualisation -->
        <div class="preview-zone" id="manual-preview" style="display: none;">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Aperçu de l'adhérent
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="preview-content"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="validation-status">
                                <div class="mb-2">
                                    <span class="badge badge-gabon-success" id="preview-age-badge">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <span id="preview-age">-</span> ans
                                    </span>
                                </div>
                                <div id="preview-warnings"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="form-actions text-center mt-4">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <button type="button" class="btn btn-gabon-outline w-100" id="manual-reset-btn" onclick="resetManualForm()">
                        <i class="fas fa-undo me-2"></i>Réinitialiser
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-gabon-accent w-100" id="manual-preview-btn" onclick="previewManualAdherent()">
                        <i class="fas fa-eye me-2"></i>Aperçu
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-gabon-primary w-100" id="manual-add-btn">
                        <i class="fas fa-plus me-2"></i>Ajouter l'Adhérent
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Statistiques de saisie -->
    <div class="manual-stats mt-4" id="manual-stats" style="display: none;">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="stat-number" id="manual-added-count">0</div>
                    <div class="stat-label">Ajoutés</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card warning">
                    <div class="stat-number" id="manual-warnings-count">0</div>
                    <div class="stat-label">Avertissements</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-number" id="manual-session-time">0m</div>
                    <div class="stat-label">Durée session</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conseils et aide -->
    <div class="help-section mt-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>Conseils de saisie
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="fas fa-check-circle me-1"></i>Bonnes pratiques
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Utilisez MAJUSCULES pour le nom</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Vérifiez le format NIP avant validation</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Téléphone optionnel mais recommandé</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Profession aide au classement</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>Points d'attention
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Âge minimum : 18 ans</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>NIP unique obligatoire</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Doublons automatiquement détectés</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Validation temps réel activée</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template de notification -->
<div id="manual-notification-template" style="display: none;">
    <div class="alert alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <span class="notification-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>

{{-- JavaScript spécifique au formulaire manuel --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeManualForm();
});

// Variables de session
let manualFormStats = {
    added: 0,
    warnings: 0,
    startTime: Date.now(),
    currentSession: []
};

/**
 * Initialiser le formulaire manuel
 */
function initializeManualForm() {
    console.log('🔧 Initialisation formulaire manuel');
    
    // Configurar la validation Bootstrap
    setupFormValidation();
    
    // Configurer les événements
    setupManualFormEvents();
    
    // Initialiser les tooltips
    initializeTooltips();
    
    // Mettre le focus sur le premier champ
    document.getElementById('manual-civilite').focus();
    
    console.log('✅ Formulaire manuel initialisé');
}

/**
 * Configurer la validation Bootstrap
 */
function setupFormValidation() {
    const form = document.getElementById('manual-adherent-form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            if (form.checkValidity()) {
                handleManualSubmit();
            } else {
                // Afficher les erreurs
                form.classList.add('was-validated');
                showManualNotification('Veuillez corriger les erreurs avant de continuer', 'warning');
            }
        });
    }
}

/**
 * Configurer les événements du formulaire
 */
function setupManualFormEvents() {
    // Validation temps réel NIP
    const nipInput = document.getElementById('manual-nip');
    if (nipInput) {
        nipInput.addEventListener('input', function(e) {
            validateNIPFormat(e.target);
            updatePreview();
        });
        
        nipInput.addEventListener('blur', function(e) {
            checkNIPDuplicate(e.target.value);
        });
    }
    
    // Formatage téléphone
    const phoneInput = document.getElementById('manual-telephone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            formatPhoneNumber(e.target);
        });
    }
    
    // Formatage nom (majuscules)
    const nomInput = document.getElementById('manual-nom');
    if (nomInput) {
        nomInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
            updatePreview();
        });
    }
    
    // Prénom (capitalisation)
    const prenomInput = document.getElementById('manual-prenom');
    if (prenomInput) {
        prenomInput.addEventListener('input', function(e) {
            e.target.value = capitalizeWords(e.target.value);
            updatePreview();
        });
    }
    
    // Mise à jour de l'aperçu en temps réel
    const allInputs = form.querySelectorAll('input, select');
    allInputs.forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
}

/**
 * Valider le format NIP
 */
function validateNIPFormat(input) {
    const nipPattern = /^[A-Z]{2}-[0-9]{4}-[0-9]{8}$/;
    const value = input.value.toUpperCase();
    
    input.value = value; // Forcer majuscules
    
    if (value && !nipPattern.test(value)) {
        input.setCustomValidity('Format NIP invalide. Attendu: XX-QQQQ-YYYYMMDD');
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        return false;
    } else if (value) {
        // Vérifier l'âge
        const age = extractAgeFromNIP(value);
        if (age < 18) {
            input.setCustomValidity('L\'adhérent doit être majeur (18 ans minimum)');
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            return false;
        } else {
            input.setCustomValidity('');
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            return true;
        }
    } else {
        input.setCustomValidity('');
        input.classList.remove('is-invalid', 'is-valid');
        return false;
    }
}

/**
 * Extraire l'âge depuis le NIP
 */
function extractAgeFromNIP(nip) {
    if (!nip || nip.length < 13) return 0;
    
    try {
        const datePart = nip.substring(7, 15); // YYYYMMDD
        const year = parseInt(datePart.substring(0, 4));
        const month = parseInt(datePart.substring(4, 6)) - 1; // mois commence à 0
        const day = parseInt(datePart.substring(6, 8));
        
        const birthDate = new Date(year, month, day);
        const today = new Date();
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    } catch (error) {
        return 0;
    }
}

/**
 * Vérifier doublon NIP
 */
function checkNIPDuplicate(nip) {
    if (!nip || !window.ConfirmationApp) return;
    
    const isDuplicate = window.ConfirmationApp.isDuplicate({ nip: nip });
    const nipInput = document.getElementById('manual-nip');
    
    if (isDuplicate) {
        nipInput.setCustomValidity('Ce NIP existe déjà');
        nipInput.classList.remove('is-valid');
        nipInput.classList.add('is-invalid');
        showManualNotification('Ce NIP existe déjà dans la liste', 'warning');
    }
}

/**
 * Formater le numéro de téléphone
 */
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, ''); // Supprimer non-chiffres
    
    if (value.length > 0) {
        if (value.length <= 2) {
            value = value;
        } else if (value.length <= 4) {
            value = value.substring(0, 2) + ' ' + value.substring(2);
        } else if (value.length <= 6) {
            value = value.substring(0, 2) + ' ' + value.substring(2, 4) + ' ' + value.substring(4);
        } else if (value.length <= 8) {
            value = value.substring(0, 2) + ' ' + value.substring(2, 4) + ' ' + value.substring(4, 6) + ' ' + value.substring(6);
        } else {
            value = value.substring(0, 2) + ' ' + value.substring(2, 4) + ' ' + value.substring(4, 6) + ' ' + value.substring(6, 8) + ' ' + value.substring(8, 10);
        }
    }
    
    input.value = value;
}

/**
 * Capitaliser les mots
 */
function capitalizeWords(str) {
    return str.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

/**
 * Mettre à jour l'aperçu
 */
function updatePreview() {
    const form = document.getElementById('manual-adherent-form');
    const preview = document.getElementById('manual-preview');
    const previewContent = document.getElementById('preview-content');
    const ageSpan = document.getElementById('preview-age');
    const warningsDiv = document.getElementById('preview-warnings');
    
    if (!form || !preview) return;
    
    const formData = new FormData(form);
    const civilite = formData.get('civilite');
    const nom = formData.get('nom');
    const prenom = formData.get('prenom');
    const nip = formData.get('nip');
    const telephone = formData.get('telephone');
    const profession = formData.get('profession');
    
    // Afficher l'aperçu si au moins nom/prénom/nip sont remplis
    if (nom && prenom && nip) {
        const age = extractAgeFromNIP(nip);
        
        previewContent.innerHTML = `
            <h6 class="text-success">${civilite || ''} ${prenom} ${nom}</h6>
            <p class="mb-1"><strong>NIP:</strong> ${nip}</p>
            ${telephone ? `<p class="mb-1"><strong>Téléphone:</strong> +241 ${telephone}</p>` : ''}
            ${profession ? `<p class="mb-1"><strong>Profession:</strong> ${profession}</p>` : ''}
        `;
        
        ageSpan.textContent = age > 0 ? age : 'Invalide';
        
        // Vérifier avertissements
        const warnings = [];
        if (age < 18) warnings.push('Adhérent mineur (non autorisé)');
        if (!telephone) warnings.push('Téléphone manquant');
        if (!profession) warnings.push('Profession non spécifiée');
        
        warningsDiv.innerHTML = warnings.map(w => 
            `<div class="text-warning small"><i class="fas fa-exclamation-triangle me-1"></i>${w}</div>`
        ).join('');
        
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

/**
 * Gérer la soumission manuelle
 */
function handleManualSubmit() {
    const form = document.getElementById('manual-adherent-form');
    const formData = new FormData(form);
    
    const adherentData = {
        civilite: formData.get('civilite'),
        nom: formData.get('nom'),
        prenom: formData.get('prenom'),
        nip: formData.get('nip'),
        telephone: formData.get('telephone'),
        profession: formData.get('profession'),
        source: 'manuel'
    };
    
    // Utiliser l'API de ConfirmationApp pour ajouter
    if (window.ConfirmationApp && window.ConfirmationApp.addAdherent) {
        const result = window.ConfirmationApp.addAdherent(adherentData);
        
        if (result) {
            // Succès
            manualFormStats.added++;
            manualFormStats.currentSession.push(result);
            
            updateManualStats();
            showManualNotification(`Adhérent ${adherentData.prenom} ${adherentData.nom} ajouté avec succès`, 'success');
            
            // Réinitialiser le formulaire
            resetManualForm();
        }
    } else {
        showManualNotification('Erreur: Module ConfirmationApp non disponible', 'danger');
    }
}

/**
 * Aperçu de l'adhérent
 */
function previewManualAdherent() {
    updatePreview();
    
    const preview = document.getElementById('manual-preview');
    if (preview && preview.style.display === 'none') {
        showManualNotification('Remplissez au minimum le nom, prénom et NIP pour voir l\'aperçu', 'info');
    } else {
        // Faire défiler vers l'aperçu
        preview.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

/**
 * Réinitialiser le formulaire
 */
function resetManualForm() {
    const form = document.getElementById('manual-adherent-form');
    const preview = document.getElementById('manual-preview');
    
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        
        // Supprimer les classes de validation
        const inputs = form.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
            input.setCustomValidity('');
        });
    }
    
    if (preview) {
        preview.style.display = 'none';
    }
    
    // Remettre le focus
    document.getElementById('manual-civilite').focus();
}

/**
 * Mettre à jour les statistiques
 */
function updateManualStats() {
    const statsDiv = document.getElementById('manual-stats');
    const addedCount = document.getElementById('manual-added-count');
    const warningsCount = document.getElementById('manual-warnings-count');
    const sessionTime = document.getElementById('manual-session-time');
    
    if (manualFormStats.added > 0) {
        statsDiv.style.display = 'block';
        
        if (addedCount) addedCount.textContent = manualFormStats.added;
        if (warningsCount) warningsCount.textContent = manualFormStats.warnings;
        
        if (sessionTime) {
            const elapsed = Math.floor((Date.now() - manualFormStats.startTime) / 60000);
            sessionTime.textContent = elapsed + 'm';
        }
    }
}

/**
 * Afficher notification
 */
function showManualNotification(message, type = 'info') {
    if (window.ConfirmationApp && window.ConfirmationApp.showNotification) {
        window.ConfirmationApp.showNotification(message, type);
    } else {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

/**
 * Initialiser les tooltips
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}
</script>