<?php
// ========================================================================
// FICHIER: config/organisation.php
// Configuration pour le module organisations PNGDI
// Compatible PHP 7.3.29
// ========================================================================

return [
    // Configuration des uploads
    'upload_max_size' => 5242880, // 5MB
    'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
    
    // Types d'organisations supportés
    'types' => [
        'association' => [
            'label' => 'Association',
            'min_fondateurs' => 3,
            'min_adherents' => 10,
            'color' => 'success',
            'icon' => 'fas fa-handshake',
            'description' => 'Groupement de personnes réunies autour d\'un projet commun'
        ],
        'ong' => [
            'label' => 'ONG',
            'min_fondateurs' => 5,
            'min_adherents' => 15,
            'color' => 'info',
            'icon' => 'fas fa-globe-africa',
            'description' => 'Organisation Non Gouvernementale à vocation humanitaire'
        ],
        'parti_politique' => [
            'label' => 'Parti Politique',
            'min_fondateurs' => 3,
            'min_adherents' => 50,
            'color' => 'warning',
            'icon' => 'fas fa-vote-yea',
            'description' => 'Organisation politique pour participer à la vie démocratique'
        ],
        'confession_religieuse' => [
            'label' => 'Confession Religieuse',
            'min_fondateurs' => 3,
            'min_adherents' => 10,
            'color' => 'purple',
            'icon' => 'fas fa-pray',
            'description' => 'Organisation religieuse pour l\'exercice du culte'
        ]
    ],
    
    // Règles de validation
    'validation_rules' => [
        'nip' => [
            'required' => true,
            'length' => 13,
            'type' => 'numeric',
            'unique_check' => true
        ],
        'phone_gabon' => [
            'prefixes' => ['01', '02', '03', '04', '05', '06', '07'],
            'length' => [8, 9],
            'format' => '/^[0-9]{8,9}$/'
        ],
        'email' => [
            'format' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            'max_length' => 255
        ]
    ],
    
    // Documents requis par type d'organisation
    'required_documents' => [
        'association' => [
            'statuts' => ['name' => 'Statuts de l\'association', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale constitutive', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'justif_siege' => ['name' => 'Justificatif siège social', 'required' => false]
        ],
        'ong' => [
            'statuts' => ['name' => 'Statuts de l\'ONG', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale constitutive', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'projet_social' => ['name' => 'Projet social détaillé', 'required' => true],
            'budget_previsionnel' => ['name' => 'Budget prévisionnel', 'required' => true],
            'cv_fondateurs' => ['name' => 'CV des fondateurs', 'required' => true]
        ],
        'parti_politique' => [
            'statuts' => ['name' => 'Statuts du parti', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale constitutive', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'programme_politique' => ['name' => 'Programme politique', 'required' => true],
            'liste_50_adherents' => ['name' => 'Liste de 50 adhérents minimum', 'required' => true],
            'repartition_geo' => ['name' => 'Répartition géographique', 'required' => true],
            'sources_financement' => ['name' => 'Sources de financement', 'required' => true]
        ],
        'confession_religieuse' => [
            'statuts' => ['name' => 'Statuts de la confession', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale constitutive', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'expose_doctrine' => ['name' => 'Exposé de la doctrine religieuse', 'required' => true],
            'justif_lieu_culte' => ['name' => 'Justificatif lieu de culte', 'required' => true],
            'attestation_responsable' => ['name' => 'Attestation responsable religieux', 'required' => true],
            'liste_fideles' => ['name' => 'Liste des fidèles', 'required' => true]
        ]
    ],
    
    // Limites par opérateur
    'operator_limits' => [
        'parti_politique' => 1, // Un seul parti par opérateur
        'confession_religieuse' => 1, // Une seule confession par opérateur
        'association' => null, // Pas de limite
        'ong' => null // Pas de limite
    ],
    
    // Configuration des étapes du formulaire
    'form_steps' => [
        1 => ['name' => 'Type', 'icon' => 'fas fa-list-ul', 'required' => true],
        2 => ['name' => 'Guide', 'icon' => 'fas fa-book-open', 'required' => true],
        3 => ['name' => 'Demandeur', 'icon' => 'fas fa-user', 'required' => true],
        4 => ['name' => 'Organisation', 'icon' => 'fas fa-building', 'required' => true],
        5 => ['name' => 'Coordonnées', 'icon' => 'fas fa-map-marker-alt', 'required' => true],
        6 => ['name' => 'Fondateurs', 'icon' => 'fas fa-users', 'required' => true],
        7 => ['name' => 'Adhérents', 'icon' => 'fas fa-user-plus', 'required' => true],
        8 => ['name' => 'Documents', 'icon' => 'fas fa-file-alt', 'required' => true],
        9 => ['name' => 'Soumission', 'icon' => 'fas fa-check-circle', 'required' => true]
    ],
    
    // Configuration de la validation NIP (simplifiée)
    'nip_validation' => [
        'enabled' => true,
        'strict_checksum' => false, // Désactiver le checksum strict
        'allow_test_values' => true, // Permettre les valeurs de test
        'basic_checks' => [
            'length' => 13,
            'numeric_only' => true,
            'no_repetition' => true, // Éviter 1111111111111
            'no_sequence' => true    // Éviter 1234567890123
        ]
    ]
];