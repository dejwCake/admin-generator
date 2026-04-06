@php
    use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
    use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
    use Illuminate\Support\Str;
    assert($relations instanceof RelationCollection);
    assert($leftFormColumns instanceof ColumnCollection);
    assert($publishedColumns instanceof ColumnCollection);
@endphp
<template>
    <form class="form-horizontal" method="post" @@submit.prevent="onSubmit" :action="action" novalidate>
@if($isUsedTwoColumnsLayout)
        <div class="row">
            <div class="col">
@endif
                <div class="card">
                    <div class="card-header">
                        <i class="fa" :class="data && Object.keys(data).length > 0 ? 'fa-pencil' : 'fa-plus'"></i>
                        {{ '{{' }} translations.form_title }}
                    </div>
                    <div class="card-body">
@if($hasTranslatable)
                        <LocalizationBar
                            :translations="translations"
                            :locales="locales"
                            :default-locale="defaultLocale"
                            :other-locales="otherLocales"
                            :is-form-localized="isFormLocalized"
                            :current-locale="currentLocale"
                            :on-small-screen="onSmallScreen"
                            :show-localized-validation-error="showLocalizedValidationError"
                            @@show-localization="showLocalization"
                            @@hide-localization="hideLocalization"
                            @@update:current-locale="currentLocale = $event"
                        />

@endif
@foreach($leftFormColumns as $column)
@if($column->name === 'password')
                        <FormPasswordConfirm
                            v-model:password="form.{{ $column->name }}"
                            v-model:passwordConfirmation="form.{{ $column->name }}_confirmation"
                            :passwordError="errors.{{ $column->name }}"
                            :confirmationError="errors.{{ $column->name }}_confirmation"
                            :translations="{
                                {{ $column->name }}: translations.columns.{{ $column->name }},
                                {{ $column->name }}_repeat: translations.columns.{{ $column->name }}_repeat
                            }"
                        />

@elseif($column->name === 'email')
                        <FormEmail
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@elseif($column->name === 'language')
                        <FormSelect
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :options="languageOptions"
                            :placeholder="translations.select_an_option"
                        />

@elseif($column->majorType === 'json' && in_array($column->name, $wysiwygTextColumnNames, true))
                        <FormLocalizedWysiwyg
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :errors="errors"
                            :locales="locales"
                            :shouldShowLangGroup="shouldShowLangGroup"
                            :isFormLocalized="isFormLocalized"
                            :upload-url="wysiwygUploadUrl"
                        />

@elseif($column->majorType === 'json')
                        <FormLocalizedInput
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :errors="errors"
                            :locales="locales"
                            :shouldShowLangGroup="shouldShowLangGroup"
                            :isFormLocalized="isFormLocalized"
                        />

@elseif($column->majorType === 'text' && in_array($column->name, $wysiwygTextColumnNames, true))
                        <FormWysiwyg
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :upload-url="wysiwygUploadUrl"
                        />

@elseif($column->majorType === 'text')
                        <FormTextarea
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@elseif($column->majorType === 'bool')
                        <FormCheckbox
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@elseif($column->majorType === 'bool' && $column->name === 'activated')
                        <FormCheckbox
                            v-if="activation"
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@elseif($column->majorType === 'date')
                        <FormDatePicker
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :config="datePickerConfig"
                            :placeholder="translations.select_a_date"
                        />

@elseif($column->majorType === 'time')
                        <FormDatePicker
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :config="timePickerConfig"
                            icon="fa-clock"
                            :placeholder="translations.select_a_time"
                        />

@elseif($column->majorType === 'datetime')
                        <FormDatePicker
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :config="datetimePickerConfig"
                            :placeholder="translations.select_date_and_time"
                        />

@elseif($column->isForeignKey && $relations->hasBelongsToByColumn($column->name))
@php $belongsToRelation = $relations->getBelongsToByColumn($column->name); @endphp
                        <FormSelect
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :options="{{ $belongsToRelation->optionsPropName }}"
                            trackBy="id"
                            optionLabel="{{ $belongsToRelation->relatedLabel }}"
                            :placeholder="translations.select_an_option"
                        />

@else
                        <FormInput
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                        />

@endif
@endforeach
@foreach($relations->getBelongsToMany() as $belongsToMany)
                        <FormMultiSelect
                            v-model="form.{{ $belongsToMany->relatedTable }}"
                            name="{{ $belongsToMany->relatedTable }}"
                            :label="translations.relations.{{ Str::lcfirst(Str::plural($belongsToMany->relatedModelName)) }}"
                            :error="errors.{{ $belongsToMany->relatedTable }}"
                            :options="{{ $belongsToMany->optionsPropName }}"
                            trackBy="id" optionLabel="{{ $belongsToMany->relatedLabel }}"
                            :placeholder="translations.select_options"
                        />

@endforeach
@foreach($leftMediaCollections as $collection)
                        <MediaUpload
                            :ref="media.{{ $collection->collectionName }}.collection + '_uploader'"
                            :label="media.{{ $collection->collectionName }}.label"
                            :collection="media.{{ $collection->collectionName }}.collection"
                            :url="media.{{ $collection->collectionName }}.url"
                            :max-number-of-files="media.{{ $collection->collectionName }}.maxNumberOfFiles"
                            :max-file-size-in-mb="media.{{ $collection->collectionName }}.maxFileSizeInMb"
                            :accepted-file-types="media.{{ $collection->collectionName }}.acceptedFileTypes"
                            :uploaded-media="media.{{ $collection->collectionName }}.uploadedMedia"
                            :is-private="media.{{ $collection->collectionName }}.isPrivate ?? false"
                        />

@endforeach
@if(!$isUsedTwoColumnsLayout)
                    </div>

                    <div class="card-footer">
                        <FormSubmit :submitting="submitting" :label="translations.save" />
                    </div>
@else
                        <FormSubmit :submitting="submitting" :label="translations.save" />
                    </div>
@endif
                </div>
@if($isUsedTwoColumnsLayout)
            </div>

            <div class="col-md-12 col-lg-12 col-xl-5 col-xxl-4">
@if($publishedColumns->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-check"></i> {{ '{{' }} translations.publish }}
                    </div>
                    <div class="card-body">
@foreach($publishedColumns as $column)
                        <FormDatePicker
                            v-model="form.{{ $column->name }}"
                            name="{{ $column->name }}"
                            :label="translations.columns.{{ $column->name }}"
                            :error="errors.{{ $column->name }}"
                            :config="datetimePickerConfig"
                            :placeholder="translations.select_date_and_time"
                        />
@endforeach
                    </div>
                </div>
@endif
@if($galleryCollections->isNotEmpty())
                <div class="card mt-2">
                    <div class="card-header">
                        <i class="fa fa-check"></i> {{ '{{' }} translations.gallery }}
                    </div>
                    <div class="card-body">
@foreach($galleryCollections as $collection)
                        <MediaUpload
                            :ref="media.{{ $collection->collectionName }}.collection + '_uploader'"
                            :label="media.{{ $collection->collectionName }}.label"
                            :collection="media.{{ $collection->collectionName }}.collection"
                            :url="media.{{ $collection->collectionName }}.url"
                            :max-number-of-files="media.{{ $collection->collectionName }}.maxNumberOfFiles"
                            :max-file-size-in-mb="media.{{ $collection->collectionName }}.maxFileSizeInMb"
                            :accepted-file-types="media.{{ $collection->collectionName }}.acceptedFileTypes"
                            :uploaded-media="media.{{ $collection->collectionName }}.uploadedMedia"
                            :is-private="media.{{ $collection->collectionName }}.isPrivate ?? false"
                        />
@endforeach
                    </div>
                </div>
@endif
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)

                <div class="card mt-2" v-if="showHistory">
                    <div class="card-header">
                        <i class="fa fa-history"></i> {{ '{{' }} translations.history }}
                    </div>
                    <div class="card-body">
@if($hasCreatedByAdminUser)
                        <div class="mb-3 row align-items-center" v-if="form.created_by_admin_user">
                            <UserDetailTooltip
                                :user="form.created_by_admin_user"
                                :datetime="formatDatetime(form.created_at)"
                                :label="translations.created_by"
                                :datetime-text="translations.created_on + ' ' + formatDatetime(form.created_at)"
                            />
                        </div>
@endif
@if($hasUpdatedByAdminUser)

                        <div class="mb-3 row align-items-center" v-if="form.updated_by_admin_user">
                            <UserDetailTooltip
                                :user="form.updated_by_admin_user"
                                :datetime="formatDatetime(form.updated_at)"
                                :label="translations.updated_by"
                                :datetime-text="translations.updated_on + ' ' + formatDatetime(form.updated_at)"
                            />
                        </div>
@endif
                    </div>
                </div>
@endif
            </div>
        </div>
@endif
    </form>
</template>

<script setup>
import {useAppForm} from '../composables/useAppForm.js';
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)
import UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue';
import {formatDatetime} from '@craftable/utils/dateFormatters.js';
@endif
@if($hasTranslatable)
import LocalizationBar from '@craftable/components/form/LocalizationBar.vue';
@endif
@if($hasFormInput)
import FormInput from '@craftable/components/form/FormInput.vue';
@endif
@if($hasEmail)
import FormEmail from '@craftable/components/form/FormEmail.vue';
@endif
@if($hasTextarea)
import FormTextarea from '@craftable/components/form/FormTextarea.vue';
@endif
@if($hasBoolColumns)
import FormCheckbox from '@craftable/components/form/FormCheckbox.vue';
@endif
import FormSelect from '@craftable/components/form/FormSelect.vue';
@if($hasDateColumns || $hasTimeColumns || $hasDatetimeColumns || $publishedColumns->isNotEmpty())
import FormDatePicker from '@craftable/components/form/FormDatePicker.vue';
@endif
@if($hasWysiwyg)
import FormWysiwyg from '@craftable/components/form/FormWysiwyg.vue';
@endif
@if($relations->hasBelongsToMany())
import FormMultiSelect from '@craftable/components/form/FormMultiSelect.vue';
@endif
@if($hasLocalizedInput)
import FormLocalizedInput from '@craftable/components/form/FormLocalizedInput.vue';
@endif
@if($hasLocalizedWysiwyg)
import FormLocalizedWysiwyg from '@craftable/components/form/FormLocalizedWysiwyg.vue';
@endif
@if($hasPassword)
import FormPasswordConfirm from '@craftable/components/form/FormPasswordConfirm.vue';
@endif
import FormSubmit from '@craftable/components/form/FormSubmit.vue';
@if($mediaCollections->isNotEmpty())
import MediaUpload from '@craftable/components/form/MediaUpload.vue';
@endif

const props = defineProps({
    action: {type: String, required: true},
    data: {type: Object, default: () => ({})},
    activation: {type: Boolean, default: false},
    languageOptions: {type: Array, default: () => []},
    translations: {type: Object, default: () => ({})},
@foreach($relations->getBelongsToMany() as $belongsToMany)
    {{ $belongsToMany->optionsPropName }}: {type: Array, default: () => []},
@endforeach
@foreach($relations->getBelongsTo() as $belongsTo)
@if(!$relations->hasRelatedTableInBelongsToMany($belongsTo->relatedTable))
    {{ $belongsTo->optionsPropName }}: {type: Array, default: () => []},
@endif
@endforeach
@if($mediaCollections->isNotEmpty())
    media: {type: Object, default: () => ({!! $mediaDefaultProp !!})},
@endif
@if($hasTranslatable)
    locales: {type: Array, default: () => []},
    defaultLocale: {type: String, default: ''},
    sendEmptyLocales: {type: Boolean, default: true},
@endif
@if($hasCreatedByAdminUser || $hasUpdatedByAdminUser)
    showHistory: {type: Boolean, default: false},
@endif
@if($hasWysiwyg)
    wysiwygUploadUrl: {type: String, default: '/admin/wysiwyg-media'},
@endif
    responsiveBreakpoint: {type: Number, default: 850},
});

const {
    form, wysiwygMedia, mediaCollections, isFormLocalized, currentLocale,
    submitting, onSmallScreen, errors, datePickerConfig, timePickerConfig,
    datetimePickerConfig, locales, defaultLocale, otherLocales,
    showLocalizedValidationError, getPostData, onSubmit, onSuccess, onFail,
    getLocalizedFormDefaults, showLocalization, hideLocalization,
    shouldShowLangGroup,
@php $hasUseAppFormOptions = !empty($validationRules) || $relations->hasBelongsTo(); @endphp
} = useAppForm(props{!! $hasUseAppFormOptions ? ', {' : '' !!}
@if(!empty($validationRules))
    validationSchema: {
@foreach($validationRules as $field => $rule)
        {{ $field }}: {!! $rule !!},
@endforeach
    },
@endif
@if($relations->hasBelongsTo())
    transformData: (data) => {
@foreach($relations->getBelongsTo() as $belongsTo)
        if (data.{{ $belongsTo->foreignKeyColumn }} && typeof data.{{ $belongsTo->foreignKeyColumn }} === 'object') {
            data.{{ $belongsTo->foreignKeyColumn }} = data.{{ $belongsTo->foreignKeyColumn }}.id;
        }
@endforeach
        return data;
    },
@endif
{!! $hasUseAppFormOptions ? '}' : '' !!});

if (!props.data || Object.keys(props.data).length === 0) {
    form.value = {
@foreach($leftFormColumns as $column)
@if($column->name === 'password')
        password: '',
        password_confirmation: '',
@elseif($column->majorType === 'json')
        {{ $column->name }}: getLocalizedFormDefaults(),
@elseif($column->majorType === 'bool')
        {{ $column->name }}: false,
@else
        {{ $column->name }}: '',
@endif
@endforeach
@foreach($publishedColumns as $column)
        {{ $column->name }}: '',
@endforeach
@if($hasCreatedByAdminUser)
        created_by_admin_user_id: '',
        updated_by_admin_user_id: '',
@endif
@foreach($relations->getBelongsToMany() as $belongsToMany)
        {{ $belongsToMany->relatedTable }}: [],
@endforeach
    };
@if($relations->hasBelongsTo())
} else {
@foreach($relations->getBelongsTo() as $belongsTo)
    if (form.value.{{ $belongsTo->foreignKeyColumn }}) {
        const match = props.{{ $belongsTo->optionsPropName }}.find(p => p.id === form.value.{{ $belongsTo->foreignKeyColumn }});
        if (match) form.value.{{ $belongsTo->foreignKeyColumn }} = match;
    }
@endforeach
@endif
}
@if($mediaCollections->isNotEmpty())

mediaCollections.value = [{!! $mediaCollectionNames !!}];
@endif
</script>
