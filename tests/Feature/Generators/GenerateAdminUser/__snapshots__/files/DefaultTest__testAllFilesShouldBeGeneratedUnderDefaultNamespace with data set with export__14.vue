<template>
    <form class="form-horizontal" method="post" @submit.prevent="onSubmit" :action="action" novalidate>
                <div class="card">
                    <div class="card-header">
                        <i class="fa" :class="data && Object.keys(data).length > 0 ? 'fa-pencil' : 'fa-plus'"></i>
                        {{ translations.form_title }}
                    </div>
                    <div class="card-body">
                        <FormInput
                            v-model="form.first_name"
                            name="first_name"
                            :label="translations.columns.first_name"
                            :error="errors.first_name"
                        />

                        <FormInput
                            v-model="form.last_name"
                            name="last_name"
                            :label="translations.columns.last_name"
                            :error="errors.last_name"
                        />

                        <FormEmail
                            v-model="form.email"
                            name="email"
                            :label="translations.columns.email"
                            :error="errors.email"
                        />

                        <FormPasswordConfirm
                            v-model:password="form.password"
                            v-model:passwordConfirmation="form.password_confirmation"
                            :passwordError="errors.password"
                            :confirmationError="errors.password_confirmation"
                            :translations="{
                                password: translations.columns.password,
                                password_repeat: translations.columns.password_repeat
                            }"
                        />

                        <FormCheckbox
                            v-model="form.activated"
                            name="activated"
                            :label="translations.columns.activated"
                            :error="errors.activated"
                        />

                        <FormCheckbox
                            v-model="form.forbidden"
                            name="forbidden"
                            :label="translations.columns.forbidden"
                            :error="errors.forbidden"
                        />

                        <FormSelect
                            v-model="form.language"
                            name="language"
                            :label="translations.columns.language"
                            :error="errors.language"
                            :options="languageOptions"
                            :placeholder="translations.select_an_option"
                        />

                        <FormMultiSelect
                            v-model="form.roles"
                            name="roles"
                            :label="translations.relations.roles"
                            :error="errors.roles"
                            :options="roleOptions"
                            trackBy="id" optionLabel="name"
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
import FormEmail from '@craftable/components/form/FormEmail.vue';
import FormCheckbox from '@craftable/components/form/FormCheckbox.vue';
import FormSelect from '@craftable/components/form/FormSelect.vue';
import FormMultiSelect from '@craftable/components/form/FormMultiSelect.vue';
import FormPasswordConfirm from '@craftable/components/form/FormPasswordConfirm.vue';
import FormSubmit from '@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: {type: String, required: true},
    data: {type: Object, default: () => ({})},
    activation: {type: Boolean, default: false},
    languageOptions: {type: Array, default: () => []},
    translations: {type: Object, default: () => ({})},
    roleOptions: {type: Array, default: () => []},
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
        email: 'required|email',
        language: 'required',
    },
});

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        activated: false,
        forbidden: false,
        language: '',
        roles: [],
    };
}
</script>
