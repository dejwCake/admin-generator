@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Str;
    assert($columns instanceof ColumnCollection);
    assert($relations instanceof RelationCollection);
@endphp
<template>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            {{ '{{' }} translations.show_title }}
        </div>
        <div class="card-body">
@foreach($columns as $column)
@if($column->name === 'created_by_admin_user_id')
            <ShowRow :label="translations.columns.{{ $column->name }}">
                <UserDetailTooltip
                    v-if="data.created_by_admin_user"
                    :user="data.created_by_admin_user"
                    :datetime-text="translations.created_on + ' ' + formatDatetime(data.created_at)"
                />
            </ShowRow>
@elseif($column->name === 'updated_by_admin_user_id')
            <ShowRow :label="translations.columns.{{ $column->name }}">
                <UserDetailTooltip
                    v-if="data.updated_by_admin_user"
                    :user="data.updated_by_admin_user"
                    :datetime-text="translations.updated_on + ' ' + formatDatetime(data.updated_at)"
                />
            </ShowRow>
@elseif($column->isForeignKey && $relations->hasBelongsToByColumn($column->name))
@php $belongsTo = $relations->getBelongsToByColumn($column->name); @endphp
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="data.{{ Str::snake($belongsTo->relationMethodName) }}?.{{ $belongsTo->relatedLabel }}"/>
@elseif(in_array($column->majorType, ['bool'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}">
                <ReadOnlySwitch :modelValue="data.{{ $column->name }}"/>
            </ShowRow>
@elseif(in_array($column->majorType, ['date'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="formatDate(data.{{ $column->name }})"/>
@elseif(in_array($column->majorType, ['time'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="formatTime(data.{{ $column->name }})"/>
@elseif(in_array($column->majorType, ['datetime'], true))
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="formatDatetime(data.{{ $column->name }})"/>
@elseif($column->isWysiwyg)
            <ShowRow :label="translations.columns.{{ $column->name }}">
                <div v-html="data.{{ $column->name }}"></div>
            </ShowRow>
@elseif($column->isArray())
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="Object.values(data.{{ $column->name }} ?? {}).join(', ')"/>
@else
            <ShowRow :label="translations.columns.{{ $column->name }}" :value="data.{{ $column->name }}"/>
@endif
@endforeach
@foreach($relations->getBelongsToMany() as $belongsToMany)
            <ShowRow
                :label="translations.relations.{{ $belongsToMany->relationTranslationKey }}"
                :value="(data.{{ Str::snake($belongsToMany->relationMethodName) }} ?? []).map((item) => item.{{ $belongsToMany->relatedLabel }}).join(', ')"
            />
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
    if ($hasUserDetailTooltip) {
        $imports->push("UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue'");
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
