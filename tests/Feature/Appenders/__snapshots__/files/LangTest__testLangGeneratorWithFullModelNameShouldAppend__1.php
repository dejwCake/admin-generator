<?php

declare(strict_types=1);

return [
    'categ-ory' => [
        'title' => 'Categories',

        'actions' => [
            'index' => 'Categories',
            'create' => 'New Category',
            'edit' => 'Edit :name',
            'will_be_published' => 'CategOry will be published at',
        ],

        'columns' => [
            'id' => 'ID',
            'user_id' => 'User',
            'title' => 'Title',
            'slug' => 'Slug',
            'perex' => 'Perex',
            'published_at' => 'Published at',
            'date_start' => 'Date start',
            'time_start' => 'Time start',
            'date_time_end' => 'Date time end',
            'text' => 'Text',
            'description' => 'Description',
            'enabled' => 'Enabled',
            'send' => 'Send',
            'price' => 'Price',
            'views' => 'Views',
            'created_by_admin_user_id' => 'Created by admin user',
            'updated_by_admin_user_id' => 'Updated by admin user',
        ],
    ],

    // Do not delete me :) I'm used for auto-generation
];
