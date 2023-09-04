<?php

namespace Workbench\App\Http\Controllers;

use Workbench\App\Http\Requests\UpdateCommentRequest;
use Workbench\App\Models\Comment;

class CommentsController
{
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return redirect()->route('articles.show', $comment->article);
    }
}
