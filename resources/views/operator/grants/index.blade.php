@extends('layouts.operator')

@section('title', 'Subventions')

@section('page-title', 'Gestion des Subventions')

@section('content')
<div class="container-fluid">
    <!-- Header avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-hand-holding-usd me-2"></i>
                                Subventions et Financements
                            </h2>
                            <p class="mb-0">Gérez vos demandes de subventions et suivez leur état d'avancement</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-dark btn-lg" data-bs-toggle="modal" data-bs-target="#newSubventionModal">
                                <i class="fas fa-plus me-2"></i>
                                Nouvelle Demande
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $totalSubventions ?? 5 }}</h3>
                            <p class="mb-0 small opacity-90">Demandes Totales</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 80%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $subventionsEnCours ?? 2 }}</h3>
                            <p class="mb-0 small opacity-90">En Cours</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $subventionsApprouvees ?? 2 }}</h3>
                            <p class="mb-0 small opacity-90">Approuvées</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ number_format($montantTotal ?? 2500000) }} FCFA</h3>
                            <p class="mb-0 small opacity-90">Montant Total</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-coins fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 70%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher une subvention..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterOrganisation">
                                <option value="">Toutes les organisations</option>
                                @if(isset($organisations))
                                    @foreach($organisations as $org)
                                        <option value="{{ $org->id }}">{{ $org->nom }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterStatut">
                                <option value="">Tous les statuts</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="soumise">Soumise</option>
                                <option value="en_cours">En Cours</option>
                                <option value="approuvee">Approuvée</option>
                                <option value="rejetee">Rejetée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterAnnee">
                                <option value="">Toutes les années</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-warning dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organisations et Subventions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2 text-warning"></i>
                            Subventions par Organisation
                        </h5>
                        <span class="badge bg-light text-dark">{{ $totalSubventions ?? 5 }} demandes</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($organisations) && $organisations->count() > 0)
                        @foreach($organisations as $organisation)
                        <div class="organisation-block mb-4">
                            <div class="card border-0 bg-light">
                                <div class="card-header bg-light border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="organisation-icon me-3">
                                                <i class="fas fa-building fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $organisation->nom }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $organisation->province ?? 'Province non définie' }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $organisation->isApprouvee() ? 'success' : 'warning' }}">
                                                {{ $organisation->statut_label ?? 'En cours' }}
                                            </span>
                                            <div class="mt-1">
                                                <button class="btn btn-sm btn-warning" onclick="nouvelleSubvention({{ $organisation->id }})">
                                                    <i class="fas fa-plus me-1"></i>Nouvelle demande
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(isset($organisation->subventions) && $organisation->subventions->count() > 0)
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="border-0">Objet</th>
                                                        <th class="border-0">Montant</th>
                                                        <th class="border-0">Date Demande</th>
                                                        <th class="border-0">Statut</th>
                                                        <th class="border-0">Échéance</th>
                                                        <th class="border-0">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($organisation->subventions as $subvention)
                                                    <tr class="subvention-row">
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="subvention-icon me-3">
                                                                    <i class="fas fa-hand-holding-usd fa-lg text-warning"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-1">{{ $subvention->objet ?? 'Développement des activités' }}</h6>
                                                                    <small class="text-muted">{{ $subvention->description ?? 'Description de la subvention' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <strong class="text-success">{{ number_format($subvention->montant ?? 500000) }} FCFA</strong>
                                                        </td>
                                                        <td>
                                                            <small>{{ $subvention->created_at ? $subvention->created_at->format('d/m/Y') : '15/01/2025' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $subvention->statut_color ?? 'info' }}">
                                                                {{ $subvention->statut_label ?? 'En cours' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small class="text-{{ $subvention->echeance_color ?? 'success' }}">
                                                                <i class="fas fa-calendar me-1"></i>
                                                                {{ $subvention->date_echeance ? $subvention->date_echeance->format('d/m/Y') : '31/12/2025' }}
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-outline-primary" title="Voir">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-success" title="Télécharger">
                                                                    <i class="fas fa-download"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <div class="card-body text-center py-4">
                                        <i class="fas fa-hand-holding-usd fa-3x text-muted opacity-50 mb-3"></i>
                                        <p class="text-muted mb-3">Aucune demande de subvention pour cette organisation</p>
                                        <button class="btn btn-warning" onclick="nouvelleSubvention({{ $organisation->id }})">
                                            <i class="fas fa-plus me-2"></i>Première demande
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <!-- État vide global -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-hand-holding-usd fa-5x text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucune demande de subvention</h4>
                            <p class="text-muted mb-4">Vous n'avez pas encore fait de demande de subvention pour vos organisations.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#newSubventionModal">
                                    <i class="fas fa-plus me-2"></i>Faire une demande
                                </button>
                                <button class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-book me-2"></i>Guide des subventions
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvelle Subvention -->
<div class="modal fade" id="newSubventionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle Demande de Subvention
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newSubventionForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Organisation <span class="text-danger">*</span></label>
                            <select class="form-select" name="organisation_id" required>
                                <option value="">Sélectionner une organisation</option>
                                @if(isset($organisations))
                                    @foreach($organisations as $org)
                                        <option value="{{ $org->id }}">{{ $org->nom }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Montant demandé (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="montant" placeholder="Ex: 500000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objet de la demande <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="objet" placeholder="Ex: Développement des activités associatives" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description détaillée <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="4" placeholder="Décrivez en détail l'utilisation prévue de cette subvention..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Budget prévisionnel <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="budget_file" accept=".pdf,.doc,.docx" required>
                            <small class="text-muted">Format: PDF, DOC, DOCX (max 5MB)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date limite souhaitée</label>
                            <input type="date" class="form-control" name="date_limite" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="submitSubvention()">
                    <i class="fas fa-save me-2"></i>Enregistrer la demande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Nouvelle Demande" data-bs-toggle="modal" data-bs-target="#newSubventionModal">
                <i class="fas fa-hand-holding-usd"></i>
            </button>
            <button class="fab-option" style="background: #009e3f;" title="Guide Subventions">
                <i class="fas fa-book"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Calculateur Budget">
                <i class="fas fa-calculator"></i>
            </button>
        </div>
    </div>
</div>

<style>
/* Animations pour les stats cards */
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
}

/* Styles pour les organisations */
.organisation-block {
    transition: all 0.3s ease;
}

.organisation-block:hover {
    transform: translateY(-2px);
}

.organisation-icon {
    width: 50px;
    text-align: center;
}

.subvention-icon {
    width: 30px;
    text-align: center;
}

/* Style pour les lignes de subventions */
.subvention-row {
    transition: background-color 0.2s ease;
}

.subvention-row:hover {
    background-color: rgba(255, 205, 0, 0.05);
}

/* FAB Style gabonais */
.fab-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.fab-menu {
    position: relative;
}

.fab-main {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffcd00 0%, #009e3f 50%, #003f7f 100%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.fab-main:hover {
    transform: scale(1.1);
}

.fab-icon {
    color: white;
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.fab-options {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.fab-menu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-menu.active .fab-icon {
    transform: rotate(45deg);
}

.fab-option {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.fab-option:hover {
    transform: scale(1.1);
}

/* Modal styles */
.modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    border: none;
}

.modal-footer {
    border: none;
    background-color: #f8f9fa;
}

/* Responsive */
@media (max-width: 768px) {
    .fab-container {
        bottom: 1rem;
        right: 1rem;
    }
    
    .fab-main {
        width: 50px;
        height: 50px;
    }
    
    .fab-option {
        width: 40px;
        height: 40px;
    }
}

/* Animations d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

/* Filtres temps réel */
.search-highlight {
    background-color: rgba(255, 205, 0, 0.3);
    padding: 2px 4px;
    border-radius: 3px;
}
</style>

<script>
// Toggle FAB Menu
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

// Nouvelle subvention pour une organisation spécifique
function nouvelleSubvention(organisationId) {
    const modal = new bootstrap.Modal(document.getElementById('newSubventionModal'));
    document.querySelector('[name="organisation_id"]').value = organisationId;
    modal.show();
}

// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.subvention-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            // Surligner les termes trouvés
            if (searchTerm.length > 2) {
                highlightSearchTerm(row, searchTerm);
            }
        } else {
            row.style.display = 'none';
        }
    });
});

// Filtres
['filterOrganisation', 'filterStatut', 'filterAnnee'].forEach(filterId => {
    const element = document.getElementById(filterId);
    if (element) {
        element.addEventListener('change', function() {
            applyFilters();
        });
    }
});

function applyFilters() {
    const organisation = document.getElementById('filterOrganisation').value;
    const statut = document.getElementById('filterStatut').value;
    const annee = document.getElementById('filterAnnee').value;
    
    const blocks = document.querySelectorAll('.organisation-block');
    
    blocks.forEach(block => {
        let show = true;
        
        if (organisation && !block.textContent.includes(organisation)) show = false;
        if (statut && !block.querySelector('.badge')?.textContent.toLowerCase().includes(statut)) show = false;
        if (annee && !block.textContent.includes(annee)) show = false;
        
        block.style.display = show ? '' : 'none';
    });
}

// Soumettre nouvelle subvention
function submitSubvention() {
    const form = document.getElementById('newSubventionForm');
    const formData = new FormData(form);
    
    // Validation simple
    const required = ['organisation_id', 'montant', 'objet', 'description'];
    let valid = true;
    
    required.forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!valid) {
        alert('Veuillez remplir tous les champs obligatoires');
        return;
    }
    
    // Ici, vous pouvez envoyer les données via AJAX
    console.log('Données de la subvention:', Object.fromEntries(formData));
    alert('Demande de subvention enregistrée avec succès !');
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('newSubventionModal'));
    modal.hide();
    
    // Réinitialiser le formulaire
    form.reset();
}

function highlightSearchTerm(element, term) {
    // Simple highlighting function
    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );
    
    const textNodes = [];
    let node;
    
    while (node = walker.nextNode()) {
        textNodes.push(node);
    }
    
    textNodes.forEach(textNode => {
        const text = textNode.textContent;
        const regex = new RegExp(`(${term})`, 'gi');
        if (regex.test(text)) {
            const highlightedText = text.replace(regex, '<span class="search-highlight">$1</span>');
            const span = document.createElement('span');
            span.innerHTML = highlightedText;
            textNode.parentNode.replaceChild(span, textNode);
        }
    });
}

// Animation au scroll
window.addEventListener('scroll', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        const rect = card.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }
    });
});
</script>
@endsection