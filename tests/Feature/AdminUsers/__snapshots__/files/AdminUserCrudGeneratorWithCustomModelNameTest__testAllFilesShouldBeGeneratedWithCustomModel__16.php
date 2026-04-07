<?php

declare(strict_types=1);

return [
    'user' => [
        'title' => 'Admin Users',

        'actions' => [
            'index' => 'Admin Users',
            'create' => 'New Admin User',
            'edit' => 'Edit :name',
            'edit_profile' => 'Edit Profile',
            'edit_password' => 'Edit Password',
            'export' => 'Export',
        ],

        'columns' => [
            'id' => 'ID',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Password Confirmation',
            'remember_token' => 'Remember token',
            'activated' => 'Activated',
            'forbidden' => 'Forbidden',
            'language' => 'Language',
            'deleted_at' => 'Deleted at',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],

        //Belongs to many relations
        'relations' => [
            'roles' => 'Roles',
        ],
    ],

    //-- Do not delete me :) I'm used for auto-generation language arrays --
];
