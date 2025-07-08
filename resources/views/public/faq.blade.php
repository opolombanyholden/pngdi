@extends('layouts.public')

@section('title', 'Foire aux questions')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Foire aux questions</h1>
                <p class="page-subtitle">
                    Trouvez rapidement des réponses à vos questions sur l'utilisation du portail SGLP 
                    et les procédures de formalisation des organisations.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">FAQ</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <form id="faq-search-form">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               id="faq-search" 
                               placeholder="Rechercher dans la FAQ..."
                               autocomplete="off">
                        <button class="btn btn-primary" type="submit">
                            Rechercher
                        </button>
                    </div>
                    <div id="search-results" class="mt-2 text-muted small"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Categories -->
            <div class="col-lg-3 mb-4">
                <div class="categories-card">
                    <h5 class="categories-title">
                        <i class="fas fa-list me-2"></i>Catégories
                    </h5>
                    <ul class="categories-list">
                        <li>
                            <a href="#" class="category-link active" data-category="all">
                                <i class="fas fa-chevron-right me-2"></i>
                                Toutes les questions
                                <span class="category-count">{{ array_sum(array_map(function($cat) { return count($cat['questions']); }, $faqs)) }}</span>
                            </a>
                        </li>
                        @foreach($faqs as $key => $category)
                        <li>
                            <a href="#{{ $key }}" class="category-link" data-category="{{ $key }}">
                                <i class="fas fa-chevron-right me-2"></i>
                                {{ $category['titre'] }}
                                <span class="category-count">{{ count($category['questions']) }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    
                    <!-- Quick Help -->
                    <div class="quick-help-box">
                        <h6 class="mb-3">
                            <i class="fas fa-headset me-2"></i>Besoin d'aide ?
                        </h6>
                        <p class="small mb-3">
                            Vous ne trouvez pas la réponse à votre question ?
                        </p>
                        <a href="{{ route('contact') }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-envelope me-2"></i>Contactez-nous
                        </a>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>+241 01 23 45 67
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Accordion -->
            <div class="col-lg-9">
                <!-- Quick Stats -->
                <div class="faq-stats mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="stat-box">
                                <i class="fas fa-question-circle"></i>
                                <div>
                                    <div class="stat-value">{{ array_sum(array_map(function($cat) { return count($cat['questions']); }, $faqs)) }}</div>
                                    <div class="stat-label">Questions répertoriées</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <i class="fas fa-layer-group"></i>
                                <div>
                                    <div class="stat-value">{{ count($faqs) }}</div>
                                    <div class="stat-label">Catégories</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <div class="stat-value">24/7</div>
                                    <div class="stat-label">Accessible</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Sections -->
                @foreach($faqs as $key => $category)
                <div class="faq-section mb-5" id="{{ $key }}">
                    <div class="section-header">
                        <h3 class="section-title">
                            @switch($key)
                                @case('general')
                                    <i class="fas fa-info-circle me-2"></i>
                                    @break
                                @case('creation')
                                    <i class="fas fa-plus-circle me-2"></i>
                                    @break
                                @case('technique')
                                    <i class="fas fa-cog me-2"></i>
                                    @break
                                @case('gestion')
                                    <i class="fas fa-tasks me-2"></i>
                                    @break
                            @endswitch
                            {{ $category['titre'] }}
                        </h3>
                        <span class="section-count">{{ count($category['questions']) }} questions</span>
                    </div>
                    
                    <div class="accordion custom-accordion" id="accordion-{{ $key }}">
                        @foreach($category['questions'] as $qIndex => $qa)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $key }}-{{ $qIndex }}">
                                <button class="accordion-button {{ $qIndex > 0 ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse-{{ $key }}-{{ $qIndex }}" 
                                        aria-expanded="{{ $qIndex == 0 ? 'true' : 'false' }}" 
                                        aria-controls="collapse-{{ $key }}-{{ $qIndex }}">
                                    <span class="question-number">{{ $qIndex + 1 }}</span>
                                    {{ $qa['question'] }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $key }}-{{ $qIndex }}" 
                                 class="accordion-collapse collapse {{ $qIndex == 0 ? 'show' : '' }}" 
                                 aria-labelledby="heading-{{ $key }}-{{ $qIndex }}" 
                                 data-bs-parent="#accordion-{{ $key }}">
                                <div class="accordion-body">
                                    <div class="answer-content">
                                        {{ $qa['reponse'] }}
                                    </div>
                                    
                                    @if($key == 'creation' && $qIndex == 0)
                                    <div class="answer-extra mt-3">
                                        <h6 class="fw-bold">Documents requis :</h6>
                                        <ul class="list-unstyled ms-3">
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Statuts de l'association</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Procès-verbal de l'assemblée constitutive</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Liste des membres fondateurs</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Copies des pièces d'identité</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Justificatif de domicile</li>
                                        </ul>
                                        <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-download me-2"></i>Télécharger les modèles
                                        </a>
                                    </div>
                                    @endif
                                    
                                    @if($key == 'technique' && $qIndex == 2)
                                    <div class="answer-extra mt-3">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Formats acceptés :</strong> PDF, JPEG, PNG<br>
                                            <strong>Taille maximale :</strong> 5 MB par fichier
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="answer-footer">
                                        <div class="helpful-section">
                                            <span class="me-3">Cette réponse vous a-t-elle été utile ?</span>
                                            <button class="btn btn-sm btn-outline-success me-2" onclick="markHelpful(true, '{{ $key }}-{{ $qIndex }}')">
                                                <i class="fas fa-thumbs-up"></i> Oui
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="markHelpful(false, '{{ $key }}-{{ $qIndex }}')">
                                                <i class="fas fa-thumbs-down"></i> Non
                                            </button>
                                        </div>
                                        <div class="feedback-message" id="feedback-{{ $key }}-{{ $qIndex }}" style="display: none;">
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Merci pour votre retour !</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                
                <!-- Additional Help Section -->
                <div class="additional-help-section mt-5 p-4 bg-light rounded-3">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h4 class="mb-3">Vous n'avez pas trouvé votre réponse ?</h4>
                            <p class="mb-0">
                                Notre équipe est là pour vous aider. N'hésitez pas à nous contacter 
                                pour toute question supplémentaire concernant vos démarches.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('contact') }}" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer une question
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        color: white;
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Search Section */
    .input-group-text {
        border-right: none;
    }

    #faq-search {
        border-left: none;
    }

    #faq-search:focus {
        box-shadow: none;
        border-color: #ced4da;
    }

    /* Categories Card */
    .categories-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        position: sticky;
        top: 100px;
    }

    .categories-title {
        color: var(--primary-blue);
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .categories-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .categories-list li {
        margin-bottom: 0.5rem;
    }

    .category-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        color: #666;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .category-link:hover {
        background: #f8f9fa;
        color: var(--primary-blue);
        transform: translateX(5px);
    }

    .category-link.active {
        background: var(--primary-blue);
        color: white;
    }

    .category-link i {
        font-size: 0.75rem;
        transition: transform 0.3s;
    }

    .category-link:hover i {
        transform: translateX(3px);
    }

    .category-count {
        background: rgba(0,0,0,0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .category-link.active .category-count {
        background: rgba(255,255,255,0.3);
    }

    /* Quick Help Box */
    .quick-help-box {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 2rem;
        text-align: center;
    }

    .quick-help-box .btn {
        background: white;
        color: var(--primary-blue);
    }

    .quick-help-box .btn:hover {
        background: var(--secondary-gold);
        color: var(--primary-blue);
    }

    /* FAQ Stats */
    .faq-stats {
        margin-bottom: 2rem;
    }

    .stat-box {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .stat-box i {
        font-size: 2.5rem;
        color: var(--primary-blue);
        opacity: 0.8;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-blue);
        line-height: 1;
    }

    .stat-label {
        color: #666;
        font-size: 0.875rem;
    }

    /* FAQ Sections */
    .faq-section {
        margin-bottom: 3rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .section-title {
        color: var(--primary-blue);
        font-size: 1.75rem;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .section-title i {
        opacity: 0.8;
    }

    .section-count {
        background: #f8f9fa;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        color: #666;
    }

    /* Custom Accordion */
    .custom-accordion .accordion-item {
        border: none;
        background: white;
        margin-bottom: 1rem;
        border-radius: 15px !important;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .custom-accordion .accordion-item:hover {
        box-shadow: 0 5px 25px rgba(0,0,0,0.12);
    }

    .accordion-button {
        background: white;
        color: var(--primary-blue);
        font-weight: 600;
        border: none;
        padding: 1.5rem;
        position: relative;
        padding-left: 4rem;
    }

    .accordion-button:not(.collapsed) {
        background: linear-gradient(to bottom, #f8f9fa, white);
        color: var(--primary-blue);
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border: none;
    }

    .accordion-button::after {
        position: absolute;
        right: 1.5rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23002B7F'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }

    .accordion-button:not(.collapsed)::after {
        transform: rotate(-180deg);
    }

    .question-number {
        position: absolute;
        left: 1.5rem;
        width: 35px;
        height: 35px;
        background: var(--primary-blue);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 700;
    }

    .accordion-button:not(.collapsed) .question-number {
        background: var(--secondary-gold);
        color: var(--primary-blue);
    }

    .accordion-body {
        padding: 2rem;
    }

    .answer-content {
        color: #666;
        line-height: 1.8;
        font-size: 1rem;
    }

    .answer-extra {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
    }

    .answer-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }

    .helpful-section {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .feedback-message {
        margin-top: 0.5rem;
    }

    /* Additional Help Section */
    .additional-help-section {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 2px solid var(--primary-blue);
        border-style: dashed;
    }

    /* Smooth Scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Highlight Effect */
    .accordion-item.highlighted {
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.5);
        animation: highlight 1s ease-out;
    }

    @keyframes highlight {
        0% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.8); }
        100% { box-shadow: 0 0 0 10px rgba(255, 215, 0, 0); }
    }

    /* Responsive */
    @media (max-width: 991px) {
        .categories-card {
            position: relative;
            margin-bottom: 2rem;
        }

        .section-header {
            flex-direction: column;
            align-items: start;
            gap: 0.5rem;
        }

        .stat-box {
            padding: 1rem;
        }

        .stat-box i {
            font-size: 2rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // FAQ Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('faq-search');
        const searchForm = document.getElementById('faq-search-form');
        const searchResults = document.getElementById('search-results');
        const accordionItems = document.querySelectorAll('.accordion-item');
        const categoryLinks = document.querySelectorAll('.category-link');

        // Search functionality
        function performSearch(query) {
            query = query.toLowerCase().trim();
            let matchCount = 0;

            if (query === '') {
                // Show all items if search is empty
                accordionItems.forEach(item => {
                    item.style.display = 'block';
                    item.classList.remove('highlighted');
                });
                searchResults.textContent = '';
                return;
            }

            accordionItems.forEach(item => {
                const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                const answer = item.querySelector('.answer-content').textContent.toLowerCase();
                
                if (question.includes(query) || answer.includes(query)) {
                    item.style.display = 'block';
                    item.classList.add('highlighted');
                    matchCount++;
                } else {
                    item.style.display = 'none';
                    item.classList.remove('highlighted');
                }
            });

            // Update search results message
            if (matchCount > 0) {
                searchResults.textContent = `${matchCount} résultat${matchCount > 1 ? 's' : ''} trouvé${matchCount > 1 ? 's' : ''}`;
                searchResults.className = 'mt-2 text-success small';
            } else {
                searchResults.textContent = 'Aucun résultat trouvé';
                searchResults.className = 'mt-2 text-danger small';
            }
        }

        // Search input event
        searchInput.addEventListener('input', function(e) {
            performSearch(e.target.value);
        });

        // Prevent form submission
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
        });

        // Category filter
        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active state
                categoryLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.dataset.category;
                
                if (category === 'all') {
                    // Show all sections
                    document.querySelectorAll('.faq-section').forEach(section => {
                        section.style.display = 'block';
                    });
                } else {
                    // Show only selected category
                    document.querySelectorAll('.faq-section').forEach(section => {
                        if (section.id === category) {
                            section.style.display = 'block';
                            // Smooth scroll to section
                            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.getAttribute('href') !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    });

    // Mark helpful function
    function markHelpful(isHelpful, questionId) {
        const feedbackElement = document.getElementById('feedback-' + questionId);
        feedbackElement.style.display = 'block';
        
        // Hide feedback message after 3 seconds
        setTimeout(() => {
            feedbackElement.style.display = 'none';
        }, 3000);

        // Here you could send an AJAX request to track the feedback
        console.log('Question ' + questionId + ' marked as ' + (isHelpful ? 'helpful' : 'not helpful'));
    }

    // Expand/Collapse all functionality (optional)
    function toggleAllAccordions(expand) {
        const accordionButtons = document.querySelectorAll('.accordion-button');
        const accordionCollapses = document.querySelectorAll('.accordion-collapse');
        
        accordionButtons.forEach(button => {
            if (expand) {
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
            } else {
                button.classList.add('collapsed');
                button.setAttribute('aria-expanded', 'false');
            }
        });
        
        accordionCollapses.forEach(collapse => {
            if (expand) {
                collapse.classList.add('show');
            } else {
                collapse.classList.remove('show');
            }
        });
    }
</script>
@endpush