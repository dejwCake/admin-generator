@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    assert($relations instanceof RelationCollection);
    assert($columns instanceof ColumnCollection);
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
@if($hasExport)
            'export' => 'Export',
@endif
@if($hasPublishedAt)
            'will_be_published' => '{{$modelBaseName}} will be published at',
@endif
        ],

        'columns' => [
@foreach($columns as $column)
            '{{ $column->name }}' => '{{ $column->defaultTranslation }}',
@if($column->name === 'password')
            '{{ $column->name }}_repeat' => '{{ $column->defaultTranslation }} Confirmation',
@endif
@endforeach
        ],
@if ($relations->hasBelongsToMany())

        //Belongs to many relations
        'relations' => [
@foreach($relations->getBelongsToMany() as $belongsToMany)
            '{{ $belongsToMany->relationTranslationKey }}' => '{{ $belongsToMany->relationTranslationValue }}',
@endforeach
        ],
@endif
@if($mediaCollections->isNotEmpty())

        'collections' => [
@foreach($mediaCollections as $collection)
            '{{ $collection->translationKey }}' => '{{ $collection->translationValue }}',
@endforeach
        ],
@endif
    ],

    // Do not delete me :) I'm used for auto-generation