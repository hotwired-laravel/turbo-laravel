<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Workbench\App\Models\Article;

class ArticlesController
{
    public function index(Request $request)
    {
        if ($request->wasFromTurboNative()) {
            return Article::query()
                ->latest()
                ->paginate();
        }

        return view('articles.index', [
            'articles' => Article::latest()->get(),
        ]);
    }

    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $article = Article::create($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]));

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream()->prepend('articles', view('articles._article_card', [
                    'article' => $article,
                ])),
                turbo_stream()->flash(__('Article created.')),
            ]);
        }

        return to_route('articles.show', $article)
            ->with('status', __('Article created.'));
    }

    public function show(Article $article)
    {
        return view('articles.show', [
            'article' => $article,
        ]);
    }

    public function edit(Article $article)
    {
        return view('articles.edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $article->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]));

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($article),
                turbo_stream()->flash(__('Article updated.')),
            ]);
        }

        return to_route('articles.show', $article)
            ->with('status', __('Article updated.'));
    }

    public function delete(Article $article)
    {
        return view('articles.delete', [
            'article' => $article,
        ]);
    }

    public function destroy(Request $request, Article $article)
    {
        if ($request->cookie('my-cookie')) {
            throw tap(ValidationException::withMessages(['article' => [__('Cannot delete article.')]]), fn ($exception) => (
                $exception->response = redirect()->to('/')->withCookie('response-cookie', 'response-cookie-value')
            ));
        }

        $article->delete();

        return to_route('articles.index')
            ->with('status', __('Article removed.'));
    }
}
