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