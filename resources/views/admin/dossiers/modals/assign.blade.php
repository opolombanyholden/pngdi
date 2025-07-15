{{-- resources/views/admin/dossiers/modals/assign.blade.php --}}
<!-- Modal d'assignation -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel">
                    <i class="fas fa-user-check me-2"></i>Assigner le Dossier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Dossier:</strong> {{ $dossier->numero_dossier ?? 'N/A' }}<br>
                                <strong>Organisation:</strong> {{ $dossier->organisation->nom ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="agent_id" class="form-label">
                                <i class="fas fa-user me-1"></i>Sélectionner un Agent <span class="text-danger">*</span>
                            </label>
                            <select name="agent_id" id="agent_id" class="form-control" required>
                                <option value="">-- Choisir un agent --</option>
                                @if(isset($agents))
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" 
                                                data-email="{{ $agent->email }}"
                                                data-phone="{{ $agent->phone ?? '' }}">
                                            {{ $agent->name }} - {{ $agent->email }}
                                            @if($agent->phone)
                                                ({{ $agent->phone }})
                                            @endif
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="form-text text-muted">
                                L'agent sélectionné recevra une notification et le dossier passera en statut "En cours"
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="commentaire_assignation" class="form-label">
                                <i class="fas fa-comment me-1"></i>Instructions pour l'agent (optionnel)
                            </label>
                            <textarea name="commentaire" 
                                      id="commentaire_assignation" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Instructions spécifiques, points d'attention, délais particuliers..."></textarea>
                            <small class="form-text text-muted">
                                Ces instructions seront visibles par l'agent et l'organisation
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notifier_agent" name="notifier_agent" checked>
                                <label class="form-check-label" for="notifier_agent">
                                    Notifier l'agent par email
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notifier_organisation" name="notifier_organisation" checked>
                                <label class="form-check-label" for="notifier_organisation">
                                    Notifier l'organisation
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-check"></i> Assigner le Dossier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>