/**
 * ========================================================================
 * VALIDATION-ENGINE.JS - MODULE DE VALIDATION AUTONOME SGLP
 * Version: 1.0 - Module spécialisé pour validation données adhérents
 * ========================================================================
 * 
 * Module de validation réutilisable pour toutes les interfaces SGLP
 * Compatible avec confirmation.blade.php, create.blade.php et chunking
 * 
 * Fonctionnalités principales :
 * - Validation NIP gabonais XX-QQQQ-YYYYMMDD avec extraction d'âge
 * - Classification automatique des anomalies (critique/majeure/mineure)
 * - Détection de doublons intelligente avec algorithmes avancés
 * - Validation téléphone gabonais avec formats locaux
 * - Rapport de validation détaillé avec recommandations
 * - Cache de validation pour optimiser les performances
 */

window.ValidationEngine = window.ValidationEngine || {};

// ========================================
// CONFIGURATION ET CONSTANTES
// ========================================

window.ValidationEngine.config = {
    // ✅ FORMAT NIP GABONAIS : XX-QQQQ-YYYYMMDD
    nipFormat: /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/,
    
    // Validation âge
    ageMin: 18,
    ageMax: 120,
    yearMin: 1900,
    
    // Patterns téléphone gabonais
    phonePatterns: {
        fixe: /^(\+241)?[01][0-9]{7}$/,     // 01XXXXXXX
        mobile: /^(\+241)?[67][0-9]{7}$/,   // 6XXXXXXXX ou 7XXXXXXXX
        international: /^(\+241)[0-9]{8}$/  // +241XXXXXXXX
    },
    
    // Email pattern
    emailPattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    
    // Civilités autorisées
    civilites: ['M.', 'Mme', 'Mlle'],
    
    // Seuils de détection doublons
    duplicateThresholds: {
        nip: 1.0,        // Exact match
        phone: 0.9,      // Très proche
        email: 0.95,     // Quasi exact
        identity: 0.85   // Nom + prénom similaires
    },
    
    // Cache pour optimisation
    cacheEnabled: true,
    cacheSize: 1000
};

// Cache interne pour validation
window.ValidationEngine.cache = new Map();
window.ValidationEngine.statistics = {
    validations: 0,
    cacheHits: 0,
    errorsFound: 0,
    anomaliesFound: 0
};

// ========================================
// FONCTIONS DE VALIDATION PRINCIPALES
// ========================================

/**
 * Valider un adherent complet avec toutes les règles SGLP
 * @param {Object} adherent - Données adherent à valider
 * @param {Array} existingAdherents - Liste des adherents existants pour détection doublons
 * @param {Object} options - Options de validation
 * @returns {Object} Résultat de validation détaillé
 */
window.ValidationEngine.validateAdherent = function(adherent, existingAdherents = [], options = {}) {
    const config = {
        skipDuplicates: false,
        strictMode: true,
        enableCache: true,
        ...options
    };
    
    // Vérifier cache si activé
    const cacheKey = this.generateCacheKey(adherent, config);
    if (config.enableCache && this.cache.has(cacheKey)) {
        this.statistics.cacheHits++;
        return this.cache.get(cacheKey);
    }
    
    this.statistics.validations++;
    
    const result = {
        isValid: true,
        errors: [],
        anomalies: [],
        warnings: [],
        metadata: {
            validatedAt: new Date().toISOString(),
            validator: 'ValidationEngine SGLP v1.0',
            adherentId: adherent.id || null
        },
        duplicates: [],
        score: 0
    };
    
    // 1. Validation NIP gabonais (CRITIQUE)
    const nipValidation = this.validateNIP(adherent.nip);
    if (!nipValidation.isValid) {
        result.errors.push(...nipValidation.errors);
        result.anomalies.push(...nipValidation.anomalies);
        result.isValid = false;
    }
    
    // 2. Validation champs obligatoires
    const requiredFields = this.validateRequiredFields(adherent);
    if (!requiredFields.isValid) {
        result.errors.push(...requiredFields.errors);
        result.anomalies.push(...requiredFields.anomalies);
        result.isValid = false;
    }
    
    // 3. Validation email
    const emailValidation = this.validateEmail(adherent.email);
    if (!emailValidation.isValid) {
        result.anomalies.push(...emailValidation.anomalies);
        if (emailValidation.severity === 'critique') {
            result.isValid = false;
        }
    }
    
    // 4. Validation téléphone gabonais
    const phoneValidation = this.validatePhoneGabon(adherent.telephone);
    if (!phoneValidation.isValid) {
        result.anomalies.push(...phoneValidation.anomalies);
    }
    
    // 5. Validation civilité
    const civiliteValidation = this.validateCivilite(adherent.civilite);
    if (!civiliteValidation.isValid) {
        result.anomalies.push(...civiliteValidation.anomalies);
    }
    
    // 6. Détection doublons si activée
    if (!config.skipDuplicates && existingAdherents.length > 0) {
        const duplicates = this.detectDuplicates(adherent, existingAdherents);
        result.duplicates = duplicates;
        
        // Marquer comme erreur si doublons critiques
        const criticalDuplicates = duplicates.filter(d => d.severity === 'critique');
        if (criticalDuplicates.length > 0) {
            result.errors.push('Doublon détecté - Adherent déjà existant');
            result.isValid = false;
        }
    }
    
    // 7. Calcul score de qualité (0-100)
    result.score = this.calculateQualityScore(result);
    
    // 8. Mise en cache si activé
    if (config.enableCache) {
        this.addToCache(cacheKey, result);
    }
    
    // 9. Mise à jour statistiques
    if (!result.isValid) this.statistics.errorsFound++;
    if (result.anomalies.length > 0) this.statistics.anomaliesFound++;
    
    return result;
};

// ========================================
// VALIDATION NIP GABONAIS SPÉCIALISÉE
// ========================================

/**
 * Validation complète du NIP gabonais XX-QQQQ-YYYYMMDD
 * @param {string} nip - NIP à valider
 * @returns {Object} Résultat validation NIP
 */
window.ValidationEngine.validateNIP = function(nip) {
    const result = {
        isValid: true,
        errors: [],
        anomalies: [],
        extractedData: null
    };
    
    // Vérification présence
    if (!nip || typeof nip !== 'string') {
        result.isValid = false;
        result.errors.push('NIP manquant ou invalide');
        result.anomalies.push({
            field: 'nip',
            type: 'champ_manquant',
            severity: 'critique',
            message: 'NIP obligatoire manquant',
            suggestion: 'Format attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
        });
        return result;
    }
    
    // Nettoyage et normalisation
    const cleanNip = nip.trim().toUpperCase();
    
    // Vérification format général
    if (!this.config.nipFormat.test(cleanNip)) {
        result.isValid = false;
        result.errors.push('Format NIP invalide');
        result.anomalies.push({
            field: 'nip',
            type: 'format_invalide',
            severity: 'critique',
            message: `Format NIP invalide: ${cleanNip}`,
            suggestion: 'Format attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
        });
        return result;
    }
    
    // Extraction des composants
    const parts = cleanNip.split('-');
    const prefix = parts[0];      // XX
    const middle = parts[1];      // QQQQ
    const datePart = parts[2];    // YYYYMMDD
    
    // Validation de la date
    const year = parseInt(datePart.substring(0, 4));
    const month = parseInt(datePart.substring(4, 6));
    const day = parseInt(datePart.substring(6, 8));
    
    const currentYear = new Date().getFullYear();
    
    // Validation année
    if (year < this.config.yearMin || year > currentYear) {
        result.anomalies.push({
            field: 'nip',
            type: 'annee_invalide',
            severity: 'majeure',
            message: `Année de naissance invalide: ${year}`,
            suggestion: `Année doit être entre ${this.config.yearMin} et ${currentYear}`
        });
    }
    
    // Validation mois
    if (month < 1 || month > 12) {
        result.anomalies.push({
            field: 'nip',
            type: 'mois_invalide',
            severity: 'majeure',
            message: `Mois invalide: ${month}`,
            suggestion: 'Mois doit être entre 01 et 12'
        });
    }
    
    // Validation jour
    if (day < 1 || day > 31) {
        result.anomalies.push({
            field: 'nip',
            type: 'jour_invalide',
            severity: 'majeure',
            message: `Jour invalide: ${day}`,
            suggestion: 'Jour doit être entre 01 et 31'
        });
    }
    
    // Validation date complète
    try {
        const birthDate = new Date(year, month - 1, day);
        const age = Math.floor((new Date() - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
        
        // Vérification âge minimum
        if (age < this.config.ageMin) {
            result.isValid = false;
            result.errors.push(`Âge insuffisant: ${age} ans`);
            result.anomalies.push({
                field: 'nip',
                type: 'age_mineur',
                severity: 'critique',
                message: `Personne mineure (${age} ans) - non autorisée`,
                suggestion: `Âge minimum requis: ${this.config.ageMin} ans`
            });
        }
        
        // Vérification âge maximum
        if (age > this.config.ageMax) {
            result.anomalies.push({
                field: 'nip',
                type: 'age_suspect',
                severity: 'majeure',
                message: `Âge suspect: ${age} ans`,
                suggestion: 'Vérifier la date de naissance'
            });
        }
        
        // Extraction données pour utilisation ultérieure
        result.extractedData = {
            prefix: prefix,
            middle: middle,
            birthDate: birthDate,
            age: age,
            isAdult: age >= this.config.ageMin,
            formattedDate: `${day.toString().padStart(2, '0')}/${month.toString().padStart(2, '0')}/${year}`
        };
        
    } catch (error) {
        result.anomalies.push({
            field: 'nip',
            type: 'date_invalide',
            severity: 'majeure',
            message: 'Date de naissance invalide dans NIP',
            suggestion: 'Vérifier le format de date YYYYMMDD'
        });
    }
    
    return result;
};

// ========================================
// VALIDATION CHAMPS OBLIGATOIRES
// ========================================

/**
 * Validation des champs obligatoires
 */
window.ValidationEngine.validateRequiredFields = function(adherent) {
    const result = { isValid: true, errors: [], anomalies: [] };
    const requiredFields = ['nom', 'prenom', 'nip', 'civilite'];
    
    requiredFields.forEach(field => {
        if (!adherent[field] || (typeof adherent[field] === 'string' && adherent[field].trim() === '')) {
            result.isValid = false;
            result.errors.push(`Champ obligatoire manquant: ${field}`);
            result.anomalies.push({
                field: field,
                type: 'champ_manquant',
                severity: 'critique',
                message: `Champ "${field}" obligatoire manquant`,
                suggestion: `Veuillez renseigner le champ "${field}"`
            });
        }
    });
    
    return result;
};

// ========================================
// VALIDATION EMAIL
// ========================================

/**
 * Validation email avec règles spécifiques
 */
window.ValidationEngine.validateEmail = function(email) {
    const result = { isValid: true, anomalies: [], severity: 'mineure' };
    
    if (!email || email.trim() === '') {
        // Email optionnel, pas d'erreur si vide
        return result;
    }
    
    const cleanEmail = email.trim().toLowerCase();
    
    if (!this.config.emailPattern.test(cleanEmail)) {
        result.isValid = false;
        result.anomalies.push({
            field: 'email',
            type: 'format_invalide',
            severity: 'mineure',
            message: `Format email invalide: ${email}`,
            suggestion: 'Format attendu: nom@domaine.com'
        });
    }
    
    // Vérifications supplémentaires
    if (cleanEmail.length > 100) {
        result.anomalies.push({
            field: 'email',
            type: 'longueur_excessive',
            severity: 'mineure',
            message: 'Email trop long (max 100 caractères)',
            suggestion: 'Utiliser un email plus court'
        });
    }
    
    // Domaines suspects (optionnel)
    const suspiciousDomains = ['example.com', 'test.com', 'fake.com'];
    const domain = cleanEmail.split('@')[1];
    if (suspiciousDomains.includes(domain)) {
        result.anomalies.push({
            field: 'email',
            type: 'domaine_suspect',
            severity: 'majeure',
            message: `Domaine email suspect: ${domain}`,
            suggestion: 'Utiliser un email professionnel'
        });
    }
    
    return result;
};

// ========================================
// VALIDATION TÉLÉPHONE GABONAIS
// ========================================

/**
 * Validation téléphone avec formats gabonais
 */
window.ValidationEngine.validatePhoneGabon = function(telephone) {
    const result = { isValid: true, anomalies: [] };
    
    if (!telephone || telephone.trim() === '') {
        // Téléphone optionnel
        return result;
    }
    
    const cleanPhone = telephone.replace(/[\s\-\(\)]/g, '');
    
    // Vérifier patterns gabonais
    const patterns = Object.values(this.config.phonePatterns);
    const isValidGabonPhone = patterns.some(pattern => pattern.test(cleanPhone));
    
    if (!isValidGabonPhone) {
        result.isValid = false;
        result.anomalies.push({
            field: 'telephone',
            type: 'format_invalide',
            severity: 'mineure',
            message: `Téléphone invalide: ${telephone}`,
            suggestion: 'Formats acceptés: 01XXXXXXX, 6XXXXXXXX, 7XXXXXXXX ou +241XXXXXXXX'
        });
    }
    
    return result;
};

// ========================================
// VALIDATION CIVILITÉ
// ========================================

/**
 * Validation civilité
 */
window.ValidationEngine.validateCivilite = function(civilite) {
    const result = { isValid: true, anomalies: [] };
    
    if (!civilite || !this.config.civilites.includes(civilite)) {
        result.isValid = false;
        result.anomalies.push({
            field: 'civilite',
            type: 'valeur_invalide',
            severity: 'majeure',
            message: `Civilité invalide: ${civilite}`,
            suggestion: `Valeurs acceptées: ${this.config.civilites.join(', ')}`
        });
    }
    
    return result;
};

// ========================================
// DÉTECTION DE DOUBLONS AVANCÉE
// ========================================

/**
 * Détection intelligente de doublons avec algorithmes de similarité
 */
window.ValidationEngine.detectDuplicates = function(adherent, existingAdherents) {
    const duplicates = [];
    
    existingAdherents.forEach((existing, index) => {
        const similarities = this.calculateSimilarities(adherent, existing);
        
        // Doublon exact par NIP (critique)
        if (similarities.nip >= this.config.duplicateThresholds.nip) {
            duplicates.push({
                type: 'nip_identique',
                severity: 'critique',
                index: index,
                score: similarities.nip,
                message: `NIP identique détecté: ${adherent.nip}`,
                existing: existing
            });
        }
        
        // Doublon par téléphone (majeur)
        if (similarities.phone >= this.config.duplicateThresholds.phone) {
            duplicates.push({
                type: 'telephone_similaire',
                severity: 'majeure',
                index: index,
                score: similarities.phone,
                message: `Téléphone similaire détecté: ${adherent.telephone}`,
                existing: existing
            });
        }
        
        // Doublon par email (majeur)
        if (similarities.email >= this.config.duplicateThresholds.email) {
            duplicates.push({
                type: 'email_similaire',
                severity: 'majeure',
                index: index,
                score: similarities.email,
                message: `Email similaire détecté: ${adherent.email}`,
                existing: existing
            });
        }
        
        // Doublon par identité (mineur)
        if (similarities.identity >= this.config.duplicateThresholds.identity) {
            duplicates.push({
                type: 'identite_similaire',
                severity: 'mineure',
                index: index,
                score: similarities.identity,
                message: `Identité similaire: ${adherent.nom} ${adherent.prenom}`,
                existing: existing
            });
        }
    });
    
    return duplicates;
};

/**
 * Calcul de similarités entre deux adherents
 */
window.ValidationEngine.calculateSimilarities = function(adherent1, adherent2) {
    return {
        nip: this.exactMatch(adherent1.nip, adherent2.nip),
        phone: this.normalizedMatch(adherent1.telephone, adherent2.telephone),
        email: this.normalizedMatch(adherent1.email, adherent2.email),
        identity: this.identitySimilarity(adherent1, adherent2)
    };
};

/**
 * Match exact (0 ou 1)
 */
window.ValidationEngine.exactMatch = function(val1, val2) {
    if (!val1 || !val2) return 0;
    return val1.trim().toLowerCase() === val2.trim().toLowerCase() ? 1 : 0;
};

/**
 * Match normalisé avec tolérance
 */
window.ValidationEngine.normalizedMatch = function(val1, val2) {
    if (!val1 || !val2) return 0;
    
    const clean1 = val1.replace(/[\s\-\(\)+]/g, '').toLowerCase();
    const clean2 = val2.replace(/[\s\-\(\)+]/g, '').toLowerCase();
    
    if (clean1 === clean2) return 1;
    
    // Calcul distance de Levenshtein normalisée
    const distance = this.levenshteinDistance(clean1, clean2);
    const maxLength = Math.max(clean1.length, clean2.length);
    
    return maxLength === 0 ? 0 : 1 - (distance / maxLength);
};

/**
 * Similarité d'identité (nom + prénom)
 */
window.ValidationEngine.identitySimilarity = function(adherent1, adherent2) {
    const name1 = `${adherent1.nom || ''} ${adherent1.prenom || ''}`.trim().toLowerCase();
    const name2 = `${adherent2.nom || ''} ${adherent2.prenom || ''}`.trim().toLowerCase();
    
    return this.normalizedMatch(name1, name2);
};

/**
 * Distance de Levenshtein pour calcul de similarité
 */
window.ValidationEngine.levenshteinDistance = function(str1, str2) {
    const matrix = [];
    
    for (let i = 0; i <= str2.length; i++) {
        matrix[i] = [i];
    }
    
    for (let j = 0; j <= str1.length; j++) {
        matrix[0][j] = j;
    }
    
    for (let i = 1; i <= str2.length; i++) {
        for (let j = 1; j <= str1.length; j++) {
            if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }
    
    return matrix[str2.length][str1.length];
};

// ========================================
// CALCUL SCORE DE QUALITÉ
// ========================================

/**
 * Calcul du score de qualité des données (0-100)
 */
window.ValidationEngine.calculateQualityScore = function(validationResult) {
    let score = 100;
    
    // Pénalités par type d'anomalie
    validationResult.anomalies.forEach(anomalie => {
        switch (anomalie.severity) {
            case 'critique':
                score -= 25;
                break;
            case 'majeure':
                score -= 10;
                break;
            case 'mineure':
                score -= 5;
                break;
        }
    });
    
    // Pénalités pour erreurs
    score -= validationResult.errors.length * 20;
    
    // Bonus pour champs optionnels remplis
    if (validationResult.metadata.adherentId) score += 5;
    
    return Math.max(0, Math.min(100, score));
};

// ========================================
// GESTION DU CACHE
// ========================================

/**
 * Génération clé de cache unique
 */
window.ValidationEngine.generateCacheKey = function(adherent, options) {
    const keyData = {
        nip: adherent.nip || '',
        nom: adherent.nom || '',
        prenom: adherent.prenom || '',
        options: JSON.stringify(options)
    };
    
    return btoa(JSON.stringify(keyData)).replace(/[^a-zA-Z0-9]/g, '').substring(0, 32);
};

/**
 * Ajout au cache avec gestion de la taille
 */
window.ValidationEngine.addToCache = function(key, result) {
    if (this.cache.size >= this.config.cacheSize) {
        // Supprimer le plus ancien
        const firstKey = this.cache.keys().next().value;
        this.cache.delete(firstKey);
    }
    
    this.cache.set(key, result);
};

// ========================================
// FONCTIONS UTILITAIRES ET RAPPORTS
// ========================================

/**
 * Générer un rapport de validation détaillé
 */
window.ValidationEngine.generateValidationReport = function(validationResults) {
    const report = {
        summary: {
            total: validationResults.length,
            valid: 0,
            invalid: 0,
            score_moyen: 0
        },
        anomalies: {
            critiques: 0,
            majeures: 0,
            mineures: 0
        },
        duplicates: {
            total: 0,
            by_type: {}
        },
        recommendations: [],
        generated_at: new Date().toISOString()
    };
    
    let totalScore = 0;
    
    validationResults.forEach(result => {
        if (result.isValid) {
            report.summary.valid++;
        } else {
            report.summary.invalid++;
        }
        
        totalScore += result.score;
        
        // Compter anomalies
        result.anomalies.forEach(anomalie => {
            report.anomalies[anomalie.severity]++;
        });
        
        // Compter doublons
        result.duplicates.forEach(duplicate => {
            report.duplicates.total++;
            report.duplicates.by_type[duplicate.type] = 
                (report.duplicates.by_type[duplicate.type] || 0) + 1;
        });
    });
    
    // Calcul score moyen
    report.summary.score_moyen = validationResults.length > 0 ? 
        Math.round(totalScore / validationResults.length) : 0;
    
    // Génération recommandations
    if (report.anomalies.critiques > 0) {
        report.recommendations.push('⚠️ Corriger les anomalies critiques avant import');
    }
    
    if (report.summary.score_moyen < 70) {
        report.recommendations.push('📊 Score de qualité faible - réviser les données source');
    }
    
    if (report.duplicates.total > report.summary.total * 0.1) {
        report.recommendations.push('👥 Nombreux doublons détectés - vérifier la source des données');
    }
    
    return report;
};

/**
 * Vider le cache de validation
 */
window.ValidationEngine.clearCache = function() {
    this.cache.clear();
    console.log('🧹 Cache de validation vidé');
};

/**
 * Obtenir les statistiques du moteur
 */
window.ValidationEngine.getStatistics = function() {
    return {
        ...this.statistics,
        cache_size: this.cache.size,
        cache_hit_rate: this.statistics.validations > 0 ? 
            Math.round((this.statistics.cacheHits / this.statistics.validations) * 100) : 0
    };
};

// ========================================
// INITIALISATION ET EXPORT
// ========================================

/**
 * Initialisation du moteur de validation
 */
window.ValidationEngine.init = function() {
    console.log('🔍 Initialisation ValidationEngine SGLP v1.0');
    
    // Vérification des dépendances (aucune requise)
    console.log('✅ Module autonome - aucune dépendance requise');
    
    // Réinitialiser statistiques
    this.statistics = {
        validations: 0,
        cacheHits: 0,
        errorsFound: 0,
        anomaliesFound: 0
    };
    
    return true;
};

// Auto-initialisation
document.addEventListener('DOMContentLoaded', function() {
    window.ValidationEngine.init();
});

// Export pour modules ES6 si supporté
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.ValidationEngine;
}

console.log(`
🎉 ========================================================================
   VALIDATION-ENGINE.JS v1.0 - MODULE COMPLET SGLP
   ========================================================================
   
   ✅ Module de validation autonome pour adhérents SGLP
   🇬🇦 Format NIP gabonais XX-QQQQ-YYYYMMDD avec extraction d'âge
   🔍 Classification anomalies (critique/majeure/mineure)
   👥 Détection doublons intelligente avec algorithmes avancés
   📞 Validation téléphone gabonais (fixe/mobile)
   📧 Validation email avec domaines suspects
   💾 Cache de validation pour optimisation performance
   📊 Génération rapports détaillés avec recommandations
   
   🚀 FONCTIONNALITÉS PRINCIPALES :
   ✅ Validation complète adhérent avec score qualité
   ✅ Extraction automatique âge depuis NIP
   ✅ Détection doublons par NIP/téléphone/email/identité
   ✅ Cache intelligent pour performance optimale
   ✅ Rapport validation avec recommandations
   ✅ Compatible avec tous les modules SGLP
   ✅ Module entièrement autonome sans dépendances
   
   🎯 Prêt pour intégration avec confirmation.blade.php
   📦 Module réutilisable pour toutes interfaces SGLP
   🇬🇦 Optimisé pour l'administration gabonaise
========================================================================
`);