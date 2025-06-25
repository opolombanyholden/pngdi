@extends('layouts.operator')

@section('title', 'Rapports d\'Activité')

@section('page-title', 'Rapports d\'Activité')

@section('content')
<div class="container-fluid">
    <!-- Header avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-chart-line me-2"></i>
                                Rapports d'Activité
                            </h2>
                            <p class="mb-0 opacity-90">Consultez et générez vos rapports d'activité annuels et trimestriels</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>
                                Nouveau Rapport
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
                            <h3 class="mb-1">{{ $totalRapports ?? 8 }}</h3>
                            <p class="mb-0 small opacity-90">Rapports Soumis</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $rapportsEnCours ?? 3 }}</h3>
                            <p class="mb-0 small">En Cours</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $rapportsValides ?? 5 }}</h3>
                            <p class="mb-0 small opacity-90">Validés</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $prochaineEcheance ?? '15 Fév' }}</h3>
                            <p class="mb-0 small opacity-90">Prochaine Échéance</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-calendar-exclamation fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 30%"></div>
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
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher un rapport..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterPeriode">
                                <option value="">Toutes les périodes</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="T1-2025">T1 2025</option>
                                <option value="T4-2024">T4 2024</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterStatut">
                                <option value="">Tous les statuts</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="soumis">Soumis</option>
                                <option value="valide">Validé</option>
                                <option value="rejete">Rejeté</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterType">
                                <option value="">Tous les types</option>
                                <option value="annuel">Rapport Annuel</option>
                                <option value="trimestriel">Rapport Trimestriel</option>
                                <option value="activite">Rapport d'Activité</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-success dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-word me-2"></i>Word</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Rapports -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2 text-success"></i>
                            Liste des Rapports d'Activité
                        </h5>
                        <span class="badge bg-light text-dark">{{ $totalRapports ?? 8 }} rapports</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($rapports) && count($rapports) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Titre</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0">Période</th>
                                        <th class="border-0">Statut</th>
                                        <th class="border-0">Date Soumission</th>
                                        <th class="border-0">Échéance</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rapports as $rapport)
                                    <tr class="rapport-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rapport-icon me-3">
                                                    <i class="fas fa-file-alt fa-2x text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $rapport->titre ?? 'Rapport d\'Activité' }}</h6>
                                                    <small class="text-muted">{{ $rapport->organisation->nom ?? 'Organisation' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $rapport->type_label ?? 'Annuel' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $rapport->periode ?? '2024' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $rapport->statut_color ?? 'success' }}">
                                                {{ $rapport->statut_label ?? 'Validé' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $rapport->date_soumission ? $rapport->date_soumission->format('d/m/Y') : '15/01/2025' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-{{ $rapport->echeance_color ?? 'success' }}">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $rapport->date_echeance ? $rapport->date_echeance->format('d/m/Y') : '31/03/2025' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info" title="Télécharger">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($rapports) && method_exists($rapports, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $rapports->links() }}
                            </div>
                        @endif
                    @else
                        <!-- État vide avec style gabonais -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-chart-line fa-5x text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucun rapport d'activité</h4>
                            <p class="text-muted mb-4">Vous n'avez pas encore créé de rapport d'activité pour vos organisations.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-success btn-lg">
                                    <i class="fas fa-plus me-2"></i>Créer mon premier rapport
                                </button>
                                <button class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-book me-2"></i>Guide de création
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Rapport Annuel">
                <i class="fas fa-calendar-alt"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Rapport Trimestriel">
                <i class="fas fa-calendar-week"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Rapport d'Activité">
                <i class="fas fa-chart-bar"></i>
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

/* Style pour les lignes de rapports */
.rapport-row {
    transition: background-color 0.2s ease;
}

.rapport-row:hover {
    background-color: rgba(0, 158, 63, 0.05);
}

.rapport-icon {
    width: 40px;
    text-align: center;
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
    background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);
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

// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.rapport-row');
    
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
['filterPeriode', 'filterStatut', 'filterType'].forEach(filterId => {
    document.getElementById(filterId).addEventListener('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const periode = document.getElementById('filterPeriode').value;
    const statut = document.getElementById('filterStatut').value;
    const type = document.getElementById('filterType').value;
    
    const rows = document.querySelectorAll('.rapport-row');
    
    rows.forEach(row => {
        let show = true;
        
        if (periode && !row.textContent.includes(periode)) show = false;
        if (statut && !row.querySelector('.badge').textContent.toLowerCase().includes(statut)) show = false;
        if (type && !row.textContent.toLowerCase().includes(type)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
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