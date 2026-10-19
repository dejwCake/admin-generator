<template>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            {{ translations.show_title }}
        </div>
        <div class="card-body">
            <ShowRow :label="translations.columns.id" :value="data.id"/>
            <ShowRow :label="translations.columns.name" :value="data.name"/>
            <ShowRow :label="translations.columns.email" :value="data.email"/>
            <ShowRow :label="translations.columns.email_verified_at" :value="formatDatetime(data.email_verified_at)"/>
            <ShowRow :label="translations.columns.created_at" :value="formatDatetime(data.created_at)"/>
            <ShowRow :label="translations.columns.updated_at" :value="formatDatetime(data.updated_at)"/>
            <ShowRow
                :label="translations.relations.roles"
                :value="(data.roles ?? []).map((item) => item.name).join(', ')"
            />
        </div>
        <div class="card-footer">
            <BackButton :url="backUrl" :translations="translations"/>
        </div>
    </div>
</template>

<script setup>
import BackButton from '@craftable/components/BackButton.vue';
import ShowRow from '@craftable/components/show/ShowRow.vue';
import {formatDatetime} from '@craftable/utils/dateFormatters.js';

defineProps({
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    backUrl: {type: String, required: true},
});
</script>
