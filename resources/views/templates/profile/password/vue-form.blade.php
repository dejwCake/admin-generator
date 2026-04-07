<template>
    <form class="form-horizontal" method="post" @@submit.prevent="onSubmit" :action="action" novalidate>
        <div class="card">
            <div class="card-header">
                <i class="fa fa-pencil"></i> {{ '{{' }} translations.form_title }}
            </div>

            <div class="card-body">
                <FormPasswordConfirm v-model:password="form.password" v-model:passwordConfirmation="form.password_confirmation"
                    :passwordError="errors.password" :confirmationError="errors.password_confirmation"
                    :translations="translations.columns" />
            </div>

            <div class="card-footer">
                <FormSubmit :submitting="submitting" :label="translations.save" />
            </div>
        </div>
    </form>
</template>

<script setup>
import { useAppForm } from '../composables/useAppForm.js';
import FormPasswordConfirm from '@@craftable/components/form/FormPasswordConfirm.vue';
import FormSubmit from '@@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: { type: String, required: true },
    data: { type: Object, default: () => ({}) },
    translations: { type: Object, default: () => ({}) },
    locales: { type: Array, default: () => [] },
    defaultLocale: { type: String, default: '' },
    sendEmptyLocales: { type: Boolean, default: true },
    responsiveBreakpoint: { type: Number, default: 850 },
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
        password: 'required|min:7',
        password_confirmation: 'required|confirmed:@@password',
    },
});
</script>
