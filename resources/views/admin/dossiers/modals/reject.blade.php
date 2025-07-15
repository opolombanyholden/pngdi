{{-- resources/views/admin/dossiers/modals/reject.blade.php --}}
<!-- Modal de rejet -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Rejeter le Dossier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Attention: Vous êtes sur le point de rejeter ce dossier</strong><br>
                                <strong>Dossier:</strong> {{ $dossier->numero_dossier ?? 'N/A' }}<br>
                                <strong>Organisation:</strong> {{ $dossier->organisation->nom ?? 'N/A' }}<br>
                                Cette action nécessite une justification obligatoire.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="motif_rejet" class="form-label">
                                <i class="fas fa-list me-1"></i>Motif Principal de Rejet <span class="text-danger">*</span>
                            </label>
                            <select name="motif_rejet" id="motif_rejet" class="form-control" required>
                                <option value="">-- Sélectionner un motif --</option>
                                <option value="documents_incomplets">Documents incomplets ou manquants</option>
                                <option value="documents_non_conformes">Documents non conformes aux exigences</option>
                                <option value="informations_incorrectes">Informations incorrectes ou incohérentes</option>
                                <option value="non_respect_legislation">Non-respect de la législation en vigueur</option>
                                <option value="objet_non_autorise">Objet social non autorisé</option>
                                <option value="denomination_existante">Dénomination déjà existante</option>
                                <option value="zone_geographique">Problème de zone géographique</option>
                                <option value="delai_expire">Délai de traitement expiré</option>
                                <option value="fraude_detectee">Suspicion de fraude ou fausses informations</option>
                                <option value="autre">Autre motif (préciser ci-dessous)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="justification_rejet" class="form-label">
                                <i class="fas fa-edit me-1"></i>Justification Détaillée <span class="text-danger">*</span>
                            </label>
                            <textarea name="justification_rejet" 
                                      id="justification_rejet" 
                                      class="form-control" 
                                      rows="6"
                                      placeholder="Expliquez en détail les raisons du rejet, les documents manquants, les corrections nécessaires, etc."
                                      required></textarea>
                            <small class="form-text text-muted">
                                Cette justification sera transmise à l'organisation et archivée. Soyez précis et constructif.
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="recommandations" class="form-label">
                                <i class="fas fa-lightbulb me-1"></i>Recommandations pour une Nouvelle Soumission (optionnel)
                            </label>
                            <textarea name="recommandations" 
                                      id="recommandations" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Conseils pour améliorer le dossier, étapes à suivre, documents à fournir..."></textarea>
                            <small class="form-text text-muted">
                                Aidez l'organisation à comprendre comment corriger et re-soumettre son dossier
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="possibilite_recours" class="form-label">
                                <i class="fas fa-balance-scale me-1"></i>Possibilité de Recours
                            </label>
                            <select name="possibilite_recours" id="possibilite_recours" class="form-control">
                                <option value="oui" selected>Oui - Nouvelle soumission possible</option>
                                <option value="oui_avec_delai">Oui - Après délai de carence</option>
                                <option value="non">Non - Rejet définitif</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="delai_recours" class="form-label">
                                <i class="fas fa-calendar-plus me-1"></i>Délai pour Recours (jours)
                            </label>
                            <input type="number" 
                                   name="delai_recours" 
                                   id="delai_recours" 
                                   class="form-control" 
                                   value="30"
                                   min="0"
                                   max="365"
                                   placeholder="30">
                            <small class="form-text text-muted">Délai en jours pour contester la décision</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="envoyer_email_rejet" name="envoyer_email_rejet" checked>
                                <label class="form-check-label" for="envoyer_email_rejet">
                                    <strong>Envoyer notification de rejet par email</strong>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="generer_lettre_rejet" name="generer_lettre_rejet" checked>
                                <label class="form-check-label" for="generer_lettre_rejet">
                                    <strong>Générer la lettre officielle de rejet</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="archiver_dossier" name="archiver_dossier">
                                <label class="form-check-label" for="archiver_dossier">
                                    Archiver automatiquement le dossier après rejet
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Confirmer le Rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
