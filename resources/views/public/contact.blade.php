@extends('layouts.public')

@section('title', 'Contact')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Contactez-nous</h1>
                <p class="page-subtitle">
                    Nous sommes à votre écoute pour répondre à toutes vos questions 
                    et vous accompagner dans vos démarches.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="contact-form-wrapper">
                    <h3 class="mb-4">Envoyez-nous un message</h3>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" 
                                       name="nom" 
                                       value="{{ old('nom') }}" 
                                       required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="sujet" class="form-label">Sujet <span class="text-danger">*</span></label>
                                <select class="form-select @error('sujet') is-invalid @enderror" 
                                        id="sujet" 
                                        name="sujet" 
                                        required>
                                    <option value="">Choisissez un sujet...</option>
                                    <option value="Information générale" {{ old('sujet') == 'Information générale' ? 'selected' : '' }}>
                                        Information générale
                                    </option>
                                    <option value="Problème technique" {{ old('sujet') == 'Problème technique' ? 'selected' : '' }}>
                                        Problème technique
                                    </option>
                                    <option value="Demande d'assistance" {{ old('sujet') == 'Demande d\'assistance' ? 'selected' : '' }}>
                                        Demande d'assistance
                                    </option>
                                    <option value="Suggestion" {{ old('sujet') == 'Suggestion' ? 'selected' : '' }}>
                                        Suggestion
                                    </option>
                                    <option value="Autre" {{ old('sujet') == 'Autre' ? 'selected' : '' }}>
                                        Autre
                                    </option>
                                </select>
                                @error('sujet')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('message') is-invalid @enderror" 
                                          id="message" 
                                          name="message" 
                                          rows="6" 
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consent" required>
                                    <label class="form-check-label" for="consent">
                                        J'accepte que mes données soient utilisées pour traiter ma demande
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4">
                <!-- Quick Contact -->
                <div class="contact-info-card mb-4">
                    <h4 class="mb-4">Informations de contact</h4>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h6>Adresse</h6>
                            <p>Ministère de l'Intérieur et de la Sécurité<br>
                            Boulevard Triomphal Omar Bongo<br>
                            BP 2110, Libreville - Gabon</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h6>Téléphone</h6>
                            <p><a href="tel:+24101234567">+241 01 23 45 67</a><br>
                            <a href="tel:+24101234568">+241 01 23 45 68</a></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h6>Email</h6>
                            <p><a href="mailto:contact@SGLP.ga">contact@SGLP.ga</a><br>
                            <a href="mailto:support@SGLP.ga">support@SGLP.ga</a></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-details">
                            <h6>Horaires d'ouverture</h6>
                            <p>Lundi - Vendredi : 8h00 - 17h00<br>
                            Samedi : 9h00 - 12h00<br>
                            Dimanche : Fermé</p>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="social-media-card">
                    <h5 class="mb-3">Suivez-nous</h5>
                    <div class="social-links">
                        <a href="#" class="social-link facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link linkedin">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-link youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container-fluid p-0">
        <div class="map-placeholder">
            <div class="map-overlay">
                <div class="map-info">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>Notre localisation</h4>
                    <p>Ministère de l'Intérieur, Libreville</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ CTA -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h3 class="mb-4">Avez-vous consulté notre FAQ ?</h3>
        <p class="lead mb-4">
            Trouvez rapidement des réponses à vos questions les plus fréquentes
        </p>
        <a href="{{ route('faq') }}" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-question-circle me-2"></i>Consulter la FAQ
        </a>
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

    /* Contact Form */
    .contact-form-wrapper {
        background: white;
        padding: 2.5rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(0,43,127,0.25);
    }

    textarea.form-control {
        resize: vertical;
    }

    /* Contact Info Card */
    .contact-info-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .contact-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .contact-item:last-child {
        margin-bottom: 0;
    }

    .contact-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .contact-details h6 {
        color: var(--primary-blue);
        margin-bottom: 0.5rem;
    }

    .contact-details p {
        margin: 0;
        color: #666;
    }

    .contact-details a {
        color: #666;
        text-decoration: none;
        transition: color 0.3s;
    }

    .contact-details a:hover {
        color: var(--primary-blue);
    }

    /* Social Media Card */
    .social-media-card {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        color: white;
    }

    .social-links {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .social-link {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: white;
        transform: translateY(-3px);
    }

    .social-link.facebook:hover {
        color: #1877f2;
    }

    .social-link.twitter:hover {
        color: #1da1f2;
    }

    .social-link.linkedin:hover {
        color: #0077b5;
    }

    .social-link.youtube:hover {
        color: #ff0000;
    }

    /* Map Section */
    .map-section {
        height: 400px;
        position: relative;
    }

    .map-placeholder {
        height: 100%;
        background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
        position: relative;
    }

    .map-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,43,127,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .map-info {
        text-align: center;
        color: white;
    }

    .map-info i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.8;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .contact-form-wrapper {
            padding: 1.5rem;
        }
    }
</style>
@endpush