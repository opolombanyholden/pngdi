-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : dim. 29 juin 2025 à 16:35
-- Version du serveur : 5.7.39
-- Version de PHP : 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pngdi_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `adherents`
--

CREATE TABLE `adherents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Numéro d''Identification Personnel',
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Gabonaise',
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profession` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Profession de l''adhérent',
  `fonction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Membre' COMMENT 'Fonction de l''adhérent dans l''organisation',
  `adresse_complete` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canton` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefecture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sous_prefecture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regroupement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_type` enum('urbaine','rurale') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville_commune` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrondissement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quartier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_dit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `piece_identite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Scan de la pièce',
  `date_adhesion` date NOT NULL,
  `date_exclusion` date DEFAULT NULL,
  `motif_exclusion` text COLLATE utf8mb4_unicode_ci,
  `is_fondateur` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `statut_validation` enum('en_attente','valide','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'valide',
  `justificatif_depart` text COLLATE utf8mb4_unicode_ci,
  `document_justificatif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_validation` timestamp NULL DEFAULT NULL,
  `validateur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commentaire_validation` text COLLATE utf8mb4_unicode_ci,
  `appartenance_multiple` tinyint(1) NOT NULL DEFAULT '0',
  `organisations_precedentes` json DEFAULT NULL,
  `fondateur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `historique` json DEFAULT NULL COMMENT 'Historique des modifications',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déclencheurs `adherents`
--
DELIMITER $$
CREATE TRIGGER `tr_adherent_detection_log` AFTER INSERT ON `adherents` FOR EACH ROW BEGIN
                DECLARE v_count INT DEFAULT 0;
                DECLARE v_orgs JSON;
                
                -- Vérifier s'il y a appartenance multiple
                SELECT COUNT(*), 
                       JSON_ARRAYAGG(
                           JSON_OBJECT(
                               "organisation_id", o.id,
                               "organisation_nom", o.nom,
                               "type", o.type,
                               "date_adhesion", a.date_adhesion,
                               "statut", a.statut_validation
                           )
                       )
                INTO v_count, v_orgs
                FROM adherents a
                JOIN organisations o ON a.organisation_id = o.id
                WHERE a.nip = NEW.nip 
                  AND a.is_active = 1 
                  AND a.statut_validation = "valide"
                  AND a.organisation_id != NEW.organisation_id;
                
                -- Logger si appartenance multiple détectée
                IF v_count > 0 THEN
                    INSERT INTO adherent_detection_logs (
                        nip,
                        nouvelle_organisation_id,
                        organisations_existantes,
                        anomalies_detectees,
                        action_prise,
                        created_at,
                        updated_at
                    ) VALUES (
                        NEW.nip,
                        NEW.organisation_id,
                        COALESCE(v_orgs, JSON_ARRAY()),
                        JSON_OBJECT(
                            "type", "appartenance_multiple",
                            "message", "Appartenance multiple détectée",
                            "statut_validation", NEW.statut_validation
                        ),
                        CASE 
                            WHEN NEW.statut_validation = "en_attente" THEN "creation_avec_validation"
                            ELSE "creation_directe"
                        END,
                        NOW(),
                        NOW()
                    );
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `adherent_anomalies`
--

CREATE TABLE `adherent_anomalies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adherent_id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `ligne_import` int(11) NOT NULL COMMENT 'Numéro de ligne dans le fichier importé',
  `type_anomalie` enum('majeure','mineure') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type d''anomalie détectée',
  `champ_concerne` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Champ où l''anomalie a été détectée',
  `message_anomalie` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Description de l''anomalie',
  `valeur_erronee` text COLLATE utf8mb4_unicode_ci COMMENT 'Valeur incorrecte détectée',
  `statut` enum('en_attente','resolu','ignore') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `valeur_corrigee` text COLLATE utf8mb4_unicode_ci COMMENT 'Valeur corrigée',
  `commentaire_correction` text COLLATE utf8mb4_unicode_ci COMMENT 'Commentaire de la correction',
  `corrige_par` bigint(20) UNSIGNED DEFAULT NULL,
  `date_correction` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adherent_detection_logs`
--

CREATE TABLE `adherent_detection_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nouvelle_organisation_id` bigint(20) UNSIGNED NOT NULL,
  `organisations_existantes` json NOT NULL,
  `anomalies_detectees` json NOT NULL,
  `action_prise` enum('creation_avec_validation','creation_directe','rejet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adherent_exclusions`
--

CREATE TABLE `adherent_exclusions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adherent_id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `type_exclusion` enum('demission_volontaire','exclusion_disciplinaire','non_paiement_cotisation','incompatibilite','deces','autre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motif_detaille` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_decision` date NOT NULL,
  `numero_decision` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_decision` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Scan de la décision',
  `lettre_notification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conteste` tinyint(1) NOT NULL DEFAULT '0',
  `detail_contestation` text COLLATE utf8mb4_unicode_ci,
  `validated_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adherent_histories`
--

CREATE TABLE `adherent_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adherent_id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `type_mouvement` enum('adhesion','exclusion','demission','transfert','reintegration','suspension','deces','radiation') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ancienne_organisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nouvelle_organisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `motif` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_justificatif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_effet` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `validated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `statut` enum('en_attente','valide','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `commentaire_validation` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adherent_imports`
--

CREATE TABLE `adherent_imports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `imported_by` bigint(20) UNSIGNED NOT NULL,
  `fichier_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichier_traite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_lignes` int(11) NOT NULL,
  `lignes_importees` int(11) NOT NULL DEFAULT '0',
  `lignes_rejetees` int(11) NOT NULL DEFAULT '0',
  `doublons_detectes` int(11) NOT NULL DEFAULT '0',
  `statut` enum('en_cours','complete','echoue','partiel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_cours',
  `erreurs` json DEFAULT NULL COMMENT 'Détail des erreurs par ligne',
  `doublons` json DEFAULT NULL COMMENT 'Liste des NIP en doublon',
  `statistiques` json DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `declarations`
--

CREATE TABLE `declarations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `declaration_type_id` bigint(20) UNSIGNED NOT NULL,
  `numero_declaration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_evenement` date DEFAULT NULL,
  `date_fin_evenement` date DEFAULT NULL,
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_participants` int(11) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `statut` enum('brouillon','soumise','validee','rejetee','archivee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `validated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `donnees_specifiques` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `declaration_documents`
--

CREATE TABLE `declaration_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `declaration_id` bigint(20) UNSIGNED NOT NULL,
  `type_document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_mime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `declaration_types`
--

CREATE TABLE `declaration_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `categorie` enum('activite','evenement','publication','changement_statutaire','changement_bureau','rapport_annuel','autre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `types_organisation` json NOT NULL COMMENT 'Types d''organisation concernés',
  `is_periodique` tinyint(1) NOT NULL DEFAULT '0',
  `periodicite` enum('mensuelle','trimestrielle','semestrielle','annuelle') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delai_declaration` int(11) DEFAULT NULL COMMENT 'Délai en jours après l''événement',
  `documents_requis` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `document_type_id` bigint(20) UNSIGNED NOT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_original` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nom original du fichier uploadé par l''utilisateur',
  `chemin_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_mime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` int(11) NOT NULL COMMENT 'Taille en octets',
  `hash_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Pour vérifier l''intégrité',
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID de l''utilisateur qui a uploadé le document',
  `is_validated` tinyint(1) NOT NULL DEFAULT '0',
  `is_system_generated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Document généré automatiquement par le système (accusés, certificats, etc.)',
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_document` enum('recepisse_provisoire','recepisse_definitif','certificat_enregistrement','attestation','notification_rejet','autre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_organisation` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contenu HTML du template',
  `variables` json NOT NULL COMMENT 'Variables disponibles dans le template',
  `has_qr_code` tinyint(1) NOT NULL DEFAULT '1',
  `has_watermark` tinyint(1) NOT NULL DEFAULT '1',
  `has_signature` tinyint(1) NOT NULL DEFAULT '1',
  `signature_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_types`
--

CREATE TABLE `document_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_organisation` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_operation` enum('creation','modification','cessation','ajout_adherent','retrait_adherent','declaration_activite','changement_statutaire') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'creation',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `ordre` int(11) NOT NULL DEFAULT '0',
  `format_accepte` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf,jpg,png' COMMENT 'Extensions acceptées',
  `taille_max` int(11) NOT NULL DEFAULT '5' COMMENT 'Taille max en MB',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_verifications`
--

CREATE TABLE `document_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `qr_code_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geolocation` json DEFAULT NULL,
  `verification_reussie` tinyint(1) NOT NULL,
  `motif_echec` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `type_operation` enum('creation','modification','cessation','ajout_adherent','retrait_adherent','declaration_activite','changement_statutaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_dossier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('brouillon','soumis','en_cours','approuve','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `current_step_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `donnees_supplementaires` json DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_archives`
--

CREATE TABLE `dossier_archives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `archived_by` bigint(20) UNSIGNED NOT NULL,
  `motif_archivage` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot_data` json NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_comments`
--

CREATE TABLE `dossier_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `workflow_step_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('interne','operateur','systeme') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'interne',
  `commentaire` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_visible_operateur` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiers_joints` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_locks`
--

CREATE TABLE `dossier_locks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `locked_by` bigint(20) UNSIGNED NOT NULL,
  `workflow_step_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID de session pour éviter les conflits',
  `locked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_operations`
--

CREATE TABLE `dossier_operations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type_operation` enum('creation','soumission','validation','rejet','modification','retour_pour_correction','archivage','verrouillage','deverrouillage','assignation','commentaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ancien_statut` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nouveau_statut` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workflow_step_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `donnees_avant` json DEFAULT NULL COMMENT 'Snapshot des données avant l''opération',
  `donnees_apres` json DEFAULT NULL COMMENT 'Snapshot des données après l''opération',
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_validations`
--

CREATE TABLE `dossier_validations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dossier_id` bigint(20) UNSIGNED NOT NULL,
  `workflow_step_id` bigint(20) UNSIGNED NOT NULL,
  `validation_entity_id` bigint(20) UNSIGNED NOT NULL,
  `validated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `decision` enum('approuve','rejete','en_attente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `visa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_enregistrement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_genere` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chemin du document généré',
  `assigned_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `duree_traitement` int(11) DEFAULT NULL COMMENT 'Durée en minutes',
  `donnees_supplementaires` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entity_agents`
--

CREATE TABLE `entity_agents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `validation_entity_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('responsable','agent','superviseur') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'agent',
  `peut_valider` tinyint(1) NOT NULL DEFAULT '1',
  `peut_rejeter` tinyint(1) NOT NULL DEFAULT '1',
  `peut_assigner` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `charge_actuelle` int(11) NOT NULL DEFAULT '0' COMMENT 'Nombre de dossiers en cours',
  `capacite_max` int(11) NOT NULL DEFAULT '5' COMMENT 'Nombre max de dossiers simultanés',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etablissements`
--

CREATE TABLE `etablissements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Siège, antenne, bureau, etc.',
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canton` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefecture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sous_prefecture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regroupement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_type` enum('urbaine','rurale') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'urbaine',
  `ville_commune` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrondissement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quartier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_dit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable_nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable_telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_siege_principal` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fondateurs`
--

CREATE TABLE `fondateurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Numéro d''Identification Personnel',
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Gabonaise',
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone_secondaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse_complete` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canton` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefecture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sous_prefecture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regroupement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_type` enum('urbaine','rurale') COLLATE utf8mb4_unicode_ci DEFAULT 'urbaine',
  `ville_commune` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrondissement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quartier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_dit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `piece_identite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Scan de la pièce d''identité',
  `type_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CNI, Passeport, etc.',
  `numero_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fonction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Président fondateur, Secrétaire général, etc.',
  `ordre` int(11) NOT NULL DEFAULT '0' COMMENT 'Ordre d''affichage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `guide_contents`
--

CREATE TABLE `guide_contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_operation` enum('creation','modification','cessation','ajout_adherent','retrait_adherent','declaration_activite','changement_statutaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_organisation` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu_intro` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `etapes` json NOT NULL,
  `documents_requis` json NOT NULL,
  `liens_utiles` json DEFAULT NULL,
  `video_tutoriel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temps_estime` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscription_links`
--

CREATE TABLE `inscription_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_courte` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_campagne` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `limite_inscriptions` int(11) DEFAULT NULL,
  `inscriptions_actuelles` int(11) NOT NULL DEFAULT '0',
  `date_debut` timestamp NULL DEFAULT NULL,
  `date_fin` timestamp NULL DEFAULT NULL,
  `requiert_validation` tinyint(1) NOT NULL DEFAULT '1',
  `champs_supplementaires` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `organe_members`
--

CREATE TABLE `organe_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `organe_type_id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poste` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_nomination` date NOT NULL,
  `date_fin_mandat` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `organe_types`
--

CREATE TABLE `organe_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_organisation` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `postes_disponibles` json NOT NULL,
  `membres_min` int(11) NOT NULL DEFAULT '3',
  `membres_max` int(11) DEFAULT NULL,
  `is_obligatoire` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `organisations`
--

CREATE TABLE `organisations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objet` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Objet social',
  `siege_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Adresse complète',
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canton` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefecture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sous_prefecture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regroupement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_type` enum('urbaine','rurale') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'urbaine',
  `ville_commune` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pour zone urbaine',
  `arrondissement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pour zone urbaine',
  `quartier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pour zone rurale',
  `lieu_dit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone_secondaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_recepisse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` date NOT NULL,
  `statut` enum('brouillon','soumis','en_validation','approuve','rejete','suspendu','radie') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `nombre_adherents_min` int(11) NOT NULL DEFAULT '10',
  `has_anomalies_majeures` tinyint(1) NOT NULL DEFAULT '0',
  `organes_gestion` json DEFAULT NULL COMMENT 'Membres des organes de gestion avec NIP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `organisation_settings`
--

CREATE TABLE `organisation_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type_organisation` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parametre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeur` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_valeur` enum('string','integer','boolean','json') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `is_editable` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `organization_drafts`
--

CREATE TABLE `organization_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `organization_type` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_data` json NOT NULL,
  `current_step` int(11) NOT NULL DEFAULT '1',
  `completion_percentage` int(11) NOT NULL DEFAULT '0',
  `validation_errors` json DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_saved_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom unique de la permission',
  `display_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom affiché de la permission',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Catégorie: users, organizations, workflow, system, content',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description détaillée',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type de document',
  `verifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type du modèle vérifiable',
  `verifiable_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID du modèle vérifiable',
  `document_numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `donnees_verification` json NOT NULL COMMENT 'Données à afficher lors de la vérification',
  `hash_verification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash pour vérifier l''intégrité',
  `nombre_verifications` int(11) NOT NULL DEFAULT '0',
  `derniere_verification` timestamp NULL DEFAULT NULL,
  `expire_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom unique du rôle',
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom affiché du rôle',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description du rôle',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#007bff' COMMENT 'Couleur hex pour UI',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Niveau hiérarchique',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Rôle actif ou non',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID du rôle',
  `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID de la permission',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `two_factor_codes`
--

CREATE TABLE `two_factor_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nom de famille de l''utilisateur',
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Prénom de l''utilisateur',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numéro d''Identification Personnel',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `role` enum('admin','agent','operator','visitor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visitor',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Gabon',
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL COMMENT 'Préférences utilisateur (notifications, langue, etc.)',
  `metadata` json DEFAULT NULL COMMENT 'Métadonnées additionnelles',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID de l''utilisateur',
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID de session Laravel',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Adresse IP (IPv4/IPv6)',
  `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Navigateur et OS',
  `login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date/heure de connexion',
  `logout_at` timestamp NULL DEFAULT NULL COMMENT 'Date/heure de déconnexion',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Session active ou fermée',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `validation_entities`
--

CREATE TABLE `validation_entities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('direction','service','departement','commission','externe') COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_notification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `capacite_traitement` int(11) NOT NULL DEFAULT '10' COMMENT 'Nombre de dossiers par jour',
  `horaires_travail` json DEFAULT NULL COMMENT 'Horaires de travail',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_adherents_en_attente_validation`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_adherents_en_attente_validation` (
`id` bigint(20) unsigned
,`nip` varchar(255)
,`nom` varchar(255)
,`prenom` varchar(255)
,`telephone` varchar(255)
,`email` varchar(255)
,`organisation_nom` varchar(255)
,`organisation_type` enum('association','ong','parti_politique','confession_religieuse')
,`date_adhesion` date
,`justificatif_depart` text
,`document_justificatif` varchar(255)
,`organisations_precedentes` json
,`historique` json
,`created_at` timestamp
,`jours_en_attente` int(7)
);

-- --------------------------------------------------------

--
-- Structure de la table `workflow_steps`
--

CREATE TABLE `workflow_steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_organisation` enum('association','ong','parti_politique','confession_religieuse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_operation` enum('creation','modification','cessation','ajout_adherent','retrait_adherent','declaration_activite','changement_statutaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_passage` int(11) NOT NULL COMMENT 'Ordre de passage dans le workflow',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `permet_rejet` tinyint(1) NOT NULL DEFAULT '1',
  `permet_commentaire` tinyint(1) NOT NULL DEFAULT '1',
  `genere_document` tinyint(1) NOT NULL DEFAULT '0',
  `template_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Template du document à générer',
  `champs_requis` json DEFAULT NULL COMMENT 'Champs à remplir à cette étape',
  `delai_traitement` int(11) NOT NULL DEFAULT '48' COMMENT 'Délai en heures',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `workflow_step_entities`
--

CREATE TABLE `workflow_step_entities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workflow_step_id` bigint(20) UNSIGNED NOT NULL,
  `validation_entity_id` bigint(20) UNSIGNED NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT '1' COMMENT 'Ordre si plusieurs entités pour une étape',
  `is_optional` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la vue `v_adherents_en_attente_validation`
--
DROP TABLE IF EXISTS `v_adherents_en_attente_validation`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pngdi_db`@`localhost` SQL SECURITY DEFINER VIEW `v_adherents_en_attente_validation`  AS SELECT `a`.`id` AS `id`, `a`.`nip` AS `nip`, `a`.`nom` AS `nom`, `a`.`prenom` AS `prenom`, `a`.`telephone` AS `telephone`, `a`.`email` AS `email`, `o`.`nom` AS `organisation_nom`, `o`.`type` AS `organisation_type`, `a`.`date_adhesion` AS `date_adhesion`, `a`.`justificatif_depart` AS `justificatif_depart`, `a`.`document_justificatif` AS `document_justificatif`, `a`.`organisations_precedentes` AS `organisations_precedentes`, `a`.`historique` AS `historique`, `a`.`created_at` AS `created_at`, (to_days(now()) - to_days(`a`.`created_at`)) AS `jours_en_attente` FROM (`adherents` `a` join `organisations` `o` on((`a`.`organisation_id` = `o`.`id`))) WHERE (`a`.`statut_validation` = 'en_attente') ORDER BY `a`.`created_at` ASC  ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adherents`
--
ALTER TABLE `adherents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adherents_nip_index` (`nip`),
  ADD KEY `adherents_organisation_id_is_active_index` (`organisation_id`,`is_active`),
  ADD KEY `adherents_fondateur_id_index` (`fondateur_id`),
  ADD KEY `idx_adherents_profession` (`profession`),
  ADD KEY `idx_adherents_fonction` (`fonction`),
  ADD KEY `adherents_validateur_id_foreign` (`validateur_id`),
  ADD KEY `adherents_statut_validation_index` (`statut_validation`),
  ADD KEY `adherents_appartenance_multiple_index` (`appartenance_multiple`),
  ADD KEY `adherents_nip_statut_validation_index` (`nip`,`statut_validation`);

--
-- Index pour la table `adherent_anomalies`
--
ALTER TABLE `adherent_anomalies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adherent_anomalies_corrige_par_foreign` (`corrige_par`),
  ADD KEY `adherent_anomalies_organisation_id_type_anomalie_index` (`organisation_id`,`type_anomalie`),
  ADD KEY `adherent_anomalies_adherent_id_statut_index` (`adherent_id`,`statut`),
  ADD KEY `adherent_anomalies_statut_type_anomalie_index` (`statut`,`type_anomalie`);

--
-- Index pour la table `adherent_detection_logs`
--
ALTER TABLE `adherent_detection_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adherent_detection_logs_nip_index` (`nip`),
  ADD KEY `adherent_detection_logs_nouvelle_organisation_id_index` (`nouvelle_organisation_id`),
  ADD KEY `adherent_detection_logs_action_prise_index` (`action_prise`),
  ADD KEY `adherent_detection_logs_created_at_index` (`created_at`);

--
-- Index pour la table `adherent_exclusions`
--
ALTER TABLE `adherent_exclusions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adherent_exclusions_organisation_id_foreign` (`organisation_id`),
  ADD KEY `adherent_exclusions_validated_by_foreign` (`validated_by`),
  ADD KEY `adherent_exclusions_adherent_id_organisation_id_index` (`adherent_id`,`organisation_id`),
  ADD KEY `adherent_exclusions_type_exclusion_index` (`type_exclusion`),
  ADD KEY `adherent_exclusions_date_decision_index` (`date_decision`);

--
-- Index pour la table `adherent_histories`
--
ALTER TABLE `adherent_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adherent_histories_ancienne_organisation_id_foreign` (`ancienne_organisation_id`),
  ADD KEY `adherent_histories_nouvelle_organisation_id_foreign` (`nouvelle_organisation_id`),
  ADD KEY `adherent_histories_created_by_foreign` (`created_by`),
  ADD KEY `adherent_histories_validated_by_foreign` (`validated_by`),
  ADD KEY `adherent_histories_adherent_id_type_mouvement_index` (`adherent_id`,`type_mouvement`),
  ADD KEY `adherent_histories_organisation_id_date_effet_index` (`organisation_id`,`date_effet`),
  ADD KEY `adherent_histories_statut_index` (`statut`);

--
-- Index pour la table `adherent_imports`
--
ALTER TABLE `adherent_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adherent_imports_organisation_id_statut_index` (`organisation_id`,`statut`),
  ADD KEY `adherent_imports_imported_by_index` (`imported_by`);

--
-- Index pour la table `declarations`
--
ALTER TABLE `declarations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `declarations_numero_declaration_unique` (`numero_declaration`),
  ADD KEY `declarations_declaration_type_id_foreign` (`declaration_type_id`),
  ADD KEY `declarations_submitted_by_foreign` (`submitted_by`),
  ADD KEY `declarations_validated_by_foreign` (`validated_by`),
  ADD KEY `declarations_organisation_id_declaration_type_id_index` (`organisation_id`,`declaration_type_id`),
  ADD KEY `declarations_statut_created_at_index` (`statut`,`created_at`),
  ADD KEY `declarations_date_evenement_index` (`date_evenement`),
  ADD KEY `declarations_numero_declaration_index` (`numero_declaration`);

--
-- Index pour la table `declaration_documents`
--
ALTER TABLE `declaration_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `declaration_documents_declaration_id_index` (`declaration_id`);

--
-- Index pour la table `declaration_types`
--
ALTER TABLE `declaration_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `declaration_types_code_unique` (`code`),
  ADD KEY `declaration_types_categorie_is_active_index` (`categorie`,`is_active`),
  ADD KEY `declaration_types_is_periodique_index` (`is_periodique`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_document_type_id_foreign` (`document_type_id`),
  ADD KEY `documents_dossier_id_document_type_id_index` (`dossier_id`,`document_type_id`),
  ADD KEY `documents_is_validated_index` (`is_validated`),
  ADD KEY `idx_documents_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_documents_system_generated` (`is_system_generated`);

--
-- Index pour la table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_templates_code_unique` (`code`),
  ADD KEY `document_templates_type_document_type_organisation_index` (`type_document`,`type_organisation`),
  ADD KEY `document_templates_is_active_index` (`is_active`);

--
-- Index pour la table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_types_code_unique` (`code`),
  ADD KEY `document_types_type_organisation_type_operation_is_active_index` (`type_organisation`,`type_operation`,`is_active`),
  ADD KEY `document_types_ordre_index` (`ordre`);

--
-- Index pour la table `document_verifications`
--
ALTER TABLE `document_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_verifications_qr_code_id_created_at_index` (`qr_code_id`,`created_at`),
  ADD KEY `document_verifications_verification_reussie_index` (`verification_reussie`),
  ADD KEY `document_verifications_ip_address_index` (`ip_address`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dossiers_numero_dossier_unique` (`numero_dossier`),
  ADD KEY `dossiers_current_step_id_foreign` (`current_step_id`),
  ADD KEY `dossiers_locked_by_foreign` (`locked_by`),
  ADD KEY `dossiers_organisation_id_statut_index` (`organisation_id`,`statut`),
  ADD KEY `dossiers_statut_created_at_index` (`statut`,`created_at`),
  ADD KEY `dossiers_numero_dossier_index` (`numero_dossier`),
  ADD KEY `dossiers_assigned_to_index` (`assigned_to`);

--
-- Index pour la table `dossier_archives`
--
ALTER TABLE `dossier_archives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_archives_archived_by_foreign` (`archived_by`),
  ADD KEY `dossier_archives_dossier_id_index` (`dossier_id`),
  ADD KEY `dossier_archives_archived_at_index` (`archived_at`);

--
-- Index pour la table `dossier_comments`
--
ALTER TABLE `dossier_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_comments_user_id_foreign` (`user_id`),
  ADD KEY `dossier_comments_workflow_step_id_foreign` (`workflow_step_id`),
  ADD KEY `dossier_comments_parent_id_foreign` (`parent_id`),
  ADD KEY `dossier_comments_dossier_id_created_at_index` (`dossier_id`,`created_at`),
  ADD KEY `dossier_comments_type_is_visible_operateur_index` (`type`,`is_visible_operateur`);

--
-- Index pour la table `dossier_locks`
--
ALTER TABLE `dossier_locks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dossier_locks_dossier_id_is_active_unique` (`dossier_id`,`is_active`),
  ADD KEY `dossier_locks_workflow_step_id_foreign` (`workflow_step_id`),
  ADD KEY `dossier_locks_locked_by_is_active_index` (`locked_by`,`is_active`),
  ADD KEY `dossier_locks_expires_at_index` (`expires_at`);

--
-- Index pour la table `dossier_operations`
--
ALTER TABLE `dossier_operations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_operations_workflow_step_id_foreign` (`workflow_step_id`),
  ADD KEY `dossier_operations_dossier_id_created_at_index` (`dossier_id`,`created_at`),
  ADD KEY `dossier_operations_user_id_type_operation_index` (`user_id`,`type_operation`),
  ADD KEY `dossier_operations_type_operation_index` (`type_operation`);

--
-- Index pour la table `dossier_validations`
--
ALTER TABLE `dossier_validations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_validations_workflow_step_id_foreign` (`workflow_step_id`),
  ADD KEY `dossier_validations_dossier_id_workflow_step_id_index` (`dossier_id`,`workflow_step_id`),
  ADD KEY `dossier_validations_validation_entity_id_decision_index` (`validation_entity_id`,`decision`),
  ADD KEY `dossier_validations_validated_by_index` (`validated_by`),
  ADD KEY `dossier_validations_decision_index` (`decision`);

--
-- Index pour la table `entity_agents`
--
ALTER TABLE `entity_agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entity_agents_validation_entity_id_user_id_unique` (`validation_entity_id`,`user_id`),
  ADD KEY `entity_agents_user_id_foreign` (`user_id`),
  ADD KEY `entity_agents_is_active_charge_actuelle_index` (`is_active`,`charge_actuelle`);

--
-- Index pour la table `etablissements`
--
ALTER TABLE `etablissements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `etablissements_organisation_id_index` (`organisation_id`),
  ADD KEY `etablissements_latitude_longitude_index` (`latitude`,`longitude`),
  ADD KEY `etablissements_province_prefecture_index` (`province`,`prefecture`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `fondateurs`
--
ALTER TABLE `fondateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fondateurs_organisation_id_nip_unique` (`organisation_id`,`nip`),
  ADD KEY `fondateurs_organisation_id_index` (`organisation_id`),
  ADD KEY `fondateurs_nip_index` (`nip`);

--
-- Index pour la table `guide_contents`
--
ALTER TABLE `guide_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guide_contents_code_unique` (`code`),
  ADD KEY `guide_contents_type_operation_type_organisation_index` (`type_operation`,`type_organisation`);

--
-- Index pour la table `inscription_links`
--
ALTER TABLE `inscription_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inscription_links_token_unique` (`token`),
  ADD UNIQUE KEY `inscription_links_url_courte_unique` (`url_courte`),
  ADD KEY `inscription_links_created_by_foreign` (`created_by`),
  ADD KEY `inscription_links_organisation_id_is_active_index` (`organisation_id`,`is_active`),
  ADD KEY `inscription_links_token_index` (`token`),
  ADD KEY `inscription_links_date_debut_date_fin_index` (`date_debut`,`date_fin`),
  ADD KEY `inscription_links_is_active_index` (`is_active`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `organe_members`
--
ALTER TABLE `organe_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_organe_member` (`organisation_id`,`organe_type_id`,`poste`,`is_active`),
  ADD KEY `organe_members_organe_type_id_foreign` (`organe_type_id`),
  ADD KEY `organe_members_organisation_id_organe_type_id_index` (`organisation_id`,`organe_type_id`),
  ADD KEY `organe_members_nip_index` (`nip`);

--
-- Index pour la table `organe_types`
--
ALTER TABLE `organe_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organe_types_code_unique` (`code`),
  ADD KEY `organe_types_type_organisation_is_active_index` (`type_organisation`,`is_active`);

--
-- Index pour la table `organisations`
--
ALTER TABLE `organisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organisations_nom_unique` (`nom`),
  ADD UNIQUE KEY `organisations_sigle_unique` (`sigle`),
  ADD UNIQUE KEY `organisations_numero_recepisse_unique` (`numero_recepisse`),
  ADD KEY `organisations_user_id_type_index` (`user_id`,`type`),
  ADD KEY `organisations_statut_index` (`statut`),
  ADD KEY `organisations_numero_recepisse_index` (`numero_recepisse`),
  ADD KEY `organisations_province_prefecture_index` (`province`,`prefecture`),
  ADD KEY `organisations_zone_type_index` (`zone_type`);

--
-- Index pour la table `organisation_settings`
--
ALTER TABLE `organisation_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organisation_settings_type_organisation_parametre_unique` (`type_organisation`,`parametre`),
  ADD KEY `organisation_settings_parametre_index` (`parametre`);

--
-- Index pour la table `organization_drafts`
--
ALTER TABLE `organization_drafts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_org_type_draft` (`user_id`,`organization_type`),
  ADD KEY `organization_drafts_user_id_organization_type_index` (`user_id`,`organization_type`),
  ADD KEY `organization_drafts_last_saved_at_index` (`last_saved_at`),
  ADD KEY `organization_drafts_expires_at_index` (`expires_at`),
  ADD KEY `organization_drafts_current_step_index` (`current_step`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD KEY `permissions_category_index` (`category`),
  ADD KEY `permissions_name_index` (`name`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_codes_code_unique` (`code`),
  ADD KEY `qr_codes_verifiable_type_verifiable_id_index` (`verifiable_type`,`verifiable_id`),
  ADD KEY `qr_codes_code_index` (`code`),
  ADD KEY `qr_codes_expire_at_index` (`expire_at`),
  ADD KEY `qr_codes_is_active_index` (`is_active`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `roles_is_active_level_index` (`is_active`,`level`),
  ADD KEY `roles_name_index` (`name`);

--
-- Index pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permissions_role_id_index` (`role_id`),
  ADD KEY `role_permissions_permission_id_index` (`permission_id`);

--
-- Index pour la table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `two_factor_codes_user_id_code_index` (`user_id`,`code`),
  ADD KEY `two_factor_codes_expires_at_used_index` (`expires_at`,`used`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_active_index` (`is_active`),
  ADD KEY `users_role_is_active_index` (`role`,`is_active`),
  ADD KEY `users_locked_until_index` (`locked_until`),
  ADD KEY `users_email_is_active_index` (`email`,`is_active`),
  ADD KEY `users_role_id_status_index` (`role_id`,`status`),
  ADD KEY `users_verification_token_index` (`verification_token`),
  ADD KEY `users_created_by_foreign` (`created_by`),
  ADD KEY `users_updated_by_foreign` (`updated_by`),
  ADD KEY `idx_users_nom_prenom` (`nom`,`prenom`);

--
-- Index pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_sessions_user_id_is_active_index` (`user_id`,`is_active`),
  ADD KEY `user_sessions_session_id_index` (`session_id`),
  ADD KEY `user_sessions_login_at_index` (`login_at`),
  ADD KEY `user_sessions_ip_address_index` (`ip_address`);

--
-- Index pour la table `validation_entities`
--
ALTER TABLE `validation_entities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `validation_entities_code_unique` (`code`),
  ADD KEY `validation_entities_is_active_index` (`is_active`),
  ADD KEY `validation_entities_type_index` (`type`);

--
-- Index pour la table `workflow_steps`
--
ALTER TABLE `workflow_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `workflow_steps_code_unique` (`code`),
  ADD KEY `workflow_steps_type_organisation_type_operation_is_active_index` (`type_organisation`,`type_operation`,`is_active`),
  ADD KEY `workflow_steps_numero_passage_index` (`numero_passage`);

--
-- Index pour la table `workflow_step_entities`
--
ALTER TABLE `workflow_step_entities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_step_entity` (`workflow_step_id`,`validation_entity_id`),
  ADD KEY `workflow_step_entities_validation_entity_id_foreign` (`validation_entity_id`),
  ADD KEY `workflow_step_entities_ordre_index` (`ordre`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `adherents`
--
ALTER TABLE `adherents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `adherent_anomalies`
--
ALTER TABLE `adherent_anomalies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `adherent_detection_logs`
--
ALTER TABLE `adherent_detection_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `adherent_exclusions`
--
ALTER TABLE `adherent_exclusions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `adherent_histories`
--
ALTER TABLE `adherent_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `adherent_imports`
--
ALTER TABLE `adherent_imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `declarations`
--
ALTER TABLE `declarations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `declaration_documents`
--
ALTER TABLE `declaration_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `declaration_types`
--
ALTER TABLE `declaration_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_verifications`
--
ALTER TABLE `document_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_archives`
--
ALTER TABLE `dossier_archives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_comments`
--
ALTER TABLE `dossier_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_locks`
--
ALTER TABLE `dossier_locks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_operations`
--
ALTER TABLE `dossier_operations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_validations`
--
ALTER TABLE `dossier_validations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entity_agents`
--
ALTER TABLE `entity_agents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `etablissements`
--
ALTER TABLE `etablissements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fondateurs`
--
ALTER TABLE `fondateurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `guide_contents`
--
ALTER TABLE `guide_contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscription_links`
--
ALTER TABLE `inscription_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `organe_members`
--
ALTER TABLE `organe_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `organe_types`
--
ALTER TABLE `organe_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `organisations`
--
ALTER TABLE `organisations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `organisation_settings`
--
ALTER TABLE `organisation_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `organization_drafts`
--
ALTER TABLE `organization_drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `validation_entities`
--
ALTER TABLE `validation_entities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `workflow_steps`
--
ALTER TABLE `workflow_steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `workflow_step_entities`
--
ALTER TABLE `workflow_step_entities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `adherents`
--
ALTER TABLE `adherents`
  ADD CONSTRAINT `adherents_fondateur_id_foreign` FOREIGN KEY (`fondateur_id`) REFERENCES `fondateurs` (`id`),
  ADD CONSTRAINT `adherents_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adherents_validateur_id_foreign` FOREIGN KEY (`validateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `adherent_anomalies`
--
ALTER TABLE `adherent_anomalies`
  ADD CONSTRAINT `adherent_anomalies_adherent_id_foreign` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adherent_anomalies_corrige_par_foreign` FOREIGN KEY (`corrige_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `adherent_anomalies_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `adherent_detection_logs`
--
ALTER TABLE `adherent_detection_logs`
  ADD CONSTRAINT `adherent_detection_logs_nouvelle_organisation_id_foreign` FOREIGN KEY (`nouvelle_organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `adherent_exclusions`
--
ALTER TABLE `adherent_exclusions`
  ADD CONSTRAINT `adherent_exclusions_adherent_id_foreign` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adherent_exclusions_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`),
  ADD CONSTRAINT `adherent_exclusions_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `adherent_histories`
--
ALTER TABLE `adherent_histories`
  ADD CONSTRAINT `adherent_histories_adherent_id_foreign` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adherent_histories_ancienne_organisation_id_foreign` FOREIGN KEY (`ancienne_organisation_id`) REFERENCES `organisations` (`id`),
  ADD CONSTRAINT `adherent_histories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `adherent_histories_nouvelle_organisation_id_foreign` FOREIGN KEY (`nouvelle_organisation_id`) REFERENCES `organisations` (`id`),
  ADD CONSTRAINT `adherent_histories_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`),
  ADD CONSTRAINT `adherent_histories_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `adherent_imports`
--
ALTER TABLE `adherent_imports`
  ADD CONSTRAINT `adherent_imports_imported_by_foreign` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `adherent_imports_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`);

--
-- Contraintes pour la table `declarations`
--
ALTER TABLE `declarations`
  ADD CONSTRAINT `declarations_declaration_type_id_foreign` FOREIGN KEY (`declaration_type_id`) REFERENCES `declaration_types` (`id`),
  ADD CONSTRAINT `declarations_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`),
  ADD CONSTRAINT `declarations_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `declarations_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `declaration_documents`
--
ALTER TABLE `declaration_documents`
  ADD CONSTRAINT `declaration_documents_declaration_id_foreign` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  ADD CONSTRAINT `documents_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `document_verifications`
--
ALTER TABLE `document_verifications`
  ADD CONSTRAINT `document_verifications_qr_code_id_foreign` FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes` (`id`);

--
-- Contraintes pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD CONSTRAINT `dossiers_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossiers_current_step_id_foreign` FOREIGN KEY (`current_step_id`) REFERENCES `workflow_steps` (`id`),
  ADD CONSTRAINT `dossiers_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossiers_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dossier_archives`
--
ALTER TABLE `dossier_archives`
  ADD CONSTRAINT `dossier_archives_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossier_archives_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`);

--
-- Contraintes pour la table `dossier_comments`
--
ALTER TABLE `dossier_comments`
  ADD CONSTRAINT `dossier_comments_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `dossier_comments` (`id`),
  ADD CONSTRAINT `dossier_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossier_comments_workflow_step_id_foreign` FOREIGN KEY (`workflow_step_id`) REFERENCES `workflow_steps` (`id`);

--
-- Contraintes pour la table `dossier_locks`
--
ALTER TABLE `dossier_locks`
  ADD CONSTRAINT `dossier_locks_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_locks_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossier_locks_workflow_step_id_foreign` FOREIGN KEY (`workflow_step_id`) REFERENCES `workflow_steps` (`id`);

--
-- Contraintes pour la table `dossier_operations`
--
ALTER TABLE `dossier_operations`
  ADD CONSTRAINT `dossier_operations_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_operations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossier_operations_workflow_step_id_foreign` FOREIGN KEY (`workflow_step_id`) REFERENCES `workflow_steps` (`id`);

--
-- Contraintes pour la table `dossier_validations`
--
ALTER TABLE `dossier_validations`
  ADD CONSTRAINT `dossier_validations_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossier_validations_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `dossier_validations_validation_entity_id_foreign` FOREIGN KEY (`validation_entity_id`) REFERENCES `validation_entities` (`id`),
  ADD CONSTRAINT `dossier_validations_workflow_step_id_foreign` FOREIGN KEY (`workflow_step_id`) REFERENCES `workflow_steps` (`id`);

--
-- Contraintes pour la table `entity_agents`
--
ALTER TABLE `entity_agents`
  ADD CONSTRAINT `entity_agents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entity_agents_validation_entity_id_foreign` FOREIGN KEY (`validation_entity_id`) REFERENCES `validation_entities` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `etablissements`
--
ALTER TABLE `etablissements`
  ADD CONSTRAINT `etablissements_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fondateurs`
--
ALTER TABLE `fondateurs`
  ADD CONSTRAINT `fondateurs_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscription_links`
--
ALTER TABLE `inscription_links`
  ADD CONSTRAINT `inscription_links_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inscription_links_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`);

--
-- Contraintes pour la table `organe_members`
--
ALTER TABLE `organe_members`
  ADD CONSTRAINT `organe_members_organe_type_id_foreign` FOREIGN KEY (`organe_type_id`) REFERENCES `organe_types` (`id`),
  ADD CONSTRAINT `organe_members_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`);

--
-- Contraintes pour la table `organisations`
--
ALTER TABLE `organisations`
  ADD CONSTRAINT `organisations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `organization_drafts`
--
ALTER TABLE `organization_drafts`
  ADD CONSTRAINT `organization_drafts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD CONSTRAINT `two_factor_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `workflow_step_entities`
--
ALTER TABLE `workflow_step_entities`
  ADD CONSTRAINT `workflow_step_entities_validation_entity_id_foreign` FOREIGN KEY (`validation_entity_id`) REFERENCES `validation_entities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workflow_step_entities_workflow_step_id_foreign` FOREIGN KEY (`workflow_step_id`) REFERENCES `workflow_steps` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
