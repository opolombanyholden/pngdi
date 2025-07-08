                        <div class="step-content" id="step7" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);">
                                    <i class="fas fa-user-plus fa-3x text-white"></i>
                                </div>
                                <h3 class="text-primary">Adhérents de l'organisation</h3>
                                <p class="text-muted">Ajoutez les adhérents initiaux de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="alert alert-warning border-0 mb-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Exigences d'adhésion
                                        </h6>
                                        <div id="adherents_requirements">
                                            <p class="mb-0">Minimum requis : <span id="min_adherents">10</span> adhérents à la création</p>
                                        </div>
                                    </div>

                                    <!-- Options d'ajout -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-cog me-2"></i>
                                                Mode d'ajout des adhérents
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="adherent_mode" id="mode_manuel" value="manuel" checked>
                                                        <label class="form-check-label fw-bold" for="mode_manuel">
                                                            <i class="fas fa-keyboard me-2 text-primary"></i>
                                                            Saisie manuelle
                                                        </label>
                                                        <div class="form-text">Ajouter un par un</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="adherent_mode" id="mode_fichier" value="fichier">
                                                        <label class="form-check-label fw-bold" for="mode_fichier">
                                                            <i class="fas fa-file-excel me-2 text-success"></i>
                                                            Import fichier Excel
                                                        </label>
                                                        <div class="form-text">Charger depuis un fichier</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Saisie manuelle -->
                                    <div id="adherent_manuel_section">
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-plus me-2"></i>
                                                    Ajouter un adhérent
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-2 mb-3">
                                                        <label for="adherent_civilite" class="form-label fw-bold">Civilité</label>
                                                        <select class="form-select" id="adherent_civilite">
                                                            <option value="M">M.</option>
                                                            <option value="Mme">Mme</option>
                                                            <option value="Mlle">Mlle</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label for="adherent_nom" class="form-label fw-bold">Nom</label>
                                                        <input type="text" class="form-control" id="adherent_nom" placeholder="Nom de famille">
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label for="adherent_prenom" class="form-label fw-bold">Prénom</label>
                                                        <input type="text" class="form-control" id="adherent_prenom" placeholder="Prénom(s)">
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label for="adherent_nip" class="form-label fw-bold">NIP</label>
                                                        <input type="text" 
                                                                class="form-control" 
                                                                id="adherent_nip" 
                                                                data-validate="nip"
                                                                placeholder="A1-2345-19901225" 
                                                                maxlength="16"
                                                                pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}">
                                                        <small class="form-text text-muted">Format: XX-QQQQ-YYYYMMDD</small>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="adherent_telephone" class="form-label fw-bold">Téléphone</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+241</span>
                                                            <input type="tel" class="form-control" id="adherent_telephone" placeholder="01234567">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="adherent_profession" class="form-label fw-bold">Profession</label>
                                                        <input type="text" class="form-control" id="adherent_profession" placeholder="Profession">
                                                    </div>

                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-info" id="addAdherentBtn">
                                                            <i class="fas fa-plus me-2"></i>Ajouter cet adhérent
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Import fichier -->
                                    <div id="adherent_fichier_section" class="d-none">
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-file-upload me-2"></i>
                                                    Import depuis un fichier Excel
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="adherents_file" class="form-label fw-bold">Fichier Excel (.xlsx, .xls)</label>
                                                        <input type="file" class="form-control" id="adherents_file" accept=".xlsx,.xls">
                                                        <div class="form-text">
                                                            <i class="fas fa-info me-1"></i>
                                                            Colonnes requises : Civilité, Nom, Prénom, NIP, Téléphone, Profession
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Modèle à télécharger</label>
                                                        <div>
                                                            <a href="#" class="btn btn-outline-success" id="downloadTemplateBtn">
                                                                <i class="fas fa-download me-2"></i>
                                                                Télécharger le modèle Excel
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Liste des adhérents -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-secondary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-list me-2"></i>
                                                    Liste des adhérents
                                                </h6>
                                                <span class="badge bg-light text-dark" id="adherents_count">0 adhérent(s)</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div id="adherents_list">
                                                <div class="text-center py-4 text-muted">
                                                    <i class="fas fa-user-plus fa-3x mb-3"></i>
                                                    <p>Aucun adhérent ajouté</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>