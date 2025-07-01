

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
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${pageAdherents.map((adherent, index) => `
                            <tr ${adherent.hasAnomalies ? 'class="table-warning"' : ''}>
                                <td>${adherent.civilite}</td>
                                <td><strong>${adherent.nom} ${adherent.prenom}</strong></td>
                                <td><code>${adherent.nip}</code></td>
                                <td>${adherent.telephone || '-'}</td>
                                <td>${adherent.profession || '-'}</td>
                                <td>
                                    ${adherent.hasAnomalies ? 
                                        `<span class="badge bg-danger" title="${adherent.anomalies[0]?.details || 'Anomalie détectée'}">Anomalie</span>` : 
                                        `<span class="badge bg-success">Valide</span>`
                                    }
                                </td>
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
        // Compter les adhérents valides et avec anomalies
        const valides = OrganisationApp.adherents.filter(a => !a.hasAnomalies).length;
        const anomalies = OrganisationApp.adherents.filter(a => a.hasAnomalies).length;
        
        if (anomalies > 0) {
            countSpan.innerHTML = `${OrganisationApp.adherents.length} adhérent(s) <small class="text-muted">(${valides} valides, ${anomalies} anomalies)</small>`;
        } else {
            countSpan.textContent = `${OrganisationApp.adherents.length} adhérent(s)`;
        }
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
        
        // Valider et traiter les données avec système d'anomalies
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