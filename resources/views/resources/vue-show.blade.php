@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    assert($columns instanceof ColumnCollection);
@endphp
<template>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            {{ '{{' }} translations.show_title }}
        </div>
        <div class="card-body">
@foreach($columns as $column)
@if(in_array($column->majorType, ['bool'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}">
                <ReadOnlySwitch :modelValue="data.{{ $column->name }}"/>
            </ShowRow>
@elseif(in_array($column->majorType, ['date'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="formatDate(data.{{ $column->name }})"/>
@elseif(in_array($column->majorType, ['time'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="formatTime(data.{{ $column->name }})"/>
@elseif(in_array($column->majorType, ['datetime'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="formatDatetime(data.{{ $column->name }})"/>
@elseif($column->isArray())
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="Object.values(data.{{ $column->name }} ?? {}).join(', ')"/>
@else
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="data.{{ $column->name }}"/>
@endif
@endforeach
        </div>
        <div class="card-footer">
            <BackButton :url="backUrl" :translations="translations"/>
        </div>
    </div>
</template>

<script setup>
@php
    use Illuminate\Support\Collection;

    $imports = new Collection();
    if ($dateImports->isNotEmpty()) {
        $imports->push('{' . $dateImports->implode(', ') . "} from '@craftable/utils/dateFormatters.js'");
    }
    if ($hasBoolean) {
        $imports->push("ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue'");
    }
    $imports->push("BackButton from '@craftable/components/BackButton.vue'");
    $imports->push("ShowRow from '@craftable/components/show/ShowRow.vue'");
    $imports = $imports->unique()->sort();
@endphp
@foreach($imports as $import)
import {!! $import !!};
@endforeach

defineProps({
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    backUrl: {type: String, required: true},
});
</script>
