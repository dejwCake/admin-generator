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
            'current_team_id' => 'Current team',
            'two_factor_secret' => 'Two factor secret',
            'two_factor_recovery_codes' => 'Two factor recovery codes',
            'two_factor_confirmed_at' => 'Two factor confirmed at',
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
