<?php

declare(strict_types=1);

return [
    'user' => [
        'title' => 'Users',

        'actions' => [
            'index' => 'Users',
            'create' => 'New User',
            'edit' => 'Edit :name',
        ],

        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'email_verified_at' => 'Email verified at',
            'password' => 'Password',
            'password_repeat' => 'Password Confirmation',
            'remember_token' => 'Remember token',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],

        //Belongs to many relations
        'relations' => [
            'roles' => 'Roles',
        ],

        'collections' => [
            'gallery' => 'Gallery',
        ],
    ],

    //-- Do not delete me :) I'm used for auto-generation language arrays --
];
