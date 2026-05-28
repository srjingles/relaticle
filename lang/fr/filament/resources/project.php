<?php

declare(strict_types=1);

return [
    'label' => 'projet',
    'plural_label' => 'projets',
    'navigation_label' => 'Projets',

    'fields' => [
        'name' => ['label' => 'Nom'],
        'slug' => ['label' => 'Slug'],
        'description' => ['label' => 'Description'],
        'status' => ['label' => 'Statut'],
        'budget' => ['label' => 'Budget'],
        'color' => ['label' => 'Couleur'],
        'start_date' => ['label' => 'Date de début'],
        'end_date' => ['label' => 'Date de fin'],
        'due_date' => ['label' => 'Date limite'],
        'company' => ['label' => 'Client'],
        'company_id' => ['label' => 'Client'],
        'account_owner' => ['label' => 'Responsable de compte'],
        'account_owner_id' => ['label' => 'Responsable de compte'],
        'creator' => ['label' => 'Créé par'],
        'creation_source' => ['label' => 'Source de création'],
        'created_at' => ['label' => 'Date de création'],
        'updated_at' => ['label' => 'Dernière mise à jour'],
        'deleted_at' => ['label' => 'Supprimé le'],
    ],

    'pages' => [
        'list' => [
            'actions' => [
                'create' => ['label' => 'Nouveau projet'],
            ],
        ],
        'view' => [
            'actions' => [
                'edit' => ['label' => 'Modifier'],
                'copy_page_url' => ['label' => 'Copier l\'URL de la page'],
                'copy_record_id' => ['label' => 'Copier l\'ID de l\'enregistrement'],
            ],
            'infolist' => [
                'fields' => [
                    'name' => ['label' => 'Nom'],
                    'status' => ['label' => 'Statut'],
                    'company' => ['label' => 'Client'],
                    'account_owner' => ['label' => 'Responsable de compte'],
                    'budget' => ['label' => 'Budget'],
                    'color' => ['label' => 'Couleur'],
                    'start_date' => ['label' => 'Date de début'],
                    'end_date' => ['label' => 'Date de fin'],
                    'due_date' => ['label' => 'Date limite'],
                    'description' => ['label' => 'Description'],
                    'creator' => ['label' => 'Créé par'],
                    'created_at' => ['label' => 'Date de création'],
                    'updated_at' => ['label' => 'Dernière mise à jour'],
                ],
            ],
        ],
    ],

    'relation_managers' => [
        'people' => [
            'model_label' => 'personne',
        ],
        'notes' => [
            'model_label' => 'note',
            'fields' => [
                'people' => ['label' => 'Personnes'],
            ],
        ],
    ],
];
