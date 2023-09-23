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
        $comment = $article->comments()->create($request->validated());

        if ($request->wantsTurboStream()) {
            return turbo_stream_view('article_comments.turbo.created', [
                'comment' => $comment,
                'status' => __('Comment created.'),
            ]);
        }

        return to_route('articles.show', $article);
    }
}
