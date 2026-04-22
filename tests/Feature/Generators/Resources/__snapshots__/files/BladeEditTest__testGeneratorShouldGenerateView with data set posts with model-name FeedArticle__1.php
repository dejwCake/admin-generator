@extends('brackets/admin-ui::admin.layout.default')

@section('title', trans('admin.feed_article.actions.edit', ['name' => $article->title]))

@section('body')

    <div class="container-xl">

        <feed-article-form
            :action="'{{ $action }}'"
            :data="{{ $article->toJson() }}"
            :category-options="{{ $categories->toJson() }}"
            :translations="{{ json_encode([
                'form_title' => trans('admin.feed_article.actions.edit', ['name' => $article->title]),
                'columns' => [
                    'title' => trans('admin.feed_article.columns.title'),
                ],
                'relations' => [
                    'categories' => trans('admin.feed_article.relations.categories'),
                ],
                'select_options' => trans('brackets/admin-ui::admin.forms.select_options'),
                'save' => trans('brackets/admin-ui::admin.btn.save'),
            ]) }}"
            v-cloak
        ></feed-article-form>

    </div>

@endsection
