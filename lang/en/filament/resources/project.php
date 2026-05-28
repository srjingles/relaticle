<?php

declare(strict_types=1);

return [
    // Filament title-cases the singular/plural labels for display contexts
    // (navigation menu, page headings) but injects them raw into the
    // "New :label" create button. Keep lowercase here so the button reads
    // "New project" while titles render as "Project"/"Projects".
    'label' => 'project',
    'plural_label' => 'projects',
    'navigation_label' => 'Projects',

    'fields' => [
        'name' => ['label' => 'Name'],
        'slug' => ['label' => 'Slug'],
        'description' => ['label' => 'Description'],
        'status' => ['label' => 'Status'],
        'budget' => ['label' => 'Budget'],
        'color' => ['label' => 'Color'],
        'start_date' => ['label' => 'Start Date'],
        'end_date' => ['label' => 'End Date'],
        'due_date' => ['label' => 'Due Date'],
        'company' => ['label' => 'Client'],
        'company_id' => ['label' => 'Client'],
        'account_owner' => ['label' => 'Account Owner'],
        'account_owner_id' => ['label' => 'Account Owner'],
        'creator' => ['label' => 'Created By'],
        'creation_source' => ['label' => 'Creation Source'],
        'created_at' => ['label' => 'Creation Date'],
        'updated_at' => ['label' => 'Last Update'],
        'deleted_at' => ['label' => 'Deleted At'],
    ],

    'pages' => [
        'list' => [
            'actions' => [
                'create' => ['label' => 'New project'],
            ],
        ],
        'view' => [
            'actions' => [
                'edit' => ['label' => 'Edit'],
                'copy_page_url' => ['label' => 'Copy page URL'],
                'copy_record_id' => ['label' => 'Copy record ID'],
            ],
            'infolist' => [
                'fields' => [
                    'name' => ['label' => 'Name'],
                    'status' => ['label' => 'Status'],
                    'company' => ['label' => 'Client'],
                    'account_owner' => ['label' => 'Account Owner'],
                    'budget' => ['label' => 'Budget'],
                    'color' => ['label' => 'Color'],
                    'start_date' => ['label' => 'Start Date'],
                    'end_date' => ['label' => 'End Date'],
                    'due_date' => ['label' => 'Due Date'],
                    'creator' => ['label' => 'Created By'],
                    'created_at' => ['label' => 'Created Date'],
                    'updated_at' => ['label' => 'Last Updated'],
                ],
            ],
        ],
    ],

    'relation_managers' => [
        'people' => [
            'model_label' => 'person',
        ],
        'notes' => [
            'fields' => [
                'people' => ['label' => 'People'],
            ],
        ],
    ],
];
