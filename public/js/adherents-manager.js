/**
 * ============================================================================
 * ADHERENTS-MANAGER.JS - GESTIONNAIRE D'ADHÉRENTS DYNAMIQUE
 * Version: 2.0 - Tableau interactif avec filtres et pagination
 * ============================================================================
 * 
 * Module pour gérer l'affichage, la recherche, le filtrage et la pagination
 * des adhérents dans l'interface confirmation.blade.php
 */

window.AdherentsManager = window.AdherentsManager || {};

// ============================================================================
// CONFIGURATION ET ÉTAT
// ============================================================================

/**
 * Configuration du gestionnaire
 */
window.AdherentsManager.config = {
    tableId: 'adherents-table',
    tbodyId: 'adherents-tbody',
    paginationId: 'adherents-pagination',
    emptyStateId: 'empty-state',
    
    // Pagination
    itemsPerPage: 20,
    maxPaginationButtons: 5,
    
    // Recherche
    searchDelay: 300,
    minSearchLength: 2,
    
    // Colonnes du tableau
    columns: [
        { key: 'select', label: '', sortable: false, width: '50px' },
        { key: 'nip', label: 'NIP', sortable: true, width: '140px' },
        { key: 'nom_complet', label: 'Nom Complet', sortable: true, width: '200px' },
        { key: 'telephone', label: 'Téléphone', sortable: true, width: '120px' },
        { key: 'profession', label: 'Profession', sortable: true, width: '150px' },
        { key: 'source', label: 'Source', sortable: true, width: '100px' },
        { key: 'statut', label: 'Statut', sortable: true, width: '100px' },
        { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
    ]
};

/**
 * État du gestionnaire
 */
window.AdherentsManager.state = {
    currentPage: 1,
    itemsPerPage: 20,
    totalItems: 0,
    totalPages: 0,
    
    // Tri
    sortColumn: 'nom_complet',
    sortDirection: 'asc',
    
    // Filtres
    searchQuery: '',
    filters: {
        statut: '',
        source: '',
        profession: ''
    },
    
    // Sélection
    selectedItems: new Set(),
    selectAll: false,
    
    // Données
    allAdherents: [],
    filteredAdherents: [],
    currentPageAdherents: [],
    
    // UI
    isLoading: false,
    searchTimeout: null
};

// ============================================================================
// INITIALISATION
// ============================================================================

/**
 * Initialiser le gestionnaire d'adhérents
 */
window.AdherentsManager.init = function() {
    console.log('🔧 Initialisation AdherentsManager v2.0');
    
    try {
        // Initialiser les événements
        this.setupEventHandlers();
        
        // Configurer la recherche
        this.setupSearch();
        
        // Configurer les filtres
        this.setupFilters();
        
        // Charger les données initiales
        this.loadInitialData();
        
        // Rafraîchir l'affichage
        this.refreshTable();
        
        console.log('✅ AdherentsManager initialisé avec succès');
        
    } catch (error) {
        console.error('❌ Erreur initialisation AdherentsManager:', error);
    }
};

/**
 * Charger les données initiales
 */
window.AdherentsManager.loadInitialData = function() {
    if (window.ConfirmationApp && window.ConfirmationApp.state.adherentsData) {
        this.state.allAdherents = [...window.ConfirmationApp.state.adherentsData];
        console.log(`📊 ${this.state.allAdherents.length} adhérents chargés`);
    }
};

/**
 * Configurer les gestionnaires d'événements
 */
window.AdherentsManager.setupEventHandlers = function() {
    // Gestionnaire de sélection globale
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (e) => {
            this.handleSelectAll(e.target.checked);
        });
    }
    
    // Gestionnaires de tri sur les en-têtes
    this.setupSortHandlers();
    
    // Gestionnaires de pagination
    this.setupPaginationHandlers();
    
    // Gestionnaires d'actions en lot
    this.setupBulkActionHandlers();
};

/**
 * Configurer la recherche
 */
window.AdherentsManager.setupSearch = function() {
    const searchInput = document.getElementById('adherents-search');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(this.state.searchTimeout);
        
        this.state.searchTimeout = setTimeout(() => {
            this.handleSearch(e.target.value);
        }, this.config.searchDelay);
    });
    
    // Recherche instantanée sur Entrée
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            clearTimeout(this.state.searchTimeout);
            this.handleSearch(e.target.value);
        }
    });
};

/**
 * Configurer les filtres
 */
window.AdherentsManager.setupFilters = function() {
    const filterInputs = ['filter-statut', 'filter-source'];
    
    filterInputs.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', (e) => {
                const filterKey = filterId.replace('filter-', '');
                this.handleFilter(filterKey, e.target.value);
            });
        }
    });
    
    // Bouton effacer filtres
    const clearFiltersBtn = document.querySelector('[onclick="clearFilters()"]');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => this.clearAllFilters());
    }
};

// ============================================================================
// GESTION DES DONNÉES
// ============================================================================

/**
 * Rafraîchir le tableau
 */
window.AdherentsManager.refreshTable = function() {
    this.loadInitialData();
    this.applyFiltersAndSearch();
    this.applySorting();
    this.calculatePagination();
    this.renderTable();
    this.renderPagination();
    this.updateCounter();
};

/**
 * Appliquer les filtres et la recherche
 */
window.AdherentsManager.applyFiltersAndSearch = function() {
    let filtered = [...this.state.allAdherents];
    
    // Appliquer la recherche
    if (this.state.searchQuery && this.state.searchQuery.length >= this.config.minSearchLength) {
        const query = this.state.searchQuery.toLowerCase();
        filtered = filtered.filter(adherent => 
            adherent.nom.toLowerCase().includes(query) ||
            adherent.prenom.toLowerCase().includes(query) ||
            adherent.nip.toLowerCase().includes(query) ||
            (adherent.telephone && adherent.telephone.includes(query)) ||
            (adherent.profession && adherent.profession.toLowerCase().includes(query))
        );
    }
    
    // Appliquer les filtres
    Object.keys(this.state.filters).forEach(filterKey => {
        const filterValue = this.state.filters[filterKey];
        if (filterValue) {
            filtered = filtered.filter(adherent => {
                if (filterKey === 'statut') {
                    return adherent.statut === filterValue;
                } else if (filterKey === 'source') {
                    return adherent.source === filterValue;
                }
                return true;
            });
        }
    });
    
    this.state.filteredAdherents = filtered;
    this.state.totalItems = filtered.length;
};

/**
 * Appliquer le tri
 */
window.AdherentsManager.applySorting = function() {
    if (!this.state.sortColumn) return;
    
    this.state.filteredAdherents.sort((a, b) => {
        let valueA, valueB;
        
        switch (this.state.sortColumn) {
            case 'nom_complet':
                valueA = `${a.prenom} ${a.nom}`.toLowerCase();
                valueB = `${b.prenom} ${b.nom}`.toLowerCase();
                break;
            case 'nip':
                valueA = a.nip;
                valueB = b.nip;
                break;
            case 'telephone':
                valueA = a.telephone || '';
                valueB = b.telephone || '';
                break;
            case 'profession':
                valueA = a.profession || '';
                valueB = b.profession || '';
                break;
            case 'source':
                valueA = a.source || '';
                valueB = b.source || '';
                break;
            case 'statut':
                valueA = a.statut || '';
                valueB = b.statut || '';
                break;
            default:
                return 0;
        }
        
        if (valueA < valueB) return this.state.sortDirection === 'asc' ? -1 : 1;
        if (valueA > valueB) return this.state.sortDirection === 'asc' ? 1 : -1;
        return 0;
    });
};

/**
 * Calculer la pagination
 */
window.AdherentsManager.calculatePagination = function() {
    this.state.totalPages = Math.ceil(this.state.totalItems / this.state.itemsPerPage);
    
    // Vérifier que la page actuelle est valide
    if (this.state.currentPage > this.state.totalPages) {
        this.state.currentPage = Math.max(1, this.state.totalPages);
    }
    
    // Calculer les éléments de la page actuelle
    const startIndex = (this.state.currentPage - 1) * this.state.itemsPerPage;
    const endIndex = startIndex + this.state.itemsPerPage;
    
    this.state.currentPageAdherents = this.state.filteredAdherents.slice(startIndex, endIndex);
};

// ============================================================================
// RENDU DE L'INTERFACE
// ============================================================================

/**
 * Rendre le tableau
 */
window.AdherentsManager.renderTable = function() {
    const tbody = document.getElementById(this.config.tbodyId);
    const emptyState = document.getElementById(this.config.emptyStateId);
    
    if (!tbody) return;
    
    // Afficher l'état vide si nécessaire
    if (this.state.currentPageAdherents.length === 0) {
        tbody.innerHTML = '';
        if (emptyState) {
            emptyState.classList.add('show');
        }
        return;
    }
    
    // Masquer l'état vide
    if (emptyState) {
        emptyState.classList.remove('show');
    }
    
    // Générer les lignes du tableau
    tbody.innerHTML = this.state.currentPageAdherents.map(adherent => 
        this.renderAdherentRow(adherent)
    ).join('');
    
    // Configurer les événements des nouvelles lignes
    this.setupRowEventHandlers();
};

/**
 * Rendre une ligne d'adhérent
 */
window.AdherentsManager.renderAdherentRow = function(adherent) {
    const isSelected = this.state.selectedItems.has(adherent.id);
    const nomComplet = `${adherent.prenom} ${adherent.nom}`;
    
    return `
        <tr class="adherent-row ${isSelected ? 'table-active' : ''}" data-id="${adherent.id}">
            <td>
                <input type="checkbox" class="form-check-input row-checkbox" 
                       ${isSelected ? 'checked' : ''} 
                       data-id="${adherent.id}">
            </td>
            <td>
                <span class="adherent-nip">${adherent.nip}</span>
            </td>
            <td>
                <div class="adherent-name">${nomComplet}</div>
                <small class="text-muted">${adherent.civilite || 'M'}</small>
            </td>
            <td>
                <span class="adherent-phone">${adherent.telephone || '-'}</span>
            </td>
            <td>
                <span class="adherent-profession">${adherent.profession || '-'}</span>
            </td>
            <td>
                <span class="adherent-source source-${adherent.source || 'manuel'}">${this.getSourceLabel(adherent.source)}</span>
            </td>
            <td>
                <span class="adherent-status status-${adherent.statut || 'valide'}">${this.getStatusLabel(adherent.statut)}</span>
            </td>
            <td>
                <div class="adherent-actions">
                    <button class="btn btn-sm btn-view" onclick="AdherentsManager.viewAdherent('${adherent.id}')" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-edit" onclick="AdherentsManager.editAdherent('${adherent.id}')" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="AdherentsManager.deleteAdherent('${adherent.id}')" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
};

/**
 * Rendre la pagination
 */
window.AdherentsManager.renderPagination = function() {
    const paginationContainer = document.getElementById(this.config.paginationId);
    if (!paginationContainer) return;
    
    if (this.state.totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    const startItem = (this.state.currentPage - 1) * this.state.itemsPerPage + 1;
    const endItem = Math.min(startItem + this.state.itemsPerPage - 1, this.state.totalItems);
    
    paginationContainer.innerHTML = `
        <div class="pagination-info">
            <span class="text-muted">
                Affichage ${startItem}-${endItem} sur ${this.state.totalItems} adhérents
            </span>
        </div>
        <div class="pagination-controls">
            ${this.generatePaginationButtons()}
        </div>
        <div class="page-size-selector">
            <label for="page-size">Éléments par page:</label>
            <select id="page-size" class="form-select form-select-sm">
                <option value="10" ${this.state.itemsPerPage === 10 ? 'selected' : ''}>10</option>
                <option value="20" ${this.state.itemsPerPage === 20 ? 'selected' : ''}>20</option>
                <option value="50" ${this.state.itemsPerPage === 50 ? 'selected' : ''}>50</option>
                <option value="100" ${this.state.itemsPerPage === 100 ? 'selected' : ''}>100</option>
            </select>
        </div>
    `;
    
    // Configurer les événements de pagination
    this.setupPaginationEvents();
};

/**
 * Générer les boutons de pagination
 */
window.AdherentsManager.generatePaginationButtons = function() {
    let buttons = '';
    
    // Bouton précédent
    buttons += `
        <button class="btn btn-sm ${this.state.currentPage === 1 ? 'disabled' : ''}" 
                onclick="AdherentsManager.goToPage(${this.state.currentPage - 1})"
                ${this.state.currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Boutons de pages
    const startPage = Math.max(1, this.state.currentPage - Math.floor(this.config.maxPaginationButtons / 2));
    const endPage = Math.min(this.state.totalPages, startPage + this.config.maxPaginationButtons - 1);
    
    if (startPage > 1) {
        buttons += `<button class="btn btn-sm" onclick="AdherentsManager.goToPage(1)">1</button>`;
        if (startPage > 2) {
            buttons += `<span class="pagination-ellipsis">...</span>`;
        }
    }
    
    for (let page = startPage; page <= endPage; page++) {
        buttons += `
            <button class="btn btn-sm ${page === this.state.currentPage ? 'active' : ''}" 
                    onclick="AdherentsManager.goToPage(${page})">
                ${page}
            </button>
        `;
    }
    
    if (endPage < this.state.totalPages) {
        if (endPage < this.state.totalPages - 1) {
            buttons += `<span class="pagination-ellipsis">...</span>`;
        }
        buttons += `<button class="btn btn-sm" onclick="AdherentsManager.goToPage(${this.state.totalPages})">${this.state.totalPages}</button>`;
    }
    
    // Bouton suivant
    buttons += `
        <button class="btn btn-sm ${this.state.currentPage === this.state.totalPages ? 'disabled' : ''}" 
                onclick="AdherentsManager.goToPage(${this.state.currentPage + 1})"
                ${this.state.currentPage === this.state.totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    return buttons;
};

// ============================================================================
// GESTION DES ÉVÉNEMENTS
// ============================================================================

/**
 * Configurer les gestionnaires de tri
 */
window.AdherentsManager.setupSortHandlers = function() {
    const table = document.getElementById(this.config.tableId);
    if (!table) return;
    
    const headers = table.querySelectorAll('thead th[data-sort]');
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.getAttribute('data-sort');
            this.handleSort(column);
        });
        
        // Ajouter l'attribut aria-sort
        header.setAttribute('role', 'columnheader');
        header.setAttribute('tabindex', '0');
    });
};

/**
 * Configurer les événements des lignes
 */
window.AdherentsManager.setupRowEventHandlers = function() {
    // Gestionnaires de sélection
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            const adherentId = parseInt(e.target.getAttribute('data-id'));
            this.handleRowSelection(adherentId, e.target.checked);
        });
    });
};

/**
 * Configurer les événements de pagination
 */
window.AdherentsManager.setupPaginationEvents = function() {
    const pageSizeSelect = document.getElementById('page-size');
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', (e) => {
            this.changePageSize(parseInt(e.target.value));
        });
    }
};

// ============================================================================
// GESTIONNAIRES D'ACTIONS
// ============================================================================

/**
 * Gérer la recherche
 */
window.AdherentsManager.handleSearch = function(query) {
    this.state.searchQuery = query;
    this.state.currentPage = 1;
    this.applyFiltersAndSearch();
    this.applySorting();
    this.calculatePagination();
    this.renderTable();
    this.renderPagination();
    this.updateCounter();
    
    console.log(`🔍 Recherche: "${query}" (${this.state.totalItems} résultats)`);
};

/**
 * Gérer un filtre
 */
window.AdherentsManager.handleFilter = function(filterKey, value) {
    this.state.filters[filterKey] = value;
    this.state.currentPage = 1;
    this.applyFiltersAndSearch();
    this.applySorting();
    this.calculatePagination();
    this.renderTable();
    this.renderPagination();
    this.updateCounter();
    
    console.log(`🔽 Filtre ${filterKey}: "${value}" (${this.state.totalItems} résultats)`);
};

/**
 * Gérer le tri
 */
window.AdherentsManager.handleSort = function(column) {
    if (this.state.sortColumn === column) {
        this.state.sortDirection = this.state.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        this.state.sortColumn = column;
        this.state.sortDirection = 'asc';
    }
    
    this.applySorting();
    this.calculatePagination();
    this.renderTable();
    this.updateSortIndicators();
    
    console.log(`📊 Tri: ${column} ${this.state.sortDirection}`);
};

/**
 * Gérer la sélection de ligne
 */
window.AdherentsManager.handleRowSelection = function(adherentId, isSelected) {
    if (isSelected) {
        this.state.selectedItems.add(adherentId);
    } else {
        this.state.selectedItems.delete(adherentId);
    }
    
    this.updateSelectAllCheckbox();
    this.updateBulkActions();
};

/**
 * Gérer la sélection globale
 */
window.AdherentsManager.handleSelectAll = function(selectAll) {
    this.state.selectAll = selectAll;
    
    if (selectAll) {
        this.state.currentPageAdherents.forEach(adherent => {
            this.state.selectedItems.add(adherent.id);
        });
    } else {
        this.state.currentPageAdherents.forEach(adherent => {
            this.state.selectedItems.delete(adherent.id);
        });
    }
    
    this.renderTable();
    this.updateBulkActions();
};

/**
 * Aller à une page
 */
window.AdherentsManager.goToPage = function(page) {
    if (page < 1 || page > this.state.totalPages) return;
    
    this.state.currentPage = page;
    this.calculatePagination();
    this.renderTable();
    this.renderPagination();
    
    // Remettre à zéro la sélection de page
    this.state.selectedItems.clear();
    this.updateSelectAllCheckbox();
    this.updateBulkActions();
};

/**
 * Changer la taille de page
 */
window.AdherentsManager.changePageSize = function(newSize) {
    this.state.itemsPerPage = newSize;
    this.state.currentPage = 1;
    this.calculatePagination();
    this.renderTable();
    this.renderPagination();
};

// ============================================================================
// ACTIONS SUR LES ADHÉRENTS
// ============================================================================

/**
 * Voir un adhérent
 */
window.AdherentsManager.viewAdherent = function(adherentId) {
    const adherent = this.findAdherentById(adherentId);
    if (!adherent) return;
    
    // Implémenter l'affichage du modal de détails
    console.log('👁️ Voir adhérent:', adherent);
    
    // TODO: Afficher modal avec détails complets
};

/**
 * Modifier un adhérent
 */
window.AdherentsManager.editAdherent = function(adherentId) {
    const adherent = this.findAdherentById(adherentId);
    if (!adherent) return;
    
    console.log('✏️ Modifier adhérent:', adherent);
    
    // TODO: Afficher modal d'édition
};

/**
 * Supprimer un adhérent
 */
window.AdherentsManager.deleteAdherent = function(adherentId) {
    const adherent = this.findAdherentById(adherentId);
    if (!adherent) return;
    
    if (!confirm(`Supprimer l'adhérent ${adherent.prenom} ${adherent.nom} ?`)) {
        return;
    }
    
    // Supprimer de la liste principale
    if (window.ConfirmationApp) {
        const index = window.ConfirmationApp.state.adherentsData.findIndex(a => a.id === adherentId);
        if (index !== -1) {
            window.ConfirmationApp.state.adherentsData.splice(index, 1);
            window.ConfirmationApp.updateStatistics();
        }
    }
    
    // Rafraîchir le tableau
    this.refreshTable();
    
    // Notification
    if (window.ConfirmationApp) {
        window.ConfirmationApp.showSuccess(`Adhérent ${adherent.prenom} ${adherent.nom} supprimé`);
    }
    
    console.log('🗑️ Adhérent supprimé:', adherent);
};

// ============================================================================
// ACTIONS EN LOT
// ============================================================================

/**
 * Configurer les actions en lot
 */
window.AdherentsManager.setupBulkActionHandlers = function() {
    // Les handlers seront ajoutés dynamiquement quand des éléments sont sélectionnés
};

/**
 * Mettre à jour les actions en lot
 */
window.AdherentsManager.updateBulkActions = function() {
    const selectedCount = this.state.selectedItems.size;
    const bulkActionsContainer = document.querySelector('.bulk-actions');
    
    if (!bulkActionsContainer) return;
    
    if (selectedCount > 0) {
        bulkActionsContainer.classList.add('show');
        bulkActionsContainer.innerHTML = `
            <div class="d-flex align-items-center justify-content-center gap-2">
                <span class="fw-bold">${selectedCount} adhérent(s) sélectionné(s)</span>
                <button class="btn btn-sm btn-outline-danger" onclick="AdherentsManager.deleteSelected()">
                    <i class="fas fa-trash me-1"></i>Supprimer
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="AdherentsManager.exportSelected()">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="AdherentsManager.clearSelection()">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
            </div>
        `;
    } else {
        bulkActionsContainer.classList.remove('show');
    }
};

/**
 * Supprimer les éléments sélectionnés
 */
window.AdherentsManager.deleteSelected = function() {
    const selectedCount = this.state.selectedItems.size;
    
    if (!confirm(`Supprimer ${selectedCount} adhérent(s) sélectionné(s) ?`)) {
        return;
    }
    
    // Supprimer de la liste principale
    if (window.ConfirmationApp) {
        window.ConfirmationApp.state.adherentsData = window.ConfirmationApp.state.adherentsData.filter(
            adherent => !this.state.selectedItems.has(adherent.id)
        );
        window.ConfirmationApp.updateStatistics();
    }
    
    // Vider la sélection
    this.state.selectedItems.clear();
    
    // Rafraîchir
    this.refreshTable();
    this.updateBulkActions();
    
    if (window.ConfirmationApp) {
        window.ConfirmationApp.showSuccess(`${selectedCount} adhérent(s) supprimé(s)`);
    }
};

/**
 * Exporter les éléments sélectionnés
 */
window.AdherentsManager.exportSelected = function() {
    const selectedAdherents = this.state.allAdherents.filter(
        adherent => this.state.selectedItems.has(adherent.id)
    );
    
    this.exportAdherents(selectedAdherents, 'adherents_selection');
};

/**
 * Vider la sélection
 */
window.AdherentsManager.clearSelection = function() {
    this.state.selectedItems.clear();
    this.state.selectAll = false;
    this.updateSelectAllCheckbox();
    this.updateBulkActions();
    this.renderTable();
};

// ============================================================================
// UTILITAIRES
// ============================================================================

/**
 * Trouver un adhérent par ID
 */
window.AdherentsManager.findAdherentById = function(adherentId) {
    return this.state.allAdherents.find(adherent => adherent.id == adherentId);
};

/**
 * Obtenir le label d'une source
 */
window.AdherentsManager.getSourceLabel = function(source) {
    const labels = {
        'manuel': 'Saisie',
        'fichier': 'Fichier',
        'chunking': 'Import',
        'massif': 'Massif'
    };
    return labels[source] || 'Inconnu';
};

/**
 * Obtenir le label d'un statut
 */
window.AdherentsManager.getStatusLabel = function(statut) {
    const labels = {
        'valide': 'Valide',
        'anomalie': 'Anomalie',
        'doublon': 'Doublon',
        'pending': 'En attente'
    };
    return labels[statut] || 'Valide';
};

/**
 * Mettre à jour le compteur
 */
window.AdherentsManager.updateCounter = function() {
    const counterElement = document.getElementById('adherents-total');
    if (counterElement) {
        counterElement.textContent = this.state.totalItems;
    }
};

/**
 * Mettre à jour la case à cocher "Tout sélectionner"
 */
window.AdherentsManager.updateSelectAllCheckbox = function() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (!selectAllCheckbox) return;
    
    const currentPageIds = this.state.currentPageAdherents.map(a => a.id);
    const selectedOnPage = currentPageIds.filter(id => this.state.selectedItems.has(id));
    
    if (selectedOnPage.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (selectedOnPage.length === currentPageIds.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
};

/**
 * Mettre à jour les indicateurs de tri
 */
window.AdherentsManager.updateSortIndicators = function() {
    const headers = document.querySelectorAll('thead th[data-sort]');
    headers.forEach(header => {
        const column = header.getAttribute('data-sort');
        header.removeAttribute('aria-sort');
        
        if (column === this.state.sortColumn) {
            header.setAttribute('aria-sort', this.state.sortDirection === 'asc' ? 'ascending' : 'descending');
        }
    });
};

/**
 * Effacer tous les filtres
 */
window.AdherentsManager.clearAllFilters = function() {
    // Effacer la recherche
    const searchInput = document.getElementById('adherents-search');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Effacer les filtres
    Object.keys(this.state.filters).forEach(key => {
        this.state.filters[key] = '';
        const filterElement = document.getElementById(`filter-${key}`);
        if (filterElement) {
            filterElement.value = '';
        }
    });
    
    // Réinitialiser la recherche
    this.state.searchQuery = '';
    this.state.currentPage = 1;
    
    // Rafraîchir
    this.refreshTable();
    
    if (window.ConfirmationApp) {
        window.ConfirmationApp.showInfo('Filtres effacés');
    }
};

/**
 * Exporter les adhérents
 */
window.AdherentsManager.exportAdherents = function(adherents, filename = 'adherents') {
    // Exporter en CSV
    const csvContent = this.generateCSV(adherents);
    this.downloadFile(csvContent, `${filename}.csv`, 'text/csv');
};

/**
 * Générer un CSV
 */
window.AdherentsManager.generateCSV = function(adherents) {
    const headers = ['Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 'Profession', 'Source', 'Statut'];
    const rows = adherents.map(adherent => [
        adherent.civilite || 'M',
        adherent.nom,
        adherent.prenom,
        adherent.nip,
        adherent.telephone || '',
        adherent.profession || '',
        this.getSourceLabel(adherent.source),
        this.getStatusLabel(adherent.statut)
    ]);
    
    return [headers, ...rows].map(row => 
        row.map(cell => `"${cell}"`).join(',')
    ).join('\n');
};

/**
 * Télécharger un fichier
 */
window.AdherentsManager.downloadFile = function(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
};

// ============================================================================
// FONCTIONS GLOBALES EXPOSÉES
// ============================================================================

// Exposer les principales fonctions pour usage dans les templates
window.clearFilters = function() {
    if (window.AdherentsManager.clearAllFilters) {
        window.AdherentsManager.clearAllFilters();
    }
};

window.exportAdherents = function(format) {
    if (window.AdherentsManager.exportAdherents) {
        const allAdherents = window.AdherentsManager.state.allAdherents;
        window.AdherentsManager.exportAdherents(allAdherents, `adherents_export`);
    }
};

window.showAdherentsStats = function() {
    if (window.AdherentsManager.state.allAdherents) {
        const stats = {
            total: window.AdherentsManager.state.allAdherents.length,
            valides: window.AdherentsManager.state.allAdherents.filter(a => a.statut === 'valide').length,
            anomalies: window.AdherentsManager.state.allAdherents.filter(a => a.statut === 'anomalie').length,
            doublons: window.AdherentsManager.state.allAdherents.filter(a => a.statut === 'doublon').length
        };
        
        alert(`Statistiques des adhérents:\n\nTotal: ${stats.total}\nValides: ${stats.valides}\nAnomalies: ${stats.anomalies}\nDoublons: ${stats.doublons}`);
    }
};

// Log de chargement
console.log('📊 AdherentsManager v2.0 chargé - Prêt pour initialisation');