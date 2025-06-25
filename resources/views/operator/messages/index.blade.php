@extends('layouts.operator')

@section('title', 'Messagerie')

@section('page-title', 'Messagerie')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Sidebar Messagerie -->
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <!-- Header Messagerie -->
                <div class="card-header border-0" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-white">
                            <i class="fas fa-inbox me-2"></i>
                            Messagerie
                        </h6>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    
                    <!-- Recherche -->
                    <div class="mt-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-0 bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-0" placeholder="Rechercher..." id="searchMessages">
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="card-body pb-0">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="stat-item">
                                <span class="stat-number text-primary">{{ $totalMessages ?? 12 }}</span>
                                <small class="stat-label d-block text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <span class="stat-number text-success">{{ $messagesLus ?? 8 }}</span>
                                <small class="stat-label d-block text-muted">Lus</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <span class="stat-number text-warning">{{ $messagesNonLus ?? 4 }}</span>
                                <small class="stat-label d-block text-muted">Non lus</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dossiers -->
                <div class="folders-section px-3 mb-3">
                    <small class="text-muted text-uppercase fw-bold">Dossiers</small>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action border-0 active" data-folder="inbox">
                            <i class="fas fa-inbox me-2 text-primary"></i>
                            Boîte de réception
                            <span class="badge bg-primary rounded-pill float-end">{{ $messagesNonLus ?? 4 }}</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0" data-folder="sent">
                            <i class="fas fa-paper-plane me-2 text-success"></i>
                            Messages envoyés
                            <span class="badge bg-light text-dark rounded-pill float-end">{{ $messagesEnvoyes ?? 6 }}</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0" data-folder="drafts">
                            <i class="fas fa-edit me-2 text-warning"></i>
                            Brouillons
                            <span class="badge bg-light text-dark rounded-pill float-end">{{ $brouillons ?? 2 }}</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0" data-folder="important">
                            <i class="fas fa-star me-2 text-danger"></i>
                            Importants
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0" data-folder="archive">
                            <i class="fas fa-archive me-2 text-secondary"></i>
                            Archivés
                        </a>
                    </div>
                </div>

                <!-- Labels -->
                <div class="labels-section px-3">
                    <small class="text-muted text-uppercase fw-bold">Étiquettes</small>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action border-0" data-label="urgent">
                            <span class="badge bg-danger rounded-pill me-2"></span>
                            Urgent
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0" data-label="dossier">
                            <span class="badge bg-info rounded-pill me-2"></span>
                            Dossiers
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0" data-label="subvention">
                            <span class="badge bg-warning rounded-pill me-2"></span>
                            Subventions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Messages -->
        <div class="col-md-4 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <span id="folderTitle">Boîte de réception</span>
                            <small class="text-muted ms-2" id="messageCount">({{ $messagesNonLus ?? 4 }} non lus)</small>
                        </h6>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" title="Actualiser" onclick="refreshMessages()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success" title="Marquer tout comme lu" onclick="markAllAsRead()">
                                <i class="fas fa-check-double"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="messages-list" id="messagesList">
                        @if(isset($messages) && count($messages) > 0)
                            @foreach($messages as $message)
                            <div class="message-item {{ !($message->is_read ?? true) ? 'unread' : '' }}" data-message-id="{{ $message->id ?? rand(1, 100) }}" onclick="openMessage({{ $message->id ?? rand(1, 100) }})">
                                <div class="d-flex align-items-start p-3 border-bottom">
                                    <div class="avatar me-3">
                                        <div class="avatar-circle bg-{{ $message->sender_color ?? 'primary' }}">
                                            {{ strtoupper(substr($message->sender_name ?? 'Administration', 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="message-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="message-sender mb-0">{{ $message->sender_name ?? 'Ministère de l\'Intérieur' }}</h6>
                                            <small class="message-time text-muted">{{ $message->created_at ? $message->created_at->format('H:i') : '14:30' }}</small>
                                        </div>
                                        <h6 class="message-subject mb-1">{{ $message->subject ?? 'Validation de votre dossier' }}</h6>
                                        <p class="message-preview mb-0 text-muted">{{ Str::limit($message->content ?? 'Votre dossier de création d\'organisation a été examiné...', 80) }}</p>
                                        <div class="message-meta mt-2">
                                            @if($message->has_attachment ?? false)
                                                <i class="fas fa-paperclip text-muted me-2"></i>
                                            @endif
                                            @if($message->is_important ?? false)
                                                <i class="fas fa-star text-warning me-2"></i>
                                            @endif
                                            @if(isset($message->label))
                                                <span class="badge bg-{{ $message->label_color ?? 'secondary' }} rounded-pill">{{ $message->label }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <!-- Messages d'exemple -->
                            <div class="message-item unread" data-message-id="1" onclick="openMessage(1)">
                                <div class="d-flex align-items-start p-3 border-bottom">
                                    <div class="avatar me-3">
                                        <div class="avatar-circle bg-success">MI</div>
                                    </div>
                                    <div class="message-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="message-sender mb-0">Ministère de l'Intérieur</h6>
                                            <small class="message-time text-muted">14:30</small>
                                        </div>
                                        <h6 class="message-subject mb-1">Validation de votre dossier d'association</h6>
                                        <p class="message-preview mb-0 text-muted">Votre dossier de création d'association "Protection de l'Environnement" a été approuvé...</p>
                                        <div class="message-meta mt-2">
                                            <i class="fas fa-paperclip text-muted me-2"></i>
                                            <span class="badge bg-success rounded-pill">Approuvé</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="message-item" data-message-id="2" onclick="openMessage(2)">
                                <div class="d-flex align-items-start p-3 border-bottom">
                                    <div class="avatar me-3">
                                        <div class="avatar-circle bg-warning">SG</div>
                                    </div>
                                    <div class="message-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="message-sender mb-0">Service des Subventions</h6>
                                            <small class="message-time text-muted">12:15</small>
                                        </div>
                                        <h6 class="message-subject mb-1">Demande de subvention - Documents manquants</h6>
                                        <p class="message-preview mb-0 text-muted">Il manque le budget prévisionnel pour votre demande de subvention...</p>
                                        <div class="message-meta mt-2">
                                            <span class="badge bg-warning rounded-pill">En attente</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="message-item unread" data-message-id="3" onclick="openMessage(3)">
                                <div class="d-flex align-items-start p-3 border-bottom">
                                    <div class="avatar me-3">
                                        <div class="avatar-circle bg-info">AD</div>
                                    </div>
                                    <div class="message-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="message-sender mb-0">Administration</h6>
                                            <small class="message-time text-muted">10:45</small>
                                        </div>
                                        <h6 class="message-subject mb-1">Rappel - Déclaration annuelle</h6>
                                        <p class="message-preview mb-0 text-muted">N'oubliez pas de soumettre votre déclaration annuelle avant le 31 mars...</p>
                                        <div class="message-meta mt-2">
                                            <i class="fas fa-star text-warning me-2"></i>
                                            <span class="badge bg-danger rounded-pill">Urgent</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu du Message -->
        <div class="col-md-4 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div id="messageContent">
                    <div class="no-message-selected text-center p-5">
                        <i class="fas fa-envelope-open fa-4x text-muted opacity-50 mb-3"></i>
                        <h5 class="text-muted">Sélectionnez un message</h5>
                        <p class="text-muted">Choisissez un message dans la liste pour l'afficher ici</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="fas fa-edit me-2"></i>Nouveau message
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouveau Message -->
<div class="modal fade" id="newMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Nouveau Message
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newMessageForm">
                    <div class="mb-3">
                        <label class="form-label">Destinataire <span class="text-danger">*</span></label>
                        <select class="form-select" name="recipient" required>
                            <option value="">Sélectionner un destinataire</option>
                            <option value="admin">Administration PNGDI</option>
                            <option value="subventions">Service des Subventions</option>
                            <option value="validation">Service de Validation</option>
                            <option value="technique">Support Technique</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Sujet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" placeholder="Objet de votre message" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Priorité</label>
                            <select class="form-select" name="priority">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                                <option value="important">Important</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="content" rows="6" placeholder="Rédigez votre message..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pièces jointes</label>
                        <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">Formats acceptés: PDF, DOC, DOCX, JPG, PNG (max 10MB par fichier)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                    <i class="fas fa-save me-2"></i>Sauvegarder
                </button>
                <button type="button" class="btn btn-primary" onclick="sendMessage()">
                    <i class="fas fa-paper-plane me-2"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles de base */
.h-100 {
    height: calc(100vh - 140px) !important;
}

/* Avatar */
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.8rem;
}

/* Messages */
.message-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.message-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.message-item.unread {
    background-color: rgba(0, 158, 63, 0.05);
    border-left: 3px solid #009e3f;
}

.message-item.selected {
    background-color: rgba(0, 123, 255, 0.1);
    border-left: 3px solid #007bff;
}

.message-sender {
    font-size: 0.9rem;
    font-weight: 600;
}

.message-subject {
    font-size: 0.85rem;
    font-weight: 500;
    color: #333;
}

.message-preview {
    font-size: 0.8rem;
    line-height: 1.3;
}

.message-time {
    font-size: 0.75rem;
}

/* Statistiques */
.stat-item {
    padding: 0.5rem 0;
}

.stat-number {
    font-size: 1.2rem;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Folders et labels */
.folders-section .list-group-item,
.labels-section .list-group-item {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
    border-radius: 5px;
    margin-bottom: 2px;
}

.folders-section .list-group-item.active {
    background-color: rgba(0, 123, 255, 0.1);
    border-color: transparent;
    color: #007bff;
}

/* Scrollbar */
.messages-list {
    max-height: calc(100vh - 280px);
    overflow-y: auto;
}

.messages-list::-webkit-scrollbar {
    width: 6px;
}

.messages-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.messages-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.messages-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Message content */
.no-message-selected {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

/* Responsive */
@media (max-width: 768px) {
    .h-100 {
        height: auto !important;
    }
    
    .messages-list {
        max-height: 400px;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-item {
    animation: fadeIn 0.3s ease-out;
}

/* Message content styles */
.message-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
}

.message-body {
    padding: 1.5rem;
    line-height: 1.6;
}

.message-actions {
    padding: 1rem 1.5rem;
    border-top: 1px solid #eee;
    background-color: #f8f9fa;
}
</style>

<script>
// Variables globales
let currentMessageId = null;
let currentFolder = 'inbox';

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaires d'événements pour les dossiers
    document.querySelectorAll('[data-folder]').forEach(folder => {
        folder.addEventListener('click', function(e) {
            e.preventDefault();
            selectFolder(this.dataset.folder, this.textContent.trim());
        });
    });

    // Gestionnaires pour les labels
    document.querySelectorAll('[data-label]').forEach(label => {
        label.addEventListener('click', function(e) {
            e.preventDefault();
            filterByLabel(this.dataset.label);
        });
    });

    // Recherche
    document.getElementById('searchMessages').addEventListener('input', function() {
        searchMessages(this.value);
    });
});

// Sélectionner un dossier
function selectFolder(folder, title) {
    // Mettre à jour l'interface
    document.querySelectorAll('[data-folder]').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-folder="${folder}"]`).classList.add('active');
    
    document.getElementById('folderTitle').textContent = title;
    currentFolder = folder;
    
    // Charger les messages du dossier
    loadMessagesForFolder(folder);
}

// Charger les messages d'un dossier
function loadMessagesForFolder(folder) {
    // Ici vous pouvez faire un appel AJAX pour charger les messages
    console.log('Chargement des messages pour:', folder);
    
    // Simulation de chargement
    const messagesList = document.getElementById('messagesList');
    messagesList.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    
    setTimeout(() => {
        // Réinitialiser avec des messages d'exemple
        loadExampleMessages();
    }, 500);
}

// Ouvrir un message
function openMessage(messageId) {
    // Marquer comme sélectionné
    document.querySelectorAll('.message-item').forEach(item => {
        item.classList.remove('selected');
    });
    document.querySelector(`[data-message-id="${messageId}"]`).classList.add('selected');
    
    // Marquer comme lu
    const messageItem = document.querySelector(`[data-message-id="${messageId}"]`);
    messageItem.classList.remove('unread');
    
    currentMessageId = messageId;
    
    // Charger le contenu du message
    loadMessageContent(messageId);
}

// Charger le contenu d'un message
function loadMessageContent(messageId) {
    const messageContent = document.getElementById('messageContent');
    
    // Messages d'exemple
    const messages = {
        1: {
            sender: 'Ministère de l\'Intérieur',
            email: 'validation@pngdi.ga',
            subject: 'Validation de votre dossier d\'association',
            date: 'Aujourd\'hui à 14:30',
            content: `
                <p>Bonjour,</p>
                <p>Nous avons le plaisir de vous informer que votre dossier de création d'association <strong>"Protection de l'Environnement"</strong> a été examiné et approuvé par nos services.</p>
                <p>Votre récépissé de déclaration sera disponible dans les prochains jours dans votre espace personnel.</p>
                <p>Vous pouvez dès à présent commencer vos activités associatives.</p>
                <p>Cordialement,<br>Le Service de Validation</p>
            `,
            attachments: ['Recepisse_Association_001.pdf']
        },
        2: {
            sender: 'Service des Subventions',
            email: 'subventions@pngdi.ga',
            subject: 'Demande de subvention - Documents manquants',
            date: 'Aujourd\'hui à 12:15',
            content: `
                <p>Bonjour,</p>
                <p>Votre demande de subvention pour le projet <strong>"Sensibilisation environnementale"</strong> a été examinée.</p>
                <p>Cependant, il manque les documents suivants :</p>
                <ul>
                    <li>Budget prévisionnel détaillé</li>
                    <li>Devis des prestations</li>
                </ul>
                <p>Merci de compléter votre dossier dans les meilleurs délais.</p>
                <p>Cordialement,<br>Le Service des Subventions</p>
            `,
            attachments: []
        },
        3: {
            sender: 'Administration PNGDI',
            email: 'admin@pngdi.ga',
            subject: 'Rappel - Déclaration annuelle',
            date: 'Aujourd\'hui à 10:45',
            content: `
                <p>Bonjour,</p>
                <p>Nous vous rappelons que la date limite pour soumettre votre déclaration annuelle d'activités approche.</p>
                <p><strong>Date limite : 31 mars 2025</strong></p>
                <p>Pour éviter toute pénalité, merci de compléter votre déclaration depuis votre espace personnel.</p>
                <p>Cordialement,<br>L'Administration PNGDI</p>
            `,
            attachments: ['Guide_Declaration_Annuelle.pdf']
        }
    };
    
    const message = messages[messageId];
    if (!message) return;
    
    messageContent.innerHTML = `
        <div class="message-header">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">${message.subject}</h5>
                    <small class="text-muted">${message.date}</small>
                </div>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary" title="Répondre" onclick="replyToMessage(${messageId})">
                        <i class="fas fa-reply"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" title="Transférer" onclick="forwardMessage(${messageId})">
                        <i class="fas fa-share"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="deleteMessage(${messageId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="sender-info">
                <strong>${message.sender}</strong>
                <small class="text-muted">&lt;${message.email}&gt;</small>
            </div>
        </div>
        <div class="message-body">
            ${message.content}
            ${message.attachments.length > 0 ? `
                <div class="attachments mt-4">
                    <h6>Pièces jointes</h6>
                    ${message.attachments.map(file => `
                        <div class="attachment-item">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            <a href="#" class="text-decoration-none">${file}</a>
                            <small class="text-muted ms-2">(245 KB)</small>
                        </div>
                    `).join('')}
                </div>
            ` : ''}
        </div>
        <div class="message-actions">
            <button class="btn btn-primary" onclick="replyToMessage(${messageId})">
                <i class="fas fa-reply me-2"></i>Répondre
            </button>
            <button class="btn btn-outline-primary ms-2" onclick="forwardMessage(${messageId})">
                <i class="fas fa-share me-2"></i>Transférer
            </button>
        </div>
    `;
}

// Rechercher des messages
function searchMessages(query) {
    const messages = document.querySelectorAll('.message-item');
    
    messages.forEach(message => {
        const text = message.textContent.toLowerCase();
        if (text.includes(query.toLowerCase())) {
            message.style.display = '';
        } else {
            message.style.display = 'none';
        }
    });
}

// Actualiser les messages
function refreshMessages() {
    location.reload();
}

// Marquer tous comme lus
function markAllAsRead() {
    document.querySelectorAll('.message-item.unread').forEach(item => {
        item.classList.remove('unread');
    });
    
    // Mettre à jour le compteur
    document.getElementById('messageCount').textContent = '(0 non lus)';
}

// Envoyer un nouveau message
function sendMessage() {
    const form = document.getElementById('newMessageForm');
    const formData = new FormData(form);
    
    // Validation
    const required = ['recipient', 'subject', 'content'];
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
    
    console.log('Envoi du message:', Object.fromEntries(formData));
    alert('Message envoyé avec succès !');
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('newMessageModal'));
    modal.hide();
    
    // Réinitialiser le formulaire
    form.reset();
}

// Sauvegarder en brouillon
function saveDraft() {
    const form = document.getElementById('newMessageForm');
    const formData = new FormData(form);
    
    console.log('Sauvegarde en brouillon:', Object.fromEntries(formData));
    alert('Message sauvegardé en brouillon');
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('newMessageModal'));
    modal.hide();
}

// Répondre à un message
function replyToMessage(messageId) {
    const modal = new bootstrap.Modal(document.getElementById('newMessageModal'));
    
    // Pré-remplir les champs
    document.querySelector('[name="recipient"]').value = 'admin';
    document.querySelector('[name="subject"]').value = 'Re: Message reçu';
    
    modal.show();
}

// Transférer un message
function forwardMessage(messageId) {
    const modal = new bootstrap.Modal(document.getElementById('newMessageModal'));
    
    // Pré-remplir les champs
    document.querySelector('[name="subject"]').value = 'Fwd: Message transféré';
    
    modal.show();
}

// Supprimer un message
function deleteMessage(messageId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
        document.querySelector(`[data-message-id="${messageId}"]`).remove();
        document.getElementById('messageContent').innerHTML = `
            <div class="no-message-selected text-center p-5">
                <i class="fas fa-envelope-open fa-4x text-muted opacity-50 mb-3"></i>
                <h5 class="text-muted">Message supprimé</h5>
                <p class="text-muted">Sélectionnez un autre message</p>
            </div>
        `;
    }
}

// Filtrer par label
function filterByLabel(label) {
    console.log('Filtrage par label:', label);
}

// Charger les messages d'exemple
function loadExampleMessages() {
    // Cette fonction serait remplacée par un appel AJAX réel
    console.log('Messages d\'exemple chargés');
}
</script>
@endsection