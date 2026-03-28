@php use Illuminate\Support\Str;
@endphp
    '{{ $modelLangFormat }}' => [
        'title' => '{{ $titlePlural }}',

        'actions' => [
            'index' => '{{ $titlePlural }}',
            'create' => 'New {{ $titleSingular }}',
            'edit' => 'Edit :name',
@if($hasProfile)
            'edit_profile' => 'Edit Profile',
            'edit_password' => 'Edit Password',
@endif
@if($export)
            'export' => 'Export',
@endif
@if($hasPublishedAt)
            'will_be_published' => '{{$modelBaseName}} will be published at',
@endif
        ],

        'columns' => [
@foreach($columns as $column)
            '{{ $column['name'] }}' => '{{ $column['defaultTranslation'] }}',
@if($column['name'] === 'password')
            '{{ $column['name'] }}_repeat' => '{{ $column['defaultTranslation'] }} Confirmation',
@endif
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)
            //Belongs to many relations
@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ Str::lcfirst($belongsToMany['related_model_name_plural']) }}' => '{{ Str::ucfirst(str_replace('_', ' ', $belongsToMany['related_model_name_plural'])) }}',
@endforeach
@endif
        ],
@if($mediaCollections->isNotEmpty())

        'collections' => [
@foreach($mediaCollections as $collection)
            '{{ Str::lcfirst($collection->collectionName) }}' => '{{ Str::headline($collection->collectionName) }}',
@endforeach
        ],
@endif
    ],

    // Do not delete me :) I'm used for auto-generation