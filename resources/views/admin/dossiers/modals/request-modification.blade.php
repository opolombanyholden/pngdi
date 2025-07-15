{{-- resources/views/admin/dossiers/modals/request-modification.blade.php --}}
<!-- Modal de demande de modification -->
<div class="modal fade" id="requestModificationModal" tabindex="-1" aria-labelledby="requestModificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="requestModificationModalLabel">
                    <i class="fas fa-edit me-2"></i>Demander des Modifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="requestModificationForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                <strong>Le dossier sera renvoyé à l'organisation pour modifications</strong><br>
                                <strong>Dossier:</strong> {{ $dossier->numero_dossier ?? 'N/A' }}<br>
                                <strong>Organisation:</strong> {{ $dossier->organisation->nom ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="modifications_demandees" class="form-label">
                                <i class="fas fa-list-check me-1"></i>Modifications Demandées <span class="text-danger">*</span>
                            </label>
                            <div class="modification-checklist">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modifications[]" value="documents_manquants" id="mod_docs">
                                    <label class="form-check-label" for="mod_docs">
                                        Documents manquants ou incomplets
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modifications[]" value="informations_incorrectes" id="mod_infos">
                                    <label class="form-check-label" for="mod_infos">
                                        Informations incorrectes à corriger
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modifications[]" value="format_documents" id="mod_format">
                                    <label class="form-check-label" for="mod_format">
                                        Format des documents non conforme
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modifications[]" value="signatures_manquantes" id="mod_signatures">
                                    <label class="form-check-label" for="mod_signatures">
                                        Signatures ou cachets manquants
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modifications[]" value="objet_social" id="mod_objet">
                                    <label class="form-check-label" for="mod_objet">
                                        Clarification de l'objet social
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modifications[]" value="autre" id="mod_autre">
                                    <label class="form-check-label" for="mod_autre">
                                        Autre (préciser ci-dessous)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="details_modifications" class="form-label">
                                <i class="fas fa-edit me-1"></i>Détails des Modifications <span class="text-danger">*</span>
                            </label>
                            <textarea name="details_modifications" 
                                      id="details_modifications" 
                                      class="form-control" 
                                      rows="6"
                                      placeholder="Décrivez précisément les modifications attendues, les documents à fournir, les corrections à apporter..."
                                      required></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="delai_modification" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Délai pour Modifications (jours)
                            </label>
                            <select name="delai_modification" id="delai_modification" class="form-control">
                                <option value="7">7 jours</option>
                                <option value="15" selected>15 jours</option>
                                <option value="30">30 jours</option>
                                <option value="45">45 jours</option>
                                <option value="60">60 jours</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priorite_modification" class="form-label">
                                <i class="fas fa-flag me-1"></i>Priorité
                            </label>
                            <select name="priorite_modification" id="priorite_modification" class="form-control">
                                <option value="normale" selected>Normale</option>
                                <option value="haute">Haute - Urgent</option>
                                <option value="basse">Basse - Non urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="envoyer_email_modification" name="envoyer_email_modification" checked>
                                <label class="form-check-label" for="envoyer_email_modification">
                                    <strong>Envoyer notification par email</strong>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="suspendre_traitement" name="suspendre_traitement" checked>
                                <label class="form-check-label" for="suspendre_traitement">
                                    <strong>Suspendre le traitement en attendant les modifications</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rappel_automatique" name="rappel_automatique" checked>
                                <label class="form-check-label" for="rappel_automatique">
                                    Envoyer des rappels automatiques
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Demander les Modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
