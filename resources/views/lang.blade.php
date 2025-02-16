    '{{ $modelLangFormat }}' => [
        'title' => '{{ $titlePlural }}',

        'actions' => [
            'index' => '{{ $titlePlural }}',
            'create' => 'New {{ $titleSingular }}',
            'edit' => 'Edit :name',
@if($export)
            'export' => 'Export',
@endif
@if($containsPublishedAtColumn)
            'will_be_published' => '{{$modelBaseName}} will be published at',
@endif
        ],

        'columns' => [
            'id' => 'ID',
@foreach($columns as $col)
            '{{ $col['name'] }}' => '{{ $col['defaultTranslation'] }}',
@if($col['name'] === 'password')
            '{{ $col['name'] }}_repeat' => '{{ ucfirst(str_replace('_', ' ', $col['name'])) }} Confirmation',
@endif
@endforeach
@if (count($relations) > 0 && count($relations['belongsToMany']) > 0)
            //Belongs to many relations
@foreach($relations['belongsToMany'] as $belongsToMany)
            '{{ lcfirst($belongsToMany['related_model_name_plural']) }}' => '{{ ucfirst(str_replace('_', ' ', $belongsToMany['related_model_name_plural'])) }}',
@endforeach
@endif
        ],
    ],

    // Do not delete me :) I'm used for auto-generation