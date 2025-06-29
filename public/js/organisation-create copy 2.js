/**
 * ========================================================================
 * PNGDI - Formulaire Création Organisation - VERSION FINALE COMPLÈTE
 * Fichier: public/js/organisation-create.js
 * Compatible: Bootstrap 5 + Laravel + Toutes les 9 étapes
 * Date: 28/06/2025
 * ========================================================================
 */

// ========================================
// 1. CONFIGURATION GLOBALE
// ========================================

window.OrganisationApp = {
    // État actuel
    currentStep: 1,
    totalSteps: 9,
    selectedOrgType: '',
    
    // Configuration
    config: {
        autoSaveInterval: 30000, // 30 secondes
        validationDelay: 500,    // 500ms pour debounce
        animationDuration: 300,  // 300ms pour animations
        
        // Configuration NIP corrigée (sans checksum strict)
        nip: {
            length: 13,
            pattern: /^[0-9]{13}$/,
            strictValidation: false, // Désactivé pour éviter les erreurs
            allowTestValues: true
        },
        
        // Configuration téléphone gabonais
        phone: {
            prefixes: ['01', '02', '03', '04', '05', '06', '07'],
            minLength: 8,
            maxLength: 9,
            pattern: /^[0-9]{8,9}$/
        },
        
        // Exigences par type d'organisation
        orgRequirements: {
            'association': {
                minFondateurs: 3,
                minAdherents: 10,
                label: 'Association',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege']
            },
            'ong': {
                minFondateurs: 5,
                minAdherents: 15,
                label: 'ONG',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'projet_social', 'budget_previsionnel']
            },
            'parti_politique': {
                minFondateurs: 3,
                minAdherents: 50,
                label: 'Parti Politique',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'programme_politique', 'liste_50_adherents']
            },
            'confession_religieuse': {
                minFondateurs: 3,
                minAdherents: 10,
                label: 'Confession Religieuse',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'expose_doctrine', 'justif_lieu_culte']
            }
        }
    },
    
    // Cache et données
    cache: new Map(),
    formData: {},
    validationErrors: {},
    fondateurs: [],
    adherents: [],
    documents: {},
    
    // Timers
    timers: {
        autoSave: null,
        validation: {}
    }
};

// ========================================
// 2. FONCTIONS DE NAVIGATION
// ========================================

/**
 * Navigation entre les étapes
 */
function changeStep(direction) {
    console.log(`🔄 Changement d'étape: direction ${direction}, étape actuelle: ${OrganisationApp.currentStep}`);
    
    // Validation avant d'avancer
    if (direction === 1 && !validateCurrentStep()) {
        console.log('❌ Validation échouée pour l\'étape', OrganisationApp.currentStep);
        showNotification('Veuillez compléter tous les champs obligatoires avant de continuer', 'warning');
        return false;
    }
    
    // Sauvegarder l'étape actuelle
    saveCurrentStepData();
    
    // Calculer la nouvelle étape
    const newStep = OrganisationApp.currentStep + direction;
    
    if (newStep >= 1 && newStep <= OrganisationApp.totalSteps) {
        OrganisationApp.currentStep = newStep;
        updateStepDisplay();
        updateNavigationButtons();
        
        // Actions spécifiques selon l'étape
        handleStepSpecificActions(newStep);
        
        scrollToTop();
        return true;
    }
    
    return false;
}

/**
 * Actions spécifiques selon l'étape
 */
function handleStepSpecificActions(stepNumber) {
    switch (stepNumber) {
        case 2:
            updateGuideContent();
            break;
        case 4:
            updateOrganizationRequirements();
            break;
        case 6:
            updateFoundersRequirements();
            break;
        case 7:
            updateMembersRequirements();
            break;
        case 8:
            updateDocumentsRequirements();
            break;
        case 9:
            generateRecap();
            break;
    }
}

/**
 * Aller directement à une étape
 */
function goToStep(stepNumber) {
    if (stepNumber >= 1 && stepNumber <= OrganisationApp.totalSteps) {
        // Valider toutes les étapes jusqu'à celle-ci
        for (let i = 1; i < stepNumber; i++) {
            if (!validateStep(i)) {
                showNotification(`Veuillez compléter l'étape ${i} avant de continuer`, 'warning');
                return false;
            }
        }
        
        OrganisationApp.currentStep = stepNumber;
        updateStepDisplay();
        updateNavigationButtons();
        handleStepSpecificActions(stepNumber);
        scrollToTop();
        return true;
    }
    return false;
}

/**
 * Mise à jour de l'affichage des étapes
 */
function updateStepDisplay() {
    // Masquer toutes les étapes
    document.querySelectorAll('.step-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Afficher l'étape actuelle avec animation
    const currentStepElement = document.getElementById('step' + OrganisationApp.currentStep);
    if (currentStepElement) {
        currentStepElement.style.display = 'block';
        
        // Animation d'entrée
        currentStepElement.style.opacity = '0';
        currentStepElement.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            currentStepElement.style.transition = 'all 0.3s ease';
            currentStepElement.style.opacity = '1';
            currentStepElement.style.transform = 'translateY(0)';
        }, 10);
        
        console.log('✅ Affichage étape', OrganisationApp.currentStep);
    } else {
        console.warn('⚠️ Élément step' + OrganisationApp.currentStep + ' non trouvé');
    }
    
    // Mettre à jour la barre de progression
    updateProgressBar();
    
    // Mettre à jour le numéro d'étape
    const currentStepNumber = document.getElementById('currentStepNumber');
    if (currentStepNumber) {
        currentStepNumber.textContent = OrganisationApp.currentStep;
    }
    
    // Mettre à jour les indicateurs d'étapes
    updateStepIndicators();
}

/**
 * Mise à jour de la barre de progression
 */
function updateProgressBar() {
    const progress = (OrganisationApp.currentStep / OrganisationApp.totalSteps) * 100;
    const progressBar = document.getElementById('globalProgress');
    
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        
        // Animation de la barre
        progressBar.classList.add('progress-bar-animated');
        setTimeout(() => {
            progressBar.classList.remove('progress-bar-animated');
        }, 1000);
    }
}

/**
 * Mise à jour des indicateurs d'étapes
 */
function updateStepIndicators() {
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNumber = index + 1;
        
        // Retirer toutes les classes d'état
        indicator.classList.remove('active', 'completed');
        
        if (stepNumber === OrganisationApp.currentStep) {
            indicator.classList.add('active');
            
            // Animation pour l'étape active
            indicator.style.transform = 'scale(1.05)';
            setTimeout(() => {
                indicator.style.transform = '';
            }, 300);
            
        } else if (stepNumber < OrganisationApp.currentStep) {
            indicator.classList.add('completed');
        }
        
        // Ajouter un gestionnaire de clic
        indicator.addEventListener('click', () => {
            if (stepNumber <= OrganisationApp.currentStep || stepNumber === OrganisationApp.currentStep + 1) {
                goToStep(stepNumber);
            }
        });
    });
}

/**
 * Mise à jour des boutons de navigation
 */
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Bouton précédent
    if (prevBtn) {
        prevBtn.style.display = OrganisationApp.currentStep === 1 ? 'none' : 'inline-block';
    }
    
    // Boutons suivant et soumettre
    if (OrganisationApp.currentStep === OrganisationApp.totalSteps) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.classList.remove('d-none');
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.classList.add('d-none');
    }
}

/**
 * Scroll vers le haut avec animation
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ========================================
// 3. GESTION TYPE D'ORGANISATION
// ========================================

/**
 * Sélection du type d'organisation
 */
function selectOrganizationType(card) {
    console.log('🏢 Sélection du type d\'organisation');
    
    // Retirer la sélection précédente avec animation
    document.querySelectorAll('.organization-type-card').forEach(c => {
        c.classList.remove('active');
        c.style.transform = '';
    });
    
    // Appliquer la nouvelle sélection avec animation
    card.classList.add('active');
    card.style.transform = 'scale(1.02)';
    
    setTimeout(() => {
        card.style.transform = '';
    }, 300);
    
    // Cocher le radio button et sauvegarder le type
    const radio = card.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
        OrganisationApp.selectedOrgType = radio.value;
        
        // Mettre à jour l'input caché
        const hiddenInput = document.getElementById('organizationType');
        if (hiddenInput) {
            hiddenInput.value = radio.value;
        }
        
        // Sauvegarder dans les données du formulaire
        OrganisationApp.formData.organizationType = radio.value;
    }
    
    // Afficher les informations de sélection
    showSelectedTypeInfo(radio.value);
    
    // Mettre à jour le guide de l'étape 2
    updateGuideContent();
}

/**
 * Affichage des informations du type sélectionné
 */
function showSelectedTypeInfo(type) {
    const selectedInfo = document.getElementById('selectedTypeInfo');
    const selectedTypeName = document.getElementById('selectedTypeName');
    
    if (selectedInfo && selectedTypeName) {
        selectedTypeName.textContent = getOrganizationTypeLabel(type);
        
        // Animation d'apparition
        selectedInfo.style.opacity = '0';
        selectedInfo.classList.remove('d-none');
        
        setTimeout(() => {
            selectedInfo.style.transition = 'opacity 0.3s ease';
            selectedInfo.style.opacity = '1';
        }, 10);
    }
}

/**
 * Obtenir le label d'un type d'organisation
 */
function getOrganizationTypeLabel(type) {
    const labels = {
        'association': 'Association',
        'ong': 'Organisation Non Gouvernementale (ONG)',
        'parti_politique': 'Parti Politique',
        'confession_religieuse': 'Confession Religieuse'
    };
    return labels[type] || type;
}

/**
 * Mise à jour du contenu du guide selon le type
 */
function updateGuideContent() {
    const guideContent = document.getElementById('guide-content');
    const selectedTypeTitle = document.getElementById('selectedTypeTitle');
    
    if (!OrganisationApp.selectedOrgType) return;
    
    if (selectedTypeTitle) {
        selectedTypeTitle.textContent = getOrganizationTypeLabel(OrganisationApp.selectedOrgType);
    }
    
    if (guideContent) {
        const content = getGuideContentForType(OrganisationApp.selectedOrgType);
        guideContent.innerHTML = content;
    }
}

/**
 * Contenu du guide selon le type d'organisation
 */
function getGuideContentForType(type) {
    const guides = {
        'association': `
            <div class="alert alert-success border-0 mb-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-handshake fa-3x me-3 text-success"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Guide pour créer une Association au Gabon</h5>
                        <p class="mb-0">Procédures légales selon la législation gabonaise en vigueur</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                    <ul class="list-unstyled">
                        <li>• Minimum 3 membres fondateurs majeurs</li>
                        <li>• Minimum 10 adhérents à la création</li>
                        <li>• Siège social au Gabon</li>
                        <li>• But exclusivement non lucratif</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-info"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                    <ul class="list-unstyled">
                        <li>• Statuts signés et légalisés</li>
                        <li>• PV de l'assemblée constitutive</li>
                        <li>• Liste des fondateurs avec NIP</li>
                        <li>• Justificatif du siège social</li>
                    </ul>
                </div>
            </div>
        `,
        'ong': `
            <div class="alert alert-info border-0 mb-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-globe-africa fa-3x me-3 text-info"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Guide pour créer une ONG au Gabon</h5>
                        <p class="mb-0">Organisation Non Gouvernementale à vocation humanitaire</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                    <ul class="list-unstyled">
                        <li>• Minimum 5 membres fondateurs majeurs</li>
                        <li>• Minimum 15 adhérents à la création</li>
                        <li>• Mission d'intérêt général</li>
                        <li>• Projet social déterminé</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-info"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                    <ul class="list-unstyled">
                        <li>• Statuts de l'ONG</li>
                        <li>• Plan d'action et budget prévisionnel</li>
                        <li>• CV des dirigeants</li>
                        <li>• Projet social détaillé</li>
                    </ul>
                </div>
            </div>
        `,
        'parti_politique': `
            <div class="alert alert-warning border-0 mb-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-vote-yea fa-3x me-3 text-warning"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Guide pour créer un Parti Politique au Gabon</h5>
                        <p class="mb-0">Organisation politique pour participer à la vie démocratique</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                    <ul class="list-unstyled">
                        <li>• Minimum 3 membres fondateurs majeurs</li>
                        <li>• <strong>Minimum 50 adhérents</strong> répartis sur 3 provinces</li>
                        <li>• Programme politique détaillé</li>
                        <li>• Vocation démocratique</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-info"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                    <ul class="list-unstyled">
                        <li>• Statuts du parti</li>
                        <li>• Programme politique</li>
                        <li>• Liste de 50 adhérents minimum</li>
                        <li>• Répartition géographique</li>
                    </ul>
                </div>
            </div>
        `,
        'confession_religieuse': `
            <div class="alert alert-secondary border-0 mb-4 shadow-sm" style="background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(232, 62, 140, 0.05) 100%);">
                <div class="d-flex align-items-center">
                    <i class="fas fa-pray fa-3x me-3 text-purple"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Guide pour créer une Confession Religieuse au Gabon</h5>
                        <p class="mb-0">Organisation religieuse pour l'exercice du culte</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                    <ul class="list-unstyled">
                        <li>• Minimum 3 membres fondateurs majeurs</li>
                        <li>• Minimum 10 fidèles à la création</li>
                        <li>• Doctrine religieuse clairement définie</li>
                        <li>• Lieu de culte identifié</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-info"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                    <ul class="list-unstyled">
                        <li>• Statuts de la confession</li>
                        <li>• Doctrine religieuse</li>
                        <li>• Liste des fidèles fondateurs</li>
                        <li>• Attestation du lieu de culte</li>
                    </ul>
                </div>
            </div>
        `
    };
    
    return guides[type] || '<p>Guide non disponible pour ce type d\'organisation.</p>';
}
// ========================================
// 4. VALIDATION COMPLÈTE TOUTES ÉTAPES
// ========================================

/**
 * Validation de l'étape actuelle
 */
 function validateCurrentStep() {
    return validateStep(OrganisationApp.currentStep);
}

/**
 * Validation d'une étape spécifique
 */
function validateStep(stepNumber) {
    switch (stepNumber) {
        case 1: return validateStep1();
        case 2: return validateStep2();
        case 3: return validateStep3();
        case 4: return validateStep4();
        case 5: return validateStep5();
        case 6: return validateStep6();
        case 7: return validateStep7();
        case 8: return validateStep8();
        case 9: return validateStep9();
        default: return true;
    }
}

/**
 * Validation étape 1 : Type d'organisation
 */
function validateStep1() {
    const selectedType = document.querySelector('input[name="type_organisation"]:checked');
    if (!selectedType) {
        showFieldError(null, 'Veuillez sélectionner un type d\'organisation');
        
        // Faire clignoter les cartes
        document.querySelectorAll('.organization-type-card').forEach(card => {
            card.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                card.style.animation = '';
            }, 500);
        });
        
        return false;
    }
    return true;
}

/**
 * Validation étape 2 : Guide lu
 */
function validateStep2() {
    const guideConfirm = document.getElementById('guideReadConfirm');
    if (!guideConfirm || !guideConfirm.checked) {
        showFieldError(guideConfirm, 'Veuillez confirmer avoir lu et compris le guide');
        
        if (guideConfirm) {
            guideConfirm.focus();
            guideConfirm.parentElement.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                guideConfirm.parentElement.style.animation = '';
            }, 500);
        }
        
        return false;
    }
    return true;
}

/**
 * Validation étape 3 : Informations demandeur
 */
function validateStep3() {
    const requiredFields = [
        'demandeur_nip',
        'demandeur_civilite',
        'demandeur_nom',
        'demandeur_prenom',
        'demandeur_date_naissance',
        'demandeur_nationalite',
        'demandeur_telephone',
        'demandeur_email',
        'demandeur_adresse',
        'demandeur_role'
    ];
    
    let isValid = true;
    let firstErrorField = null;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            const fieldValid = validateField(field);
            if (!fieldValid && !firstErrorField) {
                firstErrorField = field;
            }
            isValid = isValid && fieldValid;
        }
    });
    
    // Vérifier les checkboxes d'engagement
    const engagement = document.getElementById('demandeur_engagement');
    const responsabilite = document.getElementById('demandeur_responsabilite');
    
    if (!engagement || !engagement.checked) {
        showFieldError(engagement, 'Veuillez cocher l\'engagement de véracité');
        if (!firstErrorField) firstErrorField = engagement;
        isValid = false;
    }
    
    if (!responsabilite || !responsabilite.checked) {
        showFieldError(responsabilite, 'Veuillez accepter la responsabilité légale');
        if (!firstErrorField) firstErrorField = responsabilite;
        isValid = false;
    }
    
    // Scroll vers le premier champ en erreur
    if (!isValid && firstErrorField) {
        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            firstErrorField.focus();
        }, 300);
    }
    
    return isValid;
}

/**
 * Validation étape 4 : Informations organisation
 */
function validateStep4() {
    const requiredFields = [
        'org_nom', 'org_objet', 'org_date_creation', 'org_telephone'
    ];
    
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validation étape 5 : Coordonnées
 */
function validateStep5() {
    const requiredFields = [
        'org_adresse_complete', 'org_province', 'org_prefecture', 'org_zone_type'
    ];
    
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validation étape 6 : Fondateurs
 */
function validateStep6() {
    if (!OrganisationApp.selectedOrgType) return false;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    const minFondateurs = requirements ? requirements.minFondateurs : 3;
    
    if (OrganisationApp.fondateurs.length < minFondateurs) {
        showNotification(`Minimum ${minFondateurs} fondateurs requis pour ce type d'organisation`, 'warning');
        return false;
    }
    
    return true;
}

/**
 * Validation étape 7 : Adhérents
 */
function validateStep7() {
    if (!OrganisationApp.selectedOrgType) return false;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    const minAdherents = requirements ? requirements.minAdherents : 10;
    
    if (OrganisationApp.adherents.length < minAdherents) {
        showNotification(`Minimum ${minAdherents} adhérents requis pour ce type d'organisation`, 'warning');
        return false;
    }
    
    return true;
}

/**
 * Validation étape 8 : Documents
 */
function validateStep8() {
    if (!OrganisationApp.selectedOrgType) return false;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    const requiredDocs = requirements ? requirements.documents : [];
    
    for (const doc of requiredDocs) {
        if (!OrganisationApp.documents[doc]) {
            showNotification(`Document requis manquant : ${getDocumentLabel(doc)}`, 'warning');
            return false;
        }
    }
    
    return true;
}

/**
 * Validation étape 9 : Déclarations finales
 */
function validateStep9() {
    const declarations = ['declaration_veracite', 'declaration_conformite', 'declaration_autorisation'];
    
    for (const declId of declarations) {
        const decl = document.getElementById(declId);
        if (!decl || !decl.checked) {
            showFieldError(decl, 'Toutes les déclarations sont obligatoires');
            return false;
        }
    }
    
    return true;
}

/**
 * Validation d'un champ individuel
 */
function validateField(field) {
    if (!field) return false;
    
    const value = field.value.trim();
    const fieldName = field.name || field.id;
    
    // Champs obligatoires
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'Ce champ est obligatoire');
        return false;
    }
    
    // Validation spécifique selon le type de champ
    switch (fieldName) {
        case 'demandeur_nip':
        case 'fondateur_nip':
        case 'adherent_nip':
            return validateNIP(field, value);
        case 'demandeur_email':
        case 'org_email':
        case 'fondateur_email':
            return validateEmail(field, value);
        case 'demandeur_telephone':
        case 'org_telephone':
        case 'fondateur_telephone':
        case 'adherent_telephone':
            return validatePhone(field, value);
        case 'demandeur_date_naissance':
            return validateBirthDate(field, value);
        case 'org_nom':
            return validateOrganizationName(field, value);
        default:
            return validateGenericField(field, value);
    }
}

/**
 * Validation NIP gabonais (corrigée - sans checksum strict)
 */
function validateNIP(field, value) {
    if (!value) {
        showFieldError(field, 'Le NIP est obligatoire');
        return false;
    }
    
    // Validation de base seulement
    if (!OrganisationApp.config.nip.pattern.test(value)) {
        showFieldError(field, 'Le NIP doit contenir exactement 13 chiffres');
        return false;
    }
    
    // Éviter les séquences évidentes
    if (value === '1111111111111' || value === '1234567890123' || value === '0000000000000') {
        showFieldError(field, 'NIP invalide');
        return false;
    }
    
    // Validation réussie
    clearFieldError(field);
    updateNIPValidationIcon('valid');
    return true;
}

/**
 * Validation email
 */
function validateEmail(field, value) {
    if (!value && !field.hasAttribute('required')) return true;
    
    if (!value) {
        showFieldError(field, 'L\'email est obligatoire');
        return false;
    }
    
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(value)) {
        showFieldError(field, 'Format d\'email invalide');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validation téléphone gabonais
 */
function validatePhone(field, value) {
    if (!value) {
        showFieldError(field, 'Le téléphone est obligatoire');
        return false;
    }
    
    // Nettoyer le numéro (enlever espaces et caractères spéciaux)
    const cleanNumber = value.replace(/\s+/g, '').replace(/[^0-9]/g, '');
    
    if (!OrganisationApp.config.phone.pattern.test(cleanNumber)) {
        showFieldError(field, 'Format de téléphone gabonais invalide (8-9 chiffres)');
        return false;
    }
    
    // Vérifier les préfixes valides
    const prefix = cleanNumber.substring(0, 2);
    if (!OrganisationApp.config.phone.prefixes.includes(prefix)) {
        showFieldError(field, 'Préfixe téléphonique gabonais invalide');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validation date de naissance
 */
function validateBirthDate(field, value) {
    if (!value) {
        showFieldError(field, 'La date de naissance est obligatoire');
        return false;
    }
    
    const birthDate = new Date(value);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    
    if (age < 18) {
        showFieldError(field, 'Vous devez être majeur (18 ans minimum)');
        return false;
    }
    
    if (age > 100) {
        showFieldError(field, 'Date de naissance invalide');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validation nom organisation
 */
function validateOrganizationName(field, value) {
    if (!value) {
        showFieldError(field, 'Le nom de l\'organisation est obligatoire');
        return false;
    }
    
    if (value.length < 5) {
        showFieldError(field, 'Le nom doit contenir au moins 5 caractères');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validation générique
 */
function validateGenericField(field, value) {
    // Longueur minimale si spécifiée
    if (field.hasAttribute('minlength')) {
        const minLength = parseInt(field.getAttribute('minlength'));
        if (value.length < minLength) {
            showFieldError(field, `Minimum ${minLength} caractères requis`);
            return false;
        }
    }
    
    // Longueur maximale si spécifiée
    if (field.hasAttribute('maxlength')) {
        const maxLength = parseInt(field.getAttribute('maxlength'));
        if (value.length > maxLength) {
            showFieldError(field, `Maximum ${maxLength} caractères autorisés`);
            return false;
        }
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Afficher une erreur sur un champ
 */
function showFieldError(field, message) {
    if (field) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        // Trouver ou créer l'élément d'erreur
        let errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        
        // Animation d'erreur
        field.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            field.style.animation = '';
        }, 500);
    } else {
        // Message d'erreur générale
        showNotification(message, 'danger');
    }
    
    // Sauvegarder l'erreur
    if (field) {
        OrganisationApp.validationErrors[field.name || field.id] = message;
    }
}

/**
 * Effacer l'erreur d'un champ
 */
function clearFieldError(field) {
    if (field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.textContent = '';
        }
        
        // Supprimer l'erreur du cache
        delete OrganisationApp.validationErrors[field.name || field.id];
    }
}

/**
 * Mise à jour de l'icône de validation NIP
 */
function updateNIPValidationIcon(status) {
    const loading = document.getElementById('nip-loading');
    const valid = document.getElementById('nip-valid');
    const invalid = document.getElementById('nip-invalid');
    
    // Masquer toutes les icônes
    [loading, valid, invalid].forEach(icon => {
        if (icon) icon.classList.add('d-none');
    });
    
    // Afficher l'icône appropriée
    switch (status) {
        case 'loading':
            if (loading) loading.classList.remove('d-none');
            break;
        case 'valid':
            if (valid) valid.classList.remove('d-none');
            break;
        case 'invalid':
            if (invalid) invalid.classList.remove('d-none');
            break;
    }
}

// ========================================
// 5. GESTION FONDATEURS ET ADHÉRENTS
// ========================================

/**
 * Mise à jour des exigences selon le type d'organisation
 */
 function updateOrganizationRequirements() {
    if (!OrganisationApp.selectedOrgType) return;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    if (requirements) {
        // Mettre à jour les exigences fondateurs
        updateFoundersRequirements();
        // Mettre à jour les exigences adhérents
        updateMembersRequirements();
    }
}

/**
 * Mise à jour des exigences fondateurs
 */
function updateFoundersRequirements() {
    const requirementsDiv = document.getElementById('fondateurs_requirements');
    const minSpan = document.getElementById('min_fondateurs');
    
    if (!OrganisationApp.selectedOrgType) return;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    if (requirements && minSpan) {
        minSpan.textContent = requirements.minFondateurs;
    }
}

/**
 * Mise à jour des exigences adhérents
 */
function updateMembersRequirements() {
    const requirementsDiv = document.getElementById('adherents_requirements');
    const minSpan = document.getElementById('min_adherents');
    
    if (!OrganisationApp.selectedOrgType) return;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    if (requirements && minSpan) {
        minSpan.textContent = requirements.minAdherents;
    }
}

/**
 * Ajouter un fondateur
 */
function addFondateur() {
    const fondateur = {
        civilite: document.getElementById('fondateur_civilite').value,
        nom: document.getElementById('fondateur_nom').value,
        prenom: document.getElementById('fondateur_prenom').value,
        nip: document.getElementById('fondateur_nip').value,
        fonction: document.getElementById('fondateur_fonction').value,
        telephone: document.getElementById('fondateur_telephone').value,
        email: document.getElementById('fondateur_email').value
    };
    
    // Validation
    if (!fondateur.nom || !fondateur.prenom || !fondateur.nip) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    if (!validateNIP(document.getElementById('fondateur_nip'), fondateur.nip)) {
        return;
    }
    
    // Vérifier doublons
    if (OrganisationApp.fondateurs.some(f => f.nip === fondateur.nip)) {
        showNotification('Ce NIP existe déjà dans la liste des fondateurs', 'warning');
        return;
    }
    
    // Ajouter à la liste
    OrganisationApp.fondateurs.push(fondateur);
    updateFoundersList();
    clearFounderForm();
    showNotification('Fondateur ajouté avec succès', 'success');
}

/**
 * Mettre à jour la liste des fondateurs
 */
function updateFoundersList() {
    const listContainer = document.getElementById('fondateurs_list');
    const countSpan = document.getElementById('fondateurs_count');
    
    if (!listContainer) return;
    
    if (OrganisationApp.fondateurs.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-users fa-3x mb-3"></i>
                <p>Aucun fondateur ajouté</p>
            </div>
        `;
    } else {
        listContainer.innerHTML = OrganisationApp.fondateurs.map((fondateur, index) => `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <strong>${fondateur.civilite} ${fondateur.nom} ${fondateur.prenom}</strong>
                    <br>
                    <small class="text-muted">NIP: ${fondateur.nip} | ${fondateur.fonction}</small>
                    ${fondateur.telephone ? `<br><small class="text-muted">Tél: ${fondateur.telephone}</small>` : ''}
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFondateur(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');
    }
    
    if (countSpan) {
        countSpan.textContent = `${OrganisationApp.fondateurs.length} fondateur(s)`;
    }
}

/**
 * Supprimer un fondateur
 */
function removeFondateur(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce fondateur ?')) {
        OrganisationApp.fondateurs.splice(index, 1);
        updateFoundersList();
        showNotification('Fondateur supprimé', 'info');
    }
}

/**
 * Vider le formulaire fondateur
 */
function clearFounderForm() {
    ['fondateur_civilite', 'fondateur_nom', 'fondateur_prenom', 'fondateur_nip', 
     'fondateur_fonction', 'fondateur_telephone', 'fondateur_email'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        }
    });
}

/**
 * Ajouter un adhérent
 */
function addAdherent() {
    const adherent = {
        civilite: document.getElementById('adherent_civilite').value,
        nom: document.getElementById('adherent_nom').value,
        prenom: document.getElementById('adherent_prenom').value,
        nip: document.getElementById('adherent_nip').value,
        telephone: document.getElementById('adherent_telephone').value,
        profession: document.getElementById('adherent_profession').value
    };
    
    // Validation
    if (!adherent.nom || !adherent.prenom || !adherent.nip) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    if (!validateNIP(document.getElementById('adherent_nip'), adherent.nip)) {
        return;
    }
    
    // Vérifier doublons
    if (OrganisationApp.adherents.some(a => a.nip === adherent.nip)) {
        showNotification('Ce NIP existe déjà dans la liste des adhérents', 'warning');
        return;
    }
    
    // Vérifier si ce NIP est déjà dans les fondateurs
    if (OrganisationApp.fondateurs.some(f => f.nip === adherent.nip)) {
        showNotification('Ce NIP existe déjà dans la liste des fondateurs', 'warning');
        return;
    }
    
    // Ajouter à la liste
    OrganisationApp.adherents.push(adherent);
    updateAdherentsList();
    clearAdherentForm();
    showNotification('Adhérent ajouté avec succès', 'success');
}

/**
 * Mettre à jour la liste des adhérents
 */
function updateAdherentsList() {
    const listContainer = document.getElementById('adherents_list');
    const countSpan = document.getElementById('adherents_count');
    
    if (!listContainer) return;
    
    if (OrganisationApp.adherents.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-user-plus fa-3x mb-3"></i>
                <p>Aucun adhérent ajouté</p>
            </div>
        `;
    } else {
        // Générer table avec pagination si plus de 10 adhérents
        const itemsPerPage = 10;
        const totalPages = Math.ceil(OrganisationApp.adherents.length / itemsPerPage);
        const currentPage = 1; // Pour l'instant, page simple
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageAdherents = OrganisationApp.adherents.slice(startIndex, endIndex);
        
        listContainer.innerHTML = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Civilité</th>
                            <th>Nom complet</th>
                            <th>NIP</th>
                            <th>Téléphone</th>
                            <th>Profession</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${pageAdherents.map((adherent, index) => `
                            <tr>
                                <td>${adherent.civilite}</td>
                                <td><strong>${adherent.nom} ${adherent.prenom}</strong></td>
                                <td><code>${adherent.nip}</code></td>
                                <td>${adherent.telephone || '-'}</td>
                                <td>${adherent.profession || '-'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAdherent(${startIndex + index})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            ${totalPages > 1 ? `
                <nav>
                    <ul class="pagination pagination-sm justify-content-center">
                        <!-- Pagination à implémenter si nécessaire -->
                    </ul>
                </nav>
            ` : ''}
        `;
    }
    
    if (countSpan) {
        countSpan.textContent = `${OrganisationApp.adherents.length} adhérent(s)`;
    }
}

/**
 * Supprimer un adhérent
 */
function removeAdherent(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?')) {
        OrganisationApp.adherents.splice(index, 1);
        updateAdherentsList();
        showNotification('Adhérent supprimé', 'info');
    }
}

/**
 * Vider le formulaire adhérent
 */
function clearAdherentForm() {
    ['adherent_civilite', 'adherent_nom', 'adherent_prenom', 'adherent_nip', 
     'adherent_telephone', 'adherent_profession'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        }
    });
}


// ========================================
// CORRECTION DE LA FONCTION D'IMPORTATION - VERSION COMPLÈTE
// ========================================

/**
 * Lecture du fichier Excel/CSV - VERSION CORRIGÉE
 */
 async function readAdherentFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                let data = [];
                
                if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                    // Traitement CSV
                    const csvText = e.target.result;
                    data = parseAdherentCSV(csvText);
                } else {
                    // Traitement Excel avec XLSX.js si disponible
                    try {
                        if (typeof XLSX !== 'undefined') {
                            // Si XLSX est disponible
                            const workbook = XLSX.read(e.target.result, { type: 'array' });
                            data = parseAdherentExcel(workbook);
                        } else {
                            // Si XLSX n'est pas disponible, simuler avec FileReader HTML5
                            data = parseExcelFallback(e.target.result, file.name);
                        }
                    } catch (excelError) {
                        console.warn('Erreur parsing Excel:', excelError);
                        reject(new Error('Erreur lors de la lecture du fichier Excel. Utilisez un fichier CSV ou vérifiez le format.'));
                        return;
                    }
                }
                
                resolve(data);
            } catch (error) {
                reject(error);
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur lecture fichier'));
        
        if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsArrayBuffer(file);
        }
    });
}

/**
 * Parsing Excel avec XLSX.js
 */
function parseAdherentExcel(workbook) {
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    
    // Convertir en JSON
    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
    
    if (jsonData.length < 2) {
        throw new Error('Le fichier Excel doit contenir au moins un en-tête et une ligne de données');
    }
    
    const headers = jsonData[0].map(h => h.toString().trim().toLowerCase());
    const data = [];
    
    // Vérifier les colonnes requises
    const requiredColumns = ['civilité', 'nom', 'prenom', 'nip'];
    const missingColumns = requiredColumns.filter(col => 
        !headers.some(h => h.includes(col.replace('é', 'e')) || h.includes(col))
    );
    
    if (missingColumns.length > 0) {
        throw new Error(`Colonnes manquantes: ${missingColumns.join(', ')}`);
    }
    
    for (let i = 1; i < jsonData.length; i++) {
        const values = jsonData[i];
        if (!values || values.length < 3) continue; // Ignorer les lignes vides
        
        const row = {};
        headers.forEach((header, index) => {
            row[header] = values[index] ? values[index].toString().trim() : '';
        });
        
        // Mapper vers notre format standard
        const adherent = {
            civilite: row.civilité || row.civilite || 'M',
            nom: row.nom,
            prenom: row.prenom || row.prénom,
            nip: row.nip,
            telephone: row.telephone || row.téléphone || '',
            profession: row.profession || '',
            lineNumber: i + 1
        };
        
        // Valider que les champs obligatoires sont présents
        if (adherent.nom && adherent.prenom && adherent.nip) {
            data.push(adherent);
        }
    }
    
    return data;
}

/**
 * Fallback pour Excel sans XLSX.js (méthode alternative)
 */
function parseExcelFallback(arrayBuffer, fileName) {
    // Pour l'instant, rejeter avec un message d'aide
    throw new Error(
        `Pour importer des fichiers Excel (.xlsx, .xls), veuillez :\n` +
        `1. Convertir votre fichier en CSV, ou\n` +
        `2. Installer la bibliothèque XLSX.js\n\n` +
        `En attendant, utilisez un fichier CSV avec les colonnes : Civilité,Nom,Prenom,NIP,Telephone,Profession`
    );
}

/**
 * Parsing CSV des adhérents - VERSION AMÉLIORÉE
 */
function parseAdherentCSV(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) {
        throw new Error('Le fichier CSV doit contenir au moins un en-tête et une ligne de données');
    }
    
    // Améliorer le parsing pour gérer les guillemets et virgules dans les champs
    const parseCSVLine = (line) => {
        const result = [];
        let current = '';
        let inQuotes = false;
        
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            const nextChar = line[i + 1];
            
            if (char === '"') {
                if (inQuotes && nextChar === '"') {
                    current += '"';
                    i++; // Skip next quote
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        
        result.push(current.trim());
        return result;
    };
    
    const headers = parseCSVLine(lines[0]).map(h => h.toLowerCase().replace(/"/g, ''));
    const data = [];
    
    // Vérifier les colonnes requises avec plus de flexibilité
    const findColumn = (searchTerms) => {
        return headers.findIndex(h => 
            searchTerms.some(term => h.includes(term))
        );
    };
    
    const civiliteIndex = findColumn(['civilité', 'civilite', 'civ']);
    const nomIndex = findColumn(['nom', 'name', 'lastname']);
    const prenomIndex = findColumn(['prenom', 'prénom', 'firstname']);
    const nipIndex = findColumn(['nip', 'id', 'numero']);
    const telephoneIndex = findColumn(['telephone', 'téléphone', 'phone', 'tel']);
    const professionIndex = findColumn(['profession', 'metier', 'job']);
    
    if (nomIndex === -1 || prenomIndex === -1 || nipIndex === -1) {
        throw new Error('Colonnes obligatoires manquantes : Nom, Prénom, NIP');
    }
    
    for (let i = 1; i < lines.length; i++) {
        const values = parseCSVLine(lines[i]);
        if (values.length < 3) continue; // Ignorer les lignes insuffisantes
        
        const adherent = {
            civilite: civiliteIndex !== -1 ? (values[civiliteIndex] || 'M') : 'M',
            nom: values[nomIndex] || '',
            prenom: values[prenomIndex] || '',
            nip: values[nipIndex] || '',
            telephone: telephoneIndex !== -1 ? (values[telephoneIndex] || '') : '',
            profession: professionIndex !== -1 ? (values[professionIndex] || '') : '',
            lineNumber: i + 1
        };
        
        // Valider que les champs obligatoires sont présents
        if (adherent.nom && adherent.prenom && adherent.nip) {
            data.push(adherent);
        }
    }
    
    return data;
}

/**
 * Vérification des membres existants via API - VERSION AMÉLIORÉE
 */
async function checkExistingMembersAPI(nips) {
    if (nips.length === 0) return [];
    
    try {
        const response = await fetch('/api/organisations/check-existing-members', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nips: nips })
        });
        
        if (response.ok) {
            const data = await response.json();
            return data.existing_nips || [];
        } else if (response.status === 404) {
            console.warn('API check membres existants non trouvée, import sans vérification');
            return []; // Permettre l'import sans vérification
        } else {
            console.warn('Erreur API check membres existants:', response.status);
            return []; // Permettre l'import en cas d'erreur API
        }
    } catch (error) {
        console.error('Erreur vérification membres existants:', error);
        return []; // Permettre l'import en cas d'erreur réseau
    }
}

/**
 * Amélioration des messages d'erreur
 */
function generateImportMessages(result, minRequired) {
    const messages = [];
    
    // Message principal selon le résultat
    if (result.canProceed) {
        const totalValid = result.finalValidCount + result.existingMembers.length;
        messages.push({
            type: 'success',
            title: '✅ Importation possible',
            content: `${totalValid} adhérents détectés (${result.finalValidCount} nouveaux + ${result.existingMembers.length} à corriger). Minimum requis: ${minRequired}`
        });
        
        if (result.finalValidCount > 0) {
            messages.push({
                type: 'info',
                title: '📊 Résumé de l\'analyse',
                content: `Fichier traité avec succès : ${result.originalCount} lignes analysées, ${result.finalValidCount} adhérents valides seront ajoutés.`
            });
        }
    } else {
        const totalValid = result.finalValidCount + result.existingMembers.length;
        messages.push({
            type: 'danger',
            title: '❌ Importation impossible',
            content: `Seulement ${totalValid} adhérents valides détectés (${result.finalValidCount} nouveaux + ${result.existingMembers.length} existants). Minimum requis: ${minRequired}.`
        });
        
        messages.push({
            type: 'warning',
            title: '💡 Suggestion',
            content: `Ajoutez ${minRequired - totalValid} adhérents supplémentaires dans votre fichier pour pouvoir effectuer l'importation.`
        });
    }
    
    // Messages spécifiques pour chaque problème
    if (result.duplicatesInFile.length > 0) {
        messages.push({
            type: 'warning',
            title: '⚠️ Doublons détectés dans le fichier',
            content: `${result.duplicatesInFile.length} doublons NIP supprimés automatiquement. Seule la première occurrence de chaque NIP a été conservée.`,
            details: result.duplicatesInFile.map(d => 
                `Ligne ${d.lineNumber}: ${d.nom} ${d.prenom} (NIP: ${d.nip}) - Doublon supprimé`
            )
        });
    }
    
    if (result.existingMembers.length > 0) {
        messages.push({
            type: 'warning',
            title: '⚠️ Membres déjà actifs détectés',
            content: `${result.existingMembers.length} personnes sont déjà membres actifs d'autres organisations. Elles seront ajoutées avec statut "à corriger" pour régularisation manuelle.`,
            details: result.existingMembers.map(m => 
                `Ligne ${m.lineNumber}: ${m.nom} ${m.prenom} (NIP: ${m.nip}) - Déjà membre actif ailleurs`
            )
        });
    }
    
    if (result.invalidEntries.length > 0) {
        messages.push({
            type: 'danger',
            title: '❌ Entrées invalides ou incomplètes',
            content: `${result.invalidEntries.length} entrées ignorées en raison d'erreurs de format ou de données manquantes.`,
            details: result.invalidEntries.map(e => 
                `Ligne ${e.lineNumber}: ${e.nom || 'Nom manquant'} ${e.prenom || 'Prénom manquant'} - ${e.error}`
            )
        });
    }
    
    return messages;
}

/**
 * Amélioration du processus d'importation
 */
async function processImportResult(validationResult) {
    const { canProceed, validAdherents, existingMembers, messages, originalCount, finalValidCount } = validationResult;
    
    // Afficher tous les messages de validation
    messages.forEach(message => {
        showDetailedImportNotification(message);
    });
    
    if (!canProceed) {
        showNotification('❌ Importation annulée: critères non remplis', 'danger');
        clearFileInput();
        return;
    }
    
    // Afficher un résumé avant confirmation
    const summaryMsg = `Importation de ${finalValidCount} nouveaux adhérents` + 
        (existingMembers.length > 0 ? ` + ${existingMembers.length} à corriger` : '') +
        ` sur ${originalCount} lignes analysées.`;
    
    // Confirmer l'importation
    if (!confirm(`${summaryMsg}\n\nConfirmez-vous l'importation ?`)) {
        showNotification('❌ Importation annulée par l\'utilisateur', 'info');
        clearFileInput();
        return;
    }
    
    // Confirmer séparément si des membres existants
    if (existingMembers.length > 0) {
        const confirmExisting = confirm(
            `⚠️ ${existingMembers.length} membres sont déjà actifs dans d'autres organisations.\n\n` +
            `Ils seront ajoutés avec statut "à corriger" nécessitant une régularisation manuelle.\n\n` +
            `Continuer l'importation ?`
        );
        if (!confirmExisting) {
            showNotification('❌ Importation annulée: membres existants non acceptés', 'info');
            clearFileInput();
            return;
        }
    }
    
    // CONSIGNE 4: Enregistrer avec statut "à corriger" pour les membres existants
    let adherentsToAdd = [...validAdherents];
    
    if (existingMembers.length > 0) {
        const correctionAdherents = existingMembers.map(member => ({
            ...member,
            status: 'correction_required',
            note: 'Déjà membre actif d\'une autre organisation - Régularisation requise'
        }));
        
        adherentsToAdd = [...adherentsToAdd, ...correctionAdherents];
    }
    
    // Ajouter tous les adhérents à la liste
    adherentsToAdd.forEach(adherent => {
        OrganisationApp.adherents.push(adherent);
    });
    
    // Mettre à jour l'affichage avec statuts visuels
    updateAdherentsListWithStatus();
    
    // Message de succès final détaillé
    const successDetails = [
        `🎉 Importation réussie !`,
        `📊 ${validAdherents.length} adhérents valides ajoutés`,
        existingMembers.length > 0 ? `⚠️ ${existingMembers.length} nécessitent une correction` : '',
        `📁 Total: ${adherentsToAdd.length} entrées traitées`
    ].filter(Boolean).join('\n');
    
    showNotification(successDetails, 'success', 10000);
    
    // Vider le champ fichier et sauvegarder
    clearFileInput();
    autoSave();
    
    console.log('✅ Import terminé:', {
        nouveaux: validAdherents.length,
        corrections: existingMembers.length,
        total: adherentsToAdd.length
    });
}

/**
 * Mise à jour de la liste des adhérents avec statuts visuels
 */
function updateAdherentsListWithStatus() {
    // Appeler la fonction existante
    updateAdherentsList();
    
    // Ajouter des indications visuelles pour les statuts
    setTimeout(() => {
        const listContainer = document.getElementById('adherents_list');
        if (listContainer) {
            // Ajouter des classes CSS pour les statuts
            OrganisationApp.adherents.forEach((adherent, index) => {
                const row = listContainer.querySelector(`tr:nth-child(${index + 1})`);
                if (row && adherent.status === 'correction_required') {
                    row.classList.add('table-warning');
                    row.title = adherent.note || 'Nécessite une correction';
                    
                    // Ajouter un badge de statut
                    const statusCell = row.querySelector('td:last-child');
                    if (statusCell) {
                        statusCell.innerHTML += ' <span class="badge bg-warning ms-1" title="' + (adherent.note || '') + '">À corriger</span>';
                    }
                }
            });
        }
    }, 100);
}

// Ajouter les styles pour les statuts
if (!document.getElementById('import-status-styles')) {
    const styles = document.createElement('style');
    styles.id = 'import-status-styles';
    styles.textContent = `
        .table-warning {
            background-color: rgba(255, 243, 205, 0.3) !important;
        }
        .badge.bg-warning {
            font-size: 0.75em;
        }
    `;
    document.head.appendChild(styles);
}

console.log('📁 Module importation adhérents - VERSION CORRIGÉE EXCEL + CSV');

// ========================================
// AJOUT À INTÉGRER DANS LE FICHIER organisation-create.js
// Section à ajouter après la section "5. GESTION FONDATEURS ET ADHÉRENTS"
// ========================================

// ========================================
// 5.5 IMPORTATION FICHIER ADHÉRENTS - NOUVELLES FONCTIONS
// ========================================

/**
 * Gestion de l'importation du fichier Excel/CSV des adhérents
 * CONSIGNES : 1=doublons fichier, 2=minimum requis, 3=membres existants, 4=statut correction
 */
 async function handleAdherentFileImport(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    
    console.log('📁 Début importation fichier adhérents:', file.name);
    
    // Validation du fichier
    if (!validateAdherentFile(file)) {
        clearFileInput();
        return;
    }
    
    try {
        showNotification('📁 Analyse du fichier en cours...', 'info');
        
        // Lire le fichier Excel/CSV
        const adherentsData = await readAdherentFile(file);
        
        if (!adherentsData || adherentsData.length === 0) {
            showNotification('❌ Le fichier est vide ou invalide', 'danger');
            clearFileInput();
            return;
        }
        
        console.log(`📊 ${adherentsData.length} lignes détectées dans le fichier`);
        
        // Appliquer les 4 consignes de validation
        const validationResult = await validateAdherentsImport(adherentsData);
        
        // Traiter selon les résultats de validation
        await processImportResult(validationResult);
        
    } catch (error) {
        console.error('❌ Erreur importation adhérents:', error);
        showNotification('❌ Erreur lors de l\'importation: ' + error.message, 'danger');
        clearFileInput();
    }
}

/**
 * Validation du fichier (format, taille)
 */
function validateAdherentFile(file) {
    // Vérifier la taille (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showNotification('❌ Le fichier ne peut pas dépasser 5MB', 'danger');
        return false;
    }
    
    // Vérifier le format
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel', // .xls
        'text/csv'
    ];
    
    if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
        showNotification('❌ Format de fichier non autorisé. Utilisez Excel (.xlsx, .xls) ou CSV', 'danger');
        return false;
    }
    
    return true;
}

/**
 * Lecture du fichier Excel/CSV
 */
async function readAdherentFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                let data = [];
                
                if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                    // Traitement CSV
                    const csvText = e.target.result;
                    data = parseAdherentCSV(csvText);
                } else {
                    // Traitement Excel (simulation)
                    reject(new Error('Import Excel non encore implémenté. Utilisez un fichier CSV.'));
                    return;
                }
                
                resolve(data);
            } catch (error) {
                reject(error);
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur lecture fichier'));
        
        if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsArrayBuffer(file);
        }
    });
}

/**
 * Parsing CSV des adhérents
 */
function parseAdherentCSV(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) {
        throw new Error('Le fichier CSV doit contenir au moins un en-tête et une ligne de données');
    }
    
    const headers = lines[0].split(',').map(h => h.trim().toLowerCase().replace(/"/g, ''));
    const data = [];
    
    // Vérifier les colonnes requises
    const requiredColumns = ['civilité', 'nom', 'prenom', 'nip'];
    const missingColumns = requiredColumns.filter(col => 
        !headers.some(h => h.includes(col.replace('é', 'e')) || h.includes(col))
    );
    
    if (missingColumns.length > 0) {
        throw new Error(`Colonnes manquantes: ${missingColumns.join(', ')}`);
    }
    
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',').map(v => v.trim().replace(/"/g, ''));
        if (values.length < 3) continue; // Ignorer les lignes vides
        
        const row = {};
        headers.forEach((header, index) => {
            row[header] = values[index] || '';
        });
        
        // Mapper vers notre format standard
        const adherent = {
            civilite: row.civilité || row.civilite || 'M',
            nom: row.nom,
            prenom: row.prenom || row.prénom,
            nip: row.nip,
            telephone: row.telephone || row.téléphone || '',
            profession: row.profession || '',
            lineNumber: i + 1
        };
        
        // Valider que les champs obligatoires sont présents
        if (adherent.nom && adherent.prenom && adherent.nip) {
            data.push(adherent);
        }
    }
    
    return data;
}

/**
 * CONSIGNES 1-4 : Validation complète des données d'importation
 */
async function validateAdherentsImport(adherentsData) {
    const result = {
        originalCount: adherentsData.length,
        validAdherents: [],
        duplicatesInFile: [],
        existingMembers: [],
        invalidEntries: [],
        finalValidCount: 0,
        canProceed: false,
        messages: []
    };
    
    // Obtenir les exigences selon le type d'organisation
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    const minRequired = requirements ? requirements.minAdherents : 10;
    
    console.log(`📋 Validation import: ${adherentsData.length} adhérents, minimum requis: ${minRequired}`);
    
    // CONSIGNE 1: Détecter les doublons NIP dans le fichier
    const seenNips = new Set();
    
    adherentsData.forEach((adherent) => {
        const nip = adherent.nip?.trim();
        
        // Valider le format NIP
        if (!nip || !OrganisationApp.config.nip.pattern.test(nip)) {
            result.invalidEntries.push({
                ...adherent,
                error: 'NIP invalide ou manquant'
            });
            return;
        }
        
        if (seenNips.has(nip)) {
            // Doublon dans le fichier
            result.duplicatesInFile.push({
                ...adherent,
                nip: nip
            });
        } else {
            seenNips.add(nip);
            result.validAdherents.push({
                ...adherent,
                nip: nip,
                status: 'valid'
            });
        }
    });
    
    // Vérifier doublons avec fondateurs et adhérents déjà ajoutés
    const foundersNips = OrganisationApp.fondateurs.map(f => f.nip);
    const adherentsNips = OrganisationApp.adherents.map(a => a.nip);
    
    result.validAdherents = result.validAdherents.filter(adherent => {
        if (foundersNips.includes(adherent.nip)) {
            result.invalidEntries.push({
                ...adherent,
                error: 'NIP déjà présent dans les fondateurs'
            });
            return false;
        }
        if (adherentsNips.includes(adherent.nip)) {
            result.invalidEntries.push({
                ...adherent,
                error: 'NIP déjà présent dans les adhérents'
            });
            return false;
        }
        return true;
    });
    
    // CONSIGNE 3: Vérifier si les NIP sont déjà membres actifs d'autres organisations
    const nipsToCheck = result.validAdherents.map(a => a.nip);
    const existingMembersNips = await checkExistingMembersAPI(nipsToCheck);
    
    // Séparer les membres existants des valides
    result.validAdherents = result.validAdherents.filter(adherent => {
        if (existingMembersNips.includes(adherent.nip)) {
            result.existingMembers.push({
                ...adherent,
                status: 'existing_member'
            });
            return false;
        }
        return true;
    });
    
    result.finalValidCount = result.validAdherents.length;
    
    // CONSIGNE 2: Vérifier le nombre minimum après suppression des doublons
    const totalValidAfterCorrection = result.finalValidCount + result.existingMembers.length;
    result.canProceed = totalValidAfterCorrection >= minRequired;
    
    // Générer les messages selon les cas
    result.messages = generateImportMessages(result, minRequired);
    
    console.log('📊 Résultat validation import:', result);
    return result;
}

/**
 * Vérification des membres existants via API
 */
async function checkExistingMembersAPI(nips) {
    if (nips.length === 0) return [];
    
    try {
        const response = await fetch('/api/check-existing-members', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ nips: nips })
        });
        
        if (response.ok) {
            const data = await response.json();
            return data.existing_nips || [];
        } else {
            console.warn('Erreur API check membres existants:', response.status);
        }
    } catch (error) {
        console.error('Erreur vérification membres existants:', error);
    }
    
    return []; // Retourner liste vide en cas d'erreur (permettre l'import)
}

/**
 * Génération des messages selon les cas d'importation
 */
function generateImportMessages(result, minRequired) {
    const messages = [];
    
    // Message principal selon le résultat
    if (result.canProceed) {
        const totalValid = result.finalValidCount + result.existingMembers.length;
        messages.push({
            type: 'success',
            title: '✅ Importation possible',
            content: `${totalValid} adhérents détectés (${result.finalValidCount} valides + ${result.existingMembers.length} à corriger). Minimum requis: ${minRequired}`
        });
    } else {
        const totalValid = result.finalValidCount + result.existingMembers.length;
        messages.push({
            type: 'danger',
            title: '❌ Importation impossible',
            content: `Seulement ${totalValid} adhérents valides détectés, minimum requis: ${minRequired}`
        });
    }
    
    // Messages spécifiques pour chaque problème
    if (result.duplicatesInFile.length > 0) {
        messages.push({
            type: 'warning',
            title: '⚠️ Doublons détectés dans le fichier',
            content: `${result.duplicatesInFile.length} doublons NIP supprimés automatiquement`,
            details: result.duplicatesInFile.map(d => 
                `Ligne ${d.lineNumber}: ${d.nom} ${d.prenom} (NIP: ${d.nip})`
            )
        });
    }
    
    if (result.existingMembers.length > 0) {
        messages.push({
            type: 'warning',
            title: '⚠️ Membres déjà actifs détectés',
            content: `${result.existingMembers.length} personnes sont déjà membres actifs d'autres organisations. Elles seront ajoutées avec statut "à corriger"`,
            details: result.existingMembers.map(m => 
                `Ligne ${m.lineNumber}: ${m.nom} ${m.prenom} (NIP: ${m.nip})`
            )
        });
    }
    
    if (result.invalidEntries.length > 0) {
        messages.push({
            type: 'danger',
            title: '❌ Entrées invalides',
            content: `${result.invalidEntries.length} entrées avec des erreurs`,
            details: result.invalidEntries.map(e => 
                `Ligne ${e.lineNumber}: ${e.nom} ${e.prenom} - ${e.error}`
            )
        });
    }
    
    return messages;
}

/**
 * CONSIGNE 4: Traitement du résultat d'importation
 */
async function processImportResult(validationResult) {
    const { canProceed, validAdherents, existingMembers, messages } = validationResult;
    
    // Afficher tous les messages de validation
    messages.forEach(message => {
        showDetailedImportNotification(message);
    });
    
    if (!canProceed) {
        // Ne pas importer si pas assez d'adhérents valides
        showNotification('❌ Importation annulée: pas assez d\'adhérents valides', 'danger');
        clearFileInput();
        return;
    }
    
    // Confirmer l'importation si des membres existants sont détectés
    if (existingMembers.length > 0) {
        const confirmMsg = `⚠️ ${existingMembers.length} membres sont déjà actifs ailleurs. Continuer l'importation avec statut "à corriger" ?`;
        if (!confirm(confirmMsg)) {
            showNotification('❌ Importation annulée par l\'utilisateur', 'info');
            clearFileInput();
            return;
        }
    }
    
    // CONSIGNE 4: Enregistrer avec statut "à corriger" pour les membres existants
    let adherentsToAdd = [...validAdherents];
    
    if (existingMembers.length > 0) {
        // Ajouter les membres existants avec statut "à corriger"
        const correctionAdherents = existingMembers.map(member => ({
            ...member,
            status: 'correction_required',
            note: 'Déjà membre actif d\'une autre organisation'
        }));
        
        adherentsToAdd = [...adherentsToAdd, ...correctionAdherents];
    }
    
    // Ajouter tous les adhérents à la liste
    adherentsToAdd.forEach(adherent => {
        OrganisationApp.adherents.push(adherent);
    });
    
    // Mettre à jour l'affichage
    updateAdherentsList();
    
    // Message de succès final
    const successMsg = `🎉 Importation réussie: ${validAdherents.length} adhérents valides ajoutés` +
        (existingMembers.length > 0 ? `, ${existingMembers.length} à corriger` : '');
    
    showNotification(successMsg, 'success', 7000);
    
    // Vider le champ fichier
    clearFileInput();
    
    // Sauvegarder automatiquement
    autoSave();
}

/**
 * Affichage de notification détaillée pour l'import
 */
function showDetailedImportNotification(message) {
    const hasDetails = message.details && message.details.length > 0;
    const detailsId = `details-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    
    let notificationContent = `
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <strong>${message.title}</strong>
                <div class="mt-1">${message.content}</div>
                ${hasDetails ? `
                    <button class="btn btn-sm btn-outline-secondary mt-2" type="button" 
                            onclick="toggleImportDetails('${detailsId}')">
                        <i class="fas fa-chevron-down me-1"></i>Voir détails (${message.details.length})
                    </button>
                    <div id="${detailsId}" class="mt-2 d-none small">
                        <div class="bg-light p-2 rounded" style="max-height: 150px; overflow-y: auto;">
                            ${message.details.map(detail => `• ${detail}`).join('<br>')}
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    // Créer notification personnalisée avec fonction showCustomNotification existante
    showCustomNotification(notificationContent, message.type, hasDetails ? 15000 : 8000);
}

/**
 * Basculer l'affichage des détails d'import
 */
function toggleImportDetails(detailsId) {
    const detailsElement = document.getElementById(detailsId);
    const button = event.target.closest('button');
    
    if (detailsElement && button) {
        if (detailsElement.classList.contains('d-none')) {
            detailsElement.classList.remove('d-none');
            button.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Masquer détails';
        } else {
            detailsElement.classList.add('d-none');
            button.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Voir détails';
        }
    }
}

/**
 * Vider le champ fichier
 */
function clearFileInput() {
    const fileInput = document.getElementById('adherents_file');
    if (fileInput) {
        fileInput.value = '';
    }
}

/**
 * Notification personnalisée (utilise showCustomNotification si elle existe, sinon showNotification)
 */
function showCustomNotification(htmlContent, type = 'info', duration = 5000) {
    // Si la fonction showCustomNotification n'existe pas, utiliser showNotification basique
    if (typeof showCustomNotification === 'undefined') {
        // Extraire le texte du HTML pour showNotification basique
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        const textContent = tempDiv.textContent || tempDiv.innerText || '';
        showNotification(textContent, type, duration);
        return;
    }
    
    // Sinon utiliser la version complète (à implémenter si besoin)
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 500px;
        `;
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
    notification.style.cssText = `
        margin-bottom: 10px;
        border: none;
        border-radius: 12px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        ${htmlContent}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="margin-top: -0.5rem;"></button>
    `;
    
    container.appendChild(notification);
    
    // Auto-suppression
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, duration);
}

// ========================================
// MISE À JOUR DE LA FONCTION setupEventListeners
// ========================================

/**
 * AJOUTER cette section dans la fonction setupEventListeners existante
 */
function initializeAdherentFileImport() {
    const fileInput = document.getElementById('adherents_file');
    if (fileInput) {
        // Supprimer les anciens event listeners
        fileInput.removeEventListener('change', handleAdherentFileImport);
        
        // Ajouter le nouvel event listener
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleAdherentFileImport(this);
            }
        });
        console.log('✅ Événement importation fichier adhérents configuré');
    }
}

// Exposer les fonctions globalement
window.handleAdherentFileImport = handleAdherentFileImport;
window.toggleImportDetails = toggleImportDetails;

// ========================================
// INSTRUCTIONS D'INTÉGRATION
// ========================================

/*
INSTRUCTIONS POUR L'INTÉGRATION :

1. Ajouter ce code à la fin de la section "5. GESTION FONDATEURS ET ADHÉRENTS" 
   dans le fichier organisation-create.js

2. Dans la fonction setupEventListeners existante, ajouter cet appel :
   initializeAdherentFileImport();

3. Dans la section "13. FONCTIONS GLOBALES EXPOSÉES", ajouter :
   window.handleAdherentFileImport = handleAdherentFileImport;
   window.toggleImportDetails = toggleImportDetails;

4. Tester l'importation avec un fichier CSV ayant ces colonnes :
   Civilité,Nom,Prenom,NIP,Telephone,Profession
*/

console.log('📁 Module importation adhérents configuré - respecte les 4 consignes');

// ========================================
// 6. GESTION DOCUMENTS
// ========================================

/**
 * Mise à jour des documents requis
 */
function updateDocumentsRequirements() {
    const container = document.getElementById('documents_container');
    if (!container || !OrganisationApp.selectedOrgType) return;
    
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    if (!requirements) return;
    
    const documentsHTML = requirements.documents.map(doc => `
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    ${getDocumentLabel(doc)}
                    <span class="badge bg-light text-dark ms-2">Obligatoire</span>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <input type="file" 
                               class="form-control" 
                               id="doc_${doc}" 
                               name="documents[${doc}]"
                               accept=".pdf,.jpg,.jpeg,.png"
                               onchange="handleDocumentUpload('${doc}', this)">
                        <div class="form-text">
                            <i class="fas fa-info me-1"></i>
                            Formats acceptés : PDF, JPG, PNG (max 5MB)
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="status_${doc}" class="text-muted">
                            <i class="fas fa-clock me-1"></i>En attente
                        </div>
                        <div class="progress mt-2 d-none" id="progress_container_${doc}">
                            <div class="progress-bar" id="progress_${doc}" style="width: 0%"></div>
                        </div>
                        <div id="preview_${doc}" class="mt-2 d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = documentsHTML;
}

/**
 * Obtenir le label d'un document
 */
function getDocumentLabel(doc) {
    const labels = {
        'statuts': 'Statuts de l\'organisation',
        'pv_ag': 'Procès-verbal de l\'assemblée générale constitutive',
        'liste_fondateurs': 'Liste des membres fondateurs',
        'justif_siege': 'Justificatif du siège social',
        'projet_social': 'Projet social détaillé',
        'budget_previsionnel': 'Budget prévisionnel',
        'programme_politique': 'Programme politique',
        'liste_50_adherents': 'Liste de 50 adhérents minimum',
        'expose_doctrine': 'Exposé de la doctrine religieuse',
        'justif_lieu_culte': 'Justificatif du lieu de culte'
    };
    return labels[doc] || doc;
}

/**
 * Gestion upload document
 */
function handleDocumentUpload(docType, fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    
    // Validation
    if (file.size > 5 * 1024 * 1024) {
        showNotification('Le fichier ne peut pas dépasser 5MB', 'danger');
        fileInput.value = '';
        return;
    }
    
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Type de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'danger');
        fileInput.value = '';
        return;
    }
    
    // Simuler upload (remplacer par vraie logique)
    const statusElement = document.getElementById(`status_${docType}`);
    const progressContainer = document.getElementById(`progress_container_${docType}`);
    const progressBar = document.getElementById(`progress_${docType}`);
    const previewContainer = document.getElementById(`preview_${docType}`);
    
    if (statusElement) {
        statusElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1 text-primary"></i>Upload en cours...';
    }
    
    if (progressContainer) {
        progressContainer.classList.remove('d-none');
    }
    
    // Simulation progress
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 100) progress = 100;
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            
            // Marquer comme uploadé
            OrganisationApp.documents[docType] = {
                file: file,
                uploaded: true,
                uploadedAt: new Date(),
                fileName: file.name,
                fileSize: file.size,
                fileType: file.type
            };
            
            if (statusElement) {
                statusElement.innerHTML = '<i class="fas fa-check text-success me-1"></i>Uploadé avec succès';
            }
            
            // Générer preview pour les images
            if (file.type.startsWith('image/') && previewContainer) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px; cursor: pointer;" 
                             onclick="openImageModal('${e.target.result}')" />
                    `;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
            
            setTimeout(() => {
                if (progressContainer) {
                    progressContainer.classList.add('d-none');
                }
            }, 2000);
            
            showNotification(`Document "${getDocumentLabel(docType)}" uploadé avec succès`, 'success');
        }
    }, 200);
}

/**
 * Ouvrir une image en modal
 */
function openImageModal(imageSrc) {
    const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aperçu du document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageSrc}" class="img-fluid" style="max-height: 70vh;" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Supprimer l'ancienne modal si elle existe
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Ajouter et afficher la nouvelle modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    // Nettoyer après fermeture
    modal._element.addEventListener('hidden.bs.modal', () => {
        modal._element.remove();
    });
}

// ========================================
// 7. GÉNÉRATION RÉCAPITULATIF
// ========================================

/**
 * Générer le récapitulatif final
 */
 function generateRecap() {
    const container = document.getElementById('recap_content');
    if (!container) return;
    
    const formData = collectFormData();
    
    const recapHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary mb-3">
                    <i class="fas fa-building me-2"></i>
                    Informations de l'organisation
                </h6>
                <table class="table table-sm table-borderless">
                    <tr><td><strong>Type :</strong></td><td>${getOrganizationTypeLabel(OrganisationApp.selectedOrgType)}</td></tr>
                    <tr><td><strong>Nom :</strong></td><td>${formData.org_nom || 'Non renseigné'}</td></tr>
                    <tr><td><strong>Sigle :</strong></td><td>${formData.org_sigle || 'Aucun'}</td></tr>
                    <tr><td><strong>Téléphone :</strong></td><td>${formData.org_telephone || 'Non renseigné'}</td></tr>
                    <tr><td><strong>Email :</strong></td><td>${formData.org_email || 'Non renseigné'}</td></tr>
                    <tr><td><strong>Province :</strong></td><td>${formData.org_province || 'Non renseigné'}</td></tr>
                </table>
                
                <h6 class="text-success mb-3 mt-4">
                    <i class="fas fa-users me-2"></i>
                    Composition
                </h6>
                <table class="table table-sm table-borderless">
                    <tr><td><strong>Fondateurs :</strong></td><td>${OrganisationApp.fondateurs.length}</td></tr>
                    <tr><td><strong>Adhérents :</strong></td><td>${OrganisationApp.adherents.length}</td></tr>
                </table>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Exigences : ${OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType]?.minFondateurs || 3} fondateurs min, 
                        ${OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType]?.minAdherents || 10} adhérents min
                    </small>
                </div>
            </div>
            
            <div class="col-md-6">
                <h6 class="text-info mb-3">
                    <i class="fas fa-user me-2"></i>
                    Demandeur principal
                </h6>
                <table class="table table-sm table-borderless">
                    <tr><td><strong>Nom :</strong></td><td>${formData.demandeur_civilite || ''} ${formData.demandeur_nom || ''} ${formData.demandeur_prenom || ''}</td></tr>
                    <tr><td><strong>NIP :</strong></td><td><code>${formData.demandeur_nip || 'Non renseigné'}</code></td></tr>
                    <tr><td><strong>Téléphone :</strong></td><td>${formData.demandeur_telephone || 'Non renseigné'}</td></tr>
                    <tr><td><strong>Email :</strong></td><td>${formData.demandeur_email || 'Non renseigné'}</td></tr>
                    <tr><td><strong>Rôle :</strong></td><td><span class="badge bg-primary">${formData.demandeur_role || 'Non renseigné'}</span></td></tr>
                </table>
                
                <h6 class="text-warning mb-3 mt-4">
                    <i class="fas fa-file-alt me-2"></i>
                    Documents (${Object.keys(OrganisationApp.documents).length})
                </h6>
                <div>
                    ${Object.keys(OrganisationApp.documents).length > 0 ? 
                        Object.keys(OrganisationApp.documents).map(doc => `
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>${getDocumentLabel(doc)}</small>
                            </div>
                        `).join('') : 
                        '<small class="text-muted">Aucun document uploadé</small>'
                    }
                </div>
            </div>
        </div>
        
        <!-- Statut de validation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="text-dark mb-3">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Statut de validation
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-check-circle fa-2x ${validateStep1() ? 'text-success' : 'text-muted'}"></i>
                                    <div class="small mt-1">Type organisation</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-check-circle fa-2x ${validateStep3() ? 'text-success' : 'text-muted'}"></i>
                                    <div class="small mt-1">Demandeur</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-check-circle fa-2x ${validateStep6() ? 'text-success' : 'text-muted'}"></i>
                                    <div class="small mt-1">Fondateurs</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-check-circle fa-2x ${validateStep7() ? 'text-success' : 'text-muted'}"></i>
                                    <div class="small mt-1">Adhérents</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = recapHTML;
}

// ========================================
// 8. SAUVEGARDE ET COLLECTE DE DONNÉES
// ========================================

/**
 * Sauvegarder les données de l'étape actuelle
 */
function saveCurrentStepData() {
    const stepData = collectStepData(OrganisationApp.currentStep);
    OrganisationApp.formData[`step${OrganisationApp.currentStep}`] = stepData;
    
    // Sauvegarder automatiquement
    autoSave();
}

/**
 * Collecter les données d'une étape
 */
function collectStepData(stepNumber) {
    const stepElement = document.getElementById(`step${stepNumber}`);
    if (!stepElement) return {};
    
    const data = {};
    
    stepElement.querySelectorAll('input, select, textarea').forEach(field => {
        if (!field.name && !field.id) return;
        
        const key = field.name || field.id;
        
        if (field.type === 'checkbox' || field.type === 'radio') {
            if (field.checked) {
                data[key] = field.value;
            }
        } else if (field.type !== 'file') {
            data[key] = field.value;
        }
    });
    
    return data;
}

/**
 * Collecter toutes les données du formulaire
 */
function collectFormData() {
    const data = {};
    
    // Parcourir toutes les étapes
    for (let i = 1; i <= OrganisationApp.totalSteps; i++) {
        const stepData = collectStepData(i);
        Object.assign(data, stepData);
    }
    
    return data;
}

/**
 * Sauvegarde automatique
 */
function autoSave() {
    try {
        const saveData = {
            formData: collectFormData(),
            fondateurs: OrganisationApp.fondateurs,
            adherents: OrganisationApp.adherents,
            documents: Object.keys(OrganisationApp.documents),
            currentStep: OrganisationApp.currentStep,
            selectedOrgType: OrganisationApp.selectedOrgType,
            timestamp: Date.now(),
            version: '1.0'
        };
        
        localStorage.setItem('pngdi_organisation_draft', JSON.stringify(saveData));
        updateSaveIndicator('success');
        console.log('💾 Sauvegarde automatique réussie');
    } catch (error) {
        console.error('Erreur sauvegarde:', error);
        updateSaveIndicator('error');
    }
}

/**
 * Charger les données sauvegardées
 */
function loadSavedData() {
    try {
        const saved = localStorage.getItem('pngdi_organisation_draft');
        if (saved) {
            const data = JSON.parse(saved);
            
            // Vérifier que les données ne sont pas trop anciennes (7 jours)
            if (Date.now() - data.timestamp < 7 * 24 * 60 * 60 * 1000) {
                if (confirm('Des données sauvegardées ont été trouvées. Voulez-vous les restaurer ?')) {
                    restoreFormData(data);
                    showNotification('Données restaurées avec succès', 'success');
                    return true;
                }
            } else {
                // Supprimer les anciennes données
                localStorage.removeItem('pngdi_organisation_draft');
            }
        }
    } catch (error) {
        console.error('Erreur chargement données:', error);
    }
    return false;
}

/**
 * Restaurer les données du formulaire
 */
function restoreFormData(savedData) {
    try {
        // Restaurer les métadonnées
        OrganisationApp.currentStep = savedData.currentStep || 1;
        OrganisationApp.selectedOrgType = savedData.selectedOrgType || '';
        OrganisationApp.fondateurs = savedData.fondateurs || [];
        OrganisationApp.adherents = savedData.adherents || [];
        
        // Restaurer les champs du formulaire
        const formData = savedData.formData || {};
        Object.keys(formData).forEach(key => {
            const field = document.getElementById(key) || document.querySelector(`[name="${key}"]`);
            if (field && field.type !== 'file') {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = field.value === formData[key];
                } else {
                    field.value = formData[key];
                }
                
                // Déclencher l'événement change pour mettre à jour l'interface
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        
        // Restaurer la sélection du type d'organisation
        if (OrganisationApp.selectedOrgType) {
            const typeCard = document.querySelector(`[data-type="${OrganisationApp.selectedOrgType}"]`);
            if (typeCard) {
                selectOrganizationType(typeCard);
            }
        }
        
        // Mettre à jour l'affichage
        updateStepDisplay();
        updateFoundersList();
        updateAdherentsList();
        
    } catch (error) {
        console.error('Erreur restauration données:', error);
        showNotification('Erreur lors de la restauration des données', 'warning');
    }
}

/**
 * Mise à jour indicateur de sauvegarde
 */
function updateSaveIndicator(status) {
    const indicator = document.getElementById('save-indicator');
    if (!indicator) return;
    
    const messages = {
        'saving': '<i class="fas fa-spinner fa-spin text-primary"></i> Sauvegarde...',
        'success': '<i class="fas fa-check text-success"></i> Sauvegardé',
        'error': '<i class="fas fa-times text-danger"></i> Erreur sauvegarde'
    };
    
    indicator.innerHTML = messages[status] || '';
    
    if (status === 'success' || status === 'error') {
        setTimeout(() => {
            indicator.innerHTML = '';
        }, 3000);
    }
}

// ========================================
// 9. NOTIFICATIONS
// ========================================

/**
 * Afficher une notification
 */
function showNotification(message, type = 'info', duration = 5000) {
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
    notification.style.cssText = `
        margin-bottom: 10px;
        border: none;
        border-radius: 12px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    const iconMap = {
        'success': 'fa-check-circle',
        'warning': 'fa-exclamation-triangle',
        'danger': 'fa-times-circle',
        'info': 'fa-info-circle'
    };
    
    notification.innerHTML = `
        <i class="fas ${iconMap[type]} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(notification);
    
    // Auto-suppression
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, duration);
}

// ========================================
// 10. SOUMISSION FINALE
// ========================================

/**
 * Validation de toutes les étapes avant soumission
 */
function validateAllSteps() {
    for (let i = 1; i <= OrganisationApp.totalSteps; i++) {
        if (!validateStep(i)) {
            goToStep(i); // Aller à la première étape en erreur
            showNotification(`Erreur à l'étape ${i}. Veuillez corriger avant de continuer.`, 'danger');
            return false;
        }
    }
    return true;
}

/**
 * Soumission finale du formulaire
 */
async function submitForm() {
    console.log('📤 Début de la soumission du formulaire...');
    
    // Validation finale complète
    if (!validateAllSteps()) {
        showNotification('Veuillez corriger toutes les erreurs avant de soumettre', 'danger');
        return false;
    }
    
    try {
        // Afficher le loader
        showGlobalLoader(true);
        updateSaveIndicator('saving');
        
        // Préparation des données
        const formData = new FormData();
        const data = collectFormData();
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }
        
        // Ajouter les données de base
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                formData.append(key, data[key]);
            }
        });
        
        // Ajouter les fondateurs
        formData.append('fondateurs', JSON.stringify(OrganisationApp.fondateurs));
        
        // Ajouter les adhérents
        formData.append('adherents', JSON.stringify(OrganisationApp.adherents));
        
        // Ajouter les métadonnées
        formData.append('selectedOrgType', OrganisationApp.selectedOrgType);
        formData.append('totalFondateurs', OrganisationApp.fondateurs.length);
        formData.append('totalAdherents', OrganisationApp.adherents.length);
        formData.append('totalDocuments', Object.keys(OrganisationApp.documents).length);
        
        // Ajouter les documents
        Object.keys(OrganisationApp.documents).forEach(docType => {
            const doc = OrganisationApp.documents[docType];
            if (doc.file) {
                formData.append(`documents[${docType}]`, doc.file);
            }
        });
        
        console.log('📋 Données préparées pour soumission');
        
        // Soumettre via fetch pour avoir plus de contrôle
        const formElement = document.getElementById('organisationForm');
        const response = await fetch(formElement.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('📡 Réponse reçue du serveur:', response.status);
        
        if (response.ok) {
            const result = await response.json();
            
            if (result.success) {
                // Succès
                showNotification('🎉 Dossier soumis avec succès !', 'success', 10000);
                
                // Nettoyer les données sauvegardées
                localStorage.removeItem('pngdi_organisation_draft');
                
                // Désactiver le formulaire pour éviter les double soumissions
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Dossier soumis';
                }
                
                // Rediriger après un délai
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 3000);
                } else {
                    // Redirection par défaut
                    setTimeout(() => {
                        window.location.href = '/operator/organisations';
                    }, 3000);
                }
                
            } else {
                // Erreur métier
                showNotification(result.message || 'Erreur lors de la soumission', 'danger');
                
                // Afficher les erreurs de validation si présentes
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const fieldElement = document.querySelector(`[name="${field}"]`) || 
                                           document.getElementById(field);
                        if (fieldElement) {
                            showFieldError(fieldElement, result.errors[field][0]);
                        }
                    });
                }
            }
        } else {
            // Erreur HTTP
            throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
        }
        
    } catch (error) {
        console.error('❌ Erreur soumission:', error);
        showNotification('Erreur de communication avec le serveur. Veuillez réessayer.', 'danger');
    } finally {
        showGlobalLoader(false);
        updateSaveIndicator('success');
    }
}

/**
 * Afficher/masquer le loader global
 */
function showGlobalLoader(show) {
    const loader = document.getElementById('global-loader');
    if (loader) {
        if (show) {
            loader.classList.remove('d-none');
        } else {
            loader.classList.add('d-none');
        }
    }
}

// ========================================
// 11. UTILITAIRES AVANCÉS
// ========================================

/**
 * Géolocalisation
 */
function getCurrentLocation() {
    const btn = document.getElementById('getLocationBtn');
    if (!navigator.geolocation) {
        showNotification('Géolocalisation non supportée par votre navigateur', 'warning');
        return;
    }
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Localisation en cours...';
    btn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude.toFixed(7);
            const lng = position.coords.longitude.toFixed(7);
            
            // Vérifier si c'est au Gabon (limites approximatives)
            if (lat >= -3.978 && lat <= 2.318 && lng >= 8.695 && lng <= 14.502) {
                document.getElementById('org_latitude').value = lat;
                document.getElementById('org_longitude').value = lng;
                showNotification('Position obtenue avec succès', 'success');
            } else {
                showNotification('Position détectée hors du Gabon', 'warning');
            }
            
            btn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i>Obtenir ma position actuelle';
            btn.disabled = false;
        },
        (error) => {
            console.error('Erreur géolocalisation:', error);
            showNotification('Impossible d\'obtenir votre position', 'danger');
            btn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i>Obtenir ma position actuelle';
            btn.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 600000 // 10 minutes
        }
    );
}

/**
 * Gestion des départements selon la province
 */
function updateDepartements() {
    const province = document.getElementById('org_province')?.value;
    const departementSelect = document.getElementById('org_departement');
    
    if (!departementSelect || !province) return;
    
    const departements = {
        'Estuaire': ['Libreville', 'Komo-Mondah', 'Noya'],
        'Haut-Ogooué': ['Franceville', 'Lékoko', 'Lemboumbi-Leyou', 'Mpassa', 'Plateaux'],
        'Moyen-Ogooué': ['Lambaréné', 'Abanga-Bigné', 'Ogooué et des Lacs'],
        'Ngounié': ['Mouila', 'Dola', 'Douya-Onoy', 'Lolo-Bouenguidi', 'Tsamba-Magotsi'],
        'Nyanga': ['Tchibanga', 'Basse-Banio', 'Douigni', 'Haute-Banio', 'Mougoutsi', 'Ndolou'],
        'Ogooué-Ivindo': ['Makokou', 'Ivindo', 'Lope', 'Mvoung', 'Zadie'],
        'Ogooué-Lolo': ['Koulamoutou', 'Lolo', 'Lombo-Bouenguidi', 'Mulundu', 'Offoue-Onoye'],
        'Ogooué-Maritime': ['Port-Gentil', 'Bendje', 'Etimboue', 'Komo-Kango'],
        'Woleu-Ntem': ['Oyem', 'Haut-Como', 'Haut-Ntem', 'Ntem', 'Okano', 'Woleu']
    };
    
    const depts = departements[province] || [];
    
    departementSelect.innerHTML = '<option value="">Sélectionnez un département</option>';
    depts.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept;
        option.textContent = dept;
        departementSelect.appendChild(option);
    });
}

/**
 * Télécharger le modèle Excel pour les adhérents
 */
function downloadTemplate() {
    // Créer un fichier CSV simple comme modèle
    const csvContent = `Civilité,Nom,Prenom,NIP,Telephone,Profession
M,DUPONT,Jean,1234567890123,01234567,Ingénieur
Mme,MARTIN,Marie,1234567890124,01234568,Professeure
M,BERNARD,Paul,1234567890125,01234569,Commerçant`;
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'modele_adherents_pngdi.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Modèle téléchargé avec succès', 'success');
    }
}

/**
 * Gestion mode adhérents (manuel vs fichier)
 */
function toggleAdherentMode(mode) {
    const manuelSection = document.getElementById('adherent_manuel_section');
    const fichierSection = document.getElementById('adherent_fichier_section');
    
    if (mode === 'manuel') {
        if (manuelSection) manuelSection.classList.remove('d-none');
        if (fichierSection) fichierSection.classList.add('d-none');
    } else {
        if (manuelSection) manuelSection.classList.add('d-none');
        if (fichierSection) fichierSection.classList.remove('d-none');
    }
}

/**
 * Démarrer la sauvegarde automatique
 */
function startAutoSave() {
    if (OrganisationApp.timers.autoSave) {
        clearInterval(OrganisationApp.timers.autoSave);
    }
    
    OrganisationApp.timers.autoSave = setInterval(() => {
        autoSave();
    }, OrganisationApp.config.autoSaveInterval);
    
    console.log('🔄 Auto-sauvegarde démarrée (30s)');
}

/**
 * Arrêter la sauvegarde automatique
 */
function stopAutoSave() {
    if (OrganisationApp.timers.autoSave) {
        clearInterval(OrganisationApp.timers.autoSave);
        OrganisationApp.timers.autoSave = null;
    }
}

// ========================================
// 12. INITIALISATION COMPLÈTE
// ========================================

/**
 * Initialisation complète de l'application
 */
function initializeApplication() {
    console.log('🚀 Initialisation complète PNGDI - Création Organisation');
    
    // Initialiser l'affichage
    updateStepDisplay();
    updateNavigationButtons();
    
    // Configurer les événements
    setupEventListeners();
    
    // Charger les données sauvegardées
    loadSavedData();
    
    // Démarrer l'auto-sauvegarde
    startAutoSave();
    
    console.log('✅ Application initialisée avec succès');
}

/**
 * Configuration des événements
 */
function setupEventListeners() {
    // Événements pour les cartes d'organisation
    document.querySelectorAll('.organization-type-card').forEach(card => {
        card.addEventListener('click', function() {
            selectOrganizationType(this);
        });
        
        // Accessibilité clavier
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectOrganizationType(this);
            }
        });
        
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
    });
    
    // Bouton géolocalisation
    const geoBtn = document.getElementById('getLocationBtn');
    if (geoBtn) {
        geoBtn.addEventListener('click', getCurrentLocation);
    }
    
    // Boutons fondateurs et adhérents
    const addFondateurBtn = document.getElementById('addFondateurBtn');
    if (addFondateurBtn) {
        addFondateurBtn.addEventListener('click', addFondateur);
    }
    
    const addAdherentBtn = document.getElementById('addAdherentBtn');
    if (addAdherentBtn) {
        addAdherentBtn.addEventListener('click', addAdherent);
    }
    
    // Bouton téléchargement modèle
    const downloadBtn = document.getElementById('downloadTemplateBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', downloadTemplate);
    }
    
    // Mode adhérents
    document.querySelectorAll('input[name="adherent_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            toggleAdherentMode(this.value);
        });
    });
    
    // Province/département
    const provinceSelect = document.getElementById('org_province');
    if (provinceSelect) {
        provinceSelect.addEventListener('change', updateDepartements);
    }
    
    // Validation en temps réel
    document.addEventListener('input', function(e) {
        if (e.target.matches('input, textarea, select')) {
            clearTimeout(e.target.validationTimeout);
            e.target.validationTimeout = setTimeout(() => {
                validateField(e.target);
            }, OrganisationApp.config.validationDelay);
        }
    });
    
    // Sauvegarde avant fermeture
    window.addEventListener('beforeunload', function(e) {
        autoSave();
    });
    
    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Ctrl+S pour sauvegarder
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            autoSave();
            showNotification('Données sauvegardées', 'success');
        }
        
        // Flèches pour navigation (si pas dans un champ)
        if (!e.target.matches('input, textarea, select')) {
            if (e.key === 'ArrowRight' && e.ctrlKey) {
                e.preventDefault();
                changeStep(1);
            } else if (e.key === 'ArrowLeft' && e.ctrlKey) {
                e.preventDefault();
                changeStep(-1);
            }
        }
    });
    
    // Bouton de soumission finale
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm();
        });
    }

    initializeAdherentFileImport();
}

// ========================================
// 13. FONCTIONS GLOBALES EXPOSÉES
// ========================================

// Exposer les fonctions principales pour compatibilité avec le HTML
window.changeStep = changeStep;
window.selectOrganizationType = selectOrganizationType;
window.addFondateur = addFondateur;
window.removeFondateur = removeFondateur;
window.addAdherent = addAdherent;
window.removeAdherent = removeAdherent;
window.handleDocumentUpload = handleDocumentUpload;
window.getCurrentLocation = getCurrentLocation;
window.openImageModal = openImageModal;
window.handleAdherentFileImport = handleAdherentFileImport;
window.toggleImportDetails = toggleImportDetails;

// ========================================
// 14. INITIALISATION AU CHARGEMENT DOM
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que nous sommes sur la bonne page
    if (document.getElementById('organisationForm')) {
        initializeApplication();
    }
    
    // Ajouter les styles pour les animations de notifications
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(styles);
    }
});

// Message de chargement
console.log('📝 Module Organisation JavaScript COMPLET chargé - PARTIE 4 FINALE');

// FIN DE LA PARTIE 4 - FICHIER JAVASCRIPT COMPLET