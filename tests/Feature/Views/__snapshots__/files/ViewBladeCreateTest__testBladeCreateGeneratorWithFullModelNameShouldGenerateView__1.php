@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.categ-ory.actions.create'))

@section('body')

    <div class="container-xl">

        <categ-ory-form
            :action="'{{ $action }}'"
            :locales="{{ json_encode($locales) }}"
            :send-empty-locales="false"
            :wysiwyg-upload-url="'{{ $wysiwygUploadUrl }}'"
            :post-options="{{ $posts->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.categ-ory.actions.create'),
                'columns' => [
                    'user_id' => trans('admin.categ-ory.columns.user_id'),
                    'title' => trans('admin.categ-ory.columns.title'),
                    'slug' => trans('admin.categ-ory.columns.slug'),
                    'perex' => trans('admin.categ-ory.columns.perex'),
                    'published_at' => trans('admin.categ-ory.columns.published_at'),
                    'date_start' => trans('admin.categ-ory.columns.date_start'),
                    'time_start' => trans('admin.categ-ory.columns.time_start'),
                    'date_time_end' => trans('admin.categ-ory.columns.date_time_end'),
                    'text' => trans('admin.categ-ory.columns.text'),
                    'description' => trans('admin.categ-ory.columns.description'),
                    'enabled' => trans('admin.categ-ory.columns.enabled'),
                    'send' => trans('admin.categ-ory.columns.send'),
                    'price' => trans('admin.categ-ory.columns.price'),
                    'views' => trans('admin.categ-ory.columns.views'),
                ],
                'relations' => [
                    'posts' => trans('admin.categ-ory.columns.posts'),
                ],
                'publish' => trans('brackets/admin-ui::admin.forms.publish'),
                'currently_editing_translation' => trans('brackets/admin-ui::admin.forms.currently_editing_translation'),
                'more_can_be_managed' => trans('brackets/admin-ui::admin.forms.more_can_be_managed'),
                'manage_translations' => trans('brackets/admin-ui::admin.forms.manage_translations'),
                'choose_translation_to_edit' => trans('brackets/admin-ui::admin.forms.choose_translation_to_edit'),
                'hide' => trans('brackets/admin-ui::admin.forms.hide'),
                'select_a_date' => trans('brackets/admin-ui::admin.forms.select_a_date'),
                'select_a_time' => trans('brackets/admin-ui::admin.forms.select_a_time'),
                'select_date_and_time' => trans('brackets/admin-ui::admin.forms.select_date_and_time'),
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></categ-ory-form>

    </div>

@endsection
