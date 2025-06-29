<?php
// ========================================================================
// ROUTES API - PNGDI Création d'organisation
// Compatible PHP 7.3.29
// Fichier: routes/api.php
// ========================================================================

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\OrganisationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes API pour les fonctionnalités avancées de création d'organisation
| Toutes ces routes nécessitent une authentification
|
*/

// Groupe API v1 avec middleware auth
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // ========================================
    // VÉRIFICATIONS EN TEMPS RÉEL
    // ========================================
    
    /**
     * Vérification NIP gabonais
     * POST /api/v1/verify-nip
     */
    Route::post('verify-nip', function (Request $request) {
        $request->validate([
            'nip' => 'required|string|size:13'
        ]);
        
        $nip = $request->input('nip');
        
        // Validation basique du NIP (simplifiée)
        $isValid = preg_match('/^\d{13}$/', $nip) && 
                  !preg_match('/^(\d)\1{12}$/', $nip) && // Pas tous identiques
                  !in_array($nip, ['1234567890123', '3210987654321']); // Pas de séquences simples
        
        return response()->json([
            'success' => $isValid,
            'valid' => $isValid,
            'message' => $isValid ? 'NIP valide' : 'NIP invalide'
        ]);
    });
    
    /**
     * Vérification nom d'organisation avec suggestions
     * POST /api/v1/verify-organization-name
     */
    Route::post('verify-organization-name', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:association,ong,parti_politique,confession_religieuse',
            'suggest_alternatives' => 'boolean'
        ]);
        
        $name = $request->input('name');
        $type = $request->input('type');
        $suggestAlternatives = $request->input('suggest_alternatives', false);
        
        // Vérifier si le nom existe déjà
        $existingOrg = \App\Models\Organisation::where('nom', $name)
            ->where('type', $type)
            ->whereIn('statut', ['approuve', 'en_validation', 'soumis'])
            ->first();
        
        $suggestions = [];
        if ($existingOrg && $suggestAlternatives) {
            // Générer des suggestions
            $suggestions = [
                $name . ' - Nouvelle',
                $name . ' 2',
                'Nouvelle ' . $name,
                $name . ' Alternative'
            ];
        }
        
        return response()->json([
            'success' => !$existingOrg,
            'valid' => !$existingOrg,
            'message' => $existingOrg ? 'Ce nom est déjà utilisé' : 'Nom disponible',
            'suggestions' => $suggestions
        ]);
    });
    
    /**
     * Vérification adhérents avec détection de conflits
     * POST /api/v1/verify-members
     */
    Route::post('verify-members', function (Request $request) {
        $request->validate([
            'members' => 'required|array',
            'organization_type' => 'required|string',
            'check_party_conflicts' => 'boolean'
        ]);
        
        $members = $request->input('members');
        $organizationType = $request->input('organization_type');
        $checkPartyConflicts = $request->input('check_party_conflicts', false);
        
        $conflicts = [];
        
        if ($checkPartyConflicts && $organizationType === 'parti_politique') {
            foreach ($members as $member) {
                if (isset($member['nip'])) {
                    // Vérifier si le NIP existe dans un autre parti
                    $existingMembership = \App\Models\Adherent::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                        ->where('adherents.nip', $member['nip'])
                        ->where('organisations.type', 'parti_politique')
                        ->where('adherents.is_active', true)
                        ->select('adherents.*', 'organisations.nom as parti_actuel')
                        ->first();
                    
                    if ($existingMembership) {
                        $conflicts[] = [
                            'nip' => $member['nip'],
                            'nom_complet' => $member['nom'] . ' ' . ($member['prenom'] ?? ''),
                            'parti_actuel' => $existingMembership->parti_actuel,
                            'date_adhesion' => $existingMembership->date_adhesion
                        ];
                    }
                }
            }
        }
        
        return response()->json([
            'success' => empty($conflicts),
            'valid' => empty($conflicts),
            'conflicts' => $conflicts,
            'message' => empty($conflicts) ? 'Aucun conflit détecté' : count($conflicts) . ' conflit(s) détecté(s)'
        ]);
    });
    
    // ========================================
    // GESTION DES DOCUMENTS
    // ========================================
    
    /**
     * Upload de document
     * POST /api/v1/upload-document
     */
    Route::post('upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'document_type' => 'required|string',
            'organization_id' => 'nullable|exists:organisations,id'
        ]);
        
        $file = $request->file('file');
        $documentType = $request->input('document_type');
        $organizationId = $request->input('organization_id');
        
        // Générer un nom unique
        $fileName = time() . '_' . $documentType . '.' . $file->getClientOriginalExtension();
        
        // Stocker le fichier
        $path = $file->storeAs('documents/' . auth()->id(), $fileName, 'public');
        
        return response()->json([
            'success' => true,
            'file_path' => '/storage/' . $path,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'message' => 'Document uploadé avec succès'
        ]);
    });
    
    /**
     * Preview de document
     * POST /api/v1/preview-document
     */
    Route::post('preview-document', function (Request $request) {
        $request->validate([
            'file_path' => 'required|string',
            'type' => 'required|string'
        ]);
        
        $filePath = $request->input('file_path');
        $type = $request->input('type');
        
        // Déterminer le type de fichier
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileType = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf';
        
        return response()->json([
            'success' => true,
            'preview_url' => $filePath,
            'file_type' => $fileType,
            'message' => 'Preview généré'
        ]);
    });
    
    // ========================================
    // SYSTÈME DE BROUILLONS
    // ========================================
    
    /**
     * Sauvegarder un brouillon
     * POST /api/v1/save-draft
     */
    Route::post('save-draft', function (Request $request) {
        $request->validate([
            'form_data' => 'required|array',
            'step' => 'integer|min:1|max:9',
            'organization_type' => 'nullable|string'
        ]);
        
        $userId = auth()->id();
        $formData = $request->input('form_data');
        $step = $request->input('step', 1);
        $organizationType = $request->input('organization_type');
        
        // Créer ou mettre à jour le brouillon
        $draft = \App\Models\OrganizationDraft::updateOrCreate(
            [
                'user_id' => $userId,
                'organization_type' => $organizationType
            ],
            [
                'form_data' => json_encode($formData),
                'current_step' => $step,
                'last_saved_at' => now(),
                'expires_at' => now()->addDays(7)
            ]
        );
        
        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'message' => 'Brouillon sauvegardé avec succès'
        ]);
    });
    
    /**
     * Charger un brouillon
     * GET /api/v1/load-draft/{id}
     */
    Route::get('load-draft/{id}', function ($id) {
        $userId = auth()->id();
        
        $draft = \App\Models\OrganizationDraft::where('id', $id)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Brouillon non trouvé ou expiré'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'form_data' => json_decode($draft->form_data, true),
            'current_step' => $draft->current_step,
            'organization_type' => $draft->organization_type,
            'last_saved_at' => $draft->last_saved_at,
            'message' => 'Brouillon chargé avec succès'
        ]);
    });
    
    // ========================================
    // ANALYTICS ET SUIVI
    // ========================================
    
    /**
     * Envoyer les analytics du formulaire
     * POST /api/v1/form-analytics
     */
    Route::post('form-analytics', function (Request $request) {
        $request->validate([
            'session_duration' => 'integer',
            'step_times' => 'array',
            'interactions' => 'array',
            'user_agent' => 'string',
            'screen_resolution' => 'string',
            'organization_type' => 'nullable|string',
            'completion_rate' => 'numeric'
        ]);
        
        // Stocker les analytics (ici on peut les logger ou les sauvegarder en base)
        \Log::info('Form Analytics', [
            'user_id' => auth()->id(),
            'data' => $request->all(),
            'timestamp' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Analytics enregistrées'
        ]);
    });
    
    /**
     * Validation finale avant soumission
     * POST /api/v1/validate-complete-form
     */
    Route::post('validate-complete-form', function (Request $request) {
        $request->validate([
            'form_data' => 'required|array'
        ]);
        
        $formData = $request->input('form_data');
        $errors = [];
        
        // Validation basique des données
        if (!isset($formData['metadata']['selectedOrgType']) || empty($formData['metadata']['selectedOrgType'])) {
            $errors[] = 'Type d\'organisation non sélectionné';
        }
        
        if (!isset($formData['steps']) || count($formData['steps']) < 3) {
            $errors[] = 'Formulaire incomplet';
        }
        
        return response()->json([
            'success' => empty($errors),
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Formulaire valide' : 'Erreurs de validation détectées'
        ]);
    });
});

// ========================================
// ROUTES PUBLIQUES (sans authentification)
// ========================================

/**
 * Vérification QR Code pour authentification de documents
 * GET /api/verify-qr/{code}
 */
Route::get('verify-qr/{code}', function ($code) {
    // Rechercher le QR code dans la base
    $qrCode = \App\Models\QrCode::where('code', $code)
        ->where('is_active', true)
        ->where(function($query) {
            $query->whereNull('expire_at')
                  ->orWhere('expire_at', '>', now());
        })
        ->first();
    
    if (!$qrCode) {
        return response()->json([
            'success' => false,
            'valid' => false,
            'message' => 'Code QR invalide ou expiré'
        ], 404);
    }
    
    // Incrémenter le compteur de vérifications
    $qrCode->increment('nombre_verifications');
    $qrCode->update(['derniere_verification' => now()]);
    
    // Enregistrer la vérification
    \App\Models\DocumentVerification::create([
        'qr_code_id' => $qrCode->id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'verification_reussie' => true
    ]);
    
    return response()->json([
        'success' => true,
        'valid' => true,
        'document_info' => $qrCode->donnees_verification,
        'verification_count' => $qrCode->nombre_verifications,
        'message' => 'Document authentifié avec succès'
    ]);
});

/**
 * Statistiques publiques (optionnel)
 * GET /api/public/stats
 */
Route::get('public/stats', function () {
    $stats = [
        'total_organisations' => \App\Models\Organisation::where('statut', 'approuve')->count(),
        'associations' => \App\Models\Organisation::where('type', 'association')->where('statut', 'approuve')->count(),
        'ongs' => \App\Models\Organisation::where('type', 'ong')->where('statut', 'approuve')->count(),
        'partis_politiques' => \App\Models\Organisation::where('type', 'parti_politique')->where('statut', 'approuve')->count(),
        'confessions_religieuses' => \App\Models\Organisation::where('type', 'confession_religieuse')->where('statut', 'approuve')->count(),
    ];
    
    return response()->json([
        'success' => true,
        'stats' => $stats
    ]);
});

// AJOUTER CETTE LIGNE :
Route::post('/organisations/check-existing-members', [OrganisationController::class, 'checkExistingMembers']);