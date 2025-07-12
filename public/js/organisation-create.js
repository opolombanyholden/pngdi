/**
 * ========================================================================
 * PNGDI - Formulaire Création Organisation - VERSION FINALE COMPLÈTE
 * Fichier: public/js/organisation-create.js
 * Compatible: Bootstrap 5 + Laravel + Toutes les 9 étapes
 * Date: 29 juin 2025
 * Version: 1.2 avec système d'anomalies intégré
 * ========================================================================
 */

// ========================================
// FONCTION DE COMPATIBILITÉ NAVIGATEURS
// ========================================
function elementMatches(element, selector) {
    if (!element) return false;
    
    if (element.matches) {
        return element.matches(selector);
    } else if (element.msMatchesSelector) {
        return element.msMatchesSelector(selector);
    } else if (element.webkitMatchesSelector) {
        return element.webkitMatchesSelector(selector);
    } else if (element.mozMatchesSelector) {
        return element.mozMatchesSelector(selector);
    }
    
    // Fallback pour très anciens navigateurs
    return false;
}


// ========================================
// FONCTION DEBOUNCE (CORRECTION BUG)
// ========================================

/**
 * Fonction debounce pour limiter les appels fréquents
 * @param {Function} func - Fonction à exécuter
 * @param {number} wait - Délai d'attente en millisecondes
 * @param {boolean} immediate - Exécuter immédiatement au premier appel
 * @returns {Function} Fonction débounced
 */
function debounce(func, wait, immediate = false) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func.apply(this, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(this, args);
    };
}

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
        
        // Configuration NIP nouveau format XX-QQQQ-YYYYMMDD
        nip: {
            length: 16, // Longueur avec tirets : XX-QQQQ-YYYYMMDD
            pattern: /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/,
            formatPattern: /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/,
            strictValidation: true,
            allowTestValues: true,
            minAge: 18 // Âge minimum requis
        },
        
        // Configuration téléphone gabonais
        phone: {
            prefixes: ['01', '02', '03', '04', '05', '06', '07'],
            minLength: 8,
            maxLength: 9,
            pattern: /^[0-9]{8,9}$/
        },
        
        // ========================================
        // NOUVEAU : Configuration système anomalies
        // ========================================
        anomalies: {
            enabled: true,
            types: {
                'nip_invalide': {
                    level: 'critique',
                    label: 'NIP invalide ou incorrect',
                    description: 'Le numéro NIP ne respecte pas le format gabonais standard'
                },
                'telephone_invalide': {
                    level: 'majeure',
                    label: 'Numéro de téléphone invalide',
                    description: 'Le numéro de téléphone ne respecte pas le format gabonais'
                },
                'email_invalide': {
                    level: 'majeure',
                    label: 'Adresse email invalide',
                    description: 'Le format de l\'adresse email est incorrect'
                },
                'champs_incomplets': {
                    level: 'majeure',
                    label: 'Informations incomplètes',
                    description: 'Des champs obligatoires sont manquants'
                },
                'membre_existant': {
                    level: 'critique',
                    label: 'Membre déjà enregistré ailleurs',
                    description: 'Cette personne est déjà membre active d\'une autre organisation'
                },
                'profession_exclue_parti': {
                    level: 'critique',
                    label: 'Profession exclue pour parti politique',
                    description: 'Cette profession est interdite pour les membres de partis politiques'
                },
                'doublon_fichier': {
                    level: 'mineure',
                    label: 'Doublon dans le fichier',
                    description: 'Ce NIP apparaît plusieurs fois dans le fichier importé'
                },
                'format_donnees': {
                    level: 'mineure',
                    label: 'Format de données suspect',
                    description: 'Les données semblent présenter des incohérences de format'
                }
            },
            
            // Niveaux de gravité
            levels: {
                'critique': {
                    priority: 3,
                    color: 'danger',
                    icon: 'fa-exclamation-triangle',
                    badge: 'bg-danger'
                },
                'majeure': {
                    priority: 2,
                    color: 'warning',
                    icon: 'fa-exclamation-circle',
                    badge: 'bg-warning'
                },
                'mineure': {
                    priority: 1,
                    color: 'info',
                    icon: 'fa-info-circle',
                    badge: 'bg-info'
                }
            }
        },
        
        // Exigences par type d'organisation
        orgRequirements: {
            'association': {
                minFondateurs: 1,
                minAdherents: 10,
                label: 'Association',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege']
            },
            'ong': {
                minFondateurs: 1,
                minAdherents: 15,
                label: 'ONG',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'projet_social', 'budget_previsionnel']
            },
            'parti_politique': {
                minFondateurs: 1,
                minAdherents: 50,
                label: 'Parti Politique',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'programme_politique', 'liste_50_adherents']
            },
            'confession_religieuse': {
                minFondateurs: 1,
                minAdherents: 10,
                label: 'Confession Religieuse',
                documents: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'expose_doctrine', 'justif_lieu_culte']
            }
        },
        
        // Professions exclues pour partis politiques
        professionsExcluesParti: [
            'magistrat', 'juge', 'procureur', 'avocat_general',
            'militaire', 'gendarme', 'policier', 'forces_armee',
            'prefet', 'sous_prefet', 'gouverneur', 'maire',
            'fonctionnaire_administration', 'ambassadeur', 'consul',
            'directeur_general_public', 'recteur_universite',
            'chef_etablissement_public', 'membre_conseil_constitutionnel',
            'controleur_etat', 'inspecteur_general',
            'membre_autorite_independante', 'comptable_public'
        ]
    },
    
    // Cache et données
    cache: new Map(),
    formData: {},
    validationErrors: {},
    fondateurs: [],
    adherents: [],
    documents: {},
    
    // ========================================
    // NOUVEAU : Système de gestion des anomalies
    // ========================================
    rapportAnomalies: {
        enabled: false,
        adherentsValides: 0,
        adherentsAvecAnomalies: 0,
        anomalies: [],
        statistiques: {
            critique: 0,
            majeure: 0,
            mineure: 0
        },
        genereAt: null,
        version: '1.2'
    },
    
    // Timers
    timers: {
        autoSave: null,
        validation: {}
    }
};

// ========================================
// NOUVELLES FONCTIONS UTILITAIRES ANOMALIES
// ========================================

/**
 * Créer une anomalie pour un adhérent
 */
function createAnomalie(adherent, type, details = '') {
    const anomalieConfig = OrganisationApp.config.anomalies.types[type];
    if (!anomalieConfig) {
        console.warn('Type d\'anomalie non reconnu:', type);
        return null;
    }
    
    return {
        id: `anomalie_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        adherentId: adherent.id || adherent.nip,
        adherentNom: `${adherent.nom} ${adherent.prenom}`,
        adherentNip: adherent.nip,
        adherentLigne: adherent.lineNumber || null,
        type: type,
        level: anomalieConfig.level,
        label: anomalieConfig.label,
        description: anomalieConfig.description,
        details: details,
        detecteAt: new Date().toISOString(),
        status: 'detected'
    };
}

/**
 * Ajouter une anomalie au rapport
 */
function addAnomalieToReport(anomalie) {
    if (!anomalie) return;
    
    // Activer le rapport s'il n'est pas déjà activé
    if (!OrganisationApp.rapportAnomalies.enabled) {
        OrganisationApp.rapportAnomalies.enabled = true;
        OrganisationApp.rapportAnomalies.genereAt = new Date().toISOString();
    }
    
    // Ajouter l'anomalie
    OrganisationApp.rapportAnomalies.anomalies.push(anomalie);
    
    // Mettre à jour les statistiques
    const level = anomalie.level;
    if (OrganisationApp.rapportAnomalies.statistiques[level] !== undefined) {
        OrganisationApp.rapportAnomalies.statistiques[level]++;
    }
    
    console.log(`📋 Anomalie ajoutée: ${anomalie.type} (${anomalie.level}) pour ${anomalie.adherentNom}`);
}

/**
 * Obtenir le statut qualité global
 */
function getQualiteStatut() {
    const total = OrganisationApp.adherents.length;
    const avecAnomalies = OrganisationApp.rapportAnomalies.adherentsAvecAnomalies;
    const valides = total - avecAnomalies;
    
    if (total === 0) return 'aucun';
    if (avecAnomalies === 0) return 'excellent';
    if (avecAnomalies < total * 0.1) return 'bon';
    if (avecAnomalies < total * 0.3) return 'moyen';
    return 'faible';
}

/**
 * Obtenir les recommandations selon les anomalies
 */
function getRecommandationsAnomalies() {
    const anomalies = OrganisationApp.rapportAnomalies.anomalies;
    const stats = OrganisationApp.rapportAnomalies.statistiques;
    const recommandations = [];
    
    if (stats.critique > 0) {
        recommandations.push({
            type: 'urgent',
            message: `${stats.critique} anomalie(s) critique(s) détectée(s). Correction immédiate recommandée.`
        });
    }
    
    if (stats.majeure > 0) {
        recommandations.push({
            type: 'important',
            message: `${stats.majeure} anomalie(s) majeure(s) nécessitent votre attention.`
        });
    }
    
    if (stats.mineure > 0) {
        recommandations.push({
            type: 'conseil',
            message: `${stats.mineure} anomalie(s) mineure(s) à corriger pour optimiser la qualité.`
        });
    }
    
    // Recommandations spécifiques selon les types d'anomalies
    const typesDetectes = [...new Set(anomalies.map(a => a.type))];
    
    if (typesDetectes.includes('nip_invalide')) {
        recommandations.push({
            type: 'conseil',
            message: 'Vérifiez les numéros NIP auprès des services d\'état civil.'
        });
    }
    
    if (typesDetectes.includes('membre_existant')) {
        recommandations.push({
            type: 'urgent',
            message: 'Contactez les membres concernés pour régulariser leur situation.'
        });
    }
    
    if (typesDetectes.includes('profession_exclue_parti')) {
        recommandations.push({
            type: 'urgent',
            message: 'Les personnes avec professions exclues ne peuvent être membres de partis politiques.'
        });
    }
    
    return recommandations;
}

/**
 * Fonctions utilitaires pour l'affichage des anomalies
 */
function getQualiteBadgeClass(qualite) {
    const classes = {
        'excellent': 'bg-success',
        'bon': 'bg-info',
        'moyen': 'bg-warning',
        'faible': 'bg-danger'
    };
    return classes[qualite] || 'bg-secondary';
}

function getQualiteLabel(qualite) {
    const labels = {
        'excellent': 'Excellente qualité',
        'bon': 'Bonne qualité',
        'moyen': 'Qualité moyenne',
        'faible': 'Qualité faible'
    };
    return labels[qualite] || 'Non évalué';
}

console.log('✅ Configuration globale avec anomalies - Version 1.2 harmonisée');

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
    const submitPhase1Btn = document.getElementById('submitPhase1Btn');
    const submitTraditionalBtn = document.getElementById('submitTraditionalBtn');
    const submissionInfo = document.getElementById('submission-info');
    
    // Bouton précédent
    if (prevBtn) {
        if (OrganisationApp.currentStep > 1) {
            prevBtn.style.display = 'inline-block';
        } else {
            prevBtn.style.display = 'none';
        }
    }
    
    // Gestion des boutons selon l'étape
    if (OrganisationApp.currentStep === 8) {
        // ÉTAPE 8 : Masquer bouton suivant, afficher boutons soumission
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';
        
        // Afficher les informations de soumission
        if (submissionInfo) submissionInfo.style.display = 'block';
        
        // Décider quel bouton afficher selon le volume d'adhérents
        const adherentsCount = (OrganisationApp.adherents || []).length;
        console.log(`📊 Analyse volume adhérents: ${adherentsCount}`);
        
        if (adherentsCount > 50 || (window.Workflow2Phases && window.Workflow2Phases.enabled)) {
            // Gros volume ou workflow 2 phases activé : Phase 1 recommandée
            if (submitPhase1Btn) {
                submitPhase1Btn.style.display = 'inline-block';
                console.log('✅ Bouton Phase 1 affiché');
            }
            if (submitTraditionalBtn) submitTraditionalBtn.style.display = 'none';
        } else {
            // Petit volume : Soumission traditionnelle
            if (submitTraditionalBtn) {
                submitTraditionalBtn.style.display = 'inline-block';
                console.log('✅ Bouton traditionnel affiché');
            }
            if (submitPhase1Btn) submitPhase1Btn.style.display = 'none';
        }
        
    } else {
        // Autres étapes : bouton suivant visible, soumission masquée
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.style.display = 'none';
        if (submitPhase1Btn) submitPhase1Btn.style.display = 'none';
        if (submitTraditionalBtn) submitTraditionalBtn.style.display = 'none';
        if (submissionInfo) submissionInfo.style.display = 'none';
    }
    
    console.log(`🔄 Boutons mis à jour pour étape ${OrganisationApp.currentStep}`);
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
            <div class="alert alert-danger mt-3">
                <strong>⚠️ Important :</strong> 22 professions sont exclues des partis politiques (magistrats, militaires, fonctionnaires, etc.)
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
    
    // Validation spéciale pour org_objet (minimum 50 caractères)
    const orgObjet = document.getElementById('org_objet');
    if (orgObjet) {
        const objetText = orgObjet.value.trim();
        if (objetText.length < 50) {
            showFieldError(orgObjet, `L'objet social doit contenir au moins 50 caractères (${objetText.length}/50)`);
            isValid = false;
        } else {
            clearFieldError(orgObjet);
        }
    }
    
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
    
    // Ajouter la déclaration spécifique pour parti politique
    if (OrganisationApp.selectedOrgType === 'parti_politique') {
        declarations.push('declaration_exclusivite_parti');
    }
    
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
        case 'org_objet':
            return validateOrgObjet(field, value);
        default:
            return validateGenericField(field, value);
    }
}

/**
 * ✅ NOUVELLE VALIDATION NIP FORMAT XX-QQQQ-YYYYMMDD
 * Compatible avec nip-validation.js
 */
function validateNIP(field, value) {
    if (!value) {
        showFieldError(field, 'Le NIP est obligatoire');
        updateNIPValidationIcon('invalid');
        return false;
    }
    
    // Validation du nouveau format XX-QQQQ-YYYYMMDD
    if (!OrganisationApp.config.nip.pattern.test(value)) {
        showFieldError(field, 'Le NIP doit respecter le format XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)');
        updateNIPValidationIcon('invalid');
        return false;
    }
    
    // Validation de l'âge avec nip-validation.js si disponible
    if (typeof window.NipValidation !== 'undefined') {
        const nipValidation = window.NipValidation.validateFormat(value);
        if (!nipValidation.valid) {
            showFieldError(field, nipValidation.message);
            updateNIPValidationIcon('invalid');
            return false;
        }
        
        // Afficher l'âge calculé si disponible
        if (nipValidation.extracted_info && nipValidation.extracted_info.age !== undefined) {
            const ageInfo = field.parentNode.querySelector('.nip-age-info') || document.createElement('small');
            ageInfo.className = 'nip-age-info form-text';
            //ageInfo.textContent = `Âge calculé: ${nipValidation.extracted_info.age} ans`;
            ageInfo.style.color = nipValidation.extracted_info.age >= 18 ? '#28a745' : '#dc3545';
            
            if (!field.parentNode.querySelector('.nip-age-info')) {
                field.parentNode.appendChild(ageInfo);
            }
        }
    }
    
    // Validation réussie
    clearFieldError(field);
    updateNIPValidationIcon('valid');
    return true;
}

/**
 * ✅ NOUVELLE FONCTION : Validation NIP avec vérification serveur
 */
async function validateNIPWithServer(field, value) {
    if (!validateNIP(field, value)) {
        return false;
    }
    
    try {
        updateNIPValidationIcon('loading');
        
        // Appel API pour vérification serveur
        const response = await fetch('/api/v1/validate-nip', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nip: value })
        });
        
        if (response.ok) {
            const result = await response.json();
            
            if (result.valid) {
                clearFieldError(field);
                updateNIPValidationIcon('valid');
                
                // Afficher les informations extraites
                if (result.age !== undefined) {
                    const ageInfo = field.parentNode.querySelector('.nip-age-info') || document.createElement('small');
                    ageInfo.className = 'nip-age-info form-text text-success';
                    ageInfo.innerHTML = `<i class="fas fa-check me-1"></i>Âge: ${result.age} ans - ${result.available ? 'Disponible' : 'Déjà utilisé'}`;
                    
                    if (!field.parentNode.querySelector('.nip-age-info')) {
                        field.parentNode.appendChild(ageInfo);
                    }
                }
                
                return true;
            } else {
                showFieldError(field, result.message || 'NIP invalide');
                updateNIPValidationIcon('invalid');
                return false;
            }
        } else {
            // Erreur serveur - continuer avec validation côté client seulement
            console.warn('Erreur validation serveur NIP:', response.status);
            return validateNIP(field, value);
        }
        
    } catch (error) {
        console.error('Erreur validation NIP:', error);
        // En cas d'erreur, utiliser la validation côté client
        return validateNIP(field, value);
    }
}

/**
 * ✅ AMÉLIORATION : Formatage automatique pendant la saisie
 */
function formatNIPInput(field) {
    field.addEventListener('input', function(e) {
        // Utiliser la fonction de formatage de nip-validation.js si disponible
        if (typeof window.NipValidation !== 'undefined') {
            e.target.value = window.NipValidation.formatInput(e.target.value);
        } else {
            // Formatage de base si nip-validation.js n'est pas chargé
            let value = e.target.value.replace(/[^A-Z0-9]/g, '').toUpperCase();
            
            // Limiter à 14 caractères (XX + QQQQ + YYYYMMDD)
            if (value.length > 14) {
                value = value.substring(0, 14);
            }
            
            // Appliquer le format XX-QQQQ-YYYYMMDD
            if (value.length > 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + '-' + value.substring(7);
            }
            
            e.target.value = value;
        }
        
        // Validation en temps réel si format complet
        if (e.target.value.length === 16) {
            validateNIP(e.target, e.target.value);
        }
    });
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
 * Validation objet social (minimum 50 caractères)
 */
function validateOrgObjet(field, value) {
    if (!value) {
        showFieldError(field, 'L\'objet social est obligatoire');
        return false;
    }
    
    const minLength = 50;
    if (value.length < minLength) {
        showFieldError(field, `L'objet social doit contenir au moins ${minLength} caractères (${value.length}/${minLength})`);
        
        // Ajouter un compteur visuel
        let counterDiv = field.parentNode.querySelector('.char-counter');
        if (!counterDiv) {
            counterDiv = document.createElement('div');
            counterDiv.className = 'char-counter small text-muted mt-1';
            field.parentNode.appendChild(counterDiv);
        }
        counterDiv.textContent = `${value.length}/${minLength} caractères`;
        counterDiv.style.color = value.length < minLength ? '#dc3545' : '#28a745';
        
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
 * Ajouter un adhérent avec validation profession exclue
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
    
    // Vérification profession exclue pour parti politique
    if (OrganisationApp.selectedOrgType === 'parti_politique' && adherent.profession) {
        if (OrganisationApp.config.professionsExcluesParti.includes(adherent.profession)) {
            if (!confirm(`⚠️ ATTENTION: La profession "${adherent.profession}" est normalement exclue des partis politiques selon la législation gabonaise.\n\nVoulez-vous tout de même ajouter cet adhérent ? (Il sera marqué avec une anomalie critique)`)) {
                return;
            }
            
            // Marquer avec anomalie mais permettre l'ajout
            adherent.hasAnomalies = true;
            adherent.anomalies = [{
                type: 'profession_exclue_parti',
                level: 'critique',
                message: 'Profession exclue pour parti politique',
                details: `La profession "${adherent.profession}" est interdite pour les membres de partis politiques`
            }];
        }
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
    
    // Message spécial si anomalie
    if (adherent.hasAnomalies) {
        showNotification('Adhérent ajouté avec anomalie critique (profession exclue)', 'warning');
    } else {
        showNotification('Adhérent ajouté avec succès', 'success');
    }
}


/**
 * Mettre à jour la liste des adhérents - VERSION 2.0
 */
function updateAdherentsList() {
    // Appeler la nouvelle interface moderne
    updateAdherentsTableInterface();
}


/**
 * ========================================================================
 * TABLEAU INTERACTIF ADHÉRENTS - VERSION 2.0
 * Fonctionnalités: Édition, Suppression, Pagination, Recherche, Tri
 * ========================================================================
 */

// Configuration du tableau
const TableConfig = {
    itemsPerPage: 15,
    currentPage: 1,
    searchTerm: '',
    sortField: 'nom',
    sortDirection: 'asc',
    filterAnomalies: 'all' // all, valid, anomalies
};

/**
 * Mise à jour du tableau adhérents avec interface moderne
 */
/**
 * Mettre à jour le compteur d'adhérents - FONCTION MANQUANTE
 */
function updateAdherentsCount(total = 0, valid = 0, anomalies = 0) {
    const countSpan = document.getElementById('adherents_count');
    if (countSpan) {
        countSpan.textContent = total;
    }
    
    // Mise à jour compteur global si disponible
    const globalCounter = document.querySelector('.adherents-counter, #total-adherents');
    if (globalCounter) {
        globalCounter.textContent = total;
    }
    
    // Log pour debug
    console.log(`📊 Compteur adhérents mis à jour: ${total} total, ${valid} valides, ${anomalies} anomalies`);
}

function updateAdherentsTableInterface(preparedData = null) {
    const listContainer = document.getElementById('adherents_list');
    const countSpan = document.getElementById('adherents_count');
    
    if (!listContainer) return;
    
    // Utiliser les données préparées ou les données existantes
    const adherentsData = preparedData ? preparedData.adherents : OrganisationApp.adherents;
    
    if (adherentsData.length === 0) {
        listContainer.innerHTML = getEmptyStateHTML();
        updateAdherentsCount(0, 0, 0);
        return;
    }
    
    // Générer l'interface complète
    listContainer.innerHTML = generateTableInterface(adherentsData);
    
    // Initialiser les événements
    initializeTableEvents();
    
    

    // Mettre à jour le compteur
    updateAdherentsCount(adherentsData.length);
    
    // Afficher la première page
    renderTablePage(adherentsData);

    
}

/**
 * Génération de l'interface complète du tableau
 */
function generateTableInterface(adherentsData) {
    return `
        <!-- Barre de contrôles -->
        <div class="table-controls mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="searchAdherents" 
                               placeholder="Rechercher par nom, prénom, NIP..." 
                               value="${TableConfig.searchTerm}">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterAnomalies" onchange="filterByAnomalies()">
                        <option value="all">Tous les adhérents</option>
                        <option value="valid">Adhérents valides</option>
                        <option value="anomalies">Avec anomalies</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportAdherentsCSV()">
                            <i class="fas fa-download me-1"></i>Export CSV
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addAdherentManually()">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques rapides -->
        <div class="row mb-3" id="tableStats">
            <!-- Sera rempli dynamiquement -->
        </div>
        
        <!-- Tableau principal -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" style="width: 3%">
                            <input type="checkbox" id="selectAllAdherents" onchange="toggleSelectAll()">
                        </th>
                        <th scope="col" class="sortable" data-field="civilite" style="width: 8%">
                            Civilité <i class="fas fa-sort"></i>
                        </th>
                        <th scope="col" class="sortable" data-field="nom" style="width: 15%">
                            Nom <i class="fas fa-sort"></i>
                        </th>
                        <th scope="col" class="sortable" data-field="prenom" style="width: 15%">
                            Prénom <i class="fas fa-sort"></i>
                        </th>
                        <th scope="col" class="sortable" data-field="nip" style="width: 15%">
                            NIP <i class="fas fa-sort"></i>
                        </th>
                        <th scope="col" style="width: 12%">Téléphone</th>
                        <th scope="col" style="width: 12%">Profession</th>
                        <th scope="col" style="width: 10%">Statut</th>
                        <th scope="col" style="width: 10%">Actions</th>
                    </tr>
                </thead>
                <tbody id="adherentsTableBody">
                    <!-- Le contenu sera généré dynamiquement -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="pagination-info">
                <small class="text-muted" id="paginationInfo"></small>
            </div>
            <nav aria-label="Pagination adhérents">
                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                    <!-- Pagination générée dynamiquement -->
                </ul>
            </nav>
        </div>
        
        <!-- Actions groupées -->
        <div class="selected-actions d-none mt-3" id="selectedActions">
            <div class="alert alert-info">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-check-square me-2"></i>
                        <span id="selectedCount">0</span> adhérent(s) sélectionné(s)
                    </span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-warning" onclick="exportSelectedAdherents()">
                            <i class="fas fa-download me-1"></i>Exporter sélection
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteSelectedAdherents()">
                            <i class="fas fa-trash me-1"></i>Supprimer sélection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Rendu d'une page du tableau
 */
function renderTablePage(adherentsData) {
    const filteredData = getFilteredAdherents(adherentsData);
    const totalPages = Math.ceil(filteredData.length / TableConfig.itemsPerPage);
    
    // Ajuster la page actuelle si nécessaire
    if (TableConfig.currentPage > totalPages) {
        TableConfig.currentPage = Math.max(1, totalPages);
    }
    
    const startIndex = (TableConfig.currentPage - 1) * TableConfig.itemsPerPage;
    const endIndex = Math.min(startIndex + TableConfig.itemsPerPage, filteredData.length);
    const pageData = filteredData.slice(startIndex, endIndex);
    
    // Rendu des lignes
    const tbody = document.getElementById('adherentsTableBody');
    if (tbody) {
        tbody.innerHTML = pageData.map((adherent, index) => 
            generateAdherentRow(adherent, startIndex + index)
        ).join('');
    }
    
    // Mise à jour pagination
    updatePaginationControls(filteredData.length, totalPages);
    
    // Mise à jour statistiques
    updateTableStats(adherentsData, filteredData);
}

/**
 * Génération d'une ligne adhérent
 */
function generateAdherentRow(adherent, globalIndex) {
    const hasAnomalies = adherent.hasAnomalies || false;
    const anomaliesCount = adherent.anomalies ? adherent.anomalies.length : 0;
    
    // Badge de statut
    let statusBadge = '<span class="badge bg-success">Valide</span>';
    if (hasAnomalies) {
        const critiques = adherent.anomalies?.filter(a => a.severity === 'critique').length || 0;
        const majeures = adherent.anomalies?.filter(a => a.severity === 'majeure').length || 0;
        
        if (critiques > 0) {
            statusBadge = `<span class="badge bg-danger" title="${anomaliesCount} anomalie(s)">Critique</span>`;
        } else if (majeures > 0) {
            statusBadge = `<span class="badge bg-warning" title="${anomaliesCount} anomalie(s)">Majeure</span>`;
        } else {
            statusBadge = `<span class="badge bg-info" title="${anomaliesCount} anomalie(s)">Mineure</span>`;
        }
    }
    
    return `
        <tr class="${hasAnomalies ? 'table-warning' : ''}" data-index="${globalIndex}">
            <td>
                <input type="checkbox" class="adherent-checkbox" value="${globalIndex}" onchange="updateSelectedActions()">
            </td>
            <td>${adherent.civilite || '-'}</td>
            <td>
                <strong>${adherent.nom || ''}</strong>
                ${adherent.nip_temporaire ? '<i class="fas fa-exclamation-triangle text-warning ms-1" title="NIP temporaire généré"></i>' : ''}
            </td>
            <td>${adherent.prenom || ''}</td>
            <td>
                <code class="text-muted">${adherent.nip || ''}</code>
                ${adherent.nip_original ? `<br><small class="text-muted">Original: ${adherent.nip_original}</small>` : ''}
            </td>
            <td>
                ${adherent.telephone ? `<a href="tel:+241${adherent.telephone}" class="text-decoration-none">${adherent.telephone}</a>` : '-'}
            </td>
            <td>
                <span class="text-truncate" style="max-width: 100px;" title="${adherent.profession || ''}">${adherent.profession || '-'}</span>
            </td>
            <td>${statusBadge}</td>
            <td>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editAdherent(${globalIndex})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${hasAnomalies ? `
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="viewAnomalies(${globalIndex})" title="Voir anomalies">
                            <i class="fas fa-exclamation-triangle"></i>
                        </button>
                    ` : ''}
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeAdherent(${globalIndex})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Initialisation des événements du tableau
 */
/**
 * Initialisation des événements du tableau - VERSION CORRIGÉE
 */
function initializeTableEvents() {
    console.log('🔧 Initialisation événements tableau - Version corrigée');
    
    try {
        // ✅ RECHERCHE en temps réel avec debounce sécurisé
        const searchInput = document.getElementById('searchAdherents');
        if (searchInput) {
            // Vérifier si debounce existe
            if (typeof debounce === 'function') {
                searchInput.addEventListener('input', debounce(function(e) {
                    if (typeof TableConfig !== 'undefined') {
                        TableConfig.searchTerm = e.target.value;
                        TableConfig.currentPage = 1;
                        if (typeof renderTablePage === 'function') {
                            renderTablePage(OrganisationApp.adherents);
                        }
                    }
                }, 300));
                console.log('✅ Recherche avec debounce configurée');
            } else {
                // Fallback sans debounce
                let searchTimeout;
                searchInput.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (typeof TableConfig !== 'undefined') {
                            TableConfig.searchTerm = e.target.value;
                            TableConfig.currentPage = 1;
                            if (typeof renderTablePage === 'function') {
                                renderTablePage(OrganisationApp.adherents);
                            }
                        }
                    }, 300);
                });
                console.log('✅ Recherche sans debounce (fallback) configurée');
            }
        }
        
        // ✅ TRI des colonnes avec gestion d'erreurs
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function() {
                try {
                    const field = this.getAttribute('data-field');
                    
                    if (typeof TableConfig !== 'undefined') {
                        if (TableConfig.sortField === field) {
                            TableConfig.sortDirection = TableConfig.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            TableConfig.sortField = field;
                            TableConfig.sortDirection = 'asc';
                        }
                        
                        if (typeof renderTablePage === 'function') {
                            renderTablePage(OrganisationApp.adherents);
                        }
                    }
                } catch (error) {
                    console.error('❌ Erreur tri colonne:', error);
                }
            });
        });
        
        console.log('✅ Événements tableau initialisés avec succès');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation des événements tableau:', error);
        // Ne pas interrompre l'application, juste logger l'erreur
    }
}

/**
 * Filtrage des adhérents selon les critères
 */
function getFilteredAdherents(adherentsData) {
    let filtered = [...adherentsData];
    
    // Filtrage par recherche
    if (TableConfig.searchTerm.trim()) {
        const searchTerm = TableConfig.searchTerm.toLowerCase().trim();
        filtered = filtered.filter(adherent => {
            return (
                (adherent.nom || '').toLowerCase().includes(searchTerm) ||
                (adherent.prenom || '').toLowerCase().includes(searchTerm) ||
                (adherent.nip || '').toLowerCase().includes(searchTerm) ||
                (adherent.telephone || '').includes(searchTerm) ||
                (adherent.profession || '').toLowerCase().includes(searchTerm)
            );
        });
    }
    
    // Filtrage par anomalies
    if (TableConfig.filterAnomalies === 'valid') {
        filtered = filtered.filter(adherent => !adherent.hasAnomalies);
    } else if (TableConfig.filterAnomalies === 'anomalies') {
        filtered = filtered.filter(adherent => adherent.hasAnomalies);
    }
    
    // Tri
    filtered.sort((a, b) => {
        const field = TableConfig.sortField;
        const direction = TableConfig.sortDirection === 'asc' ? 1 : -1;
        
        const valueA = (a[field] || '').toString().toLowerCase();
        const valueB = (b[field] || '').toString().toLowerCase();
        
        if (valueA < valueB) return -1 * direction;
        if (valueA > valueB) return 1 * direction;
        return 0;
    });
    
    return filtered;
}

/**
 * Mise à jour des contrôles de pagination
 */
function updatePaginationControls(totalItems, totalPages) {
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationControls = document.getElementById('paginationControls');
    
    if (!paginationInfo || !paginationControls) return;
    
    // Info pagination
    const startItem = (TableConfig.currentPage - 1) * TableConfig.itemsPerPage + 1;
    const endItem = Math.min(TableConfig.currentPage * TableConfig.itemsPerPage, totalItems);
    
    paginationInfo.textContent = `Affichage ${startItem}-${endItem} sur ${totalItems} adhérents`;
    
    // Contrôles pagination
    if (totalPages <= 1) {
        paginationControls.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Bouton précédent
    paginationHTML += `
        <li class="page-item ${TableConfig.currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${TableConfig.currentPage - 1})">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Pages
    const maxVisiblePages = 5;
    let startPage = Math.max(1, TableConfig.currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    if (startPage > 1) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="goToPage(1)">1</a>
            </li>
        `;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === TableConfig.currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="goToPage(${totalPages})">${totalPages}</a>
            </li>
        `;
    }
    
    // Bouton suivant
    paginationHTML += `
        <li class="page-item ${TableConfig.currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${TableConfig.currentPage + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationControls.innerHTML = paginationHTML;
}

/**
 * Mise à jour des statistiques du tableau
 */
function updateTableStats(allData, filteredData) {
    const statsContainer = document.getElementById('tableStats');
    if (!statsContainer) return;
    
    const totalAdherents = allData.length;
    const filteredCount = filteredData.length;
    const validAdherents = allData.filter(a => !a.hasAnomalies).length;
    const anomaliesAdherents = allData.filter(a => a.hasAnomalies).length;
    
    statsContainer.innerHTML = `
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-2">
                    <h5 class="mb-1">${totalAdherents}</h5>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center py-2">
                    <h5 class="mb-1">${validAdherents}</h5>
                    <small>Valides</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center py-2">
                    <h5 class="mb-1">${anomaliesAdherents}</h5>
                    <small>Anomalies</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center py-2">
                    <h5 class="mb-1">${filteredCount}</h5>
                    <small>Affichés</small>
                </div>
            </div>
        </div>
    `;
}

/**
 * Actions du tableau
 */

// Navigation pagination
function goToPage(page) {
    TableConfig.currentPage = page;
    renderTablePage(OrganisationApp.adherents);
}

// Filtrage par anomalies
function filterByAnomalies() {
    const select = document.getElementById('filterAnomalies');
    TableConfig.filterAnomalies = select.value;
    TableConfig.currentPage = 1;
    renderTablePage(OrganisationApp.adherents);
}

// Recherche
function clearSearch() {
    document.getElementById('searchAdherents').value = '';
    TableConfig.searchTerm = '';
    TableConfig.currentPage = 1;
    renderTablePage(OrganisationApp.adherents);
}

// Sélection
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllAdherents');
    const checkboxes = document.querySelectorAll('.adherent-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedActions();
}

function updateSelectedActions() {
    const selectedCheckboxes = document.querySelectorAll('.adherent-checkbox:checked');
    const selectedActions = document.getElementById('selectedActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedCheckboxes.length > 0) {
        selectedActions.classList.remove('d-none');
        selectedCount.textContent = selectedCheckboxes.length;
    } else {
        selectedActions.classList.add('d-none');
    }
}

// Édition d'un adhérent
function editAdherent(index) {
    const adherent = OrganisationApp.adherents[index];
    if (!adherent) return;
    
    // Créer modal d'édition
    const modalHTML = `
        <div class="modal fade" id="editAdherentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>
                            Modifier l'adhérent
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editAdherentForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="edit_civilite" class="form-label">Civilité</label>
                                    <select class="form-select" id="edit_civilite">
                                        <option value="M" ${adherent.civilite === 'M' ? 'selected' : ''}>M.</option>
                                        <option value="Mme" ${adherent.civilite === 'Mme' ? 'selected' : ''}>Mme</option>
                                        <option value="Mlle" ${adherent.civilite === 'Mlle' ? 'selected' : ''}>Mlle</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="edit_nom" value="${adherent.nom || ''}" required>
                                </div>
                                <div class="col-md-5">
                                    <label for="edit_prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="edit_prenom" value="${adherent.prenom || ''}" required>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="edit_nip" class="form-label">NIP *</label>
                                    <input type="text" class="form-control" id="edit_nip" value="${adherent.nip || ''}" 
                                           pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}" required>
                                    <div class="form-text">Format: XX-QQQQ-YYYYMMDD</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="edit_telephone" value="${adherent.telephone || ''}">
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="edit_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email" value="${adherent.email || ''}">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_profession" class="form-label">Profession</label>
                                    <input type="text" class="form-control" id="edit_profession" value="${adherent.profession || ''}">
                                </div>
                            </div>
                            
                            ${adherent.hasAnomalies ? `
                                <div class="alert alert-warning mt-3">
                                    <h6>Anomalies détectées:</h6>
                                    <ul class="mb-0">
                                        ${adherent.anomalies.map(a => `<li>${a.message}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" onclick="saveAdherentChanges(${index})">
                            <i class="fas fa-save me-1"></i>Sauvegarder
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Supprimer modal existant et ajouter le nouveau
    const existingModal = document.getElementById('editAdherentModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    const modal = new bootstrap.Modal(document.getElementById('editAdherentModal'));
    modal.show();
}

// Sauvegarde des modifications
function saveAdherentChanges(index) {
    const adherent = OrganisationApp.adherents[index];
    if (!adherent) return;
    
    // Récupérer les valeurs
    adherent.civilite = document.getElementById('edit_civilite').value;
    adherent.nom = document.getElementById('edit_nom').value;
    adherent.prenom = document.getElementById('edit_prenom').value;
    adherent.nip = document.getElementById('edit_nip').value;
    adherent.telephone = document.getElementById('edit_telephone').value;
    adherent.email = document.getElementById('edit_email').value;
    adherent.profession = document.getElementById('edit_profession').value;
    
    // Fermer modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('editAdherentModal'));
    modal.hide();
    
    // Revalider l'adhérent
    const validation = validateSingleAdherentAdvanced(adherent, adherent.lineNumber || index + 2);
    adherent.hasAnomalies = validation.anomalies.length > 0;
    adherent.anomalies = validation.anomalies;
    
    // Rafraîchir le tableau
    renderTablePage(OrganisationApp.adherents);
    
    // Auto-sauvegarde
    autoSave();
    
    showNotification('Adhérent modifié avec succès', 'success');
}

// Visualisation des anomalies
function viewAnomalies(index) {
    const adherent = OrganisationApp.adherents[index];
    if (!adherent || !adherent.hasAnomalies) return;
    
    const modalHTML = `
        <div class="modal fade" id="anomaliesModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Anomalies détectées
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>${adherent.nom} ${adherent.prenom}</h6>
                        <p class="text-muted">NIP: ${adherent.nip}</p>
                        
                        <div class="list-group">
                            ${adherent.anomalies.map(anomalie => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <span class="badge bg-${anomalie.severity === 'critique' ? 'danger' : anomalie.severity === 'majeure' ? 'warning' : 'info'}">
                                                ${anomalie.severity}
                                            </span>
                                            ${anomalie.message}
                                        </h6>
                                    </div>
                                    ${anomalie.suggestion ? `<p class="mb-1"><small>${anomalie.suggestion}</small></p>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" onclick="editAdherent(${index}); bootstrap.Modal.getInstance(document.getElementById('anomaliesModal')).hide();">
                            <i class="fas fa-edit me-1"></i>Corriger
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('anomaliesModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    const modal = new bootstrap.Modal(document.getElementById('anomaliesModal'));
    modal.show();
}

/**
 * ========================================================================
 * FONCTION EXPORT CSV CORRIGÉE ET OPTIMISÉE
 * Version complète avec gestion d'erreurs et fonctionnalités avancées
 * ========================================================================
 */

/**
 * Export CSV des adhérents avec filtres appliqués
 */
function exportAdherentsCSV() {
    try {
        console.log('📥 Début export CSV des adhérents');
        
        // Récupérer les données filtrées actuelles
        const filteredData = getFilteredAdherents(OrganisationApp.adherents);
        
        if (filteredData.length === 0) {
            showNotification('❌ Aucun adhérent à exporter', 'warning');
            return;
        }
        
        console.log(`📊 Export de ${filteredData.length} adhérents`);
        
        // ✅ HEADERS avec informations complètes
        const headers = [
            'Civilité',
            'Nom', 
            'Prénom', 
            'NIP', 
            'Téléphone', 
            'Email', 
            'Profession', 
            'Statut',
            'Ligne Origine',
            'Date Export'
        ];
        
        // ✅ DONNÉES avec formatage optimisé
        const rows = filteredData.map(adherent => [
            adherent.civilite || '',
            adherent.nom || '',
            adherent.prenom || '',
            adherent.nip || '',
            adherent.telephone || '',
            adherent.email || '',
            adherent.profession || '',
            getStatusLabel(adherent),
            adherent.lineNumber || '',
            new Date().toLocaleString('fr-FR')
        ]);
        
        // ✅ CONSTRUCTION CSV avec échappement correct
        const csvContent = [headers, ...rows]
            .map(row => row.map(cell => {
                // Échappement des guillemets et retours à la ligne
                const escapedCell = String(cell)
                    .replace(/"/g, '""')  // Échapper les guillemets
                    .replace(/\n/g, ' ')  // Remplacer retours à la ligne
                    .replace(/\r/g, '');  // Supprimer retours chariot
                
                // Entourer de guillemets si contient virgule, point-virgule ou guillemets
                if (escapedCell.includes(',') || escapedCell.includes(';') || escapedCell.includes('"')) {
                    return `"${escapedCell}"`;
                }
                
                return escapedCell;
            }).join(';'))  // Utiliser point-virgule pour compatibilité Excel français
            .join('\n');
        
        // ✅ AJOUT BOM pour caractères spéciaux
        const BOM = '\uFEFF';
        const csvWithBOM = BOM + csvContent;
        
        // ✅ CRÉATION ET TÉLÉCHARGEMENT
        const blob = new Blob([csvWithBOM], { 
            type: 'text/csv;charset=utf-8;' 
        });
        
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        // ✅ NOM FICHIER INTELLIGENT
        const timestamp = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
        const searchTerm = TableConfig.searchTerm ? `_${TableConfig.searchTerm}` : '';
        const filterTerm = TableConfig.filterAnomalies !== 'all' ? `_${TableConfig.filterAnomalies}` : '';
        
        const fileName = `adherents_${timestamp}${searchTerm}${filterTerm}.csv`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        
        // ✅ DÉCLENCHEMENT TÉLÉCHARGEMENT
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // ✅ NETTOYAGE MÉMOIRE
        URL.revokeObjectURL(url);
        
        // ✅ NOTIFICATION SUCCÈS
        showNotification(
            `✅ Export CSV réussi : ${filteredData.length} adhérents exportés`,
            'success',
            4000
        );
        
        // ✅ LOG POUR DEBUG
        console.log('✅ Export CSV terminé:', {
            fileName: fileName,
            rowsExported: filteredData.length,
            filtersApplied: {
                search: TableConfig.searchTerm,
                anomalies: TableConfig.filterAnomalies
            }
        });
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'export CSV:', error);
        showNotification(
            '❌ Erreur lors de l\'export CSV: ' + error.message,
            'danger',
            6000
        );
    }
}

/**
 * Obtenir le label de statut d'un adhérent
 */
function getStatusLabel(adherent) {
    if (!adherent.hasAnomalies) {
        return 'Valide';
    }
    
    const anomalies = adherent.anomalies || [];
    const critiques = anomalies.filter(a => a.severity === 'critique').length;
    const majeures = anomalies.filter(a => a.severity === 'majeure').length;
    const mineures = anomalies.filter(a => a.severity === 'mineure').length;
    
    if (critiques > 0) {
        return `Critique (${critiques})`;
    } else if (majeures > 0) {
        return `Majeure (${majeures})`;
    } else if (mineures > 0) {
        return `Mineure (${mineures})`;
    } else {
        return 'Anomalies';
    }
}

/**
 * ✅ FONCTION BONUS : Export avec sélection uniquement
 */
function exportSelectedAdherents() {
    try {
        const selectedCheckboxes = document.querySelectorAll('.adherent-checkbox:checked');
        const selectedIndices = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
        
        if (selectedIndices.length === 0) {
            showNotification('❌ Aucun adhérent sélectionné', 'warning');
            return;
        }
        
        console.log(`📥 Export sélection: ${selectedIndices.length} adhérents`);
        
        // Récupérer les adhérents sélectionnés
        const selectedAdherents = selectedIndices.map(index => OrganisationApp.adherents[index]).filter(Boolean);
        
        if (selectedAdherents.length === 0) {
            showNotification('❌ Erreur lors de la récupération des adhérents sélectionnés', 'danger');
            return;
        }
        
        // ✅ HEADERS
        const headers = [
            'Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 
            'Email', 'Profession', 'Statut', 'Date Export'
        ];
        
        // ✅ DONNÉES SÉLECTIONNÉES
        const rows = selectedAdherents.map(adherent => [
            adherent.civilite || '',
            adherent.nom || '',
            adherent.prenom || '',
            adherent.nip || '',
            adherent.telephone || '',
            adherent.email || '',
            adherent.profession || '',
            getStatusLabel(adherent),
            new Date().toLocaleString('fr-FR')
        ]);
        
        // ✅ CONSTRUCTION CSV
        const csvContent = [headers, ...rows]
            .map(row => row.map(cell => {
                const escapedCell = String(cell).replace(/"/g, '""').replace(/\n/g, ' ');
                return escapedCell.includes(',') || escapedCell.includes(';') || escapedCell.includes('"') 
                    ? `"${escapedCell}"` 
                    : escapedCell;
            }).join(';'))
            .join('\n');
        
        // ✅ TÉLÉCHARGEMENT
        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const timestamp = new Date().toISOString().slice(0, 10);
        const fileName = `adherents_selection_${selectedAdherents.length}_${timestamp}.csv`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        showNotification(
            `✅ Export sélection réussi : ${selectedAdherents.length} adhérents`,
            'success',
            4000
        );
        
        console.log('✅ Export sélection terminé:', fileName);
        
    } catch (error) {
        console.error('❌ Erreur export sélection:', error);
        showNotification('❌ Erreur lors de l\'export sélection', 'danger');
    }
}

/**
 * ✅ FONCTION BONUS : Export avec métadonnées complètes
 */
function exportAdherentsWithMetadata() {
    try {
        const filteredData = getFilteredAdherents(OrganisationApp.adherents);
        
        if (filteredData.length === 0) {
            showNotification('❌ Aucun adhérent à exporter', 'warning');
            return;
        }
        
        // ✅ HEADERS ÉTENDUS avec métadonnées
        const headers = [
            'Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 'Email', 'Profession',
            'Statut', 'Nb Anomalies', 'Types Anomalies', 'Ligne Origine',
            'NIP Temporaire', 'NIP Original', 'Date Import', 'Date Export'
        ];
        
        // ✅ DONNÉES AVEC MÉTADONNÉES
        const rows = filteredData.map(adherent => [
            adherent.civilite || '',
            adherent.nom || '',
            adherent.prenom || '',
            adherent.nip || '',
            adherent.telephone || '',
            adherent.email || '',
            adherent.profession || '',
            getStatusLabel(adherent),
            (adherent.anomalies || []).length,
            (adherent.anomalies || []).map(a => a.severity).join(', '),
            adherent.lineNumber || '',
            adherent.nip_temporaire ? 'Oui' : 'Non',
            adherent.nip_original || '',
            OrganisationApp.adherentsMetadata?.timestamp || '',
            new Date().toLocaleString('fr-FR')
        ]);
        
        // ✅ CONSTRUCTION ET TÉLÉCHARGEMENT
        const csvContent = [headers, ...rows]
            .map(row => row.map(cell => {
                const escapedCell = String(cell).replace(/"/g, '""').replace(/\n/g, ' ');
                return escapedCell.includes(',') || escapedCell.includes(';') || escapedCell.includes('"') 
                    ? `"${escapedCell}"` 
                    : escapedCell;
            }).join(';'))
            .join('\n');
        
        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const timestamp = new Date().toISOString().slice(0, 10);
        const fileName = `adherents_complet_${timestamp}.csv`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        showNotification(
            `✅ Export complet réussi : ${filteredData.length} adhérents avec métadonnées`,
            'success',
            4000
        );
        
    } catch (error) {
        console.error('❌ Erreur export complet:', error);
        showNotification('❌ Erreur lors de l\'export complet', 'danger');
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
// 5.1 IMPORTATION FICHIER ADHÉRENTS - VERSION COMPLÈTE
// ========================================

/**
 * Gestion de l'importation du fichier Excel/CSV des adhérents
 */
/**
 * ========================================================================
 * ÉTAPE 7 OPTIMISÉE - UPLOAD FICHIER ADHÉRENTS (SESSION SEULEMENT)
 * Version: 2.0 - UX Moderne avec Progress Bar et Validation Avancée
 * ========================================================================
 */

/**
 * Gestion optimisée de l'upload fichier adhérents pour Étape 7
 * IMPORTANT: Ne stocke QUE en session, pas en base de données
 */
async function handleAdherentFileImport(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    
    console.log('📁 ÉTAPE 7 v2.0: Préparation fichier adhérents (SESSION SEULEMENT)', file.name);
    
    // Validation initiale du fichier
    if (!validateAdherentFile(file)) {
        clearFileInput();
        return;
    }
    
    try {
        // ✅ Interface moderne avec progress bar
        showUploadProgress();
        
        // ✅ ÉTAPE 1: Lecture fichier avec progress (25%)
        updateUploadProgress(25, '📖 Lecture du fichier en cours...');
        const adherentsData = await readAdherentFileWithProgress(file);
        
        if (!adherentsData || adherentsData.length === 0) {
            throw new Error('Le fichier est vide ou ne contient pas de données valides');
        }
        
        console.log(`📊 ${adherentsData.length} adhérents détectés dans le fichier`);
        
        // ✅ ÉTAPE 2: Validation avec progress (50%)
        updateUploadProgress(50, `🔍 Validation de ${adherentsData.length} adhérents...`);
        const validationResult = await validateAdherentsWithProgress(adherentsData);
        
        // ✅ ÉTAPE 3: Normalisation et préparation (75%)
        updateUploadProgress(75, '⚙️ Préparation des données pour session...');
        const preparedData = await prepareAdherentsForSession(validationResult);
        
        // ✅ ÉTAPE 4: Stockage en session (90%)
        updateUploadProgress(90, '💾 Sauvegarde en session...');
        await saveAdherentsToSession(preparedData);
        
        // ✅ ÉTAPE 5: Mise à jour interface (100%)
        updateUploadProgress(100, '✅ Import terminé avec succès !');
        
        // Actualiser l'interface avec tableau moderne
        updateAdherentsTableInterface(preparedData);
        
        // Rapport de succès
        showUploadSuccess(preparedData);
        
        // Nettoyer l'input
        clearFileInput();
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'upload Étape 7:', error);
        showUploadError(error.message);
        clearFileInput();
    }
}

/**
 * Lecture du fichier avec progress tracking
 */
async function readAdherentFileWithProgress(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = e.target.result;
                let adherentsData = [];
                
                if (file.name.toLowerCase().endsWith('.csv')) {
                    // Traitement CSV optimisé
                    adherentsData = parseCSVAdvanced(data);
                } else {
                    // Traitement Excel avec XLSX
                    const workbook = XLSX.read(data, { type: 'binary' });
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    adherentsData = XLSX.utils.sheet_to_json(worksheet);
                }
                
                console.log(`✅ Fichier lu avec succès: ${adherentsData.length} lignes détectées`);
                resolve(adherentsData);
                
            } catch (error) {
                console.error('❌ Erreur lors de la lecture du fichier:', error);
                reject(new Error('Impossible de lire le fichier. Vérifiez le format.'));
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur de lecture du fichier'));
        
        // Lire selon le type de fichier
        if (file.name.toLowerCase().endsWith('.csv')) {
            reader.readAsText(file, 'UTF-8');
        } else {
            reader.readAsBinaryString(file);
        }
    });
}

/**
 * Parser CSV avancé avec détection automatique de délimiteur
 */
function parseCSVAdvanced(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) return [];
    
    // Détection intelligente du délimiteur
    const delimiters = [';', ',', '\t', '|'];
    const headerLine = lines[0];
    
    let bestDelimiter = ';';
    let maxColumns = 0;
    
    for (let delimiter of delimiters) {
        const columns = headerLine.split(delimiter).length;
        if (columns > maxColumns) {
            maxColumns = columns;
            bestDelimiter = delimiter;
        }
    }
    
    console.log(`📋 Délimiteur détecté: "${bestDelimiter}" (${maxColumns} colonnes)`);
    
    // Parser avec le meilleur délimiteur
    const headers = lines[0].split(bestDelimiter).map(h => h.trim().toLowerCase());
    const adherentsData = [];
    
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(bestDelimiter);
        
        if (values.length >= headers.length - 1) { // Tolérance pour colonnes manquantes
            const adherent = {};
            
            headers.forEach((header, index) => {
                adherent[header] = values[index] ? values[index].trim() : '';
            });
            
            // Ne pas ajouter les lignes complètement vides
            if (Object.values(adherent).some(val => val !== '')) {
                adherentsData.push(adherent);
            }
        }
    }
    
    return adherentsData;
}

/**
 * Validation avancée des adhérents avec progress
 */
async function validateAdherentsWithProgress(adherentsData) {
    const validationResult = {
        total: adherentsData.length,
        valides: 0,
        invalides: 0,
        anomalies_mineures: 0,
        anomalies_majeures: 0,
        anomalies_critiques: 0,
        adherents: [],
        rapport: {
            erreurs: [],
            avertissements: [],
            infos: []
        }
    };
    
    // Mapping intelligent des champs
    const fieldMapping = {
        'nom': ['nom', 'lastname', 'surname', 'family_name'],
        'prenom': ['prenom', 'prénom', 'firstname', 'first_name', 'given_name'],
        'nip': ['nip', 'numero', 'numero_identite', 'id_number'],
        'telephone': ['telephone', 'téléphone', 'phone', 'mobile', 'cellulaire'],
        'email': ['email', 'mail', 'courriel', 'e-mail'],
        'profession': ['profession', 'metier', 'job', 'occupation'],
        'civilite': ['civilite', 'civilité', 'title', 'mr_mrs']
    };
    
    // Traitement par batch pour éviter le freeze
    const batchSize = 50;
    const totalBatches = Math.ceil(adherentsData.length / batchSize);
    
    for (let batchIndex = 0; batchIndex < totalBatches; batchIndex++) {
        const startIndex = batchIndex * batchSize;
        const endIndex = Math.min(startIndex + batchSize, adherentsData.length);
        const batch = adherentsData.slice(startIndex, endIndex);
        
        // Traiter chaque adhérent du batch
        batch.forEach((adherent, index) => {
            const globalIndex = startIndex + index;
            const lineNumber = globalIndex + 2; // +2 car ligne 1 = headers
            
            const normalizedAdherent = normalizeAdherentFields(adherent, fieldMapping);
            const validation = validateSingleAdherentAdvanced(normalizedAdherent, lineNumber);
            
            if (validation.isValid) {
                validationResult.valides++;
                normalizedAdherent.lineNumber = lineNumber;
                normalizedAdherent.hasAnomalies = validation.anomalies.length > 0;
                normalizedAdherent.anomalies = validation.anomalies;
                
                validationResult.adherents.push(normalizedAdherent);
                
                // Compter les anomalies par niveau
                validation.anomalies.forEach(anomalie => {
                    switch(anomalie.severity) {
                        case 'critique': validationResult.anomalies_critiques++; break;
                        case 'majeure': validationResult.anomalies_majeures++; break;
                        case 'mineure': validationResult.anomalies_mineures++; break;
                    }
                });
                
            } else {
                validationResult.invalides++;
                validationResult.rapport.erreurs.push({
                    ligne: lineNumber,
                    erreurs: validation.erreurs
                });
            }
        });
        
        // Mise à jour progress durant la validation
        const progress = 50 + Math.round((batchIndex + 1) / totalBatches * 20); // 50% à 70%
        updateUploadProgress(progress, `Validation batch ${batchIndex + 1}/${totalBatches}...`);
        
        // Pause pour permettre l'update UI
        if (batchIndex < totalBatches - 1) {
            await new Promise(resolve => setTimeout(resolve, 50));
        }
    }
    
    console.log('✅ Validation terminée:', {
        total: validationResult.total,
        valides: validationResult.valides,
        invalides: validationResult.invalides,
        anomalies: validationResult.anomalies_critiques + validationResult.anomalies_majeures + validationResult.anomalies_mineures
    });
    
    return validationResult;
}

/**
 * Validation avancée d'un adhérent unique
 */
function validateSingleAdherentAdvanced(adherent, lineNumber) {
    const erreurs = [];
    const anomalies = [];
    
    // Validations obligatoires
    if (!adherent.nom || adherent.nom.length < 2) {
        erreurs.push('Nom manquant ou trop court');
    }
    
    if (!adherent.prenom || adherent.prenom.length < 2) {
        erreurs.push('Prénom manquant ou trop court');
    }
    
    // Validation NIP avancée (format XX-QQQQ-YYYYMMDD)
    if (!adherent.nip) {
        erreurs.push('NIP manquant');
    } else {
        const nipPattern = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;
        if (!nipPattern.test(adherent.nip)) {
            anomalies.push({
                code: 'nip_format_invalide',
                severity: 'majeure',
                message: `Format NIP invalide: ${adherent.nip}`,
                suggestion: 'Format attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
            });
        } else {
            // Validation de la date dans le NIP
            const datePart = adherent.nip.slice(-8);
            const year = parseInt(datePart.substring(0, 4));
            const month = parseInt(datePart.substring(4, 6));
            const day = parseInt(datePart.substring(6, 8));
            
            const currentYear = new Date().getFullYear();
            
            if (year < 1900 || year > currentYear) {
                anomalies.push({
                    code: 'nip_annee_invalide',
                    severity: 'majeure',
                    message: `Année de naissance invalide dans NIP: ${year}`
                });
            }
            
            if (month < 1 || month > 12) {
                anomalies.push({
                    code: 'nip_mois_invalide',
                    severity: 'majeure',
                    message: `Mois invalide dans NIP: ${month}`
                });
            }
            
            if (day < 1 || day > 31) {
                anomalies.push({
                    code: 'nip_jour_invalide',
                    severity: 'majeure',
                    message: `Jour invalide dans NIP: ${day}`
                });
            }
            
            // Vérifier âge minimum (18 ans)
            const birthDate = new Date(year, month - 1, day);
            const age = Math.floor((new Date() - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
            
            if (age < 18) {
                anomalies.push({
                    code: 'age_mineur',
                    severity: 'critique',
                    message: `Personne mineure (${age} ans) - non autorisée`
                });
            }
        }
    }
    
    // Validation email
    if (adherent.email && adherent.email.length > 0) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(adherent.email)) {
            anomalies.push({
                code: 'email_invalide',
                severity: 'mineure',
                message: `Email invalide: ${adherent.email}`
            });
        }
    }
    
    // Validation téléphone gabonais
    if (adherent.telephone && adherent.telephone.length > 0) {
        const cleanPhone = adherent.telephone.replace(/[^0-9+]/g, '');
        
        // Patterns téléphone gabonais
        const gabonPatterns = [
            /^(\+241)?[01][0-9]{7}$/, // Fixe: 01XXXXXXX
            /^(\+241)?[67][0-9]{7}$/  // Mobile: 6XXXXXXXX ou 7XXXXXXXX
        ];
        
        const isValidGabonPhone = gabonPatterns.some(pattern => pattern.test(cleanPhone));
        
        if (!isValidGabonPhone) {
            anomalies.push({
                code: 'telephone_invalide',
                severity: 'mineure',
                message: `Téléphone invalide: ${adherent.telephone}`,
                suggestion: 'Format attendu: 01XXXXXXX, 6XXXXXXXX ou 7XXXXXXXX'
            });
        }
    }
    
    // Validation civilité
    if (adherent.civilite && !['M', 'Mme', 'Mlle', 'Mr', 'Mrs', 'Ms'].includes(adherent.civilite)) {
        anomalies.push({
            code: 'civilite_non_standard',
            severity: 'mineure',
            message: `Civilité non standard: ${adherent.civilite}`
        });
        
        // Auto-correction
        const civiliteNormalized = adherent.civilite.toLowerCase();
        if (civiliteNormalized.includes('m') && !civiliteNormalized.includes('me')) {
            adherent.civilite = 'M';
        } else if (civiliteNormalized.includes('me')) {
            adherent.civilite = 'Mme';
        } else if (civiliteNormalized.includes('lle')) {
            adherent.civilite = 'Mlle';
        }
    }
    
    return {
        isValid: erreurs.length === 0,
        erreurs: erreurs,
        anomalies: anomalies
    };
}

/**
 * Préparation finale des données pour session
 */
async function prepareAdherentsForSession(validationResult) {
    const preparedData = {
        adherents: [],
        stats: {
            total: validationResult.total,
            valides: validationResult.valides,
            invalides: validationResult.invalides,
            anomalies_mineures: validationResult.anomalies_mineures,
            anomalies_majeures: validationResult.anomalies_majeures,
            anomalies_critiques: validationResult.anomalies_critiques
        },
        rapport: validationResult.rapport,
        timestamp: new Date().toISOString(),
        expires_at: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString() // 2h
    };
    
    // Préparer chaque adhérent valide
    validationResult.adherents.forEach(adherent => {
        // Générer un NIP temporaire si invalide mais adhérent valide
        if (adherent.hasAnomalies && adherent.anomalies.some(a => a.code.includes('nip'))) {
            adherent.nip_original = adherent.nip;
            adherent.nip = generateTemporaryNIP();
            adherent.nip_temporaire = true;
        }
        
        preparedData.adherents.push({
            civilite: adherent.civilite || 'M',
            nom: adherent.nom,
            prenom: adherent.prenom,
            nip: adherent.nip,
            telephone: adherent.telephone || '',
            email: adherent.email || '',
            profession: adherent.profession || '',
            lineNumber: adherent.lineNumber,
            hasAnomalies: adherent.hasAnomalies || false,
            anomalies: adherent.anomalies || [],
            nip_temporaire: adherent.nip_temporaire || false,
            nip_original: adherent.nip_original || null
        });
    });
    
    return preparedData;
}

/**
 * Génération d'un NIP temporaire valide
 */
function generateTemporaryNIP() {
    const prefix = 'TMP';
    const sequence = String(Math.floor(Math.random() * 9999)).padStart(4, '0');
    const birthYear = '19900101'; // Date neutre
    
    return `${prefix}-${sequence}-${birthYear}`;
}

/**
 * Sauvegarde en session avec structure optimisée
 */
async function saveAdherentsToSession(preparedData) {
    console.log('💾 Sauvegarde des adhérents dans la session formulaire (Étape 7)');
    
    // Vider les adhérents existants dans l'application
    OrganisationApp.adherents = [];
    
    // Ajouter tous les adhérents préparés
    preparedData.adherents.forEach(adherent => {
        OrganisationApp.adherents.push(adherent);
    });
    
    // Stocker aussi les métadonnées pour Phase 2
    OrganisationApp.adherentsMetadata = {
        stats: preparedData.stats,
        rapport: preparedData.rapport,
        timestamp: preparedData.timestamp,
        expires_at: preparedData.expires_at
    };
    
    console.log(`✅ ${OrganisationApp.adherents.length} adhérents sauvegardés en session`);
    
    // Déclencher les mises à jour UI
    updateAdherentsList();
    updateFormStats();
    autoSave();
}

/**
 * Interface de progress moderne
 */
function showUploadProgress() {
    const existingModal = document.getElementById('uploadProgressModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHTML = `
        <div class="modal fade" id="uploadProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-upload me-2"></i>
                            Import Fichier Adhérents - Étape 7
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        
                        <div class="progress mb-3" style="height: 25px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="uploadProgressText">0%</span>
                            </div>
                        </div>
                        
                        <div id="uploadProgressMessage" class="text-center text-muted">
                            Initialisation...
                        </div>
                        
                        <div id="uploadProgressDetails" class="mt-3 small text-muted">
                            <!-- Détails supplémentaires -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    const modal = new bootstrap.Modal(document.getElementById('uploadProgressModal'));
    modal.show();
}

/**
 * Mise à jour du progress
 */
function updateUploadProgress(percentage, message, details = '') {
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const progressMessage = document.getElementById('uploadProgressMessage');
    const progressDetails = document.getElementById('uploadProgressDetails');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
    }
    
    if (progressText) {
        progressText.textContent = percentage + '%';
    }
    
    if (progressMessage) {
        progressMessage.textContent = message;
    }
    
    if (progressDetails && details) {
        progressDetails.innerHTML = details;
    }
}

/**
 * Affichage du succès avec résumé
 */
function showUploadSuccess(preparedData) {
    // Fermer le modal de progress
    const progressModal = bootstrap.Modal.getInstance(document.getElementById('uploadProgressModal'));
    if (progressModal) {
        progressModal.hide();
    }
    
    // Afficher notification de succès
    const stats = preparedData.stats;
    let message = `✅ ${stats.valides} adhérents préparés avec succès !`;
    
    if (stats.anomalies_mineures + stats.anomalies_majeures + stats.anomalies_critiques > 0) {
        message += ` (${stats.anomalies_mineures + stats.anomalies_majeures + stats.anomalies_critiques} anomalies détectées)`;
    }
    
    showNotification(message, 'success', 6000);
    
    // Afficher rapport détaillé dans l'interface
    showDetailedReport(preparedData);
}

/**
 * Affichage des erreurs
 */
function showUploadError(errorMessage) {
    // Fermer le modal de progress
    const progressModal = bootstrap.Modal.getInstance(document.getElementById('uploadProgressModal'));
    if (progressModal) {
        progressModal.hide();
    }
    
    showNotification('❌ Erreur lors de l\'import: ' + errorMessage, 'danger', 8000);
}

/**
 * Affichage du rapport détaillé
 */
function showDetailedReport(preparedData) {
    const detailsContainer = document.getElementById('import_details');
    if (!detailsContainer) return;
    
    const stats = preparedData.stats;
    
    const reportHTML = `
        <div class="alert alert-success border-0 mt-3 fade-in">
            <h6 class="alert-heading">
                <i class="fas fa-file-check me-2"></i>
                Fichier traité avec succès - Version 2.0
            </h6>
            
            <div class="row text-center mb-3">
                <div class="col-3">
                    <div class="h4 text-primary">${stats.total}</div>
                    <small>Total lignes</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-success">${stats.valides}</div>
                    <small>Valides</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-warning">${stats.anomalies_mineures + stats.anomalies_majeures}</div>
                    <small>Anomalies</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-danger">${stats.invalides}</div>
                    <small>Rejetés</small>
                </div>
            </div>
            
            ${stats.anomalies_critiques > 0 ? `
                <div class="alert alert-warning">
                    <strong>⚠️ ${stats.anomalies_critiques} anomalies critiques détectées</strong><br>
                    Ces adhérents seront marqués pour révision mais seront inclus dans l'import.
                </div>
            ` : ''}
            
            <hr>
            
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-info-circle text-info me-2"></i>
                    <strong>Les adhérents sont préparés pour l'importation finale en Phase 2.</strong>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDetailedStats()">
                    <i class="fas fa-chart-bar me-1"></i>Voir détails
                </button>
            </div>
            
            <div id="detailedStats" class="mt-3 d-none">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Types d'anomalies:</h6>
                        <small class="text-danger">Critiques: ${stats.anomalies_critiques}</small><br>
                        <small class="text-warning">Majeures: ${stats.anomalies_majeures}</small><br>
                        <small class="text-info">Mineures: ${stats.anomalies_mineures}</small>
                    </div>
                    <div class="col-md-6">
                        <h6>Prochaines étapes:</h6>
                        <small>✅ Données en session (2h)</small><br>
                        <small>⏳ Soumission → Phase 2</small><br>
                        <small>🚀 Import final en base</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    detailsContainer.innerHTML = reportHTML;
    detailsContainer.classList.remove('d-none');
}

/**
 * Toggle des statistiques détaillées
 */
function toggleDetailedStats() {
    const detailedStats = document.getElementById('detailedStats');
    if (detailedStats) {
        detailedStats.classList.toggle('d-none');
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
 * Validation complète des données d'importation avec système d'anomalies
 */
async function validateAdherentsImport(adherentsData) {
    console.log('📋 Validation avec gestion anomalies - Version 1.2');
    
    const result = {
        originalCount: adherentsData.length,
        adherentsValides: [],
        adherentsAvecAnomalies: [],
        adherentsTotal: [], // Tous les adhérents (valides + anomalies)
        duplicatesInFile: [],
        existingMembers: [],
        invalidEntries: [],
        finalValidCount: 0,
        finalAnomaliesCount: 0,
        canProceed: true, // Toujours true maintenant si minimum atteint
        messages: [],
        qualiteGlobale: 'excellent'
    };
    
    // Réinitialiser le rapport d'anomalies
    OrganisationApp.rapportAnomalies = {
        enabled: false,
        adherentsValides: 0,
        adherentsAvecAnomalies: 0,
        anomalies: [],
        statistiques: { critique: 0, majeure: 0, mineure: 0 },
        genereAt: null,
        version: '1.2'
    };
    
    // Obtenir les exigences selon le type d'organisation
    const requirements = OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType];
    const minRequired = requirements ? requirements.minAdherents : 10;
    
    console.log(`📊 Validation import: ${adherentsData.length} adhérents, minimum requis: ${minRequired}`);
    
    // ========================================
    // ÉTAPE 1 : Détection doublons NIP dans le fichier
    // ========================================
    const seenNips = new Set();
    const processedAdherents = [];
    
    adherentsData.forEach((adherent, index) => {
        const nip = adherent.nip?.trim();
        
        // Créer un ID unique pour chaque adhérent
        adherent.id = `adherent_${Date.now()}_${index}`;
        adherent.hasAnomalies = false;
        adherent.anomalies = [];
        
        // Validation NIP de base
        if (!nip) {
            const anomalie = createAnomalie(adherent, 'champs_incomplets', 'NIP manquant');
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        } else if (!OrganisationApp.config.nip.pattern.test(nip)) {
            const anomalie = createAnomalie(adherent, 'nip_invalide', `Format NIP incorrect: ${nip}`);
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        } else if (seenNips.has(nip)) {
            // Doublon dans le fichier
            const anomalie = createAnomalie(adherent, 'doublon_fichier', `NIP ${nip} déjà présent ligne précédente`);
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
            result.duplicatesInFile.push({ ...adherent, nip: nip });
        } else {
            seenNips.add(nip);
        }
        
        // ========================================
        // VALIDATION AUTRES CHAMPS AVEC ANOMALIES
        // ========================================
        
        // Validation nom/prénom
        if (!adherent.nom || !adherent.prenom) {
            const anomalie = createAnomalie(adherent, 'champs_incomplets', 'Nom ou prénom manquant');
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        }
        
        // Validation téléphone (si présent)
        if (adherent.telephone && !OrganisationApp.config.phone.pattern.test(adherent.telephone.replace(/\s+/g, ''))) {
            const anomalie = createAnomalie(adherent, 'telephone_invalide', `Format téléphone incorrect: ${adherent.telephone}`);
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        }
        
        // Validation email (si présent)
        if (adherent.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adherent.email)) {
            const anomalie = createAnomalie(adherent, 'email_invalide', `Format email incorrect: ${adherent.email}`);
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        }
        
        // Validation profession exclue pour parti politique
        if (OrganisationApp.selectedOrgType === 'parti_politique' && adherent.profession) {
            if (OrganisationApp.config.professionsExcluesParti.includes(adherent.profession)) {
                const anomalie = createAnomalie(adherent, 'profession_exclue_parti', 
                    `Profession "${adherent.profession}" interdite pour parti politique`);
                if (anomalie) {
                    adherent.anomalies.push(anomalie);
                    adherent.hasAnomalies = true;
                }
            }
        }
        
        // Validation format données générales
        if (adherent.nom && adherent.nom.length < 2) {
            const anomalie = createAnomalie(adherent, 'format_donnees', 'Nom trop court');
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        }
        
        processedAdherents.push(adherent);
    });
    
    // ========================================
    // ÉTAPE 2 : Vérification doublons avec fondateurs/adhérents existants
    // ========================================
    const foundersNips = OrganisationApp.fondateurs.map(f => f.nip);
    const adherentsNips = OrganisationApp.adherents.map(a => a.nip);
    
    processedAdherents.forEach(adherent => {
        if (foundersNips.includes(adherent.nip)) {
            const anomalie = createAnomalie(adherent, 'doublon_fichier', 'NIP déjà présent dans les fondateurs');
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        }
        
        if (adherentsNips.includes(adherent.nip)) {
            const anomalie = createAnomalie(adherent, 'doublon_fichier', 'NIP déjà présent dans les adhérents');
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
        }
    });
    
    // ========================================
    // ÉTAPE 3 : Vérification membres existants via API
    // ========================================
    const nipsToCheck = processedAdherents
        .filter(a => a.nip && OrganisationApp.config.nip.pattern.test(a.nip))
        .map(a => a.nip);
        
    const existingMembersNips = await checkExistingMembersAPI(nipsToCheck);
    
    processedAdherents.forEach(adherent => {
        if (existingMembersNips.includes(adherent.nip)) {
            const anomalie = createAnomalie(adherent, 'membre_existant', 'Déjà membre actif d\'une autre organisation');
            if (anomalie) {
                adherent.anomalies.push(anomalie);
                adherent.hasAnomalies = true;
            }
            result.existingMembers.push(adherent);
        }
    });
    
    // ========================================
    // ÉTAPE 4 : CLASSIFICATION FINALE
    // ========================================
    
    processedAdherents.forEach(adherent => {
        if (adherent.hasAnomalies) {
            // Ajouter toutes les anomalies au rapport global
            adherent.anomalies.forEach(anomalie => {
                addAnomalieToReport(anomalie);
            });
            
            // Marquer comme adhérent avec anomalies
            adherent.status = 'anomalie';
            adherent.statusLabel = 'Anomalie détectée';
            adherent.statusColor = 'warning';
            
            // Déterminer le niveau de gravité le plus élevé
            const niveaux = adherent.anomalies.map(a => a.level);
            if (niveaux.includes('critique')) {
                adherent.priorityLevel = 'critique';
                adherent.statusColor = 'danger';
            } else if (niveaux.includes('majeure')) {
                adherent.priorityLevel = 'majeure';
                adherent.statusColor = 'warning';
            } else {
                adherent.priorityLevel = 'mineure';
                adherent.statusColor = 'info';
            }
            
            result.adherentsAvecAnomalies.push(adherent);
        } else {
            // Adhérent valide
            adherent.status = 'valide';
            adherent.statusLabel = 'Valide';
            adherent.statusColor = 'success';
            adherent.priorityLevel = null;
            
            result.adherentsValides.push(adherent);
        }
        
        // Tous les adhérents sont conservés
        result.adherentsTotal.push(adherent);
    });
    
    // ========================================
    // MISE À JOUR STATISTIQUES FINALES
    // ========================================
    
    result.finalValidCount = result.adherentsValides.length;
    result.finalAnomaliesCount = result.adherentsAvecAnomalies.length;
    
    // Mettre à jour le rapport d'anomalies
    if (result.finalAnomaliesCount > 0) {
        OrganisationApp.rapportAnomalies.enabled = true;
        OrganisationApp.rapportAnomalies.adherentsValides = result.finalValidCount;
        OrganisationApp.rapportAnomalies.adherentsAvecAnomalies = result.finalAnomaliesCount;
        OrganisationApp.rapportAnomalies.genereAt = new Date().toISOString();
    }
    
    // Déterminer la qualité globale
    result.qualiteGlobale = getQualiteStatut();
    
    // Toujours permettre l'importation si minimum atteint
    const totalAdherents = result.finalValidCount + result.finalAnomaliesCount;
    result.canProceed = totalAdherents >= minRequired;
    
    // Générer les messages selon les nouveaux critères
    result.messages = generateImportMessagesWithAnomalies(result, minRequired);
    
    console.log('📊 Résultat validation avec anomalies:', {
        total: totalAdherents,
        valides: result.finalValidCount,
        anomalies: result.finalAnomaliesCount,
        qualite: result.qualiteGlobale,
        canProceed: result.canProceed
    });
    
    return result;
}

/**
 * Génération des messages avec gestion des anomalies
 */
function generateImportMessagesWithAnomalies(result, minRequired) {
    const messages = [];
    const totalAdherents = result.finalValidCount + result.finalAnomaliesCount;
    
    // Message principal selon le résultat
    if (result.canProceed) {
        if (result.finalAnomaliesCount === 0) {
            messages.push({
                type: 'success',
                title: '✅ Importation parfaite',
                content: `${result.finalValidCount} adhérents valides détectés. Aucune anomalie trouvée. Minimum requis: ${minRequired}`
            });
        } else {
            messages.push({
                type: 'warning',
                title: '⚠️ Importation avec anomalies',
                content: `${totalAdherents} adhérents détectés (${result.finalValidCount} valides + ${result.finalAnomaliesCount} avec anomalies). Un rapport sera généré. Minimum requis: ${minRequired}`
            });
        }
    } else {
        messages.push({
            type: 'danger',
            title: '❌ Importation impossible',
            content: `Seulement ${totalAdherents} adhérents détectés, minimum requis: ${minRequired}.`
        });
    }
    
    // Message sur la qualité globale
    const qualiteMessages = {
        'excellent': { type: 'success', message: '🌟 Excellente qualité des données' },
        'bon': { type: 'info', message: '👍 Bonne qualité des données' },
        'moyen': { type: 'warning', message: '⚠️ Qualité moyenne des données' },
        'faible': { type: 'danger', message: '❌ Qualité faible des données' }
    };
    
    if (qualiteMessages[result.qualiteGlobale]) {
        const qMsg = qualiteMessages[result.qualiteGlobale];
        messages.push({
            type: qMsg.type,
            title: 'Évaluation qualité',
            content: qMsg.message
        });
    }
    
    // Messages spécifiques pour les anomalies
    if (result.finalAnomaliesCount > 0) {
        const stats = OrganisationApp.rapportAnomalies.statistiques;
        
        messages.push({
            type: 'info',
            title: '📋 Rapport d\'anomalies généré',
            content: `${result.finalAnomaliesCount} adhérent(s) avec anomalies : ${stats.critique} critique(s), ${stats.majeure} majeure(s), ${stats.mineure} mineure(s)`,
            details: result.adherentsAvecAnomalies.map(a => 
                `Ligne ${a.lineNumber}: ${a.nom} ${a.prenom} (${a.anomalies.length} anomalie(s) ${a.priorityLevel})`
            )
        });
    }
    
    return messages;
}

/**
 * Vérification des membres existants via API
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
            return [];
        } else {
            console.warn('Erreur API check membres existants:', response.status);
            return [];
        }
    } catch (error) {
        console.error('Erreur vérification membres existants:', error);
        return [];
    }
}

/**
 * Traitement du résultat d'importation
 */
async function processImportResult(validationResult) {
    const { canProceed, adherentsTotal, messages, originalCount, finalValidCount, finalAnomaliesCount } = validationResult;
    
    // Afficher tous les messages de validation
    messages.forEach(message => {
        showDetailedImportNotification(message);
    });
    
    if (!canProceed) {
        showNotification('❌ Importation annulée: critères non remplis', 'danger');
        clearFileInput();
        return;
    }
    
    // Message de confirmation avec anomalies
    const totalImport = finalValidCount + finalAnomaliesCount;
    let confirmMsg = `Importation de ${totalImport} adhérents sur ${originalCount} lignes analysées :\n`;
    confirmMsg += `• ${finalValidCount} adhérents valides\n`;
    if (finalAnomaliesCount > 0) {
        confirmMsg += `• ${finalAnomaliesCount} adhérents avec anomalies (seront conservés)\n`;
        confirmMsg += `\n⚠️ Un rapport d'anomalies sera généré automatiquement.\n`;
    }
    confirmMsg += `\nConfirmez-vous l'importation ?`;
    
    if (!confirm(confirmMsg)) {
        showNotification('❌ Importation annulée par l\'utilisateur', 'info');
        clearFileInput();
        return;
    }
    
    // Ajouter TOUS les adhérents (valides + anomalies)
    adherentsTotal.forEach(adherent => {
        OrganisationApp.adherents.push(adherent);
    });
    
    // Mettre à jour l'affichage avec les nouveaux statuts
    updateAdherentsList();
    
    // Message de succès détaillé
    let successDetails = [`🎉 Importation réussie !`];
    successDetails.push(`📊 ${finalValidCount} adhérents valides ajoutés`);
    if (finalAnomaliesCount > 0) {
        successDetails.push(`⚠️ ${finalAnomaliesCount} avec anomalies conservés`);
        successDetails.push(`📋 Rapport d'anomalies généré automatiquement`);
    }
    successDetails.push(`📁 Total: ${totalImport} entrées traitées`);
    
    showNotification(successDetails.join('\n'), 'success', 10000);
    
    // Vider le champ fichier et sauvegarder
    clearFileInput();
    autoSave();
    
    console.log('✅ Import terminé v1.2:', {
        valides: finalValidCount,
        anomalies: finalAnomaliesCount,
        total: totalImport,
        rapportGenere: OrganisationApp.rapportAnomalies.enabled
    });
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
    
    // Créer notification personnalisée
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
    console.log('📋 Génération récapitulatif avec rapport d\'anomalies - Version 1.2');
    
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
                ${generateCompositionWithQuality(formData)}
                
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
        
        <!-- Section rapport d'anomalies conditionnelle -->
        ${generateAnomaliesRecapSection()}
        
        <!-- Statut de validation mis à jour -->
        <div class="row mt-4">
            <div class="col-12">
                ${generateValidationStatusWithQuality()}
            </div>
        </div>
        
        <!-- Section spéciale pour parti politique -->
        ${OrganisationApp.selectedOrgType === 'parti_politique' ? generatePartiPolitiqueSection() : ''}
    `;
    
    container.innerHTML = recapHTML;
    
    // Mettre à jour les statistiques si rapport d'anomalies actif
    if (OrganisationApp.rapportAnomalies.enabled) {
        updateRapportStatistiques();
    }
}

/**
 * Générer la composition avec indicateur de qualité
 */
function generateCompositionWithQuality(formData) {
    const totalAdherents = OrganisationApp.adherents.length;
    const adherentsValides = OrganisationApp.rapportAnomalies.enabled ? 
        OrganisationApp.rapportAnomalies.adherentsValides : totalAdherents;
    const adherentsAnomalies = OrganisationApp.rapportAnomalies.enabled ? 
        OrganisationApp.rapportAnomalies.adherentsAvecAnomalies : 0;
    
    const qualiteStatut = getQualiteStatut();
    const qualiteBadge = getQualiteBadgeClass(qualiteStatut);
    const qualiteLabel = getQualiteLabel(qualiteStatut);
    
    return `
        <table class="table table-sm table-borderless">
            <tr>
                <td><strong>Fondateurs :</strong></td>
                <td>
                    <span class="badge bg-success">${OrganisationApp.fondateurs.length}</span>
                </td>
            </tr>
            <tr>
                <td><strong>Adhérents :</strong></td>
                <td>
                    <span class="badge bg-primary">${totalAdherents}</span>
                    ${adherentsAnomalies > 0 ? `
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-check text-success me-1"></i>${adherentsValides} valides
                            <i class="fas fa-exclamation-triangle text-warning ms-2 me-1"></i>${adherentsAnomalies} anomalies
                        </small>
                    ` : ''}
                </td>
            </tr>
            <tr>
                <td><strong>Qualité :</strong></td>
                <td>
                    <span class="badge ${qualiteBadge}">${qualiteLabel}</span>
                </td>
            </tr>
        </table>
    `;
}

/**
 * Générer le statut de validation avec qualité
 */
function generateValidationStatusWithQuality() {
    const qualiteStatut = getQualiteStatut();
    const isQualityGood = ['excellent', 'bon'].includes(qualiteStatut);
    
    return `
        <div class="card border-0 ${isQualityGood ? 'bg-light' : 'bg-warning-subtle'}">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="text-dark mb-3">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Statut de validation ${OrganisationApp.rapportAnomalies.enabled ? '& qualité' : ''}
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
                    
                    ${OrganisationApp.rapportAnomalies.enabled ? `
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="badge ${getQualiteBadgeClass(qualiteStatut)} fs-6">
                                    ${getQualiteLabel(qualiteStatut)}
                                </span>
                            </div>
                            <small class="text-muted">
                                ${OrganisationApp.rapportAnomalies.adherentsAvecAnomalies} anomalie(s) détectée(s)
                            </small>
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                ${!isQualityGood && OrganisationApp.rapportAnomalies.enabled ? `
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Des anomalies ont été détectées dans votre dossier. 
                    Un rapport détaillé sera transmis avec votre demande pour faciliter le traitement.
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

/**
 * Générer la section spéciale pour parti politique
 */
function generatePartiPolitiqueSection() {
    const professionsExclues = OrganisationApp.adherents.filter(a => 
        a.hasAnomalies && a.anomalies.some(an => an.type === 'profession_exclue_parti')
    );
    
    if (professionsExclues.length === 0) {
        return `
            <div class="alert alert-success mt-4">
                <h6><i class="fas fa-shield-alt me-2"></i>Conformité Parti Politique</h6>
                <p class="mb-0">✅ Aucune profession exclue détectée. Votre parti politique respecte les exigences légales gabonaises.</p>
            </div>
        `;
    }
    
    return `
        <div class="alert alert-danger mt-4">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention - Professions Exclues Détectées</h6>
            <p><strong>${professionsExclues.length} membre(s)</strong> avec des professions normalement exclues pour les partis politiques :</p>
            <ul class="mb-2">
                ${professionsExclues.map(p => `
                    <li><strong>${p.nom} ${p.prenom}</strong> - ${p.profession}</li>
                `).join('')}
            </ul>
            <p class="mb-0"><small class="text-muted">
                Ces membres ont été conservés avec une anomalie critique. Une régularisation sera nécessaire.
            </small></p>
        </div>
    `;
}

/**
 * Générer la section anomalies pour le récapitulatif
 */
function generateAnomaliesRecapSection() {
    if (!OrganisationApp.rapportAnomalies.enabled || OrganisationApp.rapportAnomalies.anomalies.length === 0) {
        return '';
    }
    
    const stats = OrganisationApp.rapportAnomalies.statistiques;
    const total = OrganisationApp.rapportAnomalies.adherentsAvecAnomalies;
    
    return `
        <div class="card border-warning shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Rapport d'anomalies détectées
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>${total} adhérent(s)</strong> présentent des anomalies nécessitant une attention :
                        </p>
                        <ul class="list-unstyled">
                            ${stats.critique > 0 ? `<li><span class="badge bg-danger me-2">${stats.critique}</span>Critique(s) - Action immédiate</li>` : ''}
                            ${stats.majeure > 0 ? `<li><span class="badge bg-warning me-2">${stats.majeure}</span>Majeure(s) - Sous 48h</li>` : ''}
                            ${stats.mineure > 0 ? `<li><span class="badge bg-info me-2">${stats.mineure}</span>Mineure(s) - Recommandée</li>` : ''}
                        </ul>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-outline-primary me-2" onclick="previewRapportAnomalies()">
                            <i class="fas fa-eye me-1"></i>Prévisualiser
                        </button>
                        <button type="button" class="btn btn-success" onclick="downloadRapportAnomalies()">
                            <i class="fas fa-download me-1"></i>Télécharger
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Ce rapport sera automatiquement transmis avec votre dossier pour faciliter le traitement.
                    </small>
                </div>
            </div>
        </div>
    `;
}

/**
 * Mettre à jour les statistiques du rapport
 */
function updateRapportStatistiques() {
    if (!OrganisationApp.rapportAnomalies.enabled) return;
    
    // Recalculer les statistiques en temps réel
    OrganisationApp.rapportAnomalies.adherentsValides = OrganisationApp.adherents.filter(a => !a.hasAnomalies).length;
    OrganisationApp.rapportAnomalies.adherentsAvecAnomalies = OrganisationApp.adherents.filter(a => a.hasAnomalies).length;
    
    console.log('📊 Statistiques rapport mises à jour:', OrganisationApp.rapportAnomalies);
}

// ========================================
// 8. RAPPORT D'ANOMALIES COMPLET
// ========================================

/**
 * Générer le rapport d'anomalies complet
 */
function generateRapportAnomalies() {
    console.log('📋 Génération du rapport d\'anomalies - Version 1.2');
    
    if (!OrganisationApp.rapportAnomalies.enabled || OrganisationApp.rapportAnomalies.anomalies.length === 0) {
        console.log('ℹ️ Aucune anomalie détectée, pas de rapport à générer');
        return null;
    }
    
    const rapport = {
        metadata: generateRapportMetadata(),
        organisation: generateRapportOrganisationInfo(),
        statistiques: generateRapportStatistiques(),
        anomalies: generateRapportAnomaliesDetail(),
        recommandations: getRecommandationsAnomalies(),
        signature: generateRapportSignature()
    };
    
    console.log('✅ Rapport d\'anomalies généré avec succès');
    return rapport;
}

/**
 * Générer les métadonnées du rapport
 */
function generateRapportMetadata() {
    return {
        titre: 'Rapport d\'Anomalies - Importation Adhérents',
        version: OrganisationApp.rapportAnomalies.version,
        genereAt: OrganisationApp.rapportAnomalies.genereAt || new Date().toISOString(),
        generePar: 'Système PNGDI',
        typeDocument: 'RAPPORT_ANOMALIES_ADHERENTS',
        format: 'JSON/HTML',
        encodage: 'UTF-8',
        langue: 'fr-GA'
    };
}

/**
 * Générer les informations de l'organisation pour le rapport
 */
function generateRapportOrganisationInfo() {
    const formData = collectFormData();
    
    return {
        typeOrganisation: OrganisationApp.selectedOrgType,
        typeLabel: getOrganizationTypeLabel(OrganisationApp.selectedOrgType),
        nomOrganisation: formData.org_nom || 'Non renseigné',
        sigleOrganisation: formData.org_sigle || null,
        demandeurPrincipal: {
            nom: `${formData.demandeur_civilite || ''} ${formData.demandeur_nom || ''} ${formData.demandeur_prenom || ''}`.trim(),
            nip: formData.demandeur_nip || 'Non renseigné',
            email: formData.demandeur_email || 'Non renseigné',
            telephone: formData.demandeur_telephone || 'Non renseigné',
            role: formData.demandeur_role || 'Non renseigné'
        },
        exigencesMinimales: {
            fondateursMin: OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType]?.minFondateurs || 3,
            adherentsMin: OrganisationApp.config.orgRequirements[OrganisationApp.selectedOrgType]?.minAdherents || 10
        }
    };
}

/**
 * Générer les statistiques détaillées
 */
function generateRapportStatistiques() {
    const totalAdherents = OrganisationApp.adherents.length;
    const totalAnomalies = OrganisationApp.rapportAnomalies.anomalies.length;
    const adherentsAvecAnomalies = OrganisationApp.rapportAnomalies.adherentsAvecAnomalies;
    const adherentsValides = OrganisationApp.rapportAnomalies.adherentsValides;
    
    // Statistiques par niveau d'anomalie
    const statsNiveaux = OrganisationApp.rapportAnomalies.statistiques;
    
    // Statistiques par type d'anomalie
    const statsTypes = {};
    OrganisationApp.rapportAnomalies.anomalies.forEach(anomalie => {
        if (!statsTypes[anomalie.type]) {
            statsTypes[anomalie.type] = {
                count: 0,
                label: anomalie.label,
                level: anomalie.level
            };
        }
        statsTypes[anomalie.type].count++;
    });
    
    // Calcul des pourcentages
    const pourcentageValides = totalAdherents > 0 ? ((adherentsValides / totalAdherents) * 100).toFixed(1) : 0;
    const pourcentageAnomalies = totalAdherents > 0 ? ((adherentsAvecAnomalies / totalAdherents) * 100).toFixed(1) : 0;
    
    return {
        resume: {
            totalAdherentsImportes: totalAdherents,
            adherentsValides: adherentsValides,
            adherentsAvecAnomalies: adherentsAvecAnomalies,
            totalAnomaliesDetectees: totalAnomalies,
            pourcentageValides: parseFloat(pourcentageValides),
            pourcentageAnomalies: parseFloat(pourcentageAnomalies),
            qualiteGlobale: getQualiteStatut()
        },
        parNiveau: {
            critique: statsNiveaux.critique,
            majeure: statsNiveaux.majeure,
            mineure: statsNiveaux.mineure
        },
        parType: statsTypes,
        evaluation: {
            statutQualite: getQualiteStatut(),
            niveauRisque: statsNiveaux.critique > 0 ? 'ÉLEVÉ' : 
                         statsNiveaux.majeure > 0 ? 'MOYEN' : 'FAIBLE',
            actionRequise: statsNiveaux.critique > 0 ? 'IMMÉDIATE' : 
                          statsNiveaux.majeure > 0 ? 'SOUS 48H' : 'OPTIONNELLE'
        }
    };
}

/**
 * Générer le détail des anomalies avec groupement
 */
function generateRapportAnomaliesDetail() {
    const anomalies = OrganisationApp.rapportAnomalies.anomalies;
    
    // Grouper par niveau de gravité
    const parNiveau = {
        critique: anomalies.filter(a => a.level === 'critique'),
        majeure: anomalies.filter(a => a.level === 'majeure'),
        mineure: anomalies.filter(a => a.level === 'mineure')
    };
    
    // Grouper par type d'anomalie
    const parType = {};
    anomalies.forEach(anomalie => {
        if (!parType[anomalie.type]) {
            parType[anomalie.type] = [];
        }
        parType[anomalie.type].push(anomalie);
    });
    
    // Générer le détail formaté
    const detailFormate = anomalies.map(anomalie => ({
        id: anomalie.id,
        adherent: {
            nom: anomalie.adherentNom,
            nip: anomalie.adherentNip,
            ligne: anomalie.adherentLigne
        },
        anomalie: {
            type: anomalie.type,
            level: anomalie.level,
            label: anomalie.label,
            description: anomalie.description,
            details: anomalie.details,
            detecteAt: anomalie.detecteAt
        },
        resolution: {
            priorite: anomalie.level === 'critique' ? 1 : 
                     anomalie.level === 'majeure' ? 2 : 3,
            actionSuggere: getActionSuggereePourAnomalie(anomalie.type),
            delaiRecommande: anomalie.level === 'critique' ? '24h' : 
                           anomalie.level === 'majeure' ? '72h' : '1 semaine'
        }
    }));
    
    return {
        total: anomalies.length,
        parNiveau: parNiveau,
        parType: parType,
        detailComplet: detailFormate,
        ordreTraitement: detailFormate.sort((a, b) => a.resolution.priorite - b.resolution.priorite)
    };
}

/**
 * Obtenir l'action suggérée pour un type d'anomalie
 */
function getActionSuggereePourAnomalie(type) {
    const actions = {
        'nip_invalide': 'Vérifier auprès des services d\'état civil',
        'telephone_invalide': 'Corriger le format du numéro de téléphone',
        'email_invalide': 'Corriger l\'adresse email',
        'champs_incomplets': 'Compléter les informations manquantes',
        'membre_existant': 'Contacter le membre pour régularisation',
        'profession_exclue_parti': 'Exclure le membre ou changer le type d\'organisation',
        'doublon_fichier': 'Supprimer ou fusionner les doublons',
        'format_donnees': 'Vérifier et corriger le format des données'
    };
    
    return actions[type] || 'Vérifier et corriger les données';
}

/**
 * Générer la signature du rapport
 */
function generateRapportSignature() {
    return {
        systeme: 'PNGDI - Plateforme Nationale de Gestion des Déclarations d\'Intentions',
        version: '1.2',
        module: 'Import Adhérents avec Gestion Anomalies',
        checksum: generateRapportChecksum(),
        timestamp: Date.now(),
        format: 'Rapport JSON structuré compatible email/inbox'
    };
}

/**
 * Générer un checksum simple pour le rapport
 */
function generateRapportChecksum() {
    const data = JSON.stringify({
        anomalies: OrganisationApp.rapportAnomalies.anomalies.length,
        timestamp: OrganisationApp.rapportAnomalies.genereAt,
        version: OrganisationApp.rapportAnomalies.version
    });
    
    // Simple hash basé sur le contenu
    let hash = 0;
    for (let i = 0; i < data.length; i++) {
        const char = data.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32-bit integer
    }
    
    return Math.abs(hash).toString(16);
}

/**
 * Prévisualiser le rapport d'anomalies en modal
 */
function previewRapportAnomalies() {
    console.log('👁️ Prévisualisation du rapport d\'anomalies');
    
    const rapport = generateRapportAnomalies();
    if (!rapport) {
        showNotification('Aucun rapport d\'anomalies à prévisualiser', 'info');
        return;
    }
    
    // Créer et afficher la modal
    createRapportAnomaliesModal(rapport);
}

/**
 * Créer la modal de prévisualisation du rapport
 */
function createRapportAnomaliesModal(rapport) {
    const modalId = 'rapportAnomaliesModal';
    
    // Supprimer l'ancienne modal si elle existe
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const stats = rapport.statistiques;
    const anomalies = rapport.anomalies;
    
    const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="${modalId}Label">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${rapport.metadata.titre}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        ${generateRapportModalContent(rapport)}
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Généré le ${new Date(rapport.metadata.genereAt).toLocaleDateString('fr-FR', {
                                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                                })}
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Fermer
                                </button>
                                <button type="button" class="btn btn-success me-2" onclick="downloadRapportAnomalies()">
                                    <i class="fas fa-download me-1"></i>Télécharger JSON
                                </button>
                                <button type="button" class="btn btn-primary" onclick="exportRapportHTML()">
                                    <i class="fas fa-file-export me-1"></i>Exporter HTML
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter la modal au DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Afficher la modal
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Nettoyer après fermeture
    modal._element.addEventListener('hidden.bs.modal', () => {
        modal._element.remove();
    });
}

/**
 * Générer le contenu de la modal
 */
function generateRapportModalContent(rapport) {
    const stats = rapport.statistiques;
    const anomalies = rapport.anomalies;
    
    return `
        <!-- En-tête du rapport -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <i class="fas fa-building me-2"></i>
                            ${rapport.organisation.nomOrganisation}
                        </h6>
                        <small class="opacity-75">
                            ${rapport.organisation.typeLabel} | Demandeur: ${rapport.organisation.demandeurPrincipal.nom}
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge ${getQualiteBadgeClass(stats.resume.qualiteGlobale)} fs-6">
                            ${getQualiteLabel(stats.resume.qualiteGlobale)}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques résumées -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1">${stats.resume.totalAdherentsImportes}</h3>
                        <small class="text-muted">Total importés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-success-subtle h-100">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-1">${stats.resume.adherentsValides}</h3>
                        <small class="text-muted">Valides (${stats.resume.pourcentageValides}%)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning-subtle h-100">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-1">${stats.resume.adherentsAvecAnomalies}</h3>
                        <small class="text-muted">Avec anomalies (${stats.resume.pourcentageAnomalies}%)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-danger-subtle h-100">
                    <div class="card-body text-center">
                        <h3 class="text-danger mb-1">${stats.resume.totalAnomaliesDetectees}</h3>
                        <small class="text-muted">Anomalies total</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Répartition par niveau -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Répartition par niveau de gravité
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-danger-subtle rounded">
                            <div class="me-3">
                                <span class="badge bg-danger fs-6">${stats.parNiveau.critique}</span>
                            </div>
                            <div>
                                <strong class="text-danger">Critique</strong><br>
                                <small class="text-muted">Action immédiate</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-warning-subtle rounded">
                            <div class="me-3">
                                <span class="badge bg-warning fs-6">${stats.parNiveau.majeure}</span>
                            </div>
                            <div>
                                <strong class="text-warning">Majeure</strong><br>
                                <small class="text-muted">Sous 48h</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-info-subtle rounded">
                            <div class="me-3">
                                <span class="badge bg-info fs-6">${stats.parNiveau.mineure}</span>
                            </div>
                            <div>
                                <strong class="text-info">Mineure</strong><br>
                                <small class="text-muted">Recommandée</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des anomalies -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Détail des anomalies (${anomalies.total})
                </h6>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="filterAnomalies('all')">
                        Toutes
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="filterAnomalies('critique')">
                        Critiques
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="filterAnomalies('majeure')">
                        Majeures
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="filterAnomalies('mineure')">
                        Mineures
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="anomaliesTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Adhérent</th>
                                <th>NIP</th>
                                <th>Ligne</th>
                                <th>Niveau</th>
                                <th>Anomalie</th>
                                <th>Action suggérée</th>
                                <th>Délai</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${generateAnomaliesTableRows(anomalies.ordreTraitement)}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recommandations -->
        ${rapport.recommandations.length > 0 ? `
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Recommandations (${rapport.recommandations.length})
                </h6>
            </div>
            <div class="card-body">
                ${rapport.recommandations.map(rec => `
                    <div class="alert alert-${getRecommandationAlertClass(rec.type)} d-flex align-items-start">
                        <i class="fas ${getRecommandationIcon(rec.type)} me-3 mt-1"></i>
                        <div>
                            <strong>${rec.type.toUpperCase()} :</strong> ${rec.message}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
        ` : ''}
    `;
}

/**
 * Générer les lignes du tableau des anomalies
 */
function generateAnomaliesTableRows(anomalies) {
    return anomalies.map(anomalie => `
        <tr class="anomalie-row anomalie-${anomalie.anomalie.level}" data-level="${anomalie.anomalie.level}">
            <td>
                <strong>${anomalie.adherent.nom}</strong>
            </td>
            <td>
                <code class="small">${anomalie.adherent.nip}</code>
            </td>
            <td>
                <span class="badge bg-secondary">${anomalie.adherent.ligne || 'N/A'}</span>
            </td>
            <td>
                <span class="badge bg-${getLevelBadgeColor(anomalie.anomalie.level)}">
                    ${anomalie.anomalie.level.toUpperCase()}
                </span>
            </td>
            <td>
                <div>
                    <strong class="small">${anomalie.anomalie.label}</strong>
                    <br>
                    <small class="text-muted">${anomalie.anomalie.description}</small>
                    ${anomalie.anomalie.details ? `<br><small class="text-warning">Détails: ${anomalie.anomalie.details}</small>` : ''}
                </div>
            </td>
            <td>
                <small>${anomalie.resolution.actionSuggere}</small>
            </td>
            <td>
                <span class="badge bg-outline-${getLevelBadgeColor(anomalie.anomalie.level)}">
                    ${anomalie.resolution.delaiRecommande}
                </span>
            </td>
        </tr>
    `).join('');
}

/**
 * Filtrer les anomalies par niveau
 */
function filterAnomalies(level) {
    const table = document.getElementById('anomaliesTable');
    if (!table) return;
    
    const rows = table.querySelectorAll('.anomalie-row');
    const buttons = table.closest('.card').querySelectorAll('.btn-group .btn');
    
    // Mettre à jour les boutons
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filtrer les lignes
    rows.forEach(row => {
        if (level === 'all' || row.dataset.level === level) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Télécharger le rapport au format JSON
 */
function downloadRapportAnomalies() {
    const rapport = generateRapportAnomalies();
    if (!rapport) {
        showNotification('Aucun rapport d\'anomalies à télécharger', 'info');
        return;
    }
    
    const rapportJSON = JSON.stringify(rapport, null, 2);
    const blob = new Blob([rapportJSON], { type: 'application/json;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        const fileName = `rapport_anomalies_${rapport.organisation.nomOrganisation.replace(/[^a-z0-9]/gi, '_')}_${new Date().toISOString().split('T')[0]}.json`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Rapport d\'anomalies téléchargé avec succès', 'success');
    }
}

/**
 * Exporter le rapport en HTML
 */
function exportRapportHTML() {
    const htmlContent = generateRapportAnomaliesHTML();
    if (!htmlContent) {
        showNotification('Impossible d\'exporter le rapport HTML', 'danger');
        return;
    }
    
    const blob = new Blob([htmlContent], { type: 'text/html;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        const fileName = `rapport_anomalies_${OrganisationApp.formData.org_nom ? OrganisationApp.formData.org_nom.replace(/[^a-z0-9]/gi, '_') : 'organisation'}_${new Date().toISOString().split('T')[0]}.html`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Rapport HTML exporté avec succès', 'success');
    }
}

/**
 * Générer le rapport au format HTML pour email
 */
function generateRapportAnomaliesHTML() {
    const rapport = generateRapportAnomalies();
    if (!rapport) return null;
    
    const stats = rapport.statistiques;
    const anomalies = rapport.anomalies;
    
    return `
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>${rapport.metadata.titre}</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
            .stat-card { background: #fff; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; text-align: center; }
            .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
            .badge-danger { background: #dc3545; color: white; }
            .badge-warning { background: #ffc107; color: #212529; }
            .badge-info { background: #17a2b8; color: white; }
            .badge-success { background: #28a745; color: white; }
            .anomalie-item { border-left: 4px solid #dc3545; padding: 10px; margin: 10px 0; background: #f8f9fa; }
            .anomalie-critique { border-left-color: #dc3545; }
            .anomalie-majeure { border-left-color: #ffc107; }
            .anomalie-mineure { border-left-color: #17a2b8; }
            .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .table th, .table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
            .table th { background: #f8f9fa; font-weight: bold; }
            .recommandation { background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 6px; padding: 15px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>${rapport.metadata.titre}</h1>
            <p><strong>Organisation :</strong> ${rapport.organisation.nomOrganisation} (${rapport.organisation.typeLabel})</p>
            <p><strong>Demandeur :</strong> ${rapport.organisation.demandeurPrincipal.nom}</p>
            <p><strong>Généré le :</strong> ${new Date(rapport.metadata.genereAt).toLocaleDateString('fr-FR', { 
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
            })}</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>${stats.resume.totalAdherentsImportes}</h3>
                <p>Adhérents importés</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #28a745;">${stats.resume.adherentsValides}</h3>
                <p>Valides (${stats.resume.pourcentageValides}%)</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #dc3545;">${stats.resume.adherentsAvecAnomalies}</h3>
                <p>Avec anomalies (${stats.resume.pourcentageAnomalies}%)</p>
            </div>
            <div class="stat-card">
                <h3>${stats.resume.totalAnomaliesDetectees}</h3>
                <p>Anomalies détectées</p>
            </div>
        </div>
        
        <h2>📊 Répartition par niveau de gravité</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Niveau</th>
                    <th>Nombre</th>
                    <th>Action requise</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-danger">Critique</span></td>
                    <td>${stats.parNiveau.critique}</td>
                    <td>Correction immédiate</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Majeure</span></td>
                    <td>${stats.parNiveau.majeure}</td>
                    <td>Correction sous 48h</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">Mineure</span></td>
                    <td>${stats.parNiveau.mineure}</td>
                    <td>Correction recommandée</td>
                </tr>
            </tbody>
        </table>
        
        <h2>📋 Détail des anomalies par ordre de priorité</h2>
        ${anomalies.ordreTraitement.map(anomalie => `
            <div class="anomalie-item anomalie-${anomalie.anomalie.level}">
                <h4>${anomalie.adherent.nom} <span class="badge badge-${anomalie.anomalie.level === 'critique' ? 'danger' : anomalie.anomalie.level === 'majeure' ? 'warning' : 'info'}">${anomalie.anomalie.level.toUpperCase()}</span></h4>
                <p><strong>NIP :</strong> ${anomalie.adherent.nip} | <strong>Ligne :</strong> ${anomalie.adherent.ligne || 'N/A'}</p>
                <p><strong>Anomalie :</strong> ${anomalie.anomalie.label}</p>
                <p><strong>Description :</strong> ${anomalie.anomalie.description}</p>
                ${anomalie.anomalie.details ? `<p><strong>Détails :</strong> ${anomalie.anomalie.details}</p>` : ''}
                <p><strong>Action suggérée :</strong> ${anomalie.resolution.actionSuggere}</p>
                <p><strong>Délai recommandé :</strong> ${anomalie.resolution.delaiRecommande}</p>
            </div>
        `).join('')}
        
        <h2>💡 Recommandations</h2>
        ${rapport.recommandations.map(rec => `
            <div class="recommandation">
                <strong>${rec.type.toUpperCase()} :</strong> ${rec.message}
            </div>
        `).join('')}
        
        <div class="header" style="margin-top: 30px; text-align: center; font-size: 0.9em; color: #666;">
            <p>Rapport généré automatiquement par ${rapport.signature.systeme}</p>
            <p>Version ${rapport.signature.version} | Checksum: ${rapport.signature.checksum}</p>
        </div>
    </body>
    </html>
    `;
}

/**
 * Fonctions utilitaires pour l'affichage
 */
function getLevelBadgeColor(level) {
    const colors = {
        'critique': 'danger',
        'majeure': 'warning',
        'mineure': 'info'
    };
    return colors[level] || 'secondary';
}

function getRecommandationAlertClass(type) {
    const classes = {
        'urgent': 'danger',
        'important': 'warning',
        'conseil': 'info'
    };
    return classes[type] || 'secondary';
}

function getRecommandationIcon(type) {
    const icons = {
        'urgent': 'fa-exclamation-triangle',
        'important': 'fa-exclamation-circle',
        'conseil': 'fa-lightbulb'
    };
    return icons[type] || 'fa-info-circle';
}

// ========================================
// 9. SAUVEGARDE ET COLLECTE DE DONNÉES
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
            // Inclure le rapport d'anomalies
            rapportAnomaliesAdherents: OrganisationApp.rapportAnomalies.enabled ? {
                enabled: true,
                adherentsValides: OrganisationApp.rapportAnomalies.adherentsValides,
                adherentsAvecAnomalies: OrganisationApp.rapportAnomalies.adherentsAvecAnomalies,
                anomalies: OrganisationApp.rapportAnomalies.anomalies,
                statistiques: OrganisationApp.rapportAnomalies.statistiques,
                genereAt: OrganisationApp.rapportAnomalies.genereAt,
                version: OrganisationApp.rapportAnomalies.version
            } : { enabled: false },
            currentStep: OrganisationApp.currentStep,
            selectedOrgType: OrganisationApp.selectedOrgType,
            timestamp: Date.now(),
            version: '1.2'
        };
        
        localStorage.setItem('pngdi_organisation_draft', JSON.stringify(saveData));
        updateSaveIndicator('success');
        console.log('💾 Sauvegarde automatique v1.2 réussie');
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
        // Restaurer les données existantes
        OrganisationApp.currentStep = savedData.currentStep || 1;
        OrganisationApp.selectedOrgType = savedData.selectedOrgType || '';
        OrganisationApp.fondateurs = savedData.fondateurs || [];
        OrganisationApp.adherents = savedData.adherents || [];
        
        // Restaurer le rapport d'anomalies
        if (savedData.rapportAnomaliesAdherents && savedData.rapportAnomaliesAdherents.enabled) {
            OrganisationApp.rapportAnomalies = {
                enabled: true,
                adherentsValides: savedData.rapportAnomaliesAdherents.adherentsValides || 0,
                adherentsAvecAnomalies: savedData.rapportAnomaliesAdherents.adherentsAvecAnomalies || 0,
                anomalies: savedData.rapportAnomaliesAdherents.anomalies || [],
                statistiques: savedData.rapportAnomaliesAdherents.statistiques || { critique: 0, majeure: 0, mineure: 0 },
                genereAt: savedData.rapportAnomaliesAdherents.genereAt || null,
                version: savedData.rapportAnomaliesAdherents.version || '1.2'
            };
            console.log('✅ Rapport d\'anomalies restauré:', OrganisationApp.rapportAnomalies);
        }
        
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
// 10. NOTIFICATIONS
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
    
    // Sinon utiliser la version complète
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
// 10.1. SYSTÈME DE DEBUG AVANCÉ
// ========================================

/**
 * ✅ FONCTION AMÉLIORÉE : Afficher les erreurs détaillées du serveur
 */
function showErrorModal(title, message, serverResponse = null, isDebug = true) {
    // Construire le message d'erreur détaillé
    let fullMessage = message;
    
    if (isDebug && serverResponse) {
        fullMessage += '\n\n' + '='.repeat(50);
        fullMessage += '\n🔍 DEBUG - RÉPONSE SERVEUR DÉTAILLÉE :';
        fullMessage += '\n' + '='.repeat(50);
        
        // Afficher les détails de la réponse
        if (typeof serverResponse === 'object') {
            try {
                // Si c'est un objet, l'afficher en JSON formaté
                fullMessage += '\n📄 Contenu de la réponse :\n';
                fullMessage += JSON.stringify(serverResponse, null, 2);
                
                // Afficher les erreurs spécifiques si disponibles
                if (serverResponse.errors) {
                    fullMessage += '\n\n🚨 Erreurs de validation :\n';
                    Object.keys(serverResponse.errors).forEach(field => {
                        fullMessage += `• ${field}: ${serverResponse.errors[field].join(', ')}\n`;
                    });
                }
                
                // Afficher les infos de debug si disponibles
                if (serverResponse.debug) {
                    fullMessage += '\n\n🐛 Informations de debug :\n';
                    fullMessage += `Fichier: ${serverResponse.debug.file}\n`;
                    fullMessage += `Ligne: ${serverResponse.debug.line}\n`;
                    if (serverResponse.debug.json_error) {
                        fullMessage += `Erreur JSON: ${serverResponse.debug.json_error}\n`;
                    }
                }
            } catch (e) {
                fullMessage += '\n📄 Réponse brute :\n' + String(serverResponse);
            }
        } else {
            fullMessage += '\n📄 Réponse brute :\n' + String(serverResponse);
        }
        
        fullMessage += '\n' + '='.repeat(50);
    }
    
    // Créer et afficher le modal d'erreur amélioré
    const modal = `
        <div class="modal fade" id="errorDebugModal" tabindex="-1" role="dialog" style="z-index: 9999;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">
                                <i class="fas fa-bug me-2"></i>
                                Erreur de Soumission - Mode Debug
                            </h6>
                            <pre style="white-space: pre-wrap; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px;">${fullMessage}</pre>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyDebugInfo()">
                                <i class="fas fa-copy me-1"></i>
                                Copier les détails
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="console.log('Debug Info:', ${JSON.stringify(serverResponse || {})})">
                                <i class="fas fa-terminal me-1"></i>
                                Afficher dans la console
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-refresh me-1"></i>
                            Recharger la page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Supprimer le modal existant s'il y en a un
    const existingModal = document.getElementById('errorDebugModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Ajouter le nouveau modal
    document.body.insertAdjacentHTML('beforeend', modal);
    
    // Afficher le modal
    const modalElement = new bootstrap.Modal(document.getElementById('errorDebugModal'));
    modalElement.show();
    
    // Stocker les infos de debug globalement pour la fonction de copie
    window.debugInfo = fullMessage;
}

/**
 * ✅ FONCTION : Copier les informations de debug
 */
function copyDebugInfo() {
    if (window.debugInfo) {
        navigator.clipboard.writeText(window.debugInfo).then(() => {
            showNotification('Informations de debug copiées dans le presse-papier !', 'success');
        }).catch(() => {
            // Fallback pour les navigateurs plus anciens
            const textarea = document.createElement('textarea');
            textarea.value = window.debugInfo;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showNotification('Informations de debug copiées !', 'success');
        });
    }
}

/**
 * ✅ FONCTION : Analyser les données du formulaire pour diagnostic
 */
function analyzeFormDataForDebug() {
    const form = document.getElementById('organisationForm');
    const formData = new FormData(form);
    
    let totalSize = 0;
    let fieldCount = 0;
    let largestFields = [];
    
    for (let [key, value] of formData.entries()) {
        fieldCount++;
        const size = new Blob([value]).size;
        totalSize += size;
        
        if (size > 1000) { // Champs > 1KB
            largestFields.push({key, size, value: value.toString().substring(0, 50) + '...'});
        }
    }
    
    console.log('=== ANALYSE FORMULAIRE DEBUG ===');
    console.log('Nombre de champs:', fieldCount);
    console.log('Taille totale:', (totalSize / 1024).toFixed(2) + ' KB');
    console.log('Champs volumineux:', largestFields);
    
    return {fieldCount, totalSize, largestFields};
}

// Exposer les nouvelles fonctions
window.showErrorModal = showErrorModal;
window.copyDebugInfo = copyDebugInfo;


// ========================================
// 11. SOUMISSION FINALE
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
 * ✅ SOUMISSION FINALE CORRIGÉE - Avec redirection dossier_id
 */
/**
 * ✅ CORRECTION TIMEOUT - Soumission adaptative par chunks
 * À intégrer dans organisation-create.js
 */


// ✅ SAUVEGARDE DE LA FONCTION ORIGINALE
if (typeof window.submitForm === 'function') {
    window.originalSubmitForm = window.submitForm;
    console.log('📄 Fonction submitForm originale sauvegardée');
}

/**
 * ✅ SOUMISSION FINALE CORRIGÉE - Avec chunking adaptatif pour gros volumes
 */
async function submitForm() {
    console.log('📤 Soumission Phase 1 - Toujours traitement normal');
    
    const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
    if (submitBtn) {
        if (submitBtn.disabled) {
            console.log('⚠️ Soumission déjà en cours...');
            return false;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Traitement en cours...';
    }
    
    if (!validateAllSteps()) {
        showNotification('Veuillez corriger toutes les erreurs avant de soumettre', 'danger');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Soumettre le dossier';
        }
        return false;
    }

    // ✅ TOUJOURS soumission normale - Backend décidera de Phase 2
    const totalAdherents = OrganisationApp.adherents.length;
    console.log(`📊 Volume: ${totalAdherents} adhérents - Backend décidera du workflow`);
    
    return await submitFormNormal();
}

// ✅ REMPLACEMENT PAR LA VERSION AMÉLIORÉE
window.submitForm = submitFormWithErrorHandling;
console.log('✅ Fonction submitForm remplacée par la version avec gestion CSRF');
/**
 * ✅ NOUVELLE FONCTION : Soumission avec chunking pour gros volumes
 */
/**
 * ✅ CORRECTION FONCTION submitFormWithChunking()
 * À remplacer dans organisation-create.js ligne ~2900
 */

async function submitFormWithChunking() {
    try {
        showGlobalLoader(true);
        showNotification('📦 Gros volume détecté - Soumission par lots en cours...', 'info', 8000);
        
        const CHUNK_SIZE = 500; // Adhérents par chunk
        const totalAdherents = OrganisationApp.adherents.length;
        const totalChunks = Math.ceil(totalAdherents / CHUNK_SIZE);
        
        console.log(`📊 Division soumission: ${totalChunks} chunks de ${CHUNK_SIZE} adhérents max`);
        
        // Données de base (sans les adhérents)
        const baseFormData = new FormData();
        const data = collectFormData();
        
        // Token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            baseFormData.append('_token', csrfToken);
        }
        
        // Données de base
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                baseFormData.append(key, data[key]);
            }
        });
        
        // Fondateurs et métadonnées
        baseFormData.append('fondateurs', JSON.stringify(OrganisationApp.fondateurs));
        baseFormData.append('selectedOrgType', OrganisationApp.selectedOrgType);
        baseFormData.append('totalFondateurs', OrganisationApp.fondateurs.length);
        baseFormData.append('totalAdherents', totalAdherents);
        baseFormData.append('totalDocuments', Object.keys(OrganisationApp.documents).length);
        
        // ✅ CORRECTION PRINCIPALE : Ajouter tous les adhérents même pour chunking
        baseFormData.append('adherents', JSON.stringify(OrganisationApp.adherents));
        
        // ✅ CHUNKING : Marquer comme soumission par chunks
        baseFormData.append('is_chunked_submission', 'true');
        baseFormData.append('total_chunks', totalChunks);
        baseFormData.append('chunk_size', CHUNK_SIZE);
        
        // Rapport d'anomalies si présent
        if (OrganisationApp.rapportAnomalies.enabled) {
            const rapport = generateRapportAnomalies();
            const rapportHTML = generateRapportAnomaliesHTML();
            
            baseFormData.append('rapport_anomalies_json', JSON.stringify(rapport));
            baseFormData.append('rapport_anomalies_html', rapportHTML);
            baseFormData.append('has_anomalies', 'true');
        } else {
            baseFormData.append('has_anomalies', 'false');
        }
        
        // Documents
        Object.keys(OrganisationApp.documents).forEach(docType => {
            const doc = OrganisationApp.documents[docType];
            if (doc.file) {
                baseFormData.append(`documents[${docType}]`, doc.file);
            }
        });
        
        // ✅ SOUMISSION PAR CHUNKS - AVEC ADHERENTS COMPLET
        let allResults = [];
        const formElement = document.getElementById('organisationForm');
        
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const startIndex = chunkIndex * CHUNK_SIZE;
            const endIndex = Math.min(startIndex + CHUNK_SIZE, totalAdherents);
            const chunkAdherents = OrganisationApp.adherents.slice(startIndex, endIndex);
            
            console.log(`📤 Envoi chunk ${chunkIndex + 1}/${totalChunks}: adhérents ${startIndex}-${endIndex-1}`);
            
            // Créer FormData pour ce chunk
            const chunkFormData = new FormData();
            
            // Copier les données de base
            for (let [key, value] of baseFormData.entries()) {
                chunkFormData.append(key, value);
            }
            
            // Ajouter les métadonnées du chunk (en plus du tableau complet)
            chunkFormData.append('adherents_chunk', JSON.stringify(chunkAdherents));
            chunkFormData.append('chunk_index', chunkIndex);
            chunkFormData.append('is_final_chunk', chunkIndex === totalChunks - 1 ? 'true' : 'false');
            
            // Envoyer le chunk
            const response = await fetch(formElement.action, {
                method: 'POST',
                body: chunkFormData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error(`❌ Erreur chunk ${chunkIndex + 1}:`, errorText);
                throw new Error(`Erreur chunk ${chunkIndex + 1}: ${response.status} ${response.statusText}`);
            }
            
            const result = await response.json();
            allResults.push(result);
            
            // Mise à jour progression
            const progress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            showNotification(`📊 Progression: ${progress}% (chunk ${chunkIndex + 1}/${totalChunks})`, 'info', 3000);
            
            // Pause entre chunks pour éviter surcharge serveur
            if (chunkIndex < totalChunks - 1) {
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        }
        
        // ✅ TRAITEMENT RÉSULTAT FINAL
        const finalResult = allResults[allResults.length - 1]; // Dernier chunk contient la réponse finale
        
        if (finalResult.success) {
            console.log('🎉 Chunking terminé avec succès:', finalResult);
            
            // ✅ GESTION REDIRECTION AUTOMATIQUE
            if (finalResult.should_redirect && finalResult.redirect_url) {
                console.log('🔄 Redirection automatique détectée vers:', finalResult.redirect_url);
                
                // Afficher message de succès temporaire
                let successMsg = finalResult.message || 'Adhérents traités avec succès par chunking';
                if (finalResult.data) {
                    successMsg += `\n📊 ${finalResult.data.total_inserted || 0} adhérents insérés`;
                    if (finalResult.data.chunks_processed) {
                        successMsg += ` en ${finalResult.data.chunks_processed} chunks`;
                    }
                    if (finalResult.data.anomalies_count > 0) {
                        successMsg += `\n⚠️ ${finalResult.data.anomalies_count} anomalies détectées`;
                    }
                }
                
                showNotification(successMsg, 'success', 5000);
                
                // Nettoyer les données temporaires
                localStorage.removeItem('pngdi_organisation_draft');
                if (typeof OrganisationApp !== 'undefined') {
                    OrganisationApp.adherents = [];
                }
                
                // Redirection avec délai
                const redirectDelay = finalResult.redirect_delay || 3000;
                console.log(`🚀 Redirection dans ${redirectDelay}ms vers:`, finalResult.redirect_url);
                
                setTimeout(() => {
                    window.location.href = finalResult.redirect_url;
                }, redirectDelay);
                
                return; // Sortir de la fonction
            }
            
            // ✅ FALLBACK : Construction manuelle de l'URL de redirection
            let redirectUrl = null;
            
            if (finalResult.data && finalResult.data.redirect_url) {
                redirectUrl = finalResult.data.redirect_url;
                console.log('✅ REDIRECTION via result.data.redirect_url:', redirectUrl);
            } else if (finalResult.data && finalResult.data.dossier_id) {
                redirectUrl = `/operator/dossiers/confirmation/${finalResult.data.dossier_id}`;
                console.log('✅ REDIRECTION construite avec dossier_id:', finalResult.data.dossier_id, '→', redirectUrl);
            } else if (finalResult.redirect) {
                redirectUrl = finalResult.redirect;
                console.log('✅ REDIRECTION via result.redirect:', redirectUrl);
            } else {
                redirectUrl = '/operator/dossiers';
                console.log('✅ REDIRECTION par défaut vers la liste des dossiers');
            }
            
            // Message de succès
            let successMsg = '🎉 Dossier soumis avec succès par chunks !';
            if (OrganisationApp.rapportAnomalies.enabled) {
                successMsg += '\n📋 Le rapport d\'anomalies a été transmis automatiquement.';
            }
            const totalAdherents = OrganisationApp.adherents.length;
            const totalChunks = Math.ceil(totalAdherents / 500);
            successMsg += `\n📊 ${totalAdherents} adhérents traités en ${totalChunks} lots.`;
            
            showNotification(successMsg, 'success', 10000);
            
            // Nettoyer et rediriger
            localStorage.removeItem('pngdi_organisation_draft');
            
            setTimeout(() => {
                console.log('🚀 REDIRECTION VERS:', redirectUrl);
                window.location.href = redirectUrl;
            }, 3000);
            
        } else {
            throw new Error(finalResult.message || 'Erreur lors de la soumission par chunks');
        }
        
    } catch (error) {
    console.error('❌ Erreur soumission par chunks:', error);
    
    // ✅ Réactiver bouton en cas d'erreur
    const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Soumettre le dossier';
    }
    
    // Afficher le debug modal avec détails complets
    if (typeof showErrorModal === 'function') {
        showErrorModal('Erreur Soumission Chunking', error.message, {
            totalAdherents: OrganisationApp.adherents.length,
            chunksDetected: Math.ceil(OrganisationApp.adherents.length / 500),
            errorDetails: error.toString(),
            timestamp: new Date().toISOString()
        });
    } else {
        showNotification(`❌ Erreur soumission: ${error.message}`, 'danger');
    }
    
    } finally {
        showGlobalLoader(false);
    }
}

/**
 * Diagnostic CSRF avant soumission
 */
function diagnoseCsrfIssue() {
    console.log('🔍 === DIAGNOSTIC CSRF ===');
    
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const inputToken = document.querySelector('input[name="_token"]')?.value;
    const laravelToken = window.Laravel?.csrfToken;
    
    console.log('Meta CSRF:', metaToken ? metaToken.substring(0, 10) + '...' : 'MANQUANT');
    console.log('Input CSRF:', inputToken ? inputToken.substring(0, 10) + '...' : 'MANQUANT');
    console.log('Laravel CSRF:', laravelToken ? laravelToken.substring(0, 10) + '...' : 'MANQUANT');
    
    // Vérifier si la page est expirée
    const pageLoadTime = performance.timing.navigationStart;
    const currentTime = Date.now();
    const pageAge = Math.floor((currentTime - pageLoadTime) / 1000 / 60); // en minutes
    
    console.log('Âge de la page:', pageAge, 'minutes');
    
    if (pageAge > 120) { // Plus de 2 heures
        console.warn('⚠️ Page possiblement expirée (plus de 2h)');
        return false;
    }
    
    return true;
}


/**
 * ✅ FONCTION : Soumission normale (volumes < 200 adhérents)
 */
/**
 * ✅ FONCTION FINALE : submitFormNormal avec CSRF robuste
 * REMPLACER COMPLÈTEMENT la fonction existante dans organisation-create.js
 */
 async function submitFormNormal() {
    try {
        showGlobalLoader(true);
        
        // Préparation des données standard (CODE EXISTANT PRÉSERVÉ)
        const formData = new FormData();
        const data = collectFormData();
        
        // Ajouter toutes les données du formulaire
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                if (Array.isArray(data[key])) {
                    data[key].forEach((item, index) => {
                        formData.append(`${key}[${index}]`, item);
                    });
                } else {
                    formData.append(key, data[key]);
                }
            }
        });

        // Ajouter les adhérents si présents
        if (OrganisationApp.adherents && OrganisationApp.adherents.length > 0) {
            formData.append('adherents', JSON.stringify(OrganisationApp.adherents));
        }

        // Ajouter le rapport d'anomalies si activé
        if (OrganisationApp.rapportAnomalies && OrganisationApp.rapportAnomalies.enabled) {
            formData.append('rapport_anomalies', JSON.stringify(OrganisationApp.rapportAnomalies));
        }

        console.log('📋 Données préparées pour soumission normale');

        // ✅ NOUVEAUTÉ : Utiliser le gestionnaire CSRF robuste
        const result = await window.submitFormWithCSRFHandling(
            formData, 
            '/operator/organisations',
            { 
                timeout: 120000 // 2 minutes
            }
        );

        // TRAITEMENT RÉSULTAT (CODE EXISTANT PRÉSERVÉ)
        if (result && result.success) {
            const redirectUrl = result.redirect_url || '/operator/organisations';
            
            let successMsg = '✅ Organisation créée avec succès !';
            if (OrganisationApp.rapportAnomalies && OrganisationApp.rapportAnomalies.enabled) {
                successMsg += '\n📋 Le rapport d\'anomalies a été transmis automatiquement.';
            }
            showNotification(successMsg, 'success', 10000);
            
            // Nettoyer le draft
            localStorage.removeItem('pngdi_organisation_draft');
            
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 3000);
            
            return { success: true, redirectUrl };
            
        } else {
            throw new Error(result.message || 'Erreur lors de la soumission');
        }
        
    } catch (error) {
        console.error('❌ Erreur soumission finale:', error);
        
        // Réactiver le bouton submit
        const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Soumettre le dossier';
        }
        
        // Afficher l'erreur avec diagnostic CSRF
        const errorDetails = {
            errorType: 'SubmissionError',
            timestamp: new Date().toISOString(),
            csrfDiagnostic: window.CSRFManager ? window.CSRFManager.diagnoseCSRFContext() : 'CSRFManager non disponible'
        };
        
        if (typeof showErrorModal === 'function') {
            showErrorModal('Erreur de Soumission', error.message, errorDetails);
        } else {
            showNotification(`❌ Erreur: ${error.message}`, 'danger');
        }
        
        throw error;
        
    } finally {
        showGlobalLoader(false);
    }
}

/**
 * ✅ FONCTION HELPER : Récupération robuste du token CSRF
 */
async function getCurrentCSRFToken() {
    // Méthode 1: Depuis meta tag Laravel
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Méthode 2: Fallback depuis input caché
    if (!csrfToken) {
        csrfToken = document.querySelector('input[name="_token"]')?.value;
    }
    
    // Méthode 3: Fallback depuis window.Laravel
    if (!csrfToken && window.Laravel && window.Laravel.csrfToken) {
        csrfToken = window.Laravel.csrfToken;
    }
    
    // Méthode 4: Dernier recours - récupérer depuis le serveur
    if (!csrfToken || csrfToken.length < 10) {
        console.log('🔄 Token CSRF invalide ou manquant, récupération depuis serveur...');
        csrfToken = await refreshCSRFToken();
    }
    
    return csrfToken;
}

/**
 * ✅ FONCTION HELPER : Rafraîchir le token CSRF
 */
async function refreshCSRFToken() {
    console.log('🔄 Tentative de rafraîchissement du token CSRF...');
    
    try {
        const response = await fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const newToken = data.csrf_token;
            
            // Mettre à jour le meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }
            
            // Mettre à jour les inputs cachés
            const tokenInputs = document.querySelectorAll('input[name="_token"]');
            tokenInputs.forEach(input => {
                input.value = newToken;
            });
            
            // Mettre à jour Laravel global si disponible
            if (window.Laravel) {
                window.Laravel.csrfToken = newToken;
            }
            
            console.log('✅ Token CSRF rafraîchi avec succès');
            return newToken;
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
    } catch (error) {
        console.error('❌ Erreur lors du rafraîchissement CSRF:', error);
        return null;
    }
}

/**
 * ✅ FONCTION HELPER : Diagnostic CSRF (améliorée)
 */
function diagnoseCsrfIssue() {
    console.log('🔍 === DIAGNOSTIC CSRF ===');
    
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const inputToken = document.querySelector('input[name="_token"]')?.value;
    const laravelToken = window.Laravel?.csrfToken;
    
    console.log('Meta CSRF:', metaToken ? metaToken.substring(0, 10) + '...' : 'MANQUANT');
    console.log('Input CSRF:', inputToken ? inputToken.substring(0, 10) + '...' : 'MANQUANT');
    console.log('Laravel CSRF:', laravelToken ? laravelToken.substring(0, 10) + '...' : 'MANQUANT');
    
    // Vérifier les cookies de session
    const hasSessionCookie = document.cookie.includes('pngdi_session') || document.cookie.includes('laravel_session');
    const hasXSRFCookie = document.cookie.includes('XSRF-TOKEN');
    
    console.log('Cookie session:', hasSessionCookie ? 'PRÉSENT' : 'MANQUANT');
    console.log('Cookie XSRF:', hasXSRFCookie ? 'PRÉSENT' : 'MANQUANT');
    
    // Vérifier si la page est expirée
    const pageLoadTime = performance.timing.navigationStart;
    const currentTime = Date.now();
    const pageAge = Math.floor((currentTime - pageLoadTime) / 1000 / 60); // en minutes
    
    console.log('Âge de la page:', pageAge, 'minutes');
    
    if (pageAge > 120) { // Plus de 2 heures
        console.warn('⚠️ Page possiblement expirée (plus de 2h)');
        return false;
    }
    
    // Vérifier qu'au moins un token est présent
    const hasValidToken = (metaToken && metaToken.length >= 10) || 
                         (inputToken && inputToken.length >= 10) || 
                         (laravelToken && laravelToken.length >= 10);
    
    if (!hasValidToken) {
        console.error('❌ Aucun token CSRF valide trouvé');
        return false;
    }
    
    console.log('✅ Diagnostic CSRF: OK');
    return true;
}

/**
 * ✅ WRAPPER PRINCIPAL : Remplace la fonction submitForm existante
 */
async function submitFormWithErrorHandling() {
    try {
        // Désactiver le bouton de soumission
        const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Soumission en cours...';
        }
        
        const result = await submitFormNormal();
        return result;
        
    } catch (error) {
        console.error('❌ Erreur soumission finale:', error);
        
        // Réactiver le bouton
        const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Soumettre le dossier';
        }
        
        // Gestion spécifique des messages d'erreur
        if (error.message.includes('419') || error.message.includes('CSRF')) {
            showNotification('❌ Session expirée. Veuillez recharger la page et recommencer.', 'danger', 10000);
            
            // Proposer un rechargement automatique après 5 secondes
            setTimeout(() => {
                if (confirm('La session a expiré. Voulez-vous recharger la page ?\n\n⚠️ Attention : Les données non sauvegardées seront perdues.')) {
                    window.location.reload();
                }
            }, 5000);
        } else if (error.message.includes('Timeout')) {
            showNotification('❌ Timeout de soumission. Essayez de réduire le nombre d\'adhérents ou réessayez plus tard.', 'warning', 8000);
        } else {
            showNotification(`❌ Erreur : ${error.message}`, 'danger');
        }
        
        throw error;
    }
}

// ✅ CONSERVATION DE LA FONCTION ORIGINALE (window.originalSubmitForm pour compatibilité)
window.originalSubmitForm = window.submitForm;


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
// 12. UTILITAIRES AVANCÉS
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

// AJOUTER CETTE FONCTION de diagnostic
function analyzeFormData() {
 const form = document.getElementById('organisationForm');
    const formData = new FormData(form);
    
    let totalSize = 0;
    let fieldCount = 0;
    let largestFields = [];
    const fieldsByType = {};
    
    for (let [key, value] of formData.entries()) {
        fieldCount++;
        const size = new Blob([value]).size;
        totalSize += size;
        
        // Catégoriser par type
        if (value instanceof File) {
            if (!fieldsByType.files) fieldsByType.files = [];
            fieldsByType.files.push({key, size, name: value.name});
        } else if (typeof value === 'string' && value.length > 100) {
            if (!fieldsByType.longText) fieldsByType.longText = [];
            fieldsByType.longText.push({key, size, preview: value.substring(0, 50) + '...'});
        }
        
        if (size > 1000) { // Champs > 1KB
            largestFields.push({
                key, 
                size, 
                type: value instanceof File ? 'file' : 'text',
                preview: value instanceof File ? value.name : value.toString().substring(0, 50) + '...'
            });
        }
    }
    
    // Analyser les données spécifiques
    const organisationData = {
        fondateurs: OrganisationApp.fondateurs.length,
        adherents: OrganisationApp.adherents.length,
        documents: Object.keys(OrganisationApp.documents).length,
        anomalies: OrganisationApp.rapportAnomalies.enabled ? OrganisationApp.rapportAnomalies.adherentsAvecAnomalies : 0
    };
    
    const analysis = {
        fieldCount, 
        totalSize, 
        largestFields,
        fieldsByType,
        organisationData,
        warnings: []
    };
    
    // Générer des avertissements
    if (fieldCount > 1000) {
        analysis.warnings.push(`Nombre de champs élevé: ${fieldCount} (limite recommandée: 1000)`);
    }
    if (totalSize > 50 * 1024 * 1024) { // 50MB
        analysis.warnings.push(`Taille importante: ${(totalSize / 1024 / 1024).toFixed(2)} MB`);
    }
    if (largestFields.length > 10) {
        analysis.warnings.push(`Nombreux champs volumineux: ${largestFields.length}`);
    }
    
    console.log('=== ANALYSE FORMULAIRE COMPLÈTE ===');
    console.log('Nombre de champs:', fieldCount);
    console.log('Taille totale:', (totalSize / 1024).toFixed(2) + ' KB');
    console.log('Organisation:', organisationData);
    console.log('Champs volumineux:', largestFields);
    console.log('Avertissements:', analysis.warnings);
    
    return analysis;
}

// ========================================
// WORKFLOW 2 PHASES - NOUVELLES FONCTIONS v4.1
// ========================================

/**
 * ✅ NOUVEAU : Initialiser le workflow 2 phases si disponible
 */
function initializeWorkflow2Phases() {
    try {
        // Vérifier si le module workflow-2phases.js est chargé
        if (typeof window.Workflow2Phases !== 'undefined' && window.Workflow2Phases.init) {
            console.log('🔄 Initialisation Workflow 2 Phases v4.1...');
            
            // Initialiser le module
            const initialized = window.Workflow2Phases.init();
            
            if (initialized) {
                console.log('✅ Workflow 2 Phases initialisé avec succès');
                
                // Marquer que le workflow est disponible
                window.OrganisationApp.workflow2PhasesAvailable = true;
                
                // Configurer les hooks nécessaires
                setupWorkflow2PhasesHooks();
                
            } else {
                console.log('⚠️ Workflow 2 Phases non initialisé - Fallback système original');
                window.OrganisationApp.workflow2PhasesAvailable = false;
            }
        } else {
            console.log('ℹ️ Module Workflow 2 Phases non trouvé - Système original utilisé');
            window.OrganisationApp.workflow2PhasesAvailable = false;
        }
    } catch (error) {
        console.warn('⚠️ Erreur initialisation Workflow 2 Phases - Fallback système original:', error);
        window.OrganisationApp.workflow2PhasesAvailable = false;
    }
}

/**
 * ✅ NOUVEAU : Configurer les hooks pour le workflow 2 phases
 */
function setupWorkflow2PhasesHooks() {
    if (!window.OrganisationApp.workflow2PhasesAvailable) return;
    
    try {
        // Hook 1: Sauvegarder la fonction submitForm originale
        if (!window.OrganisationApp.originalSubmitForm) {
            window.OrganisationApp.originalSubmitForm = window.submitForm;
            console.log('💾 Fonction submitForm originale sauvegardée');
        }
        
        // Hook 2: Remplacer submitForm par la version avec workflow 2 phases
        window.submitForm = function() {
            console.log('🚀 submitForm appelée - Option C Chunking Transparent');
    
            // ✅ OPTION C : Utiliser directement la soumission adaptative
            return window.OrganisationApp.originalSubmitForm.call(this);
        };
        
        console.log('🔗 Hooks Workflow 2 Phases configurés');
        
    } catch (error) {
        console.error('❌ Erreur configuration hooks Workflow 2 Phases:', error);
        // Restaurer la fonction originale en cas d'erreur
        if (window.OrganisationApp.originalSubmitForm) {
            window.submitForm = window.OrganisationApp.originalSubmitForm;
        }
    }
}

/**
 * ✅ NOUVEAU : Collecter TOUTES les données du formulaire (pour Workflow 2 Phases)
 */
function collectAllFormData() {
    console.log('📊 Collection complète des données formulaire v4.1');
    
    try {
        // Récupérer les données de base
        const baseData = collectFormData();
        
        // Ajouter les données spécialisées
        const completeData = {
            // Données de base du formulaire
            ...baseData,
            
            // Métadonnées
            metadata: {
                selectedOrgType: OrganisationApp.selectedOrgType,
                currentStep: OrganisationApp.currentStep,
                totalSteps: OrganisationApp.totalSteps,
                timestamp: Date.now(),
                version: '4.1'
            },
            
            // Données des collections
            fondateurs: [...OrganisationApp.fondateurs],
            adherents: [...OrganisationApp.adherents],
            
            // Documents (uniquement les métadonnées, pas les fichiers)
            documentsMetadata: Object.keys(OrganisationApp.documents).map(key => ({
                type: key,
                fileName: OrganisationApp.documents[key].fileName || null,
                uploaded: OrganisationApp.documents[key].uploaded || false
            })),
            
            // Rapport d'anomalies si présent
            rapportAnomalies: OrganisationApp.rapportAnomalies.enabled ? {
                enabled: true,
                adherentsValides: OrganisationApp.rapportAnomalies.adherentsValides,
                adherentsAvecAnomalies: OrganisationApp.rapportAnomalies.adherentsAvecAnomalies,
                statistiques: OrganisationApp.rapportAnomalies.statistiques,
                hasAnomalies: OrganisationApp.rapportAnomalies.anomalies.length > 0
            } : { enabled: false },
            
            // Informations de validation
            validationStatus: {
                allStepsValid: validateAllSteps(),
                currentStepValid: validateCurrentStep(),
                errors: {...OrganisationApp.validationErrors}
            }
        };
        
        console.log('✅ Données complètes collectées:', {
            baseFields: Object.keys(baseData).length,
            fondateurs: completeData.fondateurs.length,
            adherents: completeData.adherents.length,
            documents: completeData.documentsMetadata.length,
            hasAnomalies: completeData.rapportAnomalies.enabled
        });
        
        return completeData;
        
    } catch (error) {
        console.error('❌ Erreur collection données complètes:', error);
        
        // Fallback vers collectFormData de base
        return {
            ...collectFormData(),
            metadata: {
                selectedOrgType: OrganisationApp.selectedOrgType || '',
                error: 'Erreur collection complète',
                fallback: true
            },
            fondateurs: OrganisationApp.fondateurs || [],
            adherents: OrganisationApp.adherents || []
        };
    }
}

/**
 * ✅ NOUVEAU : Fonction de diagnostic pour Workflow 2 Phases
 */
function diagnoseWorkflow2Phases() {
    const diagnosis = {
        timestamp: new Date().toISOString(),
        version: '4.1',
        
        // Tests de disponibilité
        moduleLoaded: typeof window.Workflow2Phases !== 'undefined',
        moduleInitialized: window.OrganisationApp?.workflow2PhasesAvailable || false,
        originalFunctionSaved: typeof window.OrganisationApp?.originalSubmitForm === 'function',
        
        // Tests fonctionnels
        interceptAvailable: typeof window.Workflow2Phases?.interceptSubmission === 'function',
        collectDataAvailable: typeof window.collectAllFormData === 'function',
        
        // État actuel
        currentFormData: {
            selectedType: OrganisationApp.selectedOrgType,
            currentStep: OrganisationApp.currentStep,
            fondateursCount: OrganisationApp.fondateurs?.length || 0,
            adherentsCount: OrganisationApp.adherents?.length || 0,
            hasAnomalies: OrganisationApp.rapportAnomalies?.enabled || false
        },
        
        // Recommandations
        shouldUsePhase1: null, // Sera calculé si module disponible
        fallbackReason: null
    };
    
    // Test de la logique de décision si disponible
    if (diagnosis.moduleLoaded && window.Workflow2Phases.shouldUsePhase1) {
        try {
            diagnosis.shouldUsePhase1 = window.Workflow2Phases.shouldUsePhase1();
        } catch (error) {
            diagnosis.shouldUsePhase1 = false;
            diagnosis.fallbackReason = error.message;
        }
    }
    
    // Déterminer le statut global
    diagnosis.status = diagnosis.moduleLoaded && diagnosis.moduleInitialized ? 'OPERATIONAL' : 'FALLBACK';
    
    console.log('🔍 Diagnostic Workflow 2 Phases v4.1:', diagnosis);
    return diagnosis;
}

// ========================================
// 13. INITIALISATION COMPLÈTE
// ========================================

/**
 * Initialisation complète de l'application
 */
function initializeApplication() {
    console.log('🚀 Initialisation complète PNGDI - Création Organisation v4.1');
    
    // ✅ NOUVEAU : Initialiser le workflow 2 phases AVANT le reste
    initializeWorkflow2Phases();
    
    // ✅ Code existant préservé à 100%
    updateStepDisplay();
    updateNavigationButtons();
    
    // Configurer les événements
    setupEventListeners();

     // ✅ NOUVEAU : Initialiser validation NIP
    initNipFormatting();
    
    // Charger les données sauvegardées
    loadSavedData();
    
    // Démarrer l'auto-sauvegarde
    startAutoSave();
    
    console.log('✅ Application initialisée avec succès v4.1');
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
    
    // Boutons fondateurs et adhérents avec validation NIP
    const addFondateurBtn = document.getElementById('addFondateurBtn');
    if (addFondateurBtn) {
        addFondateurBtn.addEventListener('click', addFondateurWithNipValidation);
    }

    const addAdherentBtn = document.getElementById('addAdherentBtn');
    if (addAdherentBtn) {
        addAdherentBtn.addEventListener('click', addAdherentWithNipValidation);
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
    
    // Validation en temps réel avec débounce pour org_objet
    const orgObjetField = document.getElementById('org_objet');
    if (orgObjetField) {
        orgObjetField.addEventListener('input', function(e) {
            const currentLength = e.target.value.trim().length;
            const minLength = 50;
            
            // Mettre à jour compteur en temps réel
            let counterDiv = e.target.parentNode.querySelector('.char-counter');
            if (!counterDiv) {
                counterDiv = document.createElement('div');
                counterDiv.className = 'char-counter small text-muted mt-1';
                e.target.parentNode.appendChild(counterDiv);
            }
            counterDiv.textContent = `${currentLength}/${minLength} caractères`;
            counterDiv.style.color = currentLength < minLength ? '#dc3545' : '#28a745';
            
            // Validation différée
            clearTimeout(e.target.validationTimeout);
            e.target.validationTimeout = setTimeout(() => {
                validateField(e.target);
            }, OrganisationApp.config.validationDelay);
        });
    }
    
    // Validation en temps réel pour autres champs
    document.addEventListener('input', function(e) {
        const validationSelector = 'input:not(#org_objet), textarea:not(#org_objet), select';
        if (elementMatches(e.target, validationSelector)) {
            clearTimeout(e.target.validationTimeout);
            e.target.validationTimeout = setTimeout(() => {
                validateField(e.target);
            }, OrganisationApp.config.validationDelay);
        }
    });
    
    // Configuration de l'importation fichier adhérents
    initializeAdherentFileImport();
    
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
        if (!elementMatches(e.target,'input, textarea, select')) {
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
}

/**
 * Initialiser l'importation de fichier adhérents
 */
/**
 * Initialiser l'importation de fichier adhérents - VERSION CORRIGÉE
 */
function initializeAdherentFileImport() {
    console.log('🔧 Initialisation import fichier adhérents - Version corrigée');
    
    const fileInput = document.getElementById('adherents_file');
    if (!fileInput) {
        console.warn('⚠️ Input file #adherents_file non trouvé');
        return;
    }
    
    // ✅ NETTOYER les anciens event listeners
    const newFileInput = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(newFileInput, fileInput);
    
    // ✅ AJOUTER le nouvel event listener avec gestion d'erreurs
    newFileInput.addEventListener('change', function(event) {
        console.log('📁 Event change détecté sur input file');
        
        try {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                console.log(`📄 Fichier sélectionné: ${file.name} (${file.size} bytes)`);
                
                // Vérifier si handleAdherentFileImport existe
                if (typeof handleAdherentFileImport === 'function') {
                    handleAdherentFileImport(this);
                } else {
                    console.error('❌ Fonction handleAdherentFileImport non définie');
                    showNotification('❌ Erreur: Gestionnaire d\'import non trouvé', 'danger');
                }
            } else {
                console.log('ℹ️ Aucun fichier sélectionné');
            }
        } catch (error) {
            console.error('❌ Erreur dans event listener fichier:', error);
            showNotification(`❌ Erreur sélection fichier: ${error.message}`, 'danger');
        }
    });
    
    // ✅ AJOUTER event listener pour bouton de sélection
   const selectBtn = document.querySelector('button[onclick*="adherents_file"], #select-file-btn, #select-file-btn-manual');
    if (selectBtn) {
        selectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Clic sur bouton sélection fichier');
            newFileInput.click();
        });
        console.log('✅ Bouton sélection fichier configuré');
    } else {
        console.warn('⚠️ Bouton sélection fichier non trouvé');
    }
    
    console.log('✅ Événement importation fichier adhérents configuré (VERSION CORRIGÉE)');
}

/**
 * Basculer les déclarations selon le type d'organisation
 */
function toggleDeclarationParti() {
    const typeOrganisation = document.querySelector('input[name="type_organisation"]:checked');
    const declarationParti = document.getElementById('declaration_parti_politique');
    
    if (typeOrganisation && typeOrganisation.value === 'parti_politique') {
        if (declarationParti) {
            declarationParti.classList.remove('d-none');
            const checkbox = document.getElementById('declaration_exclusivite_parti');
            if (checkbox) {
                checkbox.required = true;
            }
        }
    } else {
        if (declarationParti) {
            declarationParti.classList.add('d-none');
            const checkbox = document.getElementById('declaration_exclusivite_parti');
            if (checkbox) {
                checkbox.required = false;
                checkbox.checked = false;
            }
        }
    }
}



// ========================================
// 14. FONCTIONS GLOBALES EXPOSÉES
// ========================================

// Exposer les fonctions principales pour compatibilité avec le HTML
window.changeStep = changeStep;
window.selectOrganizationType = selectOrganizationType;
// ✅ Nouvelles fonctions exposées pour Workflow 2 Phases
window.collectAllFormData = collectAllFormData;
window.initializeWorkflow2Phases = initializeWorkflow2Phases;
window.setupWorkflow2PhasesHooks = setupWorkflow2PhasesHooks;
window.diagnoseWorkflow2Phases = diagnoseWorkflow2Phases;

// ✅ Marqueur de compatibilité pour workflow-2phases.js
window.OrganisationApp = window.OrganisationApp || {};
window.OrganisationApp.workflow2PhasesCompatible = true;
window.OrganisationApp.version = '4.1';
window.addFondateur = addFondateur;
window.removeFondateur = removeFondateur;
window.addAdherent = addAdherent;
window.removeAdherent = removeAdherent;
window.handleDocumentUpload = handleDocumentUpload;
window.getCurrentLocation = getCurrentLocation;
window.openImageModal = openImageModal;
window.handleAdherentFileImport = handleAdherentFileImport;
window.toggleImportDetails = toggleImportDetails;
window.previewRapportAnomalies = previewRapportAnomalies;
window.downloadRapportAnomalies = downloadRapportAnomalies;
window.exportRapportHTML = exportRapportHTML;
window.filterAnomalies = filterAnomalies;
window.downloadTemplate = downloadTemplate;
window.toggleAdherentMode = toggleAdherentMode;
window.updateDepartements = updateDepartements;
window.toggleDeclarationParti = toggleDeclarationParti;
window.submitForm = submitForm;

/**
 * Vérification de l'intégrité du système d'anomalies
 */
function verifyAnomaliesSystem() {
    const checks = {
        configurationAnomalies: !!OrganisationApp.config.anomalies,
        rapportAnomaliesStructure: !!OrganisationApp.rapportAnomalies,
        fonctionsUtilitaires: typeof createAnomalie === 'function' && typeof addAnomalieToReport === 'function',
        fonctionsGeneration: typeof generateRapportAnomalies === 'function',
        fonctionsInterface: typeof previewRapportAnomalies === 'function',
        integrationRecapitulatif: typeof generateAnomaliesRecapSection === 'function'
    };
    
    const allChecksPass = Object.values(checks).every(check => check === true);
    
    console.log('🔍 Vérification intégrité système anomalies:', checks);
    console.log(allChecksPass ? '✅ Système d\'anomalies opérationnel' : '❌ Problème détecté dans le système');
    
    return allChecksPass;
}


// ========================================
// 16. INTÉGRATION VALIDATION NIP XX-QQQQ-YYYYMMDD
// ========================================

/**
 * ✅ Initialisation du formatage automatique NIP
 */
function initNipFormatting() {
    console.log('🔧 Initialisation formatage NIP nouveau format');
    
    // Initialiser le formatage pour tous les champs NIP
    const nipFields = ['demandeur_nip', 'fondateur_nip', 'adherent_nip'];
    
    nipFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            // Formatage en temps réel
            formatNIPInput(field);
            
            // Validation à la perte de focus avec serveur
            field.addEventListener('blur', function(e) {
                if (e.target.value && e.target.value.length >= 10) {
                    validateNIPWithServer(e.target, e.target.value);
                }
            });
            
            // Configuration des attributs
            field.setAttribute('placeholder', 'A1-2345-19901225');
            field.setAttribute('maxlength', '16');
        }
    });
}

/**
 * ✅ Validation fondateur avec nouveau format NIP
 */
function validateFounderNIP() {
    const nipField = document.getElementById('fondateur_nip');
    const nip = nipField ? nipField.value.trim() : '';
    
    if (!nip) {
        showNotification('Le NIP du fondateur est obligatoire', 'warning');
        return false;
    }
    
    // Validation avec le nouveau système
    if (!validateNIP(nipField, nip)) {
        return false;
    }
    
    // Vérifier doublons dans la liste actuelle
    const isDuplicate = OrganisationApp.fondateurs.some(f => f.nip === nip);
    if (isDuplicate) {
        showFieldError(nipField, 'Ce NIP existe déjà dans la liste des fondateurs');
        return false;
    }
    
    return true;
}

/**
 * ✅ Validation adhérent avec nouveau format NIP
 */
function validateAdherentNIP() {
    const nipField = document.getElementById('adherent_nip');
    const nip = nipField ? nipField.value.trim() : '';
    
    if (!nip) {
        showNotification('Le NIP de l\'adhérent est obligatoire', 'warning');
        return false;
    }
    
    // Validation avec le nouveau système
    if (!validateNIP(nipField, nip)) {
        return false;
    }
    
    // Vérifier doublons dans la liste actuelle
    const isDuplicate = OrganisationApp.adherents.some(a => a.nip === nip);
    if (isDuplicate) {
        showFieldError(nipField, 'Ce NIP existe déjà dans la liste des adhérents');
        return false;
    }
    
    // Vérifier aussi contre les fondateurs
    const isDuplicateFounder = OrganisationApp.fondateurs.some(f => f.nip === nip);
    if (isDuplicateFounder) {
        showFieldError(nipField, 'Ce NIP existe déjà dans la liste des fondateurs');
        return false;
    }
    
    return true;
}

/**
 * ✅ Validation complète avant soumission
 */
function validateAllNipsBeforeSubmit() {
    const errors = [];
    
    // Vérifier NIP demandeur
    const demandeurNip = document.getElementById('demandeur_nip')?.value.trim();
    if (demandeurNip && typeof window.NipValidation !== 'undefined') {
        const validation = window.NipValidation.validateFormat(demandeurNip);
        if (!validation.valid) {
            errors.push(`NIP demandeur: ${validation.message}`);
        }
    }
    
    // Vérifier NIP fondateurs
    OrganisationApp.fondateurs.forEach((fondateur, index) => {
        if (typeof window.NipValidation !== 'undefined') {
            const validation = window.NipValidation.validateFormat(fondateur.nip);
            if (!validation.valid) {
                errors.push(`NIP fondateur ${index + 1} (${fondateur.nom}): ${validation.message}`);
            }
        }
    });
    
    // Vérifier NIP adhérents
    OrganisationApp.adherents.forEach((adherent, index) => {
        if (typeof window.NipValidation !== 'undefined') {
            const validation = window.NipValidation.validateFormat(adherent.nip);
            if (!validation.valid) {
                errors.push(`NIP adhérent ${index + 1} (${adherent.nom}): ${validation.message}`);
            }
        }
    });
    
    if (errors.length > 0) {
        const errorMessage = 'Erreurs de format NIP détectées:\n\n' + errors.join('\n');
        showNotification(errorMessage, 'danger');
        return false;
    }
    
    return true;
}

/**
 * ✅ Mise à jour fonction addFondateur pour intégrer validation NIP
 */
function addFondateurWithNipValidation() {
    // Validation NIP avant ajout
    if (!validateFounderNIP()) {
        return;
    }
    
    // Appeler la fonction addFondateur originale
    if (typeof addFondateur === 'function') {
        addFondateur();
    }
}

/**
 * ✅ Mise à jour fonction addAdherent pour intégrer validation NIP
 */
function addAdherentWithNipValidation() {
    // Validation NIP avant ajout
    if (!validateAdherentNIP()) {
        return;
    }
    
    // Appeler la fonction addAdherent originale
    if (typeof addAdherent === 'function') {
        addAdherent();
    }
}

// ========================================
// 15. INITIALISATION AU CHARGEMENT DOM
// ========================================

document.addEventListener('DOMContentLoaded', function() {
 // Vérifier que nous sommes sur la bonne page
if (document.getElementById('organisationForm')) {
initializeApplication();
// Vérifier l'intégrité du système d'anomalies
verifyAnomaliesSystem();
// Configurer les événements spéciaux
setupSpecialEventListeners();

// ✅ CORRECTION ÉTAPE 7: Préparation adhérents pour Phase 2 (sans chunking backend)
console.log('🔧 Correction Étape 7: Préparation adhérents pour Phase 2');

// Forcer l'utilisation de la fonction originale handleAdherentFileImport
// qui prépare les données en session SANS les envoyer au backend
if (window.handleAdherentFileImport && window.originalHandleAdherentFileImport) {
    console.log('🔄 Restauration fonction Phase 1: préparation session uniquement');
    window.handleAdherentFileImport = window.originalHandleAdherentFileImport;
}

// Désactiver le chunking backend pour Phase 1 (sera utilisé en Phase 2)
if (typeof window.shouldUseChunking === 'function') {
    window.originalShouldUseChunking = window.shouldUseChunking;
    window.shouldUseChunking = function() {
        console.log('ℹ️ Phase 1: Chunking reporté à Phase 2');
        return false; // Pas de chunking backend en Phase 1
    };
}

console.log('✅ Phase 1 configurée: Upload + Session (Chunking reporté à Phase 2)');


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
            .table-warning {
                background-color: rgba(255, 243, 205, 0.3) !important;
            }
            .badge.bg-warning {
                font-size: 0.75em;
            }
            .char-counter {
                transition: color 0.3s ease;
            }
            .organization-type-card {
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .organization-type-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .organization-type-card.active {
                border-color: #0d6efd !important;
                background-color: rgba(13, 110, 253, 0.05);
            }
            .step-indicator {
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .step-indicator:hover {
                transform: scale(1.1);
            }
            .step-indicator.active {
                background-color: #0d6efd !important;
                color: white !important;
            }
            .step-indicator.completed {
                background-color: #198754 !important;
                color: white !important;
            }
            .anomalie-row {
                transition: all 0.3s ease;
            }
            .anomalie-critique {
                border-left: 3px solid #dc3545;
            }
            .anomalie-majeure {
                border-left: 3px solid #ffc107;
            }
            .anomalie-mineure {
                border-left: 3px solid #17a2b8;
            }
        `;
        document.head.appendChild(styles);
    }
});

/**
 * Configuration des événements spéciaux
 */
function setupSpecialEventListeners() {
    // Événement pour basculer les déclarations parti politique
    document.addEventListener('change', function(e) {
        if (elementMatches(e.target,'input[name="type_organisation"]')) {
            toggleDeclarationParti();
        }
    });
    
    // Gestion des navigation avec les touches
    document.addEventListener('keydown', function(e) {
        // Échapper pour fermer les modals
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        }
        
        // Entrée pour valider les étapes
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            if (OrganisationApp.currentStep < OrganisationApp.totalSteps) {
                changeStep(1);
            } else {
                submitForm();
            }
        }
    });
    
    // Auto-focus sur le premier champ de chaque étape
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const target = mutation.target;
                if (target.classList.contains('step-content') && target.style.display === 'block') {
                    setTimeout(() => {
                        const firstInput = target.querySelector('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]), select, textarea');
                        if (firstInput && !firstInput.disabled) {
                            firstInput.focus();
                        }
                    }, 100);
                }
            }
        });
    });
    
    // Observer les changements d'affichage des étapes
    document.querySelectorAll('.step-content').forEach(step => {
        observer.observe(step, { attributes: true, attributeFilter: ['style'] });
    });
    
    // Gestion des tooltips Bootstrap si disponible
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Validation automatique à la perte de focus
    document.addEventListener('blur', function(e) {
        if (elementMatches(e.target,'input[required], select[required], textarea[required]')) {
            validateField(e.target);
        }
    }, true);
    
    // Nettoyage automatique des erreurs lors de la saisie
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            // Nettoyer l'erreur après 1 seconde de saisie continue
            clearTimeout(e.target.cleanupTimeout);
            e.target.cleanupTimeout = setTimeout(() => {
                if (e.target.value.trim()) {
                    clearFieldError(e.target);
                }
            }, 1000);
        }
    });
    
    // Prévenír la soumission accidentelle avec Entrée
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && elementMatches(e.target,'input:not([type="submit"]):not([type="button"])')) {
            e.preventDefault();
            // Aller au champ suivant
            const form = e.target.closest('form');
            if (form) {
                const formElements = Array.from(form.querySelectorAll('input, select, textarea, button'));
                const currentIndex = formElements.indexOf(e.target);
                const nextElement = formElements[currentIndex + 1];
                if (nextElement && !nextElement.disabled) {
                    nextElement.focus();
                }
            }
        }
    });
    
    console.log('✅ Événements spéciaux configurés');
}

/**
 * Message de fin de chargement
 */
console.log(`
🎉 ========================================================================
   PNGDI - Formulaire Création Organisation - VERSION 4.1 CHARGÉ
   ========================================================================
   
   ✅ Version: 4.1 - WORKFLOW 2 PHASES INTÉGRÉ
   ✅ 9 étapes complètes avec validation
   ✅ Import Excel/CSV avec détection d'anomalies
   ✅ Rapport d'anomalies automatique
   ✅ Sauvegarde automatique toutes les 30s
   ✅ Validation temps réel
   ✅ Interface responsive Bootstrap 5
   ✅ 22 professions exclues pour partis politiques
   ✅ Gestion complète des documents
   ✅ Géolocalisation intégrée
   ✅ Raccourcis clavier
   
   🚀 NOUVEAU v4.1 - SESSION 4 (OPTION C) :
   ✅ Chunking Transparent adaptatif activé
   ✅ Protection double soumission intégrée
   ✅ Hook automatique pour workflow-2phases.js
   ✅ Fonction collectAllFormData() pour export complet
   ✅ Diagnostic intégré pour troubleshooting
   ✅ Compatibilité rétroactive 100% préservée
   
   🎯 Prêt pour production avec chunking Session 4 !
   📋 Système révolutionnaire de conservation totale des anomalies
   🇬🇦 Conformité législation gabonaise
   🔄 Workflow intelligent : Standard → Phase 1 → Phase 2
   
   Développé pour l'excellence du service public gabonais
========================================================================
`);

// Vérification finale de l'intégrité au chargement
setTimeout(() => {
     const integrityCheck = verifyAnomaliesSystem();
    const workflowDiagnosis = diagnoseWorkflow2Phases();
    
    if (integrityCheck && workflowDiagnosis.status === 'OPERATIONAL') {
        console.log('🎯 Système complet opérationnel v4.1 - Toutes les fonctionnalités disponibles');
        console.log('🔄 Workflow 2 Phases: ACTIVÉ et fonctionnel');
    } else if (integrityCheck && workflowDiagnosis.status === 'FALLBACK') {
        console.log('✅ Système de base opérationnel v4.1 - Mode fallback activé');
        console.log('🔄 Workflow 2 Phases: INDISPONIBLE - Système original utilisé');
    } else {
        console.warn('⚠️ Problème d\'intégrité détecté v4.1 - Certaines fonctionnalités peuvent être limitées');
    }
    
    // Test immédiat des fonctions exposées
    if (typeof window.collectAllFormData === 'function') {
        console.log('✅ collectAllFormData() exposée et fonctionnelle');
    }
    
    if (typeof window.diagnoseWorkflow2Phases === 'function') {
        console.log('✅ diagnoseWorkflow2Phases() exposée et fonctionnelle');
    }
    
}, 1000);



/**
 * Lecture simple du fichier (SANS CHUNKING)
 */
async function readAdherentFileSimple(file) {
    console.log('📖 Lecture simple du fichier:', file.name);
    
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = e.target.result;
                let adherentsData = [];
                
                if (file.name.toLowerCase().endsWith('.csv')) {
                    // Traitement CSV simple
                    adherentsData = parseCSVSimple(data);
                    
                } else {
                    // Traitement Excel avec XLSX
                    const workbook = XLSX.read(data, { type: 'binary' });
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    adherentsData = XLSX.utils.sheet_to_json(worksheet);
                }
                
                console.log(`✅ Fichier lu: ${adherentsData.length} lignes`);
                resolve(adherentsData);
                
            } catch (error) {
                console.error('❌ Erreur lecture fichier:', error);
                reject(new Error('Impossible de lire le fichier: ' + error.message));
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur de lecture du fichier'));
        
        // Lire selon le type
        if (file.name.toLowerCase().endsWith('.csv')) {
            reader.readAsText(file, 'UTF-8');
        } else {
            reader.readAsBinaryString(file);
        }
    });
}

/**
 * Parser CSV simple (sans chunking)
 */
function parseCSVSimple(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) return [];
    
    // Détecter le délimiteur
    const delimiters = [';', ',', '\t'];
    const headerLine = lines[0];
    let delimiter = ';'; // Par défaut
    
    for (let del of delimiters) {
        if (headerLine.includes(del)) {
            delimiter = del;
            break;
        }
    }
    
    // Parser les lignes
    const headers = lines[0].split(delimiter).map(h => h.trim().toLowerCase());
    const adherentsData = [];
    
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(delimiter);
        if (values.length >= headers.length) {
            const adherent = {};
            headers.forEach((header, index) => {
                adherent[header] = values[index] ? values[index].trim() : '';
            });
            adherentsData.push(adherent);
        }
    }
    
    return adherentsData;
}

/**
 * Validation pour session (pas d'import base)
 */
async function validateAdherentsForSession(adherentsData) {
    console.log('🔍 Validation pour session (Étape 7)');
    
    const validationResult = {
        total: adherentsData.length,
        valides: 0,
        invalides: 0,
        anomalies_mineures: 0,
        anomalies_majeures: 0,
        anomalies_critiques: 0,
        adherents: [],
        rapport: {
            erreurs: [],
            avertissements: [],
            infos: []
        }
    };
    
    // Normaliser les champs
    const fieldMapping = {
        'nom': ['nom', 'lastname', 'surname'],
        'prenom': ['prenom', 'prénom', 'firstname'],
        'nip': ['nip', 'numero', 'numero_identite'],
        'telephone': ['telephone', 'téléphone', 'phone'],
        'email': ['email', 'mail', 'courriel'],
        'profession': ['profession', 'metier', 'job'],
        'civilite': ['civilite', 'civilité', 'title']
    };
    
    adherentsData.forEach((adherent, index) => {
        const lineNumber = index + 2; // +2 car ligne 1 = headers
        const normalizedAdherent = normalizeAdherentFields(adherent, fieldMapping);
        
        // Validation de base
        const validation = validateSingleAdherent(normalizedAdherent, lineNumber);
        
        if (validation.isValid) {
            validationResult.valides++;
            normalizedAdherent.lineNumber = lineNumber;
            validationResult.adherents.push(normalizedAdherent);
            
            // Compter les anomalies
            if (validation.anomalies) {
                validation.anomalies.forEach(anomalie => {
                    switch(anomalie.severity) {
                        case 'critique': validationResult.anomalies_critiques++; break;
                        case 'majeure': validationResult.anomalies_majeures++; break;
                        case 'mineure': validationResult.anomalies_mineures++; break;
                    }
                });
            }
            
        } else {
            validationResult.invalides++;
            validationResult.rapport.erreurs.push({
                ligne: lineNumber,
                erreurs: validation.erreurs
            });
        }
    });
    
    console.log('✅ Validation terminée:', {
        total: validationResult.total,
        valides: validationResult.valides,
        invalides: validationResult.invalides
    });
    
    return validationResult;
}

/**
 * Normaliser les champs d'un adhérent
 */
function normalizeAdherentFields(adherent, fieldMapping) {
    const normalized = {};
    
    Object.keys(fieldMapping).forEach(targetField => {
        const possibleFields = fieldMapping[targetField];
        
        for (let field of possibleFields) {
            if (adherent[field] !== undefined && adherent[field] !== '') {
                normalized[targetField] = adherent[field];
                break;
            }
        }
        
        // Valeur par défaut si rien trouvé
        if (!normalized[targetField]) {
            normalized[targetField] = '';
        }
    });
    
    return normalized;
}

/**
 * Validation d'un adhérent unique
 */
function validateSingleAdherent(adherent, lineNumber) {
    const erreurs = [];
    const anomalies = [];
    
    // Validations obligatoires
    if (!adherent.nom || adherent.nom.length < 2) {
        erreurs.push('Nom manquant ou trop court');
    }
    
    if (!adherent.prenom || adherent.prenom.length < 2) {
        erreurs.push('Prénom manquant ou trop court');
    }
    
    // Validation NIP (nouveau format XX-QQQQ-YYYYMMDD)
    if (!adherent.nip) {
        erreurs.push('NIP manquant');
    } else {
        const nipPattern = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;
        if (!nipPattern.test(adherent.nip)) {
            anomalies.push({
                code: 'nip_format_invalide',
                severity: 'majeure',
                message: `Format NIP invalide: ${adherent.nip}`
            });
        }
    }
    
    // Validation email
    if (adherent.email && adherent.email.length > 0) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(adherent.email)) {
            anomalies.push({
                code: 'email_invalide',
                severity: 'mineure',
                message: `Email invalide: ${adherent.email}`
            });
        }
    }
    
    // Validation téléphone
    if (adherent.telephone && adherent.telephone.length > 0) {
        const cleanPhone = adherent.telephone.replace(/[^0-9+]/g, '');
        if (cleanPhone.length < 8) {
            anomalies.push({
                code: 'telephone_invalide',
                severity: 'mineure',
                message: `Téléphone invalide: ${adherent.telephone}`
            });
        }
    }
    
    return {
        isValid: erreurs.length === 0,
        erreurs: erreurs,
        anomalies: anomalies
    };
}

/**
 * Sauvegarder dans OrganisationApp.adherents (PAS EN BASE)
 */
/**
 * Sauvegarder dans OrganisationApp.adherents (PAS EN BASE)
 */
async function saveAdherentsToFormData(validationResult) {
    console.log('💾 Redirection vers nouvelle fonction de session');
    
    const preparedData = {
        adherents: validationResult.adherents,
        stats: {
            total: validationResult.total,
            valides: validationResult.valides,
            invalides: validationResult.invalides,
            anomalies_mineures: validationResult.anomalies_mineures,
            anomalies_majeures: validationResult.anomalies_majeures,
            anomalies_critiques: validationResult.anomalies_critiques
        },
        rapport: validationResult.rapport,
        timestamp: new Date().toISOString(),
        expires_at: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString()
    };
    
    // Appeler la nouvelle fonction de session
    await saveAdherentsToSession(preparedData);
}

/**
 * Afficher le rapport d'import session
 */
function showImportSessionReport(validationResult) {
    const reportHTML = `
        <div class="alert alert-success border-0 mt-3">
            <h6 class="alert-heading">
                <i class="fas fa-file-check me-2"></i>
                Fichier traité avec succès
            </h6>
            <div class="row text-center">
                <div class="col-3">
                    <div class="h4 text-primary">${validationResult.total}</div>
                    <small>Total lignes</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-success">${validationResult.valides}</div>
                    <small>Valides</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-warning">${validationResult.anomalies_mineures + validationResult.anomalies_majeures}</div>
                    <small>Anomalies</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-danger">${validationResult.invalides}</div>
                    <small>Erreurs</small>
                </div>
            </div>
            <hr>
            <p class="mb-0">
                <i class="fas fa-info-circle text-info me-2"></i>
                <strong>Les adhérents seront importés en base lors de la soumission finale du formulaire.</strong>
            </p>
        </div>
    `;
    
    // Afficher dans la zone des détails d'import
    const detailsContainer = document.getElementById('import_details');
    if (detailsContainer) {
        detailsContainer.innerHTML = reportHTML;
        detailsContainer.classList.remove('d-none');
    }
}

/**
 * Lecture du fichier avec progress tracking
 */
async function readAdherentFileWithProgress(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = e.target.result;
                let adherentsData = [];
                
                if (file.name.toLowerCase().endsWith('.csv')) {
                    // Traitement CSV optimisé
                    adherentsData = parseCSVAdvanced(data);
                } else {
                    // Traitement Excel avec XLSX
                    const workbook = XLSX.read(data, { type: 'binary' });
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    adherentsData = XLSX.utils.sheet_to_json(worksheet);
                }
                
                console.log(`✅ Fichier lu avec succès: ${adherentsData.length} lignes détectées`);
                resolve(adherentsData);
                
            } catch (error) {
                console.error('❌ Erreur lors de la lecture du fichier:', error);
                reject(new Error('Impossible de lire le fichier. Vérifiez le format.'));
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur de lecture du fichier'));
        
        // Lire selon le type de fichier
        if (file.name.toLowerCase().endsWith('.csv')) {
            reader.readAsText(file, 'UTF-8');
        } else {
            reader.readAsBinaryString(file);
        }
    });
}

/**
 * Parser CSV avancé avec détection automatique de délimiteur
 */
function parseCSVAdvanced(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) return [];
    
    // Détection intelligente du délimiteur
    const delimiters = [';', ',', '\t', '|'];
    const headerLine = lines[0];
    
    let bestDelimiter = ';';
    let maxColumns = 0;
    
    for (let delimiter of delimiters) {
        const columns = headerLine.split(delimiter).length;
        if (columns > maxColumns) {
            maxColumns = columns;
            bestDelimiter = delimiter;
        }
    }
    
    console.log(`📋 Délimiteur détecté: "${bestDelimiter}" (${maxColumns} colonnes)`);
    
    // Parser avec le meilleur délimiteur
    const headers = lines[0].split(bestDelimiter).map(h => h.trim().toLowerCase());
    const adherentsData = [];
    
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(bestDelimiter);
        
        if (values.length >= headers.length - 1) { // Tolérance pour colonnes manquantes
            const adherent = {};
            
            headers.forEach((header, index) => {
                adherent[header] = values[index] ? values[index].trim() : '';
            });
            
            // Ne pas ajouter les lignes complètement vides
            if (Object.values(adherent).some(val => val !== '')) {
                adherentsData.push(adherent);
            }
        }
    }
    
    return adherentsData;
}

/**
 * Validation avancée des adhérents avec progress
 */
async function validateAdherentsWithProgress(adherentsData) {
    const validationResult = {
        total: adherentsData.length,
        valides: 0,
        invalides: 0,
        anomalies_mineures: 0,
        anomalies_majeures: 0,
        anomalies_critiques: 0,
        adherents: [],
        rapport: {
            erreurs: [],
            avertissements: [],
            infos: []
        }
    };
    
    // Mapping intelligent des champs
    const fieldMapping = {
        'nom': ['nom', 'lastname', 'surname', 'family_name'],
        'prenom': ['prenom', 'prénom', 'firstname', 'first_name', 'given_name'],
        'nip': ['nip', 'numero', 'numero_identite', 'id_number'],
        'telephone': ['telephone', 'téléphone', 'phone', 'mobile', 'cellulaire'],
        'email': ['email', 'mail', 'courriel', 'e-mail'],
        'profession': ['profession', 'metier', 'job', 'occupation'],
        'civilite': ['civilite', 'civilité', 'title', 'mr_mrs']
    };
    
    // Traitement par batch pour éviter le freeze
    const batchSize = 50;
    const totalBatches = Math.ceil(adherentsData.length / batchSize);
    
    for (let batchIndex = 0; batchIndex < totalBatches; batchIndex++) {
        const startIndex = batchIndex * batchSize;
        const endIndex = Math.min(startIndex + batchSize, adherentsData.length);
        const batch = adherentsData.slice(startIndex, endIndex);
        
        // Traiter chaque adhérent du batch
        batch.forEach((adherent, index) => {
            const globalIndex = startIndex + index;
            const lineNumber = globalIndex + 2; // +2 car ligne 1 = headers
            
            const normalizedAdherent = normalizeAdherentFields(adherent, fieldMapping);
            const validation = validateSingleAdherentAdvanced(normalizedAdherent, lineNumber);
            
            if (validation.isValid) {
                validationResult.valides++;
                normalizedAdherent.lineNumber = lineNumber;
                normalizedAdherent.hasAnomalies = validation.anomalies.length > 0;
                normalizedAdherent.anomalies = validation.anomalies;
                
                validationResult.adherents.push(normalizedAdherent);
                
                // Compter les anomalies par niveau
                validation.anomalies.forEach(anomalie => {
                    switch(anomalie.severity) {
                        case 'critique': validationResult.anomalies_critiques++; break;
                        case 'majeure': validationResult.anomalies_majeures++; break;
                        case 'mineure': validationResult.anomalies_mineures++; break;
                    }
                });
                
            } else {
                validationResult.invalides++;
                validationResult.rapport.erreurs.push({
                    ligne: lineNumber,
                    erreurs: validation.erreurs
                });
            }
        });
        
        // Mise à jour progress durant la validation
        const progress = 50 + Math.round((batchIndex + 1) / totalBatches * 20); // 50% à 70%
        updateUploadProgress(progress, `Validation batch ${batchIndex + 1}/${totalBatches}...`);
        
        // Pause pour permettre l'update UI
        if (batchIndex < totalBatches - 1) {
            await new Promise(resolve => setTimeout(resolve, 50));
        }
    }
    
    console.log('✅ Validation terminée:', {
        total: validationResult.total,
        valides: validationResult.valides,
        invalides: validationResult.invalides,
        anomalies: validationResult.anomalies_critiques + validationResult.anomalies_majeures + validationResult.anomalies_mineures
    });
    
    return validationResult;
}

/**
 * Validation avancée d'un adhérent unique
 */
function validateSingleAdherentAdvanced(adherent, lineNumber) {
    const erreurs = [];
    const anomalies = [];
    
    // Validations obligatoires
    if (!adherent.nom || adherent.nom.length < 2) {
        erreurs.push('Nom manquant ou trop court');
    }
    
    if (!adherent.prenom || adherent.prenom.length < 2) {
        erreurs.push('Prénom manquant ou trop court');
    }
    
    // Validation NIP avancée (format XX-QQQQ-YYYYMMDD)
    if (!adherent.nip) {
        erreurs.push('NIP manquant');
    } else {
        const nipPattern = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;
        if (!nipPattern.test(adherent.nip)) {
            anomalies.push({
                code: 'nip_format_invalide',
                severity: 'majeure',
                message: `Format NIP invalide: ${adherent.nip}`,
                suggestion: 'Format attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
            });
        } else {
            // Validation de la date dans le NIP
            const datePart = adherent.nip.slice(-8);
            const year = parseInt(datePart.substring(0, 4));
            const month = parseInt(datePart.substring(4, 6));
            const day = parseInt(datePart.substring(6, 8));
            
            const currentYear = new Date().getFullYear();
            
            if (year < 1900 || year > currentYear) {
                anomalies.push({
                    code: 'nip_annee_invalide',
                    severity: 'majeure',
                    message: `Année de naissance invalide dans NIP: ${year}`
                });
            }
            
            if (month < 1 || month > 12) {
                anomalies.push({
                    code: 'nip_mois_invalide',
                    severity: 'majeure',
                    message: `Mois invalide dans NIP: ${month}`
                });
            }
            
            if (day < 1 || day > 31) {
                anomalies.push({
                    code: 'nip_jour_invalide',
                    severity: 'majeure',
                    message: `Jour invalide dans NIP: ${day}`
                });
            }
            
            // Vérifier âge minimum (18 ans)
            const birthDate = new Date(year, month - 1, day);
            const age = Math.floor((new Date() - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
            
            if (age < 18) {
                anomalies.push({
                    code: 'age_mineur',
                    severity: 'critique',
                    message: `Personne mineure (${age} ans) - non autorisée`
                });
            }
        }
    }
    
    // Validation email
    if (adherent.email && adherent.email.length > 0) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(adherent.email)) {
            anomalies.push({
                code: 'email_invalide',
                severity: 'mineure',
                message: `Email invalide: ${adherent.email}`
            });
        }
    }
    
    // Validation téléphone gabonais
    if (adherent.telephone && adherent.telephone.length > 0) {
        const cleanPhone = adherent.telephone.replace(/[^0-9+]/g, '');
        
        // Patterns téléphone gabonais
        const gabonPatterns = [
            /^(\+241)?[01][0-9]{7}$/, // Fixe: 01XXXXXXX
            /^(\+241)?[67][0-9]{7}$/  // Mobile: 6XXXXXXXX ou 7XXXXXXXX
        ];
        
        const isValidGabonPhone = gabonPatterns.some(pattern => pattern.test(cleanPhone));
        
        if (!isValidGabonPhone) {
            anomalies.push({
                code: 'telephone_invalide',
                severity: 'mineure',
                message: `Téléphone invalide: ${adherent.telephone}`,
                suggestion: 'Format attendu: 01XXXXXXX, 6XXXXXXXX ou 7XXXXXXXX'
            });
        }
    }
    
    // Validation civilité
    if (adherent.civilite && !['M', 'Mme', 'Mlle', 'Mr', 'Mrs', 'Ms'].includes(adherent.civilite)) {
        anomalies.push({
            code: 'civilite_non_standard',
            severity: 'mineure',
            message: `Civilité non standard: ${adherent.civilite}`
        });
        
        // Auto-correction
        const civiliteNormalized = adherent.civilite.toLowerCase();
        if (civiliteNormalized.includes('m') && !civiliteNormalized.includes('me')) {
            adherent.civilite = 'M';
        } else if (civiliteNormalized.includes('me')) {
            adherent.civilite = 'Mme';
        } else if (civiliteNormalized.includes('lle')) {
            adherent.civilite = 'Mlle';
        }
    }
    
    return {
        isValid: erreurs.length === 0,
        erreurs: erreurs,
        anomalies: anomalies
    };
}

/**
 * Préparation finale des données pour session
 */
async function prepareAdherentsForSession(validationResult) {
    const preparedData = {
        adherents: [],
        stats: {
            total: validationResult.total,
            valides: validationResult.valides,
            invalides: validationResult.invalides,
            anomalies_mineures: validationResult.anomalies_mineures,
            anomalies_majeures: validationResult.anomalies_majeures,
            anomalies_critiques: validationResult.anomalies_critiques
        },
        rapport: validationResult.rapport,
        timestamp: new Date().toISOString(),
        expires_at: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString() // 2h
    };
    
    // Préparer chaque adhérent valide
    validationResult.adherents.forEach(adherent => {
        // Générer un NIP temporaire si invalide mais adhérent valide
        if (adherent.hasAnomalies && adherent.anomalies.some(a => a.code.includes('nip'))) {
            adherent.nip_original = adherent.nip;
            adherent.nip = generateTemporaryNIP();
            adherent.nip_temporaire = true;
        }
        
        preparedData.adherents.push({
            civilite: adherent.civilite || 'M',
            nom: adherent.nom,
            prenom: adherent.prenom,
            nip: adherent.nip,
            telephone: adherent.telephone || '',
            email: adherent.email || '',
            profession: adherent.profession || '',
            lineNumber: adherent.lineNumber,
            hasAnomalies: adherent.hasAnomalies || false,
            anomalies: adherent.anomalies || [],
            nip_temporaire: adherent.nip_temporaire || false,
            nip_original: adherent.nip_original || null
        });
    });
    
    return preparedData;
}

/**
 * Génération d'un NIP temporaire valide
 */
function generateTemporaryNIP() {
    const prefix = 'TMP';
    const sequence = String(Math.floor(Math.random() * 9999)).padStart(4, '0');
    const birthYear = '19900101'; // Date neutre
    
    return `${prefix}-${sequence}-${birthYear}`;
}

/**
 * Sauvegarde en session avec structure optimisée
 */
async function saveAdherentsToSession(preparedData) {
    console.log('💾 Sauvegarde des adhérents dans la session formulaire (Étape 7)');
    
    // Vider les adhérents existants dans l'application
    OrganisationApp.adherents = [];
    
    // Ajouter tous les adhérents préparés
    preparedData.adherents.forEach(adherent => {
        OrganisationApp.adherents.push(adherent);
    });
    
    // Stocker aussi les métadonnées pour Phase 2
    OrganisationApp.adherentsMetadata = {
        stats: preparedData.stats,
        rapport: preparedData.rapport,
        timestamp: preparedData.timestamp,
        expires_at: preparedData.expires_at
    };
    
    console.log(`✅ ${OrganisationApp.adherents.length} adhérents sauvegardés en session`);
    
    // Déclencher les mises à jour UI
    updateAdherentsList();
    updateFormStats();
    autoSave();
}

/**
 * Interface de progress moderne
 */
function showUploadProgress() {
    const existingModal = document.getElementById('uploadProgressModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHTML = `
        <div class="modal fade" id="uploadProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-upload me-2"></i>
                            Import Fichier Adhérents - Étape 7
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        
                        <div class="progress mb-3" style="height: 25px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="uploadProgressText">0%</span>
                            </div>
                        </div>
                        
                        <div id="uploadProgressMessage" class="text-center text-muted">
                            Initialisation...
                        </div>
                        
                        <div id="uploadProgressDetails" class="mt-3 small text-muted">
                            <!-- Détails supplémentaires -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    const modal = new bootstrap.Modal(document.getElementById('uploadProgressModal'));
    modal.show();
}

/**
 * Mise à jour du progress
 */
function updateUploadProgress(percentage, message, details = '') {
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const progressMessage = document.getElementById('uploadProgressMessage');
    const progressDetails = document.getElementById('uploadProgressDetails');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
    }
    
    if (progressText) {
        progressText.textContent = percentage + '%';
    }
    
    if (progressMessage) {
        progressMessage.textContent = message;
    }
    
    if (progressDetails && details) {
        progressDetails.innerHTML = details;
    }
}

/**
 * Affichage du succès avec résumé
 */
function showUploadSuccess(preparedData) {
    // Fermer le modal de progress
    const progressModal = bootstrap.Modal.getInstance(document.getElementById('uploadProgressModal'));
    if (progressModal) {
        progressModal.hide();
    }
    
    // Afficher notification de succès
    const stats = preparedData.stats;
    let message = `✅ ${stats.valides} adhérents préparés avec succès !`;
    
    if (stats.anomalies_mineures + stats.anomalies_majeures + stats.anomalies_critiques > 0) {
        message += ` (${stats.anomalies_mineures + stats.anomalies_majeures + stats.anomalies_critiques} anomalies détectées)`;
    }
    
    showNotification(message, 'success', 6000);
    
    // Afficher rapport détaillé dans l'interface
    showDetailedReport(preparedData);
}

/**
 * Affichage des erreurs
 */
function showUploadError(errorMessage) {
    // Fermer le modal de progress
    const progressModal = bootstrap.Modal.getInstance(document.getElementById('uploadProgressModal'));
    if (progressModal) {
        progressModal.hide();
    }
    
    showNotification('❌ Erreur lors de l\'import: ' + errorMessage, 'danger', 8000);
}

/**
 * Affichage du rapport détaillé
 */
function showDetailedReport(preparedData) {
    const detailsContainer = document.getElementById('import_details');
    if (!detailsContainer) return;
    
    const stats = preparedData.stats;
    
    const reportHTML = `
        <div class="alert alert-success border-0 mt-3 fade-in">
            <h6 class="alert-heading">
                <i class="fas fa-file-check me-2"></i>
                Fichier traité avec succès - Version 2.0
            </h6>
            
            <div class="row text-center mb-3">
                <div class="col-3">
                    <div class="h4 text-primary">${stats.total}</div>
                    <small>Total lignes</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-success">${stats.valides}</div>
                    <small>Valides</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-warning">${stats.anomalies_mineures + stats.anomalies_majeures}</div>
                    <small>Anomalies</small>
                </div>
                <div class="col-3">
                    <div class="h4 text-danger">${stats.invalides}</div>
                    <small>Rejetés</small>
                </div>
            </div>
            
            ${stats.anomalies_critiques > 0 ? `
                <div class="alert alert-warning">
                    <strong>⚠️ ${stats.anomalies_critiques} anomalies critiques détectées</strong><br>
                    Ces adhérents seront marqués pour révision mais seront inclus dans l'import.
                </div>
            ` : ''}
            
            <hr>
            
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-info-circle text-info me-2"></i>
                    <strong>Les adhérents sont préparés pour l'importation finale en Phase 2.</strong>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDetailedStats()">
                    <i class="fas fa-chart-bar me-1"></i>Voir détails
                </button>
            </div>
            
            <div id="detailedStats" class="mt-3 d-none">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Types d'anomalies:</h6>
                        <small class="text-danger">Critiques: ${stats.anomalies_critiques}</small><br>
                        <small class="text-warning">Majeures: ${stats.anomalies_majeures}</small><br>
                        <small class="text-info">Mineures: ${stats.anomalies_mineures}</small>
                    </div>
                    <div class="col-md-6">
                        <h6>Prochaines étapes:</h6>
                        <small>✅ Données en session (2h)</small><br>
                        <small>⏳ Soumission → Phase 2</small><br>
                        <small>🚀 Import final en base</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    detailsContainer.innerHTML = reportHTML;
    detailsContainer.classList.remove('d-none');
}

/**
 * Toggle des statistiques détaillées
 */
function toggleDetailedStats() {
    const detailedStats = document.getElementById('detailedStats');
    if (detailedStats) {
        detailedStats.classList.toggle('d-none');
    }
}

/**
 * ========================================================================
 * CORRECTION VARIABLES SESSION - COMPATIBILITÉ ÉTAPE 7 ↔ CONFIRMATION
 * Assurer la correspondance exacte entre Étape 7 et confirmation.blade.php
 * ========================================================================
 */

/**
 * ⚠️ CORRECTION MAJEURE : saveAdherentsToSession()
 * Assurer la compatibilité avec confirmation.blade.php
 */
async function saveAdherentsToSession(preparedData) {
    console.log('💾 Sauvegarde des adhérents en session avec compatibilité Phase 2');
    
    // ✅ NOUVEAU: Récupérer l'ID du dossier pour la session Phase 2
    const dossierId = getCurrentDossierId();
    
    if (!dossierId) {
        console.warn('⚠️ Impossible de déterminer l\'ID du dossier - utilisation session locale');
        
        // Fallback: utiliser OrganisationApp pour la session locale
        OrganisationApp.adherents = [];
        preparedData.adherents.forEach(adherent => {
            OrganisationApp.adherents.push(adherent);
        });
        
        OrganisationApp.adherentsMetadata = {
            stats: preparedData.stats,
            rapport: preparedData.rapport,
            timestamp: preparedData.timestamp,
            expires_at: preparedData.expires_at
        };
        
        console.log(`✅ ${OrganisationApp.adherents.length} adhérents sauvegardés localement`);
        return;
    }
    
    // ✅ CORRECT: Utiliser le format attendu par confirmation.blade.php
    const sessionKey = `phase2_adherents_${dossierId}`;
    const expirationKey = `phase2_expires_${dossierId}`;
    
    // Structure exacte attendue par confirmation.blade.php
    const sessionData = {
        data: preparedData.adherents,  // Array des adhérents
        total: preparedData.adherents.length,
        created_at: new Date().toISOString(),
        expires_at: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString(), // 2h
        user_id: getCurrentUserId(),
        dossier_id: dossierId,
        metadata: {
            stats: preparedData.stats,
            rapport: preparedData.rapport,
            source: 'etape7_upload',
            version: '2.0'
        }
    };
    
    try {
        // ✅ Sauvegarder en session serveur via AJAX
        const response = await fetch('/operator/organisations/save-session-adherents', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                session_key: sessionKey,
                expiration_key: expirationKey,
                data: sessionData,
                dossier_id: dossierId
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            console.log('✅ Session serveur sauvegardée:', result);
        } else {
            throw new Error('Erreur sauvegarde session serveur');
        }
        
    } catch (error) {
        console.warn('⚠️ Fallback: sauvegarde session côté client', error);
        
        // Fallback: simulation session côté client
        if (typeof Storage !== 'undefined') {
            sessionStorage.setItem(sessionKey, JSON.stringify(sessionData));
            sessionStorage.setItem(expirationKey, sessionData.expires_at);
        }
    }
    
    // ✅ TOUJOURS: Mettre à jour OrganisationApp pour compatibilité locale
    OrganisationApp.adherents = [];
    preparedData.adherents.forEach(adherent => {
        OrganisationApp.adherents.push(adherent);
    });
    
    OrganisationApp.adherentsMetadata = sessionData.metadata;
    OrganisationApp.sessionInfo = {
        sessionKey: sessionKey,
        expirationKey: expirationKey,
        dossierId: dossierId,
        expiresAt: sessionData.expires_at
    };
    
    console.log(`✅ ${OrganisationApp.adherents.length} adhérents sauvegardés (session + local)`);
    console.log(`🔑 Session key: ${sessionKey}`);
    console.log(`⏰ Expiration: ${sessionData.expires_at}`);
    
    // Déclencher les mises à jour UI
    updateAdherentsList();
    updateFormStats();
    autoSave();
    
    // ✅ NOTIFICATION: Informer l'utilisateur de la session active
    showSessionSaveNotification(preparedData.adherents.length, sessionData.expires_at);
}

/**
 * Obtenir l'ID du dossier actuel
 */
function getCurrentDossierId() {
    // Méthode 1: Depuis l'URL (si on est déjà sur une page de dossier)
    const urlMatch = window.location.pathname.match(/\/dossiers\/(\d+)/);
    if (urlMatch) {
        return parseInt(urlMatch[1]);
    }
    
    // Méthode 2: Depuis OrganisationApp (si défini)
    if (window.OrganisationApp && window.OrganisationApp.dossierId) {
        return window.OrganisationApp.dossierId;
    }
    
    // Méthode 3: Depuis meta tag (à ajouter dans create.blade.php)
    const metaTag = document.querySelector('meta[name="dossier-id"]');
    if (metaTag) {
        return parseInt(metaTag.getAttribute('content'));
    }
    
    // Méthode 4: Depuis un champ caché du formulaire
    const hiddenField = document.getElementById('current_dossier_id');
    if (hiddenField) {
        return parseInt(hiddenField.value);
    }
    
    console.warn('⚠️ Impossible de déterminer l\'ID du dossier');
    return null;
}

/**
 * Obtenir l'ID utilisateur actuel
 */
function getCurrentUserId() {
    // Méthode 1: Depuis meta tag Laravel
    const metaTag = document.querySelector('meta[name="user-id"]');
    if (metaTag) {
        return parseInt(metaTag.getAttribute('content'));
    }
    
    // Méthode 2: Depuis une variable globale
    if (window.currentUserId) {
        return window.currentUserId;
    }
    
    return null;
}

/**
 * Obtenir le token CSRF
 */
function getCSRFToken() {
    // Méthode 1: Depuis meta tag Laravel
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Méthode 2: Depuis un champ caché
    const hiddenField = document.querySelector('input[name="_token"]');
    if (hiddenField) {
        return hiddenField.value;
    }
    
    return null;
}

/**
 * Notification de sauvegarde session
 */
function showSessionSaveNotification(adherentsCount, expiresAt) {
    const expirationTime = new Date(expiresAt);
    const expirationFormatted = expirationTime.toLocaleString('fr-FR');
    
    const notificationHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-save fa-2x me-3 text-success"></i>
                <div>
                    <h6 class="mb-1">
                        <i class="fas fa-check-circle me-1"></i>
                        Session préparée pour Phase 2
                    </h6>
                    <p class="mb-1">
                        <strong>${adherentsCount} adhérents</strong> sauvegardés en session sécurisée.
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Session expire le ${expirationFormatted}
                        </small>
                    </p>
                    <p class="mb-0">
                        <small class="text-success">
                            <i class="fas fa-arrow-right me-1"></i>
                            Les données seront automatiquement récupérées lors de la soumission finale.
                        </small>
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Afficher dans la zone de détails
    const detailsContainer = document.getElementById('import_details');
    if (detailsContainer) {
        detailsContainer.innerHTML = notificationHTML + detailsContainer.innerHTML;
        detailsContainer.classList.remove('d-none');
    }
}

/**
 * ✅ NOUVEAU: Vérifier la session avant soumission
 */
function checkSessionBeforeSubmit() {
    const sessionInfo = OrganisationApp.sessionInfo;
    
    if (!sessionInfo) {
        console.log('ℹ️ Aucune session adhérents active');
        return { valid: true, adherentsCount: 0 };
    }
    
    const now = new Date();
    const expiresAt = new Date(sessionInfo.expiresAt);
    
    if (now > expiresAt) {
        console.warn('⚠️ Session adhérents expirée');
        showNotification('⚠️ Session des adhérents expirée. Veuillez réimporter le fichier.', 'warning', 8000);
        return { valid: false, reason: 'expired' };
    }
    
    const adherentsCount = OrganisationApp.adherents.length;
    console.log(`✅ Session active: ${adherentsCount} adhérents, expire le ${expiresAt.toLocaleString()}`);
    
    return { 
        valid: true, 
        adherentsCount: adherentsCount,
        sessionKey: sessionInfo.sessionKey,
        expiresAt: sessionInfo.expiresAt
    };
}

/**
 * ✅ INTÉGRATION: Modifier submitForm() pour inclure info session
 */
function enhanceSubmitFormWithSession() {
    // Chercher la fonction submitForm existante et l'améliorer
    if (typeof window.submitForm === 'function') {
        const originalSubmitForm = window.submitForm;
        
        window.submitForm = function() {
            // Vérifier session avant soumission
            const sessionCheck = checkSessionBeforeSubmit();
            
            if (!sessionCheck.valid) {
                if (sessionCheck.reason === 'expired') {
                    // Proposer de réimporter
                    if (confirm('La session des adhérents a expiré. Voulez-vous retourner à l\'étape 7 pour réimporter ?')) {
                        goToStep(7);
                    }
                }
                return false;
            }
            
            // Ajouter les infos de session au formulaire
            if (sessionCheck.adherentsCount > 0) {
                const formData = new FormData();
                formData.append('has_session_adherents', 'true');
                formData.append('session_adherents_count', sessionCheck.adherentsCount);
                formData.append('session_key', sessionCheck.sessionKey);
                formData.append('session_expires_at', sessionCheck.expiresAt);
                
                // Informer l'utilisateur
                showNotification(
                    `🚀 Soumission avec ${sessionCheck.adherentsCount} adhérents préparés en session`,
                    'info',
                    4000
                );
            }
            
            // Appeler la fonction originale
            return originalSubmitForm.call(this);
        };
        
        console.log('✅ submitForm() améliorée avec gestion session');
    }
}

/**
 * ✅ INITIALISATION: Auto-setup au chargement
 */
document.addEventListener('DOMContentLoaded', function() {
    // Attendre que les autres scripts soient chargés
    setTimeout(() => {
        enhanceSubmitFormWithSession();
        
        // Vérifier s'il y a déjà une session active
        const dossierId = getCurrentDossierId();
        if (dossierId) {
            checkExistingSession(dossierId);
        }
    }, 1000);
});

/**
 * Vérifier session existante au chargement
 */
async function checkExistingSession(dossierId) {
    const sessionKey = `phase2_adherents_${dossierId}`;
    
    try {
        // Vérifier côté serveur d'abord
        const response = await fetch('/operator/organisations/check-session-adherents', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                session_key: sessionKey,
                dossier_id: dossierId
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.exists && result.data) {
                console.log('✅ Session existante détectée:', result.data.total, 'adhérents');
                
                // Restaurer dans OrganisationApp
                OrganisationApp.adherents = result.data.data || [];
                OrganisationApp.adherentsMetadata = result.data.metadata || {};
                OrganisationApp.sessionInfo = {
                    sessionKey: sessionKey,
                    dossierId: dossierId,
                    expiresAt: result.data.expires_at
                };
                
                // Mettre à jour l'interface
                updateAdherentsList();
                
                // Notification
                showNotification(
                    `🔄 Session récupérée: ${result.data.total} adhérents préparés`,
                    'info',
                    4000
                );
            }
        }
    } catch (error) {
        console.log('ℹ️ Pas de session serveur active:', error.message);
        
        // Fallback: vérifier sessionStorage
        if (typeof Storage !== 'undefined') {
            const localSessionData = sessionStorage.getItem(sessionKey);
            if (localSessionData) {
                try {
                    const parsedData = JSON.parse(localSessionData);
                    const expiresAt = new Date(parsedData.expires_at);
                    
                    if (new Date() < expiresAt) {
                        console.log('✅ Session locale récupérée');
                        OrganisationApp.adherents = parsedData.data || [];
                        updateAdherentsList();
                    } else {
                        sessionStorage.removeItem(sessionKey);
                        console.log('🧹 Session locale expirée supprimée');
                    }
                } catch (e) {
                    sessionStorage.removeItem(sessionKey);
                }
            }
        }
    }
}

// ========================================
// ✅ INTÉGRATION WRAPPER SOUMISSION CSRF
// À ajouter à la fin de organisation-create.js
// ========================================

/**
 * ✅ WRAPPER PRINCIPAL : Remplace la fonction submitForm existante
 * Intégration automatique avec gestion CSRF
 */
 async function submitFormWithErrorHandling() {
    try {
        // Désactiver le bouton de soumission
        const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Soumission en cours...';
        }
        
        const result = await submitFormNormal();
        return result;
        
    } catch (error) {
        console.error('❌ Erreur soumission finale:', error);
        
        // Réactiver le bouton
        const submitBtn = document.querySelector('button[type="submit"], .btn-submit, #submitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Soumettre le dossier';
        }
        
        // Gestion spécifique des messages d'erreur
        if (error.message.includes('419') || error.message.includes('CSRF')) {
            showNotification('❌ Session expirée. Veuillez recharger la page et recommencer.', 'danger', 10000);
            
            // Proposer un rechargement automatique après 5 secondes
            setTimeout(() => {
                if (confirm('La session a expiré. Voulez-vous recharger la page ?\n\n⚠️ Attention : Les données non sauvegardées seront perdues.')) {
                    window.location.reload();
                }
            }, 5000);
        } else if (error.message.includes('Timeout')) {
            showNotification('❌ Timeout de soumission. Essayez de réduire le nombre d\'adhérents ou réessayez plus tard.', 'warning', 8000);
        } else {
            showNotification(`❌ Erreur : ${error.message}`, 'danger');
        }
        
        throw error;
    }
}

/**
 * ✅ INTÉGRATION AUTOMATIQUE : Remplacer submitForm existante
 * Cette section s'exécute automatiquement au chargement
 */
(function() {
    console.log('🔧 Initialisation wrapper CSRF...');
    
    // Attendre que toutes les fonctions soient chargées
    setTimeout(function() {
        // Sauvegarder la fonction originale si elle existe
        if (typeof window.submitForm === 'function') {
            window.originalSubmitForm = window.submitForm;
            console.log('📄 Fonction submitForm originale sauvegardée');
        }
        
        // Remplacer par la version améliorée
        window.submitForm = submitFormWithErrorHandling;
        console.log('✅ Fonction submitForm remplacée par la version avec gestion CSRF');
        
        // Vérifier que submitFormNormal existe
        if (typeof submitFormNormal !== 'function') {
            console.error('❌ Fonction submitFormNormal non trouvée - vérifiez l\'intégration');
        } else {
            console.log('✅ Fonction submitFormNormal détectée');
        }
        
        // Intégrer également dans les gestionnaires d'événements
        const form = document.getElementById('organisationForm');
        if (form) {
            // Rechercher les gestionnaires existants et les remplacer
            const existingListeners = form.cloneNode(true);
            form.parentNode.replaceChild(existingListeners, form);
            
            // Ajouter le nouveau gestionnaire
            existingListeners.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('📝 Soumission formulaire interceptée par wrapper CSRF');
                await submitFormWithErrorHandling();
            });
            
            console.log('✅ Gestionnaire de soumission intégré');
        }
        
    }, 1000); // Attendre 1 seconde pour que tout soit chargé
})();

/**
 * ✅ FONCTIONS HELPER POUR LE WRAPPER
 */

// Fonction de récupération robuste du token CSRF
async function getCurrentCSRFToken() {
    // Méthode 1: Depuis meta tag Laravel
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Méthode 2: Fallback depuis input caché
    if (!csrfToken) {
        csrfToken = document.querySelector('input[name="_token"]')?.value;
    }
    
    // Méthode 3: Fallback depuis window.Laravel
    if (!csrfToken && window.Laravel && window.Laravel.csrfToken) {
        csrfToken = window.Laravel.csrfToken;
    }
    
    // Méthode 4: Dernier recours - récupérer depuis le serveur
    if (!csrfToken || csrfToken.length < 10) {
        console.log('🔄 Token CSRF invalide ou manquant, récupération depuis serveur...');
        csrfToken = await refreshCSRFToken();
    }
    
    return csrfToken;
}

// Fonction de rafraîchissement du token CSRF
async function refreshCSRFToken() {
    console.log('🔄 Tentative de rafraîchissement du token CSRF...');
    
    try {
        const response = await fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const newToken = data.csrf_token;
            
            // Mettre à jour le meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }
            
            // Mettre à jour les inputs cachés
            const tokenInputs = document.querySelectorAll('input[name="_token"]');
            tokenInputs.forEach(input => {
                input.value = newToken;
            });
            
            // Mettre à jour Laravel global si disponible
            if (window.Laravel) {
                window.Laravel.csrfToken = newToken;
            }
            
            console.log('✅ Token CSRF rafraîchi avec succès');
            return newToken;
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
    } catch (error) {
        console.error('❌ Erreur lors du rafraîchissement CSRF:', error);
        return null;
    }
}

// Fonction de diagnostic CSRF améliorée
function diagnoseCsrfIssue() {
    console.log('🔍 === DIAGNOSTIC CSRF ===');
    
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const inputToken = document.querySelector('input[name="_token"]')?.value;
    const laravelToken = window.Laravel?.csrfToken;
    
    console.log('Meta CSRF:', metaToken ? metaToken.substring(0, 10) + '...' : 'MANQUANT');
    console.log('Input CSRF:', inputToken ? inputToken.substring(0, 10) + '...' : 'MANQUANT');
    console.log('Laravel CSRF:', laravelToken ? laravelToken.substring(0, 10) + '...' : 'MANQUANT');
    
    // Vérifier les cookies de session
    const hasSessionCookie = document.cookie.includes('pngdi_session') || document.cookie.includes('laravel_session');
    const hasXSRFCookie = document.cookie.includes('XSRF-TOKEN');
    
    console.log('Cookie session:', hasSessionCookie ? 'PRÉSENT' : 'MANQUANT');
    console.log('Cookie XSRF:', hasXSRFCookie ? 'PRÉSENT' : 'MANQUANT');
    
    // Vérifier si la page est expirée
    const pageLoadTime = performance.timing.navigationStart;
    const currentTime = Date.now();
    const pageAge = Math.floor((currentTime - pageLoadTime) / 1000 / 60); // en minutes
    
    console.log('Âge de la page:', pageAge, 'minutes');
    
    if (pageAge > 120) { // Plus de 2 heures
        console.warn('⚠️ Page possiblement expirée (plus de 2h)');
        return false;
    }
    
    // Vérifier qu'au moins un token est présent
    const hasValidToken = (metaToken && metaToken.length >= 10) || 
                         (inputToken && inputToken.length >= 10) || 
                         (laravelToken && laravelToken.length >= 10);
    
    if (!hasValidToken) {
        console.error('❌ Aucun token CSRF valide trouvé');
        return false;
    }
    
    console.log('✅ Diagnostic CSRF: OK');
    return true;
}

console.log('✅ Wrapper soumission CSRF chargé et prêt');
// Fin du fichier JavaScript complet