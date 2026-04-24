@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.billing_cat.actions.edit', ['name' => $cat->title]))

@section('body')

    <div class="container-xl">

        <billing-cat-form
            :action="'{{ $action }}'"
            :data="{{ $cat->toJsonAllLocales() }}"
            :locales="{{ json_encode($locales) }}"
            :send-empty-locales="false"
            :wysiwyg-upload-url="'{{ $wysiwygUploadUrl }}'"
            :show-history="true"
            :post-options="{{ $posts->toJson() }}"
            :user-options="{{ $users->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.billing_cat.actions.edit', ['name' => $cat->title]),
                'columns' => [
                    'user_id' => trans('admin.billing_cat.columns.user_id'),
                    'title' => trans('admin.billing_cat.columns.title'),
                    'name' => trans('admin.billing_cat.columns.name'),
                    'first_name' => trans('admin.billing_cat.columns.first_name'),
                    'last_name' => trans('admin.billing_cat.columns.last_name'),
                    'subject' => trans('admin.billing_cat.columns.subject'),
                    'email' => trans('admin.billing_cat.columns.email'),
                    'password' => trans('admin.billing_cat.columns.password'),
                    'password_repeat' => trans('admin.billing_cat.columns.password_repeat'),
                    'language' => trans('admin.billing_cat.columns.language'),
                    'slug' => trans('admin.billing_cat.columns.slug'),
                    'perex' => trans('admin.billing_cat.columns.perex'),
                    'long_text' => trans('admin.billing_cat.columns.long_text'),
                    'published_at' => trans('admin.billing_cat.columns.published_at'),
                    'published_to' => trans('admin.billing_cat.columns.published_to'),
                    'date_start' => trans('admin.billing_cat.columns.date_start'),
                    'time_start' => trans('admin.billing_cat.columns.time_start'),
                    'date_time_end' => trans('admin.billing_cat.columns.date_time_end'),
                    'released_at' => trans('admin.billing_cat.columns.released_at'),
                    'text' => trans('admin.billing_cat.columns.text'),
                    'description' => trans('admin.billing_cat.columns.description'),
                    'enabled' => trans('admin.billing_cat.columns.enabled'),
                    'send' => trans('admin.billing_cat.columns.send'),
                    'price' => trans('admin.billing_cat.columns.price'),
                    'rating' => trans('admin.billing_cat.columns.rating'),
                    'views' => trans('admin.billing_cat.columns.views'),
                ],
                'relations' => [
                    'posts' => trans('admin.billing_cat.relations.posts'),
                ],
                'publish' => trans('brackets/admin-ui::admin.forms.publish'),
                'history' => trans('brackets/admin-ui::admin.forms.history'),
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
                'created_by' => trans('brackets/admin-ui::admin.forms.created_by'),
                'created_on' => trans('brackets/admin-ui::admin.forms.created_on'),
                'updated_by' => trans('brackets/admin-ui::admin.forms.updated_by'),
                'updated_on' => trans('brackets/admin-ui::admin.forms.updated_on'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></billing-cat-form>

    </div>

@endsection
