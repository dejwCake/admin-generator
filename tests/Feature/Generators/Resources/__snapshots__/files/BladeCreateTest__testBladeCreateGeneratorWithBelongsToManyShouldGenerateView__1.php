@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.category.actions.create'))

@section('body')

    <div class="container-xl">

        <category-form
            :action="'{{ $action }}'"
            :locales="{{ json_encode($locales) }}"
            :send-empty-locales="false"
            :wysiwyg-upload-url="'{{ $wysiwygUploadUrl }}'"
            :post-options="{{ $posts->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.category.actions.create'),
                'columns' => [
                    'user_id' => trans('admin.category.columns.user_id'),
                    'title' => trans('admin.category.columns.title'),
                    'slug' => trans('admin.category.columns.slug'),
                    'perex' => trans('admin.category.columns.perex'),
                    'published_at' => trans('admin.category.columns.published_at'),
                    'date_start' => trans('admin.category.columns.date_start'),
                    'time_start' => trans('admin.category.columns.time_start'),
                    'date_time_end' => trans('admin.category.columns.date_time_end'),
                    'text' => trans('admin.category.columns.text'),
                    'description' => trans('admin.category.columns.description'),
                    'enabled' => trans('admin.category.columns.enabled'),
                    'send' => trans('admin.category.columns.send'),
                    'price' => trans('admin.category.columns.price'),
                    'views' => trans('admin.category.columns.views'),
                ],
                'relations' => [
                    'posts' => trans('admin.category.relations.posts'),
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
        ></category-form>

    </div>

@endsection
