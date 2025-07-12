-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : ven. 11 juil. 2025 à 15:05
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
-- Base de données : `sglp_online`
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
  `civilite` enum('M','Mme','Mlle') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M' COMMENT 'Civilité de l''adhérent',
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
  `has_anomalies` tinyint(1) NOT NULL DEFAULT '0',
  `anomalies_data` json DEFAULT NULL,
  `anomalies_severity` enum('critique','majeure','mineure') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `type_mouvement` enum('adhesion','exclusion','demission','transfert','reintegration','suspension','deces','radiation','correction','validation_anomalie','creation_avec_anomalie') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ancienne_organisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nouvelle_organisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `motif` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_justificatif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_effet` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `validated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `statut` enum('en_attente','valide','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `commentaire_validation` text COLLATE utf8mb4_unicode_ci,
  `commentaires` text COLLATE utf8mb4_unicode_ci,
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
  `has_anomalies_info` tinyint(1) NOT NULL DEFAULT '0',
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `dossier_id`, `document_type_id`, `nom_fichier`, `nom_original`, `chemin_fichier`, `type_mime`, `taille`, `hash_fichier`, `uploaded_by`, `is_validated`, `is_system_generated`, `has_anomalies_info`, `commentaire`, `created_at`, `updated_at`) VALUES
(5, 14, 99, 'accuse_reception_PP20250007_1751278290.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250007_1751278290.pdf', 'application/pdf', 2268, '261356c4dd52e301fc2b8d5e74d1aa2c390859f38fa803a6cc02f97073a855fe', 3, 0, 1, 0, NULL, '2025-06-30 10:11:30', '2025-06-30 10:11:30'),
(6, 15, 99, 'accuse_reception_PP20250008_1751309712.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250008_1751309712.pdf', 'application/pdf', 2269, '43c534f06abb2f5e52d7f3cb7c743f85a69ba57bd848e720df9f05752fbb511d', 3, 0, 1, 0, NULL, '2025-06-30 18:55:12', '2025-06-30 18:55:12'),
(7, 16, 99, 'accuse_reception_PP20250009_1751311170.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250009_1751311170.pdf', 'application/pdf', 2260, '8e498c962338f90001a0e249e598fadff2b7424911ee7821b6b302cc1fcec291', 3, 0, 1, 0, NULL, '2025-06-30 19:19:30', '2025-06-30 19:19:30'),
(8, 17, 99, 'accuse_reception_PP20250010_1751314801.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250010_1751314801.pdf', 'application/pdf', 2260, 'd8053120b9c5c8b3fb30d3175ef305d87a7b12c7e12f8dde1d713cae74611873', 3, 0, 1, 0, NULL, '2025-06-30 20:20:01', '2025-06-30 20:20:01'),
(9, 18, 99, 'accuse_reception_PP20250011_1751316106.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250011_1751316106.pdf', 'application/pdf', 2272, '09f8cabb92afc53f92448e20aa3a47330a3adbb458ce70b3cf056ea24615c91d', 3, 0, 1, 0, NULL, '2025-06-30 20:41:46', '2025-06-30 20:41:46'),
(10, 19, 99, 'accuse_reception_PP20250012_1751318932.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250012_1751318932.pdf', 'application/pdf', 2274, '6bcc72ed5fee2cdc643fe5fbd409af5d3256ede27972ae0ef7bfda9d6b73501a', 3, 0, 1, 0, NULL, '2025-06-30 21:28:52', '2025-06-30 21:28:52'),
(11, 20, 99, 'accuse_reception_PP20250013_1751337448.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250013_1751337448.pdf', 'application/pdf', 2256, 'be8cc2337175896d1065ff07dba5eb00832fcec4aca3e84656b99ebdcd582f7c', 3, 0, 1, 0, NULL, '2025-07-01 02:37:28', '2025-07-01 02:37:28'),
(12, 21, 99, 'accuse_reception_PP20250014_1751358569.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250014_1751358569.pdf', 'application/pdf', 2258, '4fdf18e1cb20d811e29ac4f948b5513596a8d71668339a9be1eacf85dd0976c0', 3, 0, 1, 0, NULL, '2025-07-01 08:29:29', '2025-07-01 08:29:29'),
(13, 22, 99, 'accuse_reception_PP20250015_1751359106.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250015_1751359106.pdf', 'application/pdf', 2257, 'a12f401873d362419967893acb2fde5fb5e26f297bf33cea07687137cc994b1d', 3, 0, 1, 0, NULL, '2025-07-01 08:38:26', '2025-07-01 08:38:26'),
(14, 23, 99, 'accuse_reception_PP20250016_1751361193.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250016_1751361193.pdf', 'application/pdf', 2258, '7132bc7e7f2a0a3dfc9d35c10485d3f055059135844610ce86f34cb3d407f3ae', 3, 0, 1, 0, NULL, '2025-07-01 09:13:13', '2025-07-01 09:13:13'),
(15, 24, 99, 'accuse_reception_PP20250017_1751363817.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250017_1751363817.pdf', 'application/pdf', 2258, '014cc5dda05250573f416dcbd2f01c2f4d10ffd0444c4f1a95a8e361b7027118', 3, 0, 1, 0, NULL, '2025-07-01 09:56:57', '2025-07-01 09:56:57'),
(16, 26, 99, 'accuse_reception_phase1_PP20250018_1751435743.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250018_1751435743.pdf', 'application/pdf', 4010, 'c7d373e779935a1f001356250859eb0b58684223cf4b9aae8b819bda95702120', 3, 0, 1, 0, NULL, '2025-07-02 05:55:43', '2025-07-02 05:55:43'),
(17, 27, 99, 'accuse_reception_phase1_PP20250019_1751436725.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250019_1751436725.pdf', 'application/pdf', 4009, '2da3c2ad2b60fb30b6ca6f03e793d83c25ec1b08e9112701d0c6e98d2d7a966a', 3, 0, 1, 0, NULL, '2025-07-02 06:12:05', '2025-07-02 06:12:05'),
(18, 28, 99, 'accuse_reception_phase1_PP20250020_1751459374.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250020_1751459374.pdf', 'application/pdf', 4001, '909791651d537e4f23e628c691459ff17a8b18e24c3050b025be064e875c7334', 3, 0, 1, 0, NULL, '2025-07-02 12:29:34', '2025-07-02 12:29:34'),
(19, 29, 99, 'accuse_reception_phase1_PP20250021_1751556011.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250021_1751556011.pdf', 'application/pdf', 4014, '72e96d5cce8d1ac33038e437b58f52de190a278b44c8e8eeebfdfea4198f7e6b', 3, 0, 1, 0, NULL, '2025-07-03 15:20:11', '2025-07-03 15:20:11'),
(20, 30, 99, 'accuse_reception_phase1_PP20250022_1751558127.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250022_1751558127.pdf', 'application/pdf', 4004, 'a2a4f6419758a1a8b05392815de3a988cc3e4154f17d12609c5d4072e0950f46', 3, 0, 1, 0, NULL, '2025-07-03 15:55:27', '2025-07-03 15:55:27'),
(21, 31, 99, 'accuse_reception_phase1_PP20250023_1751604698.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250023_1751604698.pdf', 'application/pdf', 4011, '3d1bb2980ad15c33089663cc33e7f80a33c697e5be3dda48589a528d0f8a7c94', 3, 0, 1, 0, NULL, '2025-07-04 04:51:38', '2025-07-04 04:51:38'),
(22, 32, 99, 'accuse_reception_phase1_PP20250024_1751607349.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250024_1751607349.pdf', 'application/pdf', 4014, '2b0435044c78d7552ec7f745321837a598912ac38417889dd49526c7606d3d5c', 3, 0, 1, 0, NULL, '2025-07-04 05:35:49', '2025-07-04 05:35:49'),
(23, 33, 99, 'accuse_reception_phase1_AS20250001_1751608009.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_AS20250001_1751608009.pdf', 'application/pdf', 4013, '17ce1ccd5f82b085e0ac4b70d8d50cc84e2003fc6d14d2662bfc868e0615475d', 3, 0, 1, 0, NULL, '2025-07-04 05:46:49', '2025-07-04 05:46:49'),
(24, 34, 99, 'accuse_reception_phase1_PP20250025_1751613189.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250025_1751613189.pdf', 'application/pdf', 4013, '61a759c0f484b9a948a7e5e9371aece5ffb24ecca8eb29db299fa2a3aa831791', 3, 0, 1, 0, NULL, '2025-07-04 07:13:09', '2025-07-04 07:13:09'),
(25, 35, 99, 'accuse_reception_PP20250026_1751617412.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250026_1751617412.pdf', 'application/pdf', 2268, '53e92269ee3160a635e6c6c0bb7afe009e77a93ea0647a49dcb5e98915a38c06', 3, 0, 1, 0, NULL, '2025-07-04 08:23:32', '2025-07-04 08:23:32'),
(26, 37, 99, 'accuse_reception_PP20250027_1751625920.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250027_1751625920.pdf', 'application/pdf', 2268, '9108cffed4f533c66e810b6e0910c946295a0076fba89c9b75d2ec06822d9cbc', 3, 0, 1, 0, NULL, '2025-07-04 10:45:20', '2025-07-04 10:45:20'),
(27, 39, 99, 'accuse_reception_PP20250028_1751649975.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250028_1751649975.pdf', 'application/pdf', 2268, '546e027ab4cd0b4fb849fd9352a8ea8831c11b5b5d22ca07b7482ca664cdf008', 15, 0, 1, 0, NULL, '2025-07-04 17:26:15', '2025-07-04 17:26:15'),
(28, 40, 99, 'accuse_reception_PP20250029_1751654726.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250029_1751654726.pdf', 'application/pdf', 2271, '8a50006449dda1d58f6f5ebec065e69208ca2246503ea86cb5ff57f88c8bfcc0', 15, 0, 1, 0, NULL, '2025-07-04 18:45:26', '2025-07-04 18:45:26'),
(29, 41, 99, 'accuse_reception_PP20250030_1751864134.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250030_1751864134.pdf', 'application/pdf', 2261, 'a97ac730260c0c61d43c42193d7b9760955540d3fea6ff5927872453dc6f1b51', 3, 0, 1, 0, NULL, '2025-07-07 04:55:34', '2025-07-07 04:55:34'),
(30, 42, 99, 'accuse_reception_PP20250031_1751870742.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250031_1751870742.pdf', 'application/pdf', 2265, 'aeaf6fd67331937d8448847dfe2a4b788db193b58f40b8bd67037fe1e0928588', 3, 0, 1, 0, NULL, '2025-07-07 06:45:42', '2025-07-07 06:45:42'),
(31, 43, 99, 'accuse_reception_PP20250032_1751871178.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250032_1751871178.pdf', 'application/pdf', 2266, '1d153d2f96a4681229b5fb5811b893d60a3bf7d9701a35df7dfee6430bbf8574', 3, 0, 1, 0, NULL, '2025-07-07 06:52:58', '2025-07-07 06:52:58'),
(32, 44, 99, 'accuse_reception_PP20250033_1751871369.pdf', 'Accus___de_r__ception', 'accuses_reception/accuse_reception_PP20250033_1751871369.pdf', 'application/pdf', 2266, 'e2870b9e5e3c25b743a9dd481de54dcc3b1317c8aa2ac3e2713721b3010e3321', 3, 0, 1, 0, NULL, '2025-07-07 06:56:09', '2025-07-07 06:56:09'),
(33, 62, 99, 'accuse_reception_phase1_PP20250051_1751978489.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250051_1751978489.pdf', 'application/pdf', 4007, '8bce3423c68b2086332bac8594798e3fe5360875772b4f42b0d319688959d6ee', 3, 0, 1, 0, NULL, '2025-07-08 12:41:29', '2025-07-08 12:41:29'),
(34, 63, 99, 'accuse_reception_phase1_PP20250052_1751981875.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250052_1751981875.pdf', 'application/pdf', 4006, '445634d8628e42153f8da645b8bd3597f5beaec6ff9c5e81cd0c2f1dcdf2a039', 3, 0, 1, 0, NULL, '2025-07-08 13:37:55', '2025-07-08 13:37:55'),
(35, 64, 99, 'accuse_reception_phase1_AS20250002_1751989533.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_AS20250002_1751989533.pdf', 'application/pdf', 4006, '65ea4b216ddbc68d685801589a712e0f68b81f65143d42916d70e137c9c20cb4', 3, 0, 1, 0, NULL, '2025-07-08 15:45:33', '2025-07-08 15:45:33'),
(36, 65, 99, 'accuse_reception_phase1_PP20250053_1751990962.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250053_1751990962.pdf', 'application/pdf', 4012, '0b4579f585e749f3f113b9c9eb99352a54fc2053887fd07905e3226069fcc198', 3, 0, 1, 0, NULL, '2025-07-08 16:09:22', '2025-07-08 16:09:22'),
(37, 66, 99, 'accuse_reception_phase1_PP20250054_1751991854.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250054_1751991854.pdf', 'application/pdf', 4008, '82a2433e7f2b5e4175cdcc45205be8f8db035f9eed70ca2927183ade51803cdb', 3, 0, 1, 0, NULL, '2025-07-08 16:24:14', '2025-07-08 16:24:14'),
(38, 67, 99, 'accuse_reception_phase1_PP20250055_1751993424.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250055_1751993424.pdf', 'application/pdf', 4010, '98576c6400fdc69269ce3bea6ce4cb18950390fe8b9c05c3eb9fc5c0c732fa85', 3, 0, 1, 0, NULL, '2025-07-08 16:50:24', '2025-07-08 16:50:24'),
(39, 68, 99, 'accuse_reception_phase1_PP20250056_1752002679.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250056_1752002679.pdf', 'application/pdf', 4012, '2805961bdb4ceeaa687579671e0a5e3e2627bc0c46ff981b34484839dad3605f', 3, 0, 1, 0, NULL, '2025-07-08 19:24:39', '2025-07-08 19:24:39'),
(40, 69, 99, 'accuse_reception_phase1_PP20250057_1752018146.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250057_1752018146.pdf', 'application/pdf', 4012, 'cbbdb07036e104e450f8071e2748b12fc69b77c92d99f158924bc1820a4df6d1', 3, 0, 1, 0, NULL, '2025-07-08 23:42:26', '2025-07-08 23:42:26'),
(41, 70, 99, 'accuse_reception_phase1_PP20250058_1752018409.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250058_1752018409.pdf', 'application/pdf', 4006, '3f27b29e39aca6c80f3cf00476a2350bf40d40530051972e3983f62b0e72035b', 3, 0, 1, 0, NULL, '2025-07-08 23:46:49', '2025-07-08 23:46:49'),
(42, 71, 99, 'accuse_reception_phase1_PP20250059_1752024186.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250059_1752024186.pdf', 'application/pdf', 4007, 'b2bbece4671033a8bbac42d31f8c6d327c957200ebdfb54f2784258af8bbf97e', 3, 0, 1, 0, NULL, '2025-07-09 01:23:06', '2025-07-09 01:23:06'),
(43, 72, 99, 'accuse_reception_phase1_PP20250060_1752041487.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250060_1752041487.pdf', 'application/pdf', 4015, '0f59337b19cf43ffd19d6b089de036e30e14a80e246fd3c7226d2de38b3fd0f3', 3, 0, 1, 0, NULL, '2025-07-09 06:11:27', '2025-07-09 06:11:27'),
(44, 73, 99, 'accuse_reception_phase1_PP20250061_1752046272.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250061_1752046272.pdf', 'application/pdf', 4006, 'aff05256df5b2e544d3c2bdc537709a24fb3e127130a76b2311039b2d5c012f6', 3, 0, 1, 0, NULL, '2025-07-09 07:31:12', '2025-07-09 07:31:12'),
(45, 74, 99, 'accuse_reception_phase1_PP20250062_1752084200.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250062_1752084200.pdf', 'application/pdf', 4006, '16d14b0c4aba5c69626166d7b7120faee755835709c945e560c9f3ec6a523d91', 3, 0, 1, 0, NULL, '2025-07-09 18:03:20', '2025-07-09 18:03:20'),
(46, 75, 99, 'accuse_reception_phase1_PP20250063_1752089791.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250063_1752089791.pdf', 'application/pdf', 4012, '2f569e852b09fa5563e46aa95c3b8a5b9fbce990eef466aed0d858688c4d8255', 3, 0, 1, 0, NULL, '2025-07-09 19:36:31', '2025-07-09 19:36:31'),
(47, 76, 99, 'accuse_reception_phase1_PP20250064_1752090190.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250064_1752090190.pdf', 'application/pdf', 4011, '352ceb68fb85c2e41bbe18ededfd06e49f6b7cb4316f21ac60069230bd519b14', 3, 0, 1, 0, NULL, '2025-07-09 19:43:10', '2025-07-09 19:43:10'),
(48, 77, 99, 'accuse_reception_phase1_PP20250065_1752105042.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250065_1752105042.pdf', 'application/pdf', 4013, '3cd0733d9b1ad02844f9c6738d608ffcbcaec40dee715827748f709d589e560c', 3, 0, 1, 0, NULL, '2025-07-09 23:50:42', '2025-07-09 23:50:42'),
(49, 78, 99, 'accuse_reception_phase1_PP20250066_1752130514.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250066_1752130514.pdf', 'application/pdf', 4016, '392f643bcb975eb761bfc297eb51fb56b24126ffe8d6021f06bccf8884aae61a', 3, 0, 1, 0, NULL, '2025-07-10 06:55:14', '2025-07-10 06:55:14'),
(50, 79, 99, 'accuse_reception_phase1_PP20250067_1752131096.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250067_1752131096.pdf', 'application/pdf', 4005, '77c06313bfc2109dd8b3cf042ddf8811d43b44c51b022817622e82bf64ee53ea', 3, 0, 1, 0, NULL, '2025-07-10 07:04:56', '2025-07-10 07:04:56'),
(51, 80, 99, 'accuse_reception_phase1_PP20250068_1752132085.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250068_1752132085.pdf', 'application/pdf', 4005, '1147a3921ca8da3dbe37d4108ef63f6a6248947f327738dd4cd79186587b07e7', 3, 0, 1, 0, NULL, '2025-07-10 07:21:25', '2025-07-10 07:21:25'),
(52, 81, 99, 'accuse_reception_phase1_PP20250069_1752176881.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250069_1752176881.pdf', 'application/pdf', 4006, 'f845190201da982d4073098c0346561d9b9ede31683249fd2348197ac818f93c', 3, 0, 1, 0, NULL, '2025-07-10 19:48:01', '2025-07-10 19:48:01'),
(53, 82, 99, 'accuse_reception_phase1_PP20250070_1752177755.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_PP20250070_1752177755.pdf', 'application/pdf', 4010, '1f5f94641058567371e811f5c4cc18834af739d93612b6e00a8a15e17d87096f', 3, 0, 1, 0, NULL, '2025-07-10 20:02:35', '2025-07-10 20:02:35'),
(54, 83, 99, 'accuse_reception_phase1_AS20250003_1752191216.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_AS20250003_1752191216.pdf', 'application/pdf', 4009, '00f25d13cfd96e91e16040097ab423efa2df1a5f29526c22577e2bcb1439e55a', 3, 0, 1, 0, NULL, '2025-07-10 23:46:56', '2025-07-10 23:46:56'),
(55, 84, 99, 'accuse_reception_phase1_AS20250004_1752221260.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_AS20250004_1752221260.pdf', 'application/pdf', 4010, '8956619c3c6c11c203ee9253f7206f5d06578c2f0e5b1c0b6582e386266b4e8d', 3, 0, 1, 0, NULL, '2025-07-11 08:07:40', '2025-07-11 08:07:40'),
(56, 85, 99, 'accuse_reception_phase1_AS20250005_1752238649.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_AS20250005_1752238649.pdf', 'application/pdf', 4012, 'c9536b841338e5955d53ef996bb616fc7e07dc8529f4e3eceda7d5dfca3ccb2c', 3, 0, 1, 0, NULL, '2025-07-11 12:57:29', '2025-07-11 12:57:29'),
(57, 86, 99, 'accuse_reception_phase1_AS20250006_1752239589.pdf', 'Accus___de_r__ception_Phase_1', 'accuses_reception/accuse_reception_phase1_AS20250006_1752239589.pdf', 'application/pdf', 4012, '3faa392c41f863e9c5e16d1cb6c46ff42119a30e6b4b360e40e4bc2e34be9f3e', 3, 0, 1, 0, NULL, '2025-07-11 13:13:09', '2025-07-11 13:13:09');

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

--
-- Déchargement des données de la table `document_types`
--

INSERT INTO `document_types` (`id`, `code`, `libelle`, `description`, `type_organisation`, `type_operation`, `is_active`, `is_required`, `ordre`, `format_accepte`, `taille_max`, `created_at`, `updated_at`) VALUES
(99, 'accuse', 'Accusé de réception', 'Document généré automatiquement', 'association', 'creation', 1, 0, 0, 'pdf,jpg,png', 5, '2025-06-29 22:23:24', '2025-06-29 22:23:24');

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
  `statut` enum('brouillon','soumis','en_cours','approuve','rejete','accepte') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `has_anomalies_majeures` tinyint(1) NOT NULL DEFAULT '0',
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
-- Structure de la table `draft_accuses`
--

CREATE TABLE `draft_accuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `draft_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `step_number` int(11) NOT NULL,
  `step_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accuse_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'step_completion',
  `numero_accuse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu_html` text COLLATE utf8mb4_unicode_ci,
  `fichier_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `donnees_etape` json DEFAULT NULL,
  `hash_verification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_valide` tinyint(1) NOT NULL DEFAULT '1',
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_06_19_091003_add_fields_to_users_table', 1),
(6, '2025_06_23_151822_create_organisations_table', 1),
(7, '2025_06_23_151827_create_fondateurs_table', 1),
(8, '2025_06_23_151829_create_adherents_table', 1),
(9, '2025_06_23_151832_create_etablissements_table', 1),
(10, '2025_06_23_151835_create_document_types_table', 1),
(11, '2025_06_23_151911_create_workflow_steps_table', 1),
(12, '2025_06_23_151912_create_dossiers_table', 1),
(13, '2025_06_23_151913_create_documents_table', 1),
(14, '2025_06_23_151914_create_validation_entities_table', 1),
(15, '2025_06_23_151917_create_entity_agents_table', 1),
(16, '2025_06_23_151919_create_workflow_step_entities_table', 1),
(17, '2025_06_23_151922_create_dossier_validations_table', 1),
(18, '2025_06_23_151925_create_dossier_locks_table', 2),
(19, '2025_06_23_151927_create_dossier_operations_table', 2),
(20, '2025_06_23_151930_create_dossier_comments_table', 2),
(21, '2025_06_23_151933_create_document_templates_table', 2),
(22, '2025_06_23_152027_create_adherent_histories_table', 2),
(23, '2025_06_23_152030_create_adherent_exclusions_table', 2),
(24, '2025_06_23_152032_create_adherent_imports_table', 2),
(25, '2025_06_23_152109_create_declaration_types_table', 2),
(26, '2025_06_23_152112_create_declarations_table', 2),
(27, '2025_06_23_152114_create_declaration_documents_table', 2),
(28, '2025_06_23_152132_create_qr_codes_table', 3),
(29, '2025_06_23_152134_create_document_verifications_table', 3),
(30, '2025_06_23_152137_create_inscription_links_table', 3),
(31, '2025_06_23_152220_create_organisation_settings_table', 3),
(32, '2025_06_23_152223_create_guide_contents_table', 3),
(33, '2025_06_23_152226_create_organe_types_table', 3),
(34, '2025_06_23_152228_create_organe_members_table', 4),
(35, '2025_06_23_161122_create_dossier_archives_table', 4),
(36, '2025_06_24_162144_add_pngdi_fields_to_users_table', 5),
(38, '2025_06_25_202949_create_two_factor_codes_table', 6),
(39, '2025_06_27_083426_add_advanced_fields_to_users_table', 6),
(40, '2025_06_27_083533_create_roles_table', 6),
(41, '2025_06_27_083620_create_permissions_table', 6),
(42, '2025_06_27_083714_create_role_permissions_table', 6),
(43, '2025_06_27_083804_create_user_sessions_table', 6),
(44, '2025_06_27_083904_add_foreign_keys_to_users_table', 6),
(45, '2025_06_28_160007_create_organization_drafts_table', 7),
(46, '2025_06_29_035330_add_profession_fonction_to_adherents_table', 8),
(47, '2025_06_29_035333_add_upload_fields_to_documents_table', 8),
(48, '2025_06_29_035336_add_nom_prenom_to_users_table', 8),
(49, '2025_06_29_143435_add_validation_fields_to_adherents_table', 9),
(50, '2025_06_29_164027_add_has_anomalies_majeures_to_organisations_table', 10),
(51, '2025_06_29_164030_create_adherent_anomalies_table', 10),
(52, '2025_06_29_164036_add_has_anomalies_majeures_to_dossiers_table', 11),
(53, '2025_06_29_164039_update_type_mouvement_enum_in_adherent_histories_table', 11),
(54, '2025_06_29_164042_add_has_anomalies_info_to_documents_table', 11),
(55, '2025_06_29_211122_create_draft_accuses_table', 12),
(56, '2025_06_29_231223_add_anomalies_columns_to_adherents_table', 12),
(57, '2025_07_07_195712_create_sessions_table', 13),
(58, '2025_07_09_105226_add_civilite_to_adherents_table', 14),
(59, '2025_07_09_112216_add_accepte_to_dossiers_statut_enum', 15);

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

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'users.view', 'Consulter les utilisateurs', 'users', 'Permet de consulter la liste des utilisateurs et leurs informations de base', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(2, 'users.create', 'Créer des utilisateurs', 'users', 'Permet de créer de nouveaux comptes utilisateurs dans le système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(3, 'users.edit', 'Modifier les utilisateurs', 'users', 'Permet de modifier les informations des utilisateurs existants', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(4, 'users.delete', 'Supprimer les utilisateurs', 'users', 'Permet de supprimer définitivement des comptes utilisateurs', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(5, 'users.export', 'Exporter les utilisateurs', 'users', 'Permet d\'exporter les données utilisateurs vers Excel, PDF ou CSV', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(6, 'users.import', 'Importer les utilisateurs', 'users', 'Permet d\'importer des utilisateurs en masse depuis des fichiers', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(7, 'users.roles', 'Gérer les rôles utilisateurs', 'users', 'Permet de gérer les rôles et attributions des utilisateurs', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(8, 'users.permissions', 'Gérer les permissions utilisateurs', 'users', 'Permet de gérer les permissions spécifiques des utilisateurs', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(9, 'users.sessions', 'Gérer les sessions utilisateurs', 'users', 'Permet de consulter et gérer les sessions actives des utilisateurs', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(10, 'users.verify', 'Vérifier les comptes utilisateurs', 'users', 'Permet de vérifier et valider les comptes utilisateurs', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(11, 'orgs.view', 'Consulter les organisations', 'organizations', 'Permet de consulter les organisations enregistrées dans le système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(12, 'orgs.create', 'Créer des organisations', 'organizations', 'Permet de créer de nouvelles organisations (associations, partis, etc.)', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(13, 'orgs.edit', 'Modifier les organisations', 'organizations', 'Permet de modifier les informations des organisations existantes', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(14, 'orgs.delete', 'Supprimer les organisations', 'organizations', 'Permet de supprimer définitivement des organisations', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(15, 'orgs.validate', 'Valider les organisations', 'organizations', 'Permet de valider les demandes d\'enregistrement d\'organisations', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(16, 'orgs.reject', 'Rejeter les organisations', 'organizations', 'Permet de rejeter les demandes d\'enregistrement avec motifs', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(17, 'orgs.archive', 'Archiver les organisations', 'organizations', 'Permet d\'archiver les organisations inactives ou radiées', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(18, 'orgs.export', 'Exporter les organisations', 'organizations', 'Permet d\'exporter les données des organisations', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(19, 'orgs.suspend', 'Suspendre les organisations', 'organizations', 'Permet de suspendre temporairement une organisation', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(20, 'orgs.reactivate', 'Réactiver les organisations', 'organizations', 'Permet de réactiver une organisation suspendue', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(21, 'orgs.manage_adherents', 'Gérer les adhérents', 'organizations', 'Permet de gérer les adhérents des organisations', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(22, 'orgs.manage_documents', 'Gérer les documents', 'organizations', 'Permet de gérer les documents des organisations', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(23, 'workflow.view', 'Consulter le workflow', 'workflow', 'Permet de consulter l\'état du workflow et des dossiers', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(24, 'workflow.assign', 'Assigner des tâches', 'workflow', 'Permet d\'assigner des dossiers à des agents pour traitement', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(25, 'workflow.validate', 'Valider les étapes', 'workflow', 'Permet de valider les étapes du processus de traitement', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(26, 'workflow.reject', 'Rejeter les demandes', 'workflow', 'Permet de rejeter des demandes avec justification', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(27, 'workflow.reports', 'Générer des rapports workflow', 'workflow', 'Permet de générer des rapports sur l\'activité du workflow', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(28, 'workflow.lock', 'Verrouiller les dossiers', 'workflow', 'Permet de verrouiller des dossiers pour traitement exclusif', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(29, 'workflow.unlock', 'Déverrouiller les dossiers', 'workflow', 'Permet de déverrouiller des dossiers bloqués', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(30, 'workflow.comment', 'Commenter les dossiers', 'workflow', 'Permet d\'ajouter des commentaires aux dossiers', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(31, 'workflow.history', 'Consulter l\'historique', 'workflow', 'Permet de consulter l\'historique complet des dossiers', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(32, 'workflow.priority', 'Modifier les priorités', 'workflow', 'Permet de modifier les priorités de traitement', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(33, 'system.config', 'Configuration système', 'system', 'Permet de modifier la configuration générale du système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(34, 'system.backup', 'Sauvegardes système', 'system', 'Permet de créer et restaurer des sauvegardes système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(35, 'system.logs', 'Consulter les logs', 'system', 'Permet de consulter les journaux d\'activité du système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(36, 'system.reports', 'Rapports système', 'system', 'Permet de générer des rapports système et statistiques', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(37, 'system.maintenance', 'Mode maintenance', 'system', 'Permet d\'activer le mode maintenance du système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(38, 'system.updates', 'Mises à jour système', 'system', 'Permet de gérer les mises à jour du système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(39, 'system.monitoring', 'Monitoring système', 'system', 'Permet d\'accéder aux outils de monitoring et surveillance', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(40, 'system.security', 'Paramètres de sécurité', 'system', 'Permet de gérer les paramètres de sécurité avancés', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(41, 'system.integrations', 'Gestion des intégrations', 'system', 'Permet de configurer les intégrations avec d\'autres systèmes', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(42, 'system.notifications', 'Configuration notifications', 'system', 'Permet de configurer les notifications système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(43, 'content.view', 'Consulter les contenus', 'content', 'Permet de consulter tous les contenus du système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(44, 'content.create', 'Créer des contenus', 'content', 'Permet de créer de nouveaux contenus et articles', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(45, 'content.edit', 'Modifier les contenus', 'content', 'Permet de modifier les contenus existants', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(46, 'content.delete', 'Supprimer les contenus', 'content', 'Permet de supprimer des contenus', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(47, 'content.publish', 'Publier les contenus', 'content', 'Permet de publier des contenus pour les rendre visibles', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(48, 'content.moderate', 'Modérer les contenus', 'content', 'Permet de modérer et valider les contenus soumis', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(49, 'content.media', 'Gérer les médias', 'content', 'Permet de gérer les fichiers médias (images, documents)', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(50, 'content.templates', 'Gérer les templates', 'content', 'Permet de gérer les modèles de documents', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(51, 'reports.view', 'Consulter les rapports', 'reports', 'Permet de consulter tous les rapports disponibles', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(52, 'reports.create', 'Créer des rapports', 'reports', 'Permet de créer des rapports personnalisés', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(53, 'reports.export', 'Exporter les rapports', 'reports', 'Permet d\'exporter les rapports dans différents formats', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(54, 'reports.schedule', 'Programmer les rapports', 'reports', 'Permet de programmer des rapports automatiques', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(55, 'reports.analytics', 'Accès aux analytics', 'reports', 'Permet d\'accéder aux analytics et tableaux de bord', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(56, 'reports.statistics', 'Statistiques avancées', 'reports', 'Permet d\'accéder aux statistiques avancées', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(57, 'api.access', 'Accès API', 'api', 'Permet d\'accéder aux API du système', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(58, 'api.manage', 'Gérer les clés API', 'api', 'Permet de gérer les clés d\'accès API', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(59, 'api.webhooks', 'Gérer les webhooks', 'api', 'Permet de configurer les webhooks', '2025-06-27 08:55:47', '2025-06-27 08:55:47'),
(60, 'api.logs', 'Logs API', 'api', 'Permet de consulter les logs d\'utilisation API', '2025-06-27 08:55:47', '2025-06-27 08:55:47');

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
  `donnees_verification` json DEFAULT NULL COMMENT 'Données à afficher lors de la vérification',
  `hash_verification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash pour vérifier l''intégrité',
  `nombre_verifications` int(11) NOT NULL DEFAULT '0',
  `derniere_verification` timestamp NULL DEFAULT NULL,
  `expire_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `qr_codes`
--

INSERT INTO `qr_codes` (`id`, `code`, `type`, `verifiable_type`, `verifiable_id`, `document_numero`, `donnees_verification`, `hash_verification`, `nombre_verifications`, `derniere_verification`, `expire_at`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'QR-TMYP97UV3OX7YWSP', 'dossier_verification', 'App\\Models\\Dossier', 31, 'PP20250023', '\"{\\\"dossier_numero\\\":\\\"PP20250023\\\",\\\"organisation_nom\\\":\\\"OPOLO 2 - TEST\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00023\\\",\\\"date_soumission\\\":\\\"2025-07-04T04:51:38.856796Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"058cad05ef1363330b5257d328126ed1091878f7c8c1e891c2ee190314610dbd\\\"}\"', '058cad05ef1363330b5257d328126ed1091878f7c8c1e891c2ee190314610dbd', 0, NULL, '2030-07-04 04:51:38', 1, '2025-07-04 04:51:38', '2025-07-04 04:51:38'),
(2, 'QR-LQDGCPWERXMJRTAX', 'dossier_verification', 'App\\Models\\Dossier', 32, 'PP20250024', '\"{\\\"dossier_numero\\\":\\\"PP20250024\\\",\\\"organisation_nom\\\":\\\"OPOLO 3 - test\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00024\\\",\\\"date_soumission\\\":\\\"2025-07-04T05:35:49.309424Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"6a2510a07c2b17c70b8bc8a0c82b968d5917885f577de41e7094acd8351abb8a\\\"}\"', '6a2510a07c2b17c70b8bc8a0c82b968d5917885f577de41e7094acd8351abb8a', 0, NULL, '2030-07-04 05:35:49', 1, '2025-07-04 05:35:49', '2025-07-04 05:35:49'),
(3, 'QR-XDDNWOLTDYP9QDQD', 'dossier_verification', 'App\\Models\\Dossier', 33, 'AS20250001', '\"{\\\"dossier_numero\\\":\\\"AS20250001\\\",\\\"organisation_nom\\\":\\\"OPOLO 1 ASSOCIATION\\\",\\\"organisation_type\\\":\\\"association\\\",\\\"numero_recepisse\\\":\\\"REC-AS-2025-00001\\\",\\\"date_soumission\\\":\\\"2025-07-04T05:46:49.506668Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"e618da062b13f64a956521b2f569d3c2fc42e16b2871bbf2a00c8dedfd27d1a9\\\"}\"', 'e618da062b13f64a956521b2f569d3c2fc42e16b2871bbf2a00c8dedfd27d1a9', 0, NULL, '2030-07-04 05:46:49', 1, '2025-07-04 05:46:49', '2025-07-04 05:46:49'),
(4, 'QR-D42QXJBMYF1PQSUN', 'dossier_verification', 'App\\Models\\Dossier', 34, 'PP20250025', '\"{\\\"dossier_numero\\\":\\\"PP20250025\\\",\\\"organisation_nom\\\":\\\"OPOLO 4 - TEST\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00025\\\",\\\"date_soumission\\\":\\\"2025-07-04T07:13:09.843071Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"2f2306f89d39fda6c55a63556efb485698f39c005729ae597b33bec335201188\\\"}\"', '2f2306f89d39fda6c55a63556efb485698f39c005729ae597b33bec335201188', 0, NULL, '2030-07-04 07:13:09', 1, '2025-07-04 07:13:09', '2025-07-04 07:13:09'),
(5, 'QR-2KRWKQYVZHOTNXT9', 'dossier_verification', 'App\\Models\\Dossier', 35, 'PP20250026', '\"{\\\"dossier_numero\\\":\\\"PP20250026\\\",\\\"organisation_nom\\\":\\\"OPOLO 6 - PARTI\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00026\\\",\\\"date_soumission\\\":\\\"2025-07-04T08:23:32.061994Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"afb663062f2f1343e5c275342bcfa8992b7903cfdc95aebd4a7ed0a90fd920b3\\\"}\"', 'afb663062f2f1343e5c275342bcfa8992b7903cfdc95aebd4a7ed0a90fd920b3', 0, NULL, '2030-07-04 08:23:32', 1, '2025-07-04 08:23:32', '2025-07-04 08:23:32'),
(6, 'QR-ESJZF2IRAV4AJH6P', 'dossier_verification', 'App\\Models\\Dossier', 37, 'PP20250027', '\"{\\\"dossier_numero\\\":\\\"PP20250027\\\",\\\"organisation_nom\\\":\\\"OPOLO 7 - parti\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00027\\\",\\\"date_soumission\\\":\\\"2025-07-04T10:45:20.690748Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"f65226c9b16704808e0f80b4c6ca6ff94fef29cc4ab9242f3b6b66a1b840e34c\\\"}\"', 'f65226c9b16704808e0f80b4c6ca6ff94fef29cc4ab9242f3b6b66a1b840e34c', 0, NULL, '2030-07-04 10:45:20', 1, '2025-07-04 10:45:20', '2025-07-04 10:45:20'),
(7, 'QR-GIMYXOPMSIV6BGIB', 'dossier_verification', 'App\\Models\\Dossier', 39, 'PP20250028', '\"{\\\"dossier_numero\\\":\\\"PP20250028\\\",\\\"organisation_nom\\\":\\\"OPOLO 9 - parti\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00028\\\",\\\"date_soumission\\\":\\\"2025-07-04T17:26:15.597873Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"e6f3f798bf34665c99d5aba38d5bff2a476f8b5b5b59189733673f33030d8291\\\"}\"', 'e6f3f798bf34665c99d5aba38d5bff2a476f8b5b5b59189733673f33030d8291', 0, NULL, '2030-07-04 17:26:15', 1, '2025-07-04 17:26:15', '2025-07-04 17:26:15'),
(8, 'QR-KNLGD0VGENRFCC1T', 'dossier_verification', 'App\\Models\\Dossier', 40, 'PP20250029', '\"{\\\"dossier_numero\\\":\\\"PP20250029\\\",\\\"organisation_nom\\\":\\\"OPOLO 10 - Partie\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00029\\\",\\\"date_soumission\\\":\\\"2025-07-04T18:45:26.612940Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"87e9414c8edb55d2bc2651d344306dc181210af6a4d3b384ef598d6e4d616367\\\"}\"', '87e9414c8edb55d2bc2651d344306dc181210af6a4d3b384ef598d6e4d616367', 0, NULL, '2030-07-04 18:45:26', 1, '2025-07-04 18:45:26', '2025-07-04 18:45:26'),
(9, 'QR-BUGSZFCWKYINQNYZ', 'dossier_verification', 'App\\Models\\Dossier', 41, 'PP20250030', '\"{\\\"dossier_numero\\\":\\\"PP20250030\\\",\\\"organisation_nom\\\":\\\"OPOLO 1 P\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00030\\\",\\\"date_soumission\\\":\\\"2025-07-07T04:55:34.062862Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"fc4d7c73d12d578aa4e48bef5f5d7ded504bd4f01e779863d4b5b07e65574609\\\"}\"', 'fc4d7c73d12d578aa4e48bef5f5d7ded504bd4f01e779863d4b5b07e65574609', 0, NULL, '2030-07-07 04:55:34', 1, '2025-07-07 04:55:34', '2025-07-07 04:55:34'),
(10, 'QR-0Q5IEYEBD2HFOEHI', 'dossier_verification', 'App\\Models\\Dossier', 42, 'PP20250031', '\"{\\\"dossier_numero\\\":\\\"PP20250031\\\",\\\"organisation_nom\\\":\\\"test 1 partie\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00031\\\",\\\"date_soumission\\\":\\\"2025-07-07T06:45:42.148609Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"173616db2f17001f606f70ba913d3e523f76162749ef63e023d314030834608f\\\"}\"', '173616db2f17001f606f70ba913d3e523f76162749ef63e023d314030834608f', 0, NULL, '2030-07-07 06:45:42', 1, '2025-07-07 06:45:42', '2025-07-07 06:45:42'),
(11, 'QR-2SABTC2Y3ZLOC0U2', 'dossier_verification', 'App\\Models\\Dossier', 43, 'PP20250032', '\"{\\\"dossier_numero\\\":\\\"PP20250032\\\",\\\"organisation_nom\\\":\\\"test 1 partie 2\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00032\\\",\\\"date_soumission\\\":\\\"2025-07-07T06:52:58.466473Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"14f8a13662a5e7f93cb41dca6e9fae7105ef958d238864e3ff3e971e5d8a90cb\\\"}\"', '14f8a13662a5e7f93cb41dca6e9fae7105ef958d238864e3ff3e971e5d8a90cb', 0, NULL, '2030-07-07 06:52:58', 1, '2025-07-07 06:52:58', '2025-07-07 06:52:58'),
(12, 'QR-JB03OMMFVVWXIYIL', 'dossier_verification', 'App\\Models\\Dossier', 44, 'PP20250033', '\"{\\\"dossier_numero\\\":\\\"PP20250033\\\",\\\"organisation_nom\\\":\\\"test 3 partie 3\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00033\\\",\\\"date_soumission\\\":\\\"2025-07-07T06:56:09.226297Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"1ed9944929735c0b0bfe5beb2b218349b093773a61ccd344c2650a0e26e97bac\\\"}\"', '1ed9944929735c0b0bfe5beb2b218349b093773a61ccd344c2650a0e26e97bac', 0, NULL, '2030-07-07 06:56:09', 1, '2025-07-07 06:56:09', '2025-07-07 06:56:09'),
(13, 'QR-HSVFOFBAGAYO9QVQ', 'dossier_verification', 'App\\Models\\Dossier', 62, 'PP20250051', '\"{\\\"dossier_numero\\\":\\\"PP20250051\\\",\\\"organisation_nom\\\":\\\"OPOLO INDUSTRIE\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00051\\\",\\\"date_soumission\\\":\\\"2025-07-08T12:41:29.091757Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"27aba896103bab2bfdf29e51b58ba17cd568d30e649741613cf855b04ad56847\\\"}\"', '27aba896103bab2bfdf29e51b58ba17cd568d30e649741613cf855b04ad56847', 0, NULL, '2030-07-08 12:41:29', 1, '2025-07-08 12:41:29', '2025-07-08 12:41:29'),
(14, 'QR-QK9MYQFJQK1GR2NP', 'dossier_verification', 'App\\Models\\Dossier', 63, 'PP20250052', '\"{\\\"dossier_numero\\\":\\\"PP20250052\\\",\\\"organisation_nom\\\":\\\"OPEN NUMERIK\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00052\\\",\\\"date_soumission\\\":\\\"2025-07-08T13:37:55.895977Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"02075b632c8f86aa1104cb251aa566d228687cf863e4f6303ee5d31888da1aec\\\"}\"', '02075b632c8f86aa1104cb251aa566d228687cf863e4f6303ee5d31888da1aec', 0, NULL, '2030-07-08 13:37:55', 1, '2025-07-08 13:37:55', '2025-07-08 13:37:55'),
(15, 'QR-SUHM3T1HQ4PYATLK', 'dossier_verification', 'App\\Models\\Dossier', 64, 'AS20250002', '\"{\\\"dossier_numero\\\":\\\"AS20250002\\\",\\\"organisation_nom\\\":\\\"OPOLO CORP\\\",\\\"organisation_type\\\":\\\"association\\\",\\\"numero_recepisse\\\":\\\"REC-AS-2025-00002\\\",\\\"date_soumission\\\":\\\"2025-07-08T15:45:33.622891Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"f8aeb9f53d7ad495d4f1241e82ec303f1d1c866093c01c7083b3506a5668da6a\\\"}\"', 'f8aeb9f53d7ad495d4f1241e82ec303f1d1c866093c01c7083b3506a5668da6a', 0, NULL, '2030-07-08 15:45:33', 1, '2025-07-08 15:45:33', '2025-07-08 15:45:33'),
(16, 'QR-V3XKHSJJGGMTSPPE', 'dossier_verification', 'App\\Models\\Dossier', 65, 'PP20250053', '\"{\\\"dossier_numero\\\":\\\"PP20250053\\\",\\\"organisation_nom\\\":\\\"OPOLO CORP 24\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00053\\\",\\\"date_soumission\\\":\\\"2025-07-08T16:09:22.920413Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"b360c6df91a71a22147dfaa0a4424a12c4a747cd23b1d2c6f2d2def4cab6ced5\\\"}\"', 'b360c6df91a71a22147dfaa0a4424a12c4a747cd23b1d2c6f2d2def4cab6ced5', 0, NULL, '2030-07-08 16:09:22', 1, '2025-07-08 16:09:22', '2025-07-08 16:09:22'),
(17, 'QR-XJCC4Q532BTVX5RJ', 'dossier_verification', 'App\\Models\\Dossier', 66, 'PP20250054', '\"{\\\"dossier_numero\\\":\\\"PP20250054\\\",\\\"organisation_nom\\\":\\\"OPOLO CORP 9\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00054\\\",\\\"date_soumission\\\":\\\"2025-07-08T16:24:14.993987Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"73354fcf0011143b1babcd2a0db9b4a74fc39e62e5b6aa236d4fae187d488443\\\"}\"', '73354fcf0011143b1babcd2a0db9b4a74fc39e62e5b6aa236d4fae187d488443', 0, NULL, '2030-07-08 16:24:14', 1, '2025-07-08 16:24:14', '2025-07-08 16:24:14'),
(18, 'QR-T83KAP12FAG9PMCX', 'dossier_verification', 'App\\Models\\Dossier', 67, 'PP20250055', '\"{\\\"dossier_numero\\\":\\\"PP20250055\\\",\\\"organisation_nom\\\":\\\"OPOLO CORP 12\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00055\\\",\\\"date_soumission\\\":\\\"2025-07-08T16:50:24.403959Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"e16376351e83ad71710de24b11681b52b1b513670c91699a9c6bfe105d9048b5\\\"}\"', 'e16376351e83ad71710de24b11681b52b1b513670c91699a9c6bfe105d9048b5', 0, NULL, '2030-07-08 16:50:24', 1, '2025-07-08 16:50:24', '2025-07-08 16:50:24'),
(19, 'QR-EK15GJF54XJX7JO7', 'dossier_verification', 'App\\Models\\Dossier', 68, 'PP20250056', '\"{\\\"dossier_numero\\\":\\\"PP20250056\\\",\\\"organisation_nom\\\":\\\"OPOLO CORP 120\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00056\\\",\\\"date_soumission\\\":\\\"2025-07-08T19:24:39.677729Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"4b56147bfeaa0ad6cad2496e25333996e6840646ef063cf25f1e2d79dd7d5a8e\\\"}\"', '4b56147bfeaa0ad6cad2496e25333996e6840646ef063cf25f1e2d79dd7d5a8e', 0, NULL, '2030-07-08 19:24:39', 1, '2025-07-08 19:24:39', '2025-07-08 19:24:39'),
(20, 'QR-ULRIJYIGB8SS6DMZ', 'dossier_verification', 'App\\Models\\Dossier', 69, 'PP20250057', '\"{\\\"dossier_numero\\\":\\\"PP20250057\\\",\\\"organisation_nom\\\":\\\"OPOLO CORPO 12\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00057\\\",\\\"date_soumission\\\":\\\"2025-07-08T23:42:26.241289Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"db81846c8c0eb96ad43f7ab1a30a37f0f0fba17e7aa76836fc75ffef47f83a68\\\"}\"', 'db81846c8c0eb96ad43f7ab1a30a37f0f0fba17e7aa76836fc75ffef47f83a68', 0, NULL, '2030-07-08 23:42:26', 1, '2025-07-08 23:42:26', '2025-07-08 23:42:26'),
(21, 'QR-OVFNCJ4LUUTG80ZI', 'dossier_verification', 'App\\Models\\Dossier', 70, 'PP20250058', '\"{\\\"dossier_numero\\\":\\\"PP20250058\\\",\\\"organisation_nom\\\":\\\"OPOLO TOP9\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00058\\\",\\\"date_soumission\\\":\\\"2025-07-08T23:46:49.850894Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"bc8dc95924b7bd15084ee1b4dab77587e3d81d14b539735ccd80a8261cd05baf\\\"}\"', 'bc8dc95924b7bd15084ee1b4dab77587e3d81d14b539735ccd80a8261cd05baf', 0, NULL, '2030-07-08 23:46:49', 1, '2025-07-08 23:46:49', '2025-07-08 23:46:49'),
(22, 'QR-MYFPFKM991VK22N1', 'dossier_verification', 'App\\Models\\Dossier', 71, 'PP20250059', '\"{\\\"dossier_numero\\\":\\\"PP20250059\\\",\\\"organisation_nom\\\":\\\"OPOLO Dyanasti\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00059\\\",\\\"date_soumission\\\":\\\"2025-07-09T01:23:06.714877Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"abf8a00f704e50e268d0552844b2de6bde9d8e59b97d938aba6c1896414598a4\\\"}\"', 'abf8a00f704e50e268d0552844b2de6bde9d8e59b97d938aba6c1896414598a4', 0, NULL, '2030-07-09 01:23:06', 1, '2025-07-09 01:23:06', '2025-07-09 01:23:06'),
(23, 'QR-NBKXKYJJQYXQF4XU', 'dossier_verification', 'App\\Models\\Dossier', 72, 'PP20250060', '\"{\\\"dossier_numero\\\":\\\"PP20250060\\\",\\\"organisation_nom\\\":\\\"OPOLO Dyanasti MEN\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00060\\\",\\\"date_soumission\\\":\\\"2025-07-09T06:11:27.407635Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"633ef5151bc597ac5ef018b2a2b70ad2cd374958806c3ecda2be0d92b7248d87\\\"}\"', '633ef5151bc597ac5ef018b2a2b70ad2cd374958806c3ecda2be0d92b7248d87', 0, NULL, '2030-07-09 06:11:27', 1, '2025-07-09 06:11:27', '2025-07-09 06:11:27'),
(24, 'QR-J5ETR7Z0SAJZHM73', 'dossier_verification', 'App\\Models\\Dossier', 73, 'PP20250061', '\"{\\\"dossier_numero\\\":\\\"PP20250061\\\",\\\"organisation_nom\\\":\\\"Dynastie OPOLO\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00061\\\",\\\"date_soumission\\\":\\\"2025-07-09T07:31:12.464466Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"92d8fb9b057a576e8ff16f18054d37629a1a268808916daadf14e95aca2e87ae\\\"}\"', '92d8fb9b057a576e8ff16f18054d37629a1a268808916daadf14e95aca2e87ae', 0, NULL, '2030-07-09 07:31:12', 1, '2025-07-09 07:31:12', '2025-07-09 07:31:12'),
(25, 'QR-MVTB9K4JU6YSSHJK', 'dossier_verification', 'App\\Models\\Dossier', 74, 'PP20250062', '\"{\\\"dossier_numero\\\":\\\"PP20250062\\\",\\\"organisation_nom\\\":\\\"TEST MER 25\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00062\\\",\\\"date_soumission\\\":\\\"2025-07-09T18:03:20.457480Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"12e8c6363de973b52598dedecb1c49d8eea4a1b85a8deae938dd059ea97ab76c\\\"}\"', '12e8c6363de973b52598dedecb1c49d8eea4a1b85a8deae938dd059ea97ab76c', 0, NULL, '2030-07-09 18:03:20', 1, '2025-07-09 18:03:20', '2025-07-09 18:03:20'),
(26, 'QR-J3K4Q08V03QZDTMI', 'dossier_verification', 'App\\Models\\Dossier', 75, 'PP20250063', '\"{\\\"dossier_numero\\\":\\\"PP20250063\\\",\\\"organisation_nom\\\":\\\"OUSMAN OPOLO\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00063\\\",\\\"date_soumission\\\":\\\"2025-07-09T19:36:31.754283Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"373ff04535f0c320fc57e54fa1cea1f0eb187d52f4076b7d18197f70b14478ad\\\"}\"', '373ff04535f0c320fc57e54fa1cea1f0eb187d52f4076b7d18197f70b14478ad', 0, NULL, '2030-07-09 19:36:31', 1, '2025-07-09 19:36:31', '2025-07-09 19:36:31'),
(27, 'QR-RHKFVBSFQCH7B1KI', 'dossier_verification', 'App\\Models\\Dossier', 76, 'PP20250064', '\"{\\\"dossier_numero\\\":\\\"PP20250064\\\",\\\"organisation_nom\\\":\\\"OUSMAN OPOLO I\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00064\\\",\\\"date_soumission\\\":\\\"2025-07-09T19:43:10.330093Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"c9ab95f20961afbf5e62fe3da75f5c4b742005738211460c5b661119567e0203\\\"}\"', 'c9ab95f20961afbf5e62fe3da75f5c4b742005738211460c5b661119567e0203', 0, NULL, '2030-07-09 19:43:10', 1, '2025-07-09 19:43:10', '2025-07-09 19:43:10'),
(28, 'QR-6QXBA4V3CSSBSA0I', 'dossier_verification', 'App\\Models\\Dossier', 77, 'PP20250065', '\"{\\\"dossier_numero\\\":\\\"PP20250065\\\",\\\"organisation_nom\\\":\\\"OUSMAN OPOLO II\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00065\\\",\\\"date_soumission\\\":\\\"2025-07-09T23:50:42.595966Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"90ed0b8c502aad8b58ebbe35ca7f8db631624ff35d2fed5610ff9d5eb1f36060\\\"}\"', '90ed0b8c502aad8b58ebbe35ca7f8db631624ff35d2fed5610ff9d5eb1f36060', 0, NULL, '2030-07-09 23:50:42', 1, '2025-07-09 23:50:42', '2025-07-09 23:50:42'),
(29, 'QR-XN3KJUJJTEIMAKJT', 'dossier_verification', 'App\\Models\\Dossier', 78, 'PP20250066', '\"{\\\"dossier_numero\\\":\\\"PP20250066\\\",\\\"organisation_nom\\\":\\\"OUSMAN OPOLO III\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00066\\\",\\\"date_soumission\\\":\\\"2025-07-10T06:55:14.832503Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"a3191175d13c1ec660a28f9d7781c866e392e100e8ed3fb0df8eafbd0516f864\\\"}\"', 'a3191175d13c1ec660a28f9d7781c866e392e100e8ed3fb0df8eafbd0516f864', 0, NULL, '2030-07-10 06:55:14', 1, '2025-07-10 06:55:14', '2025-07-10 06:55:14'),
(30, 'QR-PGOCPOVEW5N36AIY', 'dossier_verification', 'App\\Models\\Dossier', 79, 'PP20250067', '\"{\\\"dossier_numero\\\":\\\"PP20250067\\\",\\\"organisation_nom\\\":\\\"ONCONSEIL 2\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00067\\\",\\\"date_soumission\\\":\\\"2025-07-10T07:04:56.435908Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"4a22c14128c20c4c44e137e439677bba6c4d5fecf8179bafb978e03f48bb528b\\\"}\"', '4a22c14128c20c4c44e137e439677bba6c4d5fecf8179bafb978e03f48bb528b', 0, NULL, '2030-07-10 07:04:56', 1, '2025-07-10 07:04:56', '2025-07-10 07:04:56'),
(31, 'QR-1P7EW0637KNTDTXH', 'dossier_verification', 'App\\Models\\Dossier', 80, 'PP20250068', '\"{\\\"dossier_numero\\\":\\\"PP20250068\\\",\\\"organisation_nom\\\":\\\"ONCONSEIL 3\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00068\\\",\\\"date_soumission\\\":\\\"2025-07-10T07:21:25.673193Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"12de0120032b830e190bcf248175895fce96717b6f0beb74d158b26ab23fecf8\\\"}\"', '12de0120032b830e190bcf248175895fce96717b6f0beb74d158b26ab23fecf8', 0, NULL, '2030-07-10 07:21:25', 1, '2025-07-10 07:21:25', '2025-07-10 07:21:25'),
(32, 'QR-U5CJMXRTWIEYGDFJ', 'dossier_verification', 'App\\Models\\Dossier', 81, 'PP20250069', '\"{\\\"dossier_numero\\\":\\\"PP20250069\\\",\\\"organisation_nom\\\":\\\"SGLP PRO\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00069\\\",\\\"date_soumission\\\":\\\"2025-07-10T19:48:01.695457Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"ffc7684b419fd89752ccea2fbe88828bda2f556627490c9a6afdd11444c8c496\\\"}\"', 'ffc7684b419fd89752ccea2fbe88828bda2f556627490c9a6afdd11444c8c496', 0, NULL, '2030-07-10 19:48:01', 1, '2025-07-10 19:48:01', '2025-07-10 19:48:01'),
(33, 'QR-TGZBKRAUJFPUFHDC', 'dossier_verification', 'App\\Models\\Dossier', 82, 'PP20250070', '\"{\\\"dossier_numero\\\":\\\"PP20250070\\\",\\\"organisation_nom\\\":\\\"SGLP PRO 3\\\",\\\"organisation_type\\\":\\\"parti_politique\\\",\\\"numero_recepisse\\\":\\\"REC-PP-2025-00070\\\",\\\"date_soumission\\\":\\\"2025-07-10T20:02:35.766741Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"dc7427d1e06d8d28b411116ff51cff99f5db4eab0287027a9f451ff00fa977ba\\\"}\"', 'dc7427d1e06d8d28b411116ff51cff99f5db4eab0287027a9f451ff00fa977ba', 0, NULL, '2030-07-10 20:02:35', 1, '2025-07-10 20:02:35', '2025-07-10 20:02:35'),
(34, 'QR-MIGRFD6FSBSIGRN1', 'dossier_verification', 'App\\Models\\Dossier', 83, 'AS20250003', '\"{\\\"dossier_numero\\\":\\\"AS20250003\\\",\\\"organisation_nom\\\":\\\"SGLP ASSOCIATION\\\",\\\"organisation_type\\\":\\\"association\\\",\\\"numero_recepisse\\\":\\\"REC-AS-2025-00003\\\",\\\"date_soumission\\\":\\\"2025-07-10T23:46:56.650856Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"d177fb47db66a888ec0010e7f87deeaca46be6126569f35273bbe73f4886ed58\\\"}\"', 'd177fb47db66a888ec0010e7f87deeaca46be6126569f35273bbe73f4886ed58', 0, NULL, '2030-07-10 23:46:56', 1, '2025-07-10 23:46:56', '2025-07-10 23:46:56'),
(35, 'QR-QYQACL8DRJTE9AOZ', 'dossier_verification', 'App\\Models\\Dossier', 84, 'AS20250004', '\"{\\\"dossier_numero\\\":\\\"AS20250004\\\",\\\"organisation_nom\\\":\\\"ASSOCIATION COMIT\\\",\\\"organisation_type\\\":\\\"association\\\",\\\"numero_recepisse\\\":\\\"REC-AS-2025-00004\\\",\\\"date_soumission\\\":\\\"2025-07-11T08:07:40.761084Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"4f6080fa8a748b4ff27aef90438d362fa79e0f6712058cd4d42461eaa6bc74da\\\"}\"', '4f6080fa8a748b4ff27aef90438d362fa79e0f6712058cd4d42461eaa6bc74da', 0, NULL, '2030-07-11 08:07:40', 1, '2025-07-11 08:07:40', '2025-07-11 08:07:40'),
(36, 'QR-2Y6GRF74HWGYMQCX', 'dossier_verification', 'App\\Models\\Dossier', 85, 'AS20250005', '\"{\\\"dossier_numero\\\":\\\"AS20250005\\\",\\\"organisation_nom\\\":\\\"ASSOCIATION COMIT 2\\\",\\\"organisation_type\\\":\\\"association\\\",\\\"numero_recepisse\\\":\\\"REC-AS-2025-00005\\\",\\\"date_soumission\\\":\\\"2025-07-11T12:57:29.723259Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"0c8aa95573f8d98b12806c4611bef8ba2c4b5dbf37b1160103a0e2746f0b9663\\\"}\"', '0c8aa95573f8d98b12806c4611bef8ba2c4b5dbf37b1160103a0e2746f0b9663', 0, NULL, '2030-07-11 12:57:29', 1, '2025-07-11 12:57:29', '2025-07-11 12:57:29'),
(37, 'QR-GJA3QCICGPPNWEQ5', 'dossier_verification', 'App\\Models\\Dossier', 86, 'AS20250006', '\"{\\\"dossier_numero\\\":\\\"AS20250006\\\",\\\"organisation_nom\\\":\\\"ASSOCIATION COMIT 3\\\",\\\"organisation_type\\\":\\\"association\\\",\\\"numero_recepisse\\\":\\\"REC-AS-2025-00006\\\",\\\"date_soumission\\\":\\\"2025-07-11T13:13:09.308283Z\\\",\\\"statut\\\":\\\"soumis\\\",\\\"province\\\":\\\"Estuaire\\\",\\\"hash_verification\\\":\\\"f45dd9b35f0f765966dabe2c44d651c64e6e5c4ccd79f12f21365723defa1854\\\"}\"', 'f45dd9b35f0f765966dabe2c44d651c64e6e5c4ccd79f12f21365723defa1854', 0, NULL, '2030-07-11 13:13:09', 1, '2025-07-11 13:13:09', '2025-07-11 13:13:09');

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

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `color`, `level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Administrateur', 'Accès total au système PNGDI - Tous pouvoirs', '#8b1538', 10, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 'admin_general', 'Administrateur Général', 'Gestion globale de toutes les organisations', '#003f7f', 9, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 'admin_associations', 'Admin Associations', 'Gestion spécialisée des organisations associatives', '#009e3f', 8, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 'admin_religieuses', 'Admin Religieuses', 'Gestion spécialisée des confessions religieuses', '#ffcd00', 8, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 'admin_politiques', 'Admin Politiques', 'Gestion spécialisée des partis politiques', '#007bff', 8, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 'moderateur', 'Modérateur', 'Validation et modération des contenus', '#17a2b8', 6, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 'operateur', 'Opérateur', 'Saisie et consultation des données', '#28a745', 4, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 'auditeur', 'Auditeur', 'Consultation uniquement - Accès lecture seule', '#6c757d', 2, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40');

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

--
-- Déchargement des données de la table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 2, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 3, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 4, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 5, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 6, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 7, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 8, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 9, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 10, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 12, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 13, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 14, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 15, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 16, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 17, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 19, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 20, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 21, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 22, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 24, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 25, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 26, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 27, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 28, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 29, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 32, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 33, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 34, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 35, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 36, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 37, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 38, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 39, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 40, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 41, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 42, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 44, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 45, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 46, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 47, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 48, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 49, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 50, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 52, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 53, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 54, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 55, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 56, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 57, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 58, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 59, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(1, 60, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 1, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 2, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 3, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 5, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 6, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 7, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 9, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 10, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 12, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 13, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 15, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 16, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 17, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 19, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 20, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 21, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 22, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 24, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 25, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 26, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 27, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 28, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 29, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 32, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 35, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 36, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 39, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 44, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 45, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 46, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 47, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 48, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 49, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 50, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 52, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 53, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 54, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 55, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(2, 56, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 12, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 13, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 15, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 16, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 19, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 20, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 21, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 22, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 24, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 25, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 26, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 27, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 44, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 45, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 48, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 49, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 52, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(3, 53, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 12, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 13, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 15, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 16, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 19, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 20, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 21, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 22, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 24, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 25, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 26, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 27, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 44, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 45, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 48, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 49, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 52, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(4, 53, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 12, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 13, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 15, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 16, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 19, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 20, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 21, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 22, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 24, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 25, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 26, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 27, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 44, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 45, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 48, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 49, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 52, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(5, 53, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 15, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 16, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 25, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 26, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 47, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 48, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(6, 53, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 12, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 13, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 18, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 21, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 22, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 30, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 44, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 45, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 49, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(7, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 11, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 23, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 31, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 35, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 36, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 43, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 51, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 55, '2025-06-27 09:00:40', '2025-06-27 09:00:40'),
(8, 56, '2025-06-27 09:00:40', '2025-06-27 09:00:40');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('d084ue7b8V43H4FfO7ERqtkwcJv8ItCUTzjtpGqB', 3, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:140.0) Gecko/20100101 Firefox/140.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZ0t3QTlpOVAxT1g0TlRCZXFvUkcyV2IxOFZsRmlJSHlvMzhhRVdKNSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjUyOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvb3BlcmF0b3IvZG9zc2llcnMvY3JlYXRlL3BhcnRpIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9', 1751924587);

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

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `nom`, `prenom`, `email`, `nip`, `phone`, `email_verified_at`, `password`, `role_id`, `status`, `role`, `remember_token`, `created_at`, `updated_at`, `address`, `city`, `country`, `date_naissance`, `lieu_naissance`, `sexe`, `photo_path`, `avatar`, `is_active`, `two_factor_enabled`, `two_factor_secret`, `last_login_at`, `login_attempts`, `is_verified`, `verification_token`, `last_login_ip`, `preferences`, `metadata`, `created_by`, `updated_by`, `failed_login_attempts`, `locked_until`) VALUES
(1, 'Admin PNGDI', NULL, NULL, 'admin@pngdi.ga', NULL, '+24101234567', '2025-06-25 18:35:38', '$2y$10$Lf06.noTxd3BfH01.X1WOOJ7sEZSnvPdBRDuOFi.E1GwQdhaY/UKa', 1, 'active', 'admin', 'K3jJQBTd9A4Jq5yBPEZIg4oLuGKQY6bGH2HjG62AGeIr5vy8FATUq4QQcPwA', '2025-06-23 20:11:15', '2025-06-30 19:24:48', NULL, 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-06-30 19:24:48', 0, 0, NULL, '127.0.0.1', '{\"theme\": \"light\", \"language\": \"fr\", \"timezone\": \"Africa/Libreville\", \"notifications\": {\"sms\": false, \"email\": true, \"browser\": true}}', NULL, NULL, 1, 0, NULL),
(2, 'Agent PNGDI', NULL, NULL, 'agent@pngdi.ga', NULL, '+24101234568', NULL, '$2y$10$yVtWbex9TRstjtSQws2D7uDbiTX8raWrXuXd5AyqPS.pWORd5mX.e', NULL, 'pending', 'agent', NULL, '2025-06-23 20:11:15', '2025-06-25 23:30:33', NULL, 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 0, NULL, NULL, '{\"theme\": \"light\", \"language\": \"fr\", \"timezone\": \"Africa/Libreville\", \"notifications\": {\"sms\": false, \"email\": true, \"browser\": true}}', NULL, NULL, NULL, 0, NULL),
(3, 'Jean NGUEMA', NULL, NULL, 'operator@pngdi.ga', NULL, '+24101234569', '2025-06-23 22:59:10', '$2y$10$QJ6hlxKg/.yy/czdZxVtdeopOeHnMJAwCfJEmlbKIySXxXrKMtVE2', NULL, 'active', 'operator', NULL, '2025-06-23 20:11:15', '2025-07-10 19:58:16', NULL, 'Port-Gentil', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-07-10 19:58:16', 0, 0, NULL, '127.0.0.1', '{\"theme\": \"light\", \"language\": \"fr\", \"timezone\": \"Africa/Libreville\", \"notifications\": {\"sms\": false, \"email\": true, \"browser\": true}}', NULL, NULL, 3, 0, NULL),
(4, 'Admin PNGDI 2', NULL, NULL, 'admin2@pngdi.ga', NULL, NULL, NULL, '$2y$10$3BW5S/WGDxpMPhKrNeJIA.5XqVX8Vjv5l15KA5QMSL7dCJZk/Qlke', NULL, 'pending', 'admin', NULL, '2025-06-25 20:09:43', '2025-06-25 23:30:33', NULL, NULL, 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-06-25 20:09:43', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(5, 'Marie Ngoma', NULL, NULL, 'marie.ngoma@pngdi.ga', NULL, NULL, NULL, '$2y$10$Tv/WEViUX97pQ7NGsExFau3TPggJgrugCmIu84aqJZ1NA4f/ab2LW', NULL, 'pending', 'agent', NULL, '2025-06-27 04:55:59', '2025-06-27 04:55:59', NULL, NULL, 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-06-26 04:55:59', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(6, 'Pierre Obame', NULL, NULL, 'pierre.obame@pngdi.ga', NULL, NULL, NULL, '$2y$10$./C5GfP3OEkrc4aj9Mu5u.LMAM7XbmgbScfp14.gmghsCi62mXq1.', NULL, 'pending', 'agent', NULL, '2025-06-27 04:55:59', '2025-06-27 04:55:59', NULL, NULL, 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-06-23 04:55:59', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(7, 'Sylvie Mintsa', NULL, NULL, 'sylvie.mintsa@pngdi.ga', NULL, NULL, NULL, '$2y$10$RrTkEVMy0bMBWwUqDGMmvOvsaSW0SYfnSJOhhoQkD4V9cY.bfvtuy', NULL, 'pending', 'agent', NULL, '2025-06-27 04:55:59', '2025-06-27 04:55:59', NULL, NULL, 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-06-26 16:55:59', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(8, 'Admin Général PNGDI', NULL, NULL, 'admin.general@pngdi.ga', NULL, '+24103399553', '2025-06-27 09:00:40', '$2y$10$OqSM0BOKFMe1gyPcysUDA.a/0D91.7deu77d6LDl5UDRsNP0h45D.', 2, 'active', 'admin', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Administrateur Général\", \"department\": \"Direction Générale\"}', NULL, NULL, 0, NULL),
(9, 'Admin Associations', NULL, NULL, 'admin.associations@pngdi.ga', NULL, '+24101852629', '2025-06-27 09:00:40', '$2y$10$sAW8Vrx.BEI.iIBD5F5cR.ATM6YfWZjg.lTuS/Efwh3Whq2UnDZu.', 3, 'active', 'agent', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Responsable Associations\", \"department\": \"Service Associations\"}', NULL, NULL, 0, NULL),
(10, 'Admin Religieuses', NULL, NULL, 'admin.religieuses@pngdi.ga', NULL, '+24103851272', '2025-06-27 09:00:40', '$2y$10$byNfcv.ZsfygJjtDW9Szz.txZDn7JX9k4fijJOxLgRbIFaEfGAfPy', 4, 'active', 'agent', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Responsable Confessions Religieuses\", \"department\": \"Service Confessions\"}', NULL, NULL, 0, NULL),
(11, 'Admin Politiques', NULL, NULL, 'admin.politiques@pngdi.ga', NULL, '+24105254987', '2025-06-27 09:00:40', '$2y$10$mzKeXWGVOszgBAXvqu33vuEQFWL5TcZ0YFKmiYAmOiTaZi.HkBfSO', 5, 'active', 'agent', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Responsable Partis Politiques\", \"department\": \"Service Partis Politiques\"}', NULL, NULL, 0, NULL),
(12, 'Modérateur PNGDI', NULL, NULL, 'moderateur@pngdi.ga', NULL, '+24108616268', '2025-06-27 09:00:40', '$2y$10$FCkM82oZKy0CsWYnKoA9P.7907jd7FmxmHF3mYYqQMZH8DGq/BKHi', 6, 'active', 'agent', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Modérateur Principal\", \"department\": \"Service Validation\"}', NULL, NULL, 0, NULL),
(13, 'Opérateur PNGDI', NULL, NULL, 'operateur@pngdi.ga', NULL, '+24104829157', '2025-06-27 09:00:40', '$2y$10$JKDaViApxbSF1S0vTilc5.ym4bh.kCKNS.1omcyM9yNLa6E1lXQTq', 7, 'active', 'operator', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Opérateur de Saisie\", \"department\": \"Service Saisie\"}', NULL, NULL, 0, NULL),
(14, 'Auditeur PNGDI', NULL, NULL, 'auditeur@pngdi.ga', NULL, '+24106816825', '2025-06-27 09:00:40', '$2y$10$oIhigeSMWMn9Sw89pDz6X.vhu4UE966wFDYLu2xwO6P9ugqYHVyB2', 8, 'active', 'visitor', NULL, '2025-06-27 09:00:40', '2025-06-27 09:00:40', 'Ministère de l\'Intérieur', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, 1, NULL, NULL, '{\"theme\": \"gabonais\", \"language\": \"fr\", \"two_factor\": false, \"notifications\": true, \"dashboard_layout\": \"standard\"}', '{\"source\": \"system_seed\", \"function\": \"Auditeur Système\", \"department\": \"Service Audit\"}', NULL, NULL, 0, NULL),
(15, 'yubile', 'Yubile', NULL, 'yubile.technologie@gmail.com', NULL, '074520842', '2025-07-04 13:28:04', '$2y$10$gmx.Ck4vV/WGNPr8/xylw.ggYR6rSHMziY2OEIQ2HlOx9bo9Sg32e', NULL, 'active', 'operator', NULL, '2025-07-04 13:21:07', '2025-07-04 13:28:04', 'Damas', 'Libreville', 'Gabon', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-07-04 13:28:04', 0, 0, NULL, '127.0.0.1', NULL, NULL, NULL, 15, 0, NULL);

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
  ADD KEY `adherents_nip_statut_validation_index` (`nip`,`statut_validation`),
  ADD KEY `adherents_has_anomalies_anomalies_severity_index` (`has_anomalies`,`anomalies_severity`),
  ADD KEY `adherents_is_active_has_anomalies_index` (`is_active`,`has_anomalies`);

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
-- Index pour la table `draft_accuses`
--
ALTER TABLE `draft_accuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `draft_accuses_numero_accuse_unique` (`numero_accuse`),
  ADD KEY `draft_accuses_draft_id_step_number_index` (`draft_id`,`step_number`),
  ADD KEY `draft_accuses_numero_accuse_index` (`numero_accuse`),
  ADD KEY `draft_accuses_user_id_generated_at_index` (`user_id`,`generated_at`);

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
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

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
-- AUTO_INCREMENT pour la table `draft_accuses`
--
ALTER TABLE `draft_accuses`
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Contraintes pour la table `draft_accuses`
--
ALTER TABLE `draft_accuses`
  ADD CONSTRAINT `draft_accuses_draft_id_foreign` FOREIGN KEY (`draft_id`) REFERENCES `organization_drafts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `draft_accuses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
