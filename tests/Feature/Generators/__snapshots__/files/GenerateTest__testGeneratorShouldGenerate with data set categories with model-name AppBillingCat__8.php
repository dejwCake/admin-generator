@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.cat.actions.create'))

@section('body')

    <div class="container-xl">

        <cat-form
            :action="'{{ $action }}'"
            :locales="{{ json_encode($locales) }}"
            :send-empty-locales="false"
            :wysiwyg-upload-url="'{{ $wysiwygUploadUrl }}'"
            :post-options="{{ $posts->toJson() }}"
            :user-options="{{ $users->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.cat.actions.create'),
                'columns' => [
                    'user_id' => trans('admin.cat.columns.user_id'),
                    'title' => trans('admin.cat.columns.title'),
                    'name' => trans('admin.cat.columns.name'),
                    'first_name' => trans('admin.cat.columns.first_name'),
                    'last_name' => trans('admin.cat.columns.last_name'),
                    'subject' => trans('admin.cat.columns.subject'),
                    'email' => trans('admin.cat.columns.email'),
                    'password' => trans('admin.cat.columns.password'),
                    'password_repeat' => trans('admin.cat.columns.password_repeat'),
                    'language' => trans('admin.cat.columns.language'),
                    'slug' => trans('admin.cat.columns.slug'),
                    'perex' => trans('admin.cat.columns.perex'),
                    'long_text' => trans('admin.cat.columns.long_text'),
                    'published_at' => trans('admin.cat.columns.published_at'),
                    'published_to' => trans('admin.cat.columns.published_to'),
                    'date_start' => trans('admin.cat.columns.date_start'),
                    'time_start' => trans('admin.cat.columns.time_start'),
                    'date_time_end' => trans('admin.cat.columns.date_time_end'),
                    'released_at' => trans('admin.cat.columns.released_at'),
                    'text' => trans('admin.cat.columns.text'),
                    'description' => trans('admin.cat.columns.description'),
                    'enabled' => trans('admin.cat.columns.enabled'),
                    'send' => trans('admin.cat.columns.send'),
                    'price' => trans('admin.cat.columns.price'),
                    'rating' => trans('admin.cat.columns.rating'),
                    'views' => trans('admin.cat.columns.views'),
                ],
                'relations' => [
                    'posts' => trans('admin.cat.relations.posts'),
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
                'select_an_option' => trans('brackets/admin-ui::admin.forms.select_an_option'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></cat-form>

    </div>

@endsection
