<?php

declare(strict_types=1);

return [
    'label' => 'proyecto',
    'plural_label' => 'proyectos',
    'navigation_label' => 'Proyectos',

    'fields' => [
        'name' => ['label' => 'Nombre'],
        'slug' => ['label' => 'Slug'],
        'description' => ['label' => 'Descripción'],
        'status' => ['label' => 'Estado'],
        'budget' => ['label' => 'Presupuesto'],
        'color' => ['label' => 'Color'],
        'start_date' => ['label' => 'Fecha de inicio'],
        'end_date' => ['label' => 'Fecha de fin'],
        'due_date' => ['label' => 'Fecha límite'],
        'company' => ['label' => 'Cliente'],
        'company_id' => ['label' => 'Cliente'],
        'account_owner' => ['label' => 'Responsable'],
        'account_owner_id' => ['label' => 'Responsable'],
        'creator' => ['label' => 'Creado por'],
        'creation_source' => ['label' => 'Fuente de creación'],
        'created_at' => ['label' => 'Fecha de creación'],
        'updated_at' => ['label' => 'Última actualización'],
        'deleted_at' => ['label' => 'Eliminado el'],
    ],

    'pages' => [
        'list' => [
            'actions' => [
                'create' => ['label' => 'Nuevo proyecto'],
            ],
        ],
        'view' => [
            'actions' => [
                'edit' => ['label' => 'Editar'],
                'copy_page_url' => ['label' => 'Copiar URL de página'],
                'copy_record_id' => ['label' => 'Copiar ID del registro'],
            ],
            'infolist' => [
                'fields' => [
                    'name' => ['label' => 'Nombre'],
                    'status' => ['label' => 'Estado'],
                    'company' => ['label' => 'Cliente'],
                    'account_owner' => ['label' => 'Responsable'],
                    'budget' => ['label' => 'Presupuesto'],
                    'color' => ['label' => 'Color'],
                    'start_date' => ['label' => 'Fecha de inicio'],
                    'end_date' => ['label' => 'Fecha de fin'],
                    'due_date' => ['label' => 'Fecha límite'],
                    'creator' => ['label' => 'Creado por'],
                    'created_at' => ['label' => 'Fecha de creación'],
                    'updated_at' => ['label' => 'Última actualización'],
                ],
            ],
        ],
    ],

    'relation_managers' => [
        'people' => [
            'model_label' => 'persona',
        ],
        'notes' => [
            'fields' => [
                'people' => ['label' => 'Personas'],
            ],
        ],
    ],
];
