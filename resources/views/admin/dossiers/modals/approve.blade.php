{{-- resources/views/admin/dossiers/modals/approve.blade.php --}}
<!-- Modal d'approbation -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approuver le Dossier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Vous êtes sur le point d'approuver ce dossier</strong><br>
                                <strong>Dossier:</strong> {{ $dossier->numero_dossier ?? 'N/A' }}<br>
                                <strong>Organisation:</strong> {{ $dossier->organisation->nom ?? 'N/A' }}<br>
                                Cette action changera le statut à "Approuvé" et sera définitive.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="numero_recepisse_final" class="form-label">
                                <i class="fas fa-certificate me-1"></i>Numéro de Récépissé Final <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="numero_recepisse_final" 
                                   id="numero_recepisse_final" 
                                   class="form-control" 
                                   placeholder="Ex: REC-2025-001234"
                                   value="{{ $dossier->organisation->numero_recepisse ?? '' }}"
                                   required>
                            <small class="form-text text-muted">
                                Ce numéro sera affiché sur le récépissé officiel généré
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_approbation" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Date d'Approbation
                            </label>
                            <input type="date" 
                                   name="date_approbation" 
                                   id="date_approbation" 
                                   class="form-control" 
                                   value="{{ date('Y-m-d') }}"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validite_mois" class="form-label">
                                <i class="fas fa-clock me-1"></i>Validité (mois)
                            </label>
                            <select name="validite_mois" id="validite_mois" class="form-control">
                                <option value="12">12 mois (1 an)</option>
                                <option value="24">24 mois (2 ans)</option>
                                <option value="36" selected>36 mois (3 ans)</option>
                                <option value="60">60 mois (5 ans)</option>
                                <option value="">Illimitée</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="commentaire_approbation" class="form-label">
                                <i class="fas fa-comment me-1"></i>Commentaire d'Approbation (optionnel)
                            </label>
                            <textarea name="commentaire_approbation" 
                                      id="commentaire_approbation" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Félicitations, observations particulières, recommandations..."></textarea>
                            <small class="form-text text-muted">
                                Ce commentaire sera visible dans l'historique et peut être inclus dans le récépissé
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="generer_recepisse" name="generer_recepisse" checked>
                                <label class="form-check-label" for="generer_recepisse">
                                    <strong>Générer automatiquement le récépissé PDF</strong>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="envoyer_email_approbation" name="envoyer_email_approbation" checked>
                                <label class="form-check-label" for="envoyer_email_approbation">
                                    <strong>Envoyer notification email à l'organisation</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="publier_annuaire" name="publier_annuaire">
                                <label class="form-check-label" for="publier_annuaire">
                                    Publier dans l'annuaire public des organisations
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Approuver Définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
