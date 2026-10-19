<template>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            {{ translations.show_title }}
        </div>
        <div class="card-body">
            <ShowRow :label="translations.columns.id" :value="data.id"/>
            <ShowRow :label="translations.columns.first_name" :value="data.first_name"/>
            <ShowRow :label="translations.columns.last_name" :value="data.last_name"/>
            <ShowRow :label="translations.columns.email" :value="data.email"/>
            <ShowRow :label="translations.columns.activated">
                <ReadOnlySwitch :modelValue="data.activated"/>
            </ShowRow>
            <ShowRow :label="translations.columns.forbidden">
                <ReadOnlySwitch :modelValue="data.forbidden"/>
            </ShowRow>
            <ShowRow :label="translations.columns.language" :value="data.language"/>
            <ShowRow :label="translations.columns.created_at" :value="formatDatetime(data.created_at)"/>
            <ShowRow :label="translations.columns.updated_at" :value="formatDatetime(data.updated_at)"/>
        </div>
        <div class="card-footer">
            <BackButton :url="backUrl" :translations="translations"/>
        </div>
    </div>
</template>

<script setup>
import BackButton from '@craftable/components/BackButton.vue';
import ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue';
import ShowRow from '@craftable/components/show/ShowRow.vue';
import {formatDatetime} from '@craftable/utils/dateFormatters.js';

defineProps({
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    backUrl: {type: String, required: true},
});
</script>
