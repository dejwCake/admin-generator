<?php

declare(strict_types=1);

return [
    'billing_cat' => [
        'title' => 'Categories',

        'actions' => [
            'index' => 'Categories',
            'create' => 'New Category',
            'edit' => 'Edit :name',
            'will_be_published' => 'Cat will be published at',
        ],

        'columns' => [
            'id' => 'ID',
            'user_id' => 'User',
            'title' => 'Title',
            'name' => 'Name',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'subject' => 'Subject',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Password Confirmation',
            'remember_token' => 'Remember token',
            'language' => 'Language',
            'slug' => 'Slug',
            'perex' => 'Perex',
            'long_text' => 'Long text',
            'published_at' => 'Published at',
            'date_start' => 'Date start',
            'time_start' => 'Time start',
            'date_time_end' => 'Date time end',
            'released_at' => 'Released at',
            'text' => 'Text',
            'description' => 'Description',
            'enabled' => 'Enabled',
            'send' => 'Send',
            'price' => 'Price',
            'rating' => 'Rating',
            'views' => 'Views',
            'created_by_admin_user_id' => 'Created by admin user',
            'updated_by_admin_user_id' => 'Updated by admin user',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
        ],

        //Belongs to many relations
        'relations' => [
            'posts' => 'Posts',
        ],
    ],

    //-- Do not delete me :) I'm used for auto-generation language arrays --
];
