<?php

declare(strict_types=1);

return [
    'category' => [
        'title' => 'Categories',

        'actions' => [
            'index' => 'Categories',
            'create' => 'New Category',
            'edit' => 'Edit :name',
            'export' => 'Export',
        ],

        'columns' => [
            'id' => 'ID',
            'title' => 'Title',
        ],
    ],

    // Do not delete me :) I'm used for auto-generation
];
