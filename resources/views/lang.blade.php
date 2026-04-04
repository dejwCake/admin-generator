@php
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Str;
    assert($relations instanceof RelationCollection);
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
@if ($relations->hasBelongsToMany())
            //Belongs to many relations
@foreach($relations->getBelongsToMany() as $belongsToMany)
            '{{ Str::lcfirst($belongsToMany->relatedModelNamePlural) }}' => '{{ Str::ucfirst(str_replace('_', ' ', $belongsToMany->relatedModelNamePlural)) }}',
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