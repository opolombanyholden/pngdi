<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\WorkflowStep;
use App\Models\DossierValidation;
use App\Models\DossierOperation;
use App\Models\DossierLock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class WorkflowService
{
    /**
     * Avancer le dossier à l'étape suivante
     */
    public function moveToNextStep(Dossier $dossier, array $data = []): bool
    {
        DB::beginTransaction();
        
        try {
            // Vérifier que le dossier peut avancer
            if (!$this->canMoveForward($dossier)) {
                throw new Exception('Le dossier ne peut pas avancer à l\'étape suivante');
            }
            
            // Obtenir l'étape suivante
            $nextStep = $dossier->getNextStep();
            if (!$nextStep) {
                // Si pas d'étape suivante, le dossier est terminé
                $dossier->update([
                    'statut' => Dossier::STATUT_ACCEPTE,
                    'date_traitement' => now()
                ]);
                
                // Enregistrer l'opération
                $this->recordOperation($dossier, 'workflow_completed', $data);
                
                DB::commit();
                return true;
            }
            
            // Créer la validation pour l'étape actuelle si elle existe
            if ($dossier->current_step_id) {
                DossierValidation::create([
                    'dossier_id' => $dossier->id,
                    'workflow_step_id' => $dossier->current_step_id,
                    'validation_entity_id' => $dossier->currentStep->validation_entity_id,
                    'decision' => 'approuve',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                    'commentaire' => $data['commentaire'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'visa' => $data['visa'] ?? null
                ]);
            }
            
            // Mettre à jour le dossier
            $dossier->update([
                'current_step_id' => $nextStep->id,
                'statut' => Dossier::STATUT_EN_COURS
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'step_forward', array_merge($data, [
                'from_step' => $dossier->currentStep->nom ?? 'Début',
                'to_step' => $nextStep->nom
            ]));
            
            // Déverrouiller le dossier
            $this->unlockDossier($dossier);
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Rejeter le dossier et le renvoyer à l'étape précédente ou à l'opérateur
     */
    public function rejectDossier(Dossier $dossier, string $motif, array $data = []): bool
    {
        if (empty($motif)) {
            throw new Exception('Un motif de rejet est obligatoire');
        }
        
        DB::beginTransaction();
        
        try {
            // Créer la validation de rejet
            if ($dossier->current_step_id) {
                DossierValidation::create([
                    'dossier_id' => $dossier->id,
                    'workflow_step_id' => $dossier->current_step_id,
                    'validation_entity_id' => $dossier->currentStep->validation_entity_id,
                    'decision' => 'rejete',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                    'commentaire' => $motif,
                    'motif_rejet' => $motif
                ]);
            }
            
            // Déterminer l'étape de retour
            $previousStep = $dossier->getPreviousStep();
            
            if ($previousStep) {
                // Retour à l'étape précédente
                $dossier->update([
                    'current_step_id' => $previousStep->id,
                    'statut' => Dossier::STATUT_EN_COURS
                ]);
            } else {
                // Retour à l'opérateur
                $dossier->update([
                    'current_step_id' => null,
                    'statut' => Dossier::STATUT_REJETE,
                    'motif_rejet' => $motif
                ]);
            }
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'rejected', array_merge($data, [
                'motif' => $motif,
                'rejected_at_step' => $dossier->currentStep->nom ?? 'Inconnu'
            ]));
            
            // Déverrouiller le dossier
            $this->unlockDossier($dossier);
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Verrouiller un dossier pour traitement
     */
    public function lockDossier(Dossier $dossier, User $user): DossierLock
    {
        // Vérifier si le dossier est déjà verrouillé
        if ($dossier->isLocked() && !$dossier->isLockedBy($user->id)) {
            $lockedBy = $dossier->getLockedByUser();
            throw new Exception(sprintf(
                'Ce dossier est actuellement en cours de traitement par %s',
                $lockedBy ? $lockedBy->name : 'un autre utilisateur'
            ));
        }
        
        // Si déjà verrouillé par le même utilisateur, retourner le verrou existant
        if ($dossier->isLockedBy($user->id)) {
            return $dossier->lock()->where('is_active', true)->first();
        }
        
        // Créer un nouveau verrou
        $lock = DossierLock::create([
            'dossier_id' => $dossier->id,
            'user_id' => $user->id,
            'locked_at' => now(),
            'expires_at' => now()->addMinutes(30), // Expiration après 30 minutes
            'is_active' => true
        ]);
        
        // Enregistrer l'opération
        $this->recordOperation($dossier, 'locked', [
            'locked_by' => $user->name
        ]);
        
        return $lock;
    }
    
    /**
     * Déverrouiller un dossier
     */
    public function unlockDossier(Dossier $dossier): bool
    {
        $lock = $dossier->lock()->where('is_active', true)->first();
        
        if ($lock) {
            $lock->update([
                'is_active' => false,
                'unlocked_at' => now()
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'unlocked');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtenir le prochain dossier à traiter (FIFO)
     */
    public function getNextDossierToProcess(User $user): ?Dossier
    {
        // Obtenir les entités de validation de l'utilisateur
        $validationEntities = $user->validationEntities()->pluck('id');
        
        if ($validationEntities->isEmpty()) {
            return null;
        }
        
        // Chercher le prochain dossier non verrouillé
        return Dossier::whereIn('statut', [Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])
            ->whereHas('currentStep', function ($query) use ($validationEntities) {
                $query->whereIn('validation_entity_id', $validationEntities);
            })
            ->whereDoesntHave('lock', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('date_soumission', 'asc')
            ->first();
    }
    
    /**
     * Attribuer un dossier spécifique à un agent (réservé aux managers)
     */
    public function assignDossierToAgent(Dossier $dossier, User $agent, User $manager): bool
    {
        // Vérifier que le manager a les droits
        if (!$manager->hasRole(['admin', 'manager'])) {
            throw new Exception('Seuls les managers peuvent attribuer des dossiers');
        }
        
        DB::beginTransaction();
        
        try {
            // Verrouiller le dossier pour l'agent
            $this->lockDossier($dossier, $agent);
            
            // Enregistrer l'attribution
            $this->recordOperation($dossier, 'assigned', [
                'assigned_to' => $agent->name,
                'assigned_by' => $manager->name
            ]);
            
            // Notification à l'agent
            // TODO: Implémenter la notification
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Vérifier si un dossier peut avancer
     */
    protected function canMoveForward(Dossier $dossier): bool
    {
        // Le dossier doit être en cours ou soumis
        if (!in_array($dossier->statut, [Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])) {
            return false;
        }
        
        // Vérifier que tous les documents obligatoires sont validés
        if (!$dossier->hasAllRequiredDocuments()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Enregistrer une opération sur le dossier
     */
    protected function recordOperation(Dossier $dossier, string $type, array $data = []): DossierOperation
    {
        return DossierOperation::create([
            'dossier_id' => $dossier->id,
            'type_operation' => $type,
            'user_id' => Auth::id(),
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
    
    /**
     * Obtenir l'historique complet d'un dossier
     */
    public function getDossierHistory(Dossier $dossier): array
    {
        $history = [];
        
        // Opérations
        foreach ($dossier->operations()->with('user')->orderBy('created_at', 'desc')->get() as $operation) {
            $history[] = [
                'type' => 'operation',
                'action' => $operation->type_operation,
                'user' => $operation->user ? $operation->user->name : 'Système',
                'date' => $operation->created_at,
                'details' => $operation->data
            ];
        }
        
        // Validations
        foreach ($dossier->validations()->with(['validatedBy', 'workflowStep'])->orderBy('validated_at', 'desc')->get() as $validation) {
            $history[] = [
                'type' => 'validation',
                'action' => $validation->decision,
                'user' => $validation->validatedBy ? $validation->validatedBy->name : 'Inconnu',
                'date' => $validation->validated_at,
                'step' => $validation->workflowStep ? $validation->workflowStep->nom : 'Inconnu',
                'details' => [
                    'commentaire' => $validation->commentaire,
                    'motif_rejet' => $validation->motif_rejet
                ]
            ];
        }
        
        // Trier par date
        usort($history, function ($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });
        
        return $history;
    }
    
    /**
     * Obtenir les statistiques du workflow
     */
    public function getWorkflowStatistics(string $typeOrganisation = null, string $typeOperation = null): array
    {
        $query = Dossier::query();
        
        if ($typeOrganisation) {
            $query->whereHas('organisation', function ($q) use ($typeOrganisation) {
                $q->where('type', $typeOrganisation);
            });
        }
        
        if ($typeOperation) {
            $query->where('type_operation', $typeOperation);
        }
        
        $stats = [
            'total' => $query->count(),
            'par_statut' => [],
            'temps_moyen_traitement' => null,
            'taux_approbation' => null
        ];
        
        // Dossiers par statut
        $parStatut = (clone $query)->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();
        
        foreach ($parStatut as $stat) {
            $stats['par_statut'][$stat->statut] = $stat->total;
        }
        
        // Temps moyen de traitement (en jours)
        $dossiersTraites = (clone $query)->whereIn('statut', [Dossier::STATUT_ACCEPTE, Dossier::STATUT_REJETE])
            ->whereNotNull('date_soumission')
            ->whereNotNull('date_traitement')
            ->get();
        
        if ($dossiersTraites->count() > 0) {
            $totalJours = 0;
            foreach ($dossiersTraites as $dossier) {
                $totalJours += $dossier->date_soumission->diffInDays($dossier->date_traitement);
            }
            $stats['temps_moyen_traitement'] = round($totalJours / $dossiersTraites->count(), 1);
        }
        
        // Taux d'approbation
        $acceptes = $stats['par_statut'][Dossier::STATUT_ACCEPTE] ?? 0;
        $rejetes = $stats['par_statut'][Dossier::STATUT_REJETE] ?? 0;
        $total = $acceptes + $rejetes;
        
        if ($total > 0) {
            $stats['taux_approbation'] = round(($acceptes / $total) * 100, 1);
        }
        
        return $stats;
    }
    
    /**
     * Nettoyer les verrous expirés
     */
    public function cleanExpiredLocks(): int
    {
        $expired = DossierLock::where('is_active', true)
            ->where('expires_at', '<', now())
            ->get();
        
        $count = 0;
        foreach ($expired as $lock) {
            $lock->update([
                'is_active' => false,
                'unlocked_at' => now()
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($lock->dossier, 'lock_expired', [
                'expired_at' => $lock->expires_at->toDateTimeString()
            ]);
            
            $count++;
        }
        
        return $count;
    }
}