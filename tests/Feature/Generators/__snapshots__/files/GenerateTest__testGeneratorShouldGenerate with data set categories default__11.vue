<template>
    <form class="form-horizontal" method="post" @submit.prevent="onSubmit" :action="action" novalidate>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <i class="fa" :class="data && Object.keys(data).length > 0 ? 'fa-pencil' : 'fa-plus'"></i>
                        {{ translations.form_title }}
                    </div>
                    <div class="card-body">
                        <LocalizationBar
                            :translations="translations"
                            :locales="locales"
                            :default-locale="defaultLocale"
                            :other-locales="otherLocales"
                            :is-form-localized="isFormLocalized"
                            :current-locale="currentLocale"
                            :on-small-screen="onSmallScreen"
                            :show-localized-validation-error="showLocalizedValidationError"
                            @show-localization="showLocalization"
                            @hide-localization="hideLocalization"
                            @update:current-locale="currentLocale = $event"
                        />

                        <FormSelect
                            v-model="form.user_id"
                            name="user_id"
                            :label="translations.columns.user_id"
                            :error="errors.user_id"
                            :options="userOptions"
                            trackBy="id"
                            optionLabel="name"
                            :placeholder="translations.select_an_option"
                        />

                        <FormInput
                            v-model="form.title"
                            name="title"
                            :label="translations.columns.title"
                            :error="errors.title"
                        />

                        <FormInput
                            v-model="form.name"
                            name="name"
                            :label="translations.columns.name"
                            :error="errors.name"
                        />

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

                        <FormInput
                            v-model="form.subject"
                            name="subject"
                            :label="translations.columns.subject"
                            :error="errors.subject"
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

                        <FormInput
                            v-model="form.language"
                            name="language"
                            :label="translations.columns.language"
                            :error="errors.language"
                        />

                        <FormInput
                            v-model="form.slug"
                            name="slug"
                            :label="translations.columns.slug"
                            :error="errors.slug"
                        />

                        <FormWysiwyg
                            v-model="form.perex"
                            name="perex"
                            :label="translations.columns.perex"
                            :error="errors.perex"
                            :upload-url="wysiwygUploadUrl"
                        />

                        <FormLocalizedInput
                            v-model="form.long_text"
                            name="long_text"
                            :label="translations.columns.long_text"
                            :errors="errors"
                            :locales="locales"
                            :shouldShowLangGroup="shouldShowLangGroup"
                            :isFormLocalized="isFormLocalized"
                        />

                        <FormDatePicker
                            v-model="form.published_to"
                            name="published_to"
                            :label="translations.columns.published_to"
                            :error="errors.published_to"
                            :config="datePickerConfig"
                            :placeholder="translations.select_a_date"
                        />

                        <FormDatePicker
                            v-model="form.date_start"
                            name="date_start"
                            :label="translations.columns.date_start"
                            :error="errors.date_start"
                            :config="datePickerConfig"
                            :placeholder="translations.select_a_date"
                        />

                        <FormDatePicker
                            v-model="form.time_start"
                            name="time_start"
                            :label="translations.columns.time_start"
                            :error="errors.time_start"
                            :config="timePickerConfig"
                            icon="fa-clock"
                            :placeholder="translations.select_a_time"
                        />

                        <FormDatePicker
                            v-model="form.date_time_end"
                            name="date_time_end"
                            :label="translations.columns.date_time_end"
                            :error="errors.date_time_end"
                            :config="datetimePickerConfig"
                            :placeholder="translations.select_date_and_time"
                        />

                        <FormDatePicker
                            v-model="form.released_at"
                            name="released_at"
                            :label="translations.columns.released_at"
                            :error="errors.released_at"
                            :config="datetimePickerConfig"
                            :placeholder="translations.select_date_and_time"
                        />

                        <FormLocalizedWysiwyg
                            v-model="form.text"
                            name="text"
                            :label="translations.columns.text"
                            :errors="errors"
                            :locales="locales"
                            :shouldShowLangGroup="shouldShowLangGroup"
                            :isFormLocalized="isFormLocalized"
                            :upload-url="wysiwygUploadUrl"
                        />

                        <FormLocalizedWysiwyg
                            v-model="form.description"
                            name="description"
                            :label="translations.columns.description"
                            :errors="errors"
                            :locales="locales"
                            :shouldShowLangGroup="shouldShowLangGroup"
                            :isFormLocalized="isFormLocalized"
                            :upload-url="wysiwygUploadUrl"
                        />

                        <FormCheckbox
                            v-model="form.enabled"
                            name="enabled"
                            :label="translations.columns.enabled"
                            :error="errors.enabled"
                        />

                        <FormCheckbox
                            v-model="form.send"
                            name="send"
                            :label="translations.columns.send"
                            :error="errors.send"
                        />

                        <FormInput
                            v-model="form.price"
                            name="price"
                            :label="translations.columns.price"
                            :error="errors.price"
                        />

                        <FormInput
                            v-model="form.rating"
                            name="rating"
                            :label="translations.columns.rating"
                            :error="errors.rating"
                        />

                        <FormInput
                            v-model="form.views"
                            name="views"
                            :label="translations.columns.views"
                            :error="errors.views"
                        />

                        <FormMultiSelect
                            v-model="form.posts"
                            name="posts"
                            :label="translations.relations.posts"
                            :error="errors.posts"
                            :options="postOptions"
                            trackBy="id"
                            optionLabel="title"
                            :placeholder="translations.select_options"
                        />

                        <FormSubmit :submitting="submitting" :label="translations.save" />
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-lg-12 col-xl-5 col-xxl-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-check"></i> {{ translations.publish }}
                    </div>
                    <div class="card-body">
                        <FormDatePicker
                            v-model="form.published_at"
                            name="published_at"
                            :label="translations.columns.published_at"
                            :error="errors.published_at"
                            :config="datetimePickerConfig"
                            :placeholder="translations.select_date_and_time"
                        />
                    </div>
                </div>

                <div class="card mt-2" v-if="showHistory">
                    <div class="card-header">
                        <i class="fa fa-history"></i> {{ translations.history }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3 row align-items-center" v-if="form.created_by_admin_user">
                            <UserDetailTooltip
                                :user="form.created_by_admin_user"
                                :datetime="formatDatetime(form.created_at)"
                                :label="translations.created_by"
                                :datetime-text="translations.created_on + ' ' + formatDatetime(form.created_at)"
                            />
                        </div>

                        <div class="mb-3 row align-items-center" v-if="form.updated_by_admin_user">
                            <UserDetailTooltip
                                :user="form.updated_by_admin_user"
                                :datetime="formatDatetime(form.updated_at)"
                                :label="translations.updated_by"
                                :datetime-text="translations.updated_on + ' ' + formatDatetime(form.updated_at)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script setup>
import {useAppForm} from '../composables/useAppForm.js';
import UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue';
import {formatDatetime} from '@craftable/utils/dateFormatters.js';
import LocalizationBar from '@craftable/components/form/LocalizationBar.vue';
import FormInput from '@craftable/components/form/FormInput.vue';
import FormEmail from '@craftable/components/form/FormEmail.vue';
import FormCheckbox from '@craftable/components/form/FormCheckbox.vue';
import FormDatePicker from '@craftable/components/form/FormDatePicker.vue';
import FormWysiwyg from '@craftable/components/form/FormWysiwyg.vue';
import FormSelect from '@craftable/components/form/FormSelect.vue';
import FormMultiSelect from '@craftable/components/form/FormMultiSelect.vue';
import FormLocalizedInput from '@craftable/components/form/FormLocalizedInput.vue';
import FormLocalizedWysiwyg from '@craftable/components/form/FormLocalizedWysiwyg.vue';
import FormPasswordConfirm from '@craftable/components/form/FormPasswordConfirm.vue';
import FormSubmit from '@craftable/components/form/FormSubmit.vue';

const props = defineProps({
    action: {type: String, required: true},
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    postOptions: {type: Array, default: () => []},
    userOptions: {type: Array, default: () => []},
    locales: {type: Array, default: () => []},
    defaultLocale: {type: String, default: ''},
    sendEmptyLocales: {type: Boolean, default: true},
    showHistory: {type: Boolean, default: false},
    wysiwygUploadUrl: {type: String, default: '/admin/wysiwyg-media'},
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
        email: 'email',
        language: 'required',
        slug: 'required',
        released_at: 'required',
        text: 'required',
        description: 'required',
        views: 'required|integer',
    },
    transformData: (data) => {
        if (data.user_id && typeof data.user_id === 'object') {
            data.user_id = data.user_id.id;
        }
        return data;
    },
});

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
        user_id: '',
        title: '',
        name: '',
        first_name: '',
        last_name: '',
        subject: '',
        email: '',
        password: '',
        password_confirmation: '',
        language: '',
        slug: '',
        perex: '',
        long_text: getLocalizedFormDefaults(),
        published_to: '',
        date_start: '',
        time_start: '',
        date_time_end: '',
        released_at: '',
        text: getLocalizedFormDefaults(),
        description: getLocalizedFormDefaults(),
        enabled: false,
        send: false,
        price: '',
        rating: '',
        views: '',
        published_at: '',
        created_by_admin_user_id: '',
        updated_by_admin_user_id: '',
        posts: [],
    };
} else {
    if (form.value.user_id) {
        const match = props.userOptions.find(p => p.id === form.value.user_id);
        if (match) form.value.user_id = match;
    }
}
</script>
