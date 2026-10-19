<template>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            {{ translations.show_title }}
        </div>
        <div class="card-body">
            <ShowRow :label="translations.columns.id" :value="data.id"/>
            <ShowRow :label="translations.columns.title" :value="data.title"/>
            <ShowRow
                :label="translations.relations.categories"
                :value="(data.categories ?? []).map((item) => item.title).join(', ')"
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

defineProps({
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    backUrl: {type: String, required: true},
});
</script>
