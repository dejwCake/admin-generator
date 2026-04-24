<template>
    <form class="form-horizontal" method="post" @submit.prevent="onSubmit" :action="action" novalidate>
                <div class="card">
                    <div class="card-header">
                        <i class="fa" :class="data && Object.keys(data).length > 0 ? 'fa-pencil' : 'fa-plus'"></i>
                        {{ translations.form_title }}
                    </div>
                    <div class="card-body">
                        <FormInput
                            v-model="form.title"
                            name="title"
                            :label="translations.columns.title"
                            :error="errors.title"
                        />

                        <FormMultiSelect
                            v-model="form.categories"
                            name="categories"
                            :label="translations.relations.categories"
                            :error="errors.categories"
                            :options="categoryOptions"
                            trackBy="id"
                            optionLabel="title"
                            :placeholder="translations.select_options"
                        />

                    </div>

                    <div class="card-footer">
                        <FormSubmit :submitting="submitting" :label="translations.save" />
                    </div>
                </div>
    </form>
</template>

<script setup>
import {useAppForm} from '../composables/useAppForm.js';
import FormInput from '@craftable/components/form/FormInput.vue';
import FormMultiSelect from '@craftable/components/form/FormMultiSelect.vue';
import FormSubmit from '@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: {type: String, required: true},
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    categoryOptions: {type: Array, default: () => []},
    responsiveBreakpoint: {type: Number, default: 850},
});

const {
    form, wysiwygMedia, mediaCollections, isFormLocalized, currentLocale,
    submitting, onSmallScreen, errors, datePickerConfig, timePickerConfig,
    datetimePickerConfig, locales, defaultLocale, otherLocales,
    showLocalizedValidationError, getPostData, onSubmit, onSuccess, onFail,
    getLocalizedFormDefaults, showLocalization, hideLocalization,
    shouldShowLangGroup,
} = useAppForm(props, {
    validationSchema: {
        title: 'required',
    },
});

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
        title: '',
        categories: [],
    };
}
</script>
