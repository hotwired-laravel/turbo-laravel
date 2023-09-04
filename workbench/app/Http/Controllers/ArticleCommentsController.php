<?php

namespace Workbench\App\Http\Controllers;

use Workbench\App\Http\Requests\CreateCommentRequest;
use Workbench\App\Models\Article;

class ArticleCommentsController
{
    public function create(Article $article)
    {
        return view('article_comments.create', [
            'article' => $article,
        ]);
    }

    public function store(CreateCommentRequest $request, Article $article)
    {
        $article->comments()->create($request->validated());

        return to_route('articles.show', $article);
    }
}
