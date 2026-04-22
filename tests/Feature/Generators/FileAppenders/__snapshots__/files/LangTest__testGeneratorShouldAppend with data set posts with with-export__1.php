<?php

declare(strict_types=1);

return [
    'post' => [
        'title' => 'Posts',

        'actions' => [
            'index' => 'Posts',
            'create' => 'New Post',
            'edit' => 'Edit :name',
            'export' => 'Export',
        ],

        'columns' => [
            'id' => 'ID',
            'title' => 'Title',
        ],

        //Belongs to many relations
        'relations' => [
            'categories' => 'Categories',
        ],
    ],

    //-- Do not delete me :) I'm used for auto-generation language arrays --
];
