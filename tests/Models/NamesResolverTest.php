<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\NamesResolver;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Workbench\Database\Factories\ArticleFactory;

class NamesResolverTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function resolves_partial_naming(): void
    {
        $article = ArticleFactory::new()->make();

        $this->assertEquals('articles._article', NamesResolver::partialNameFor($article));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolves_partial_naming_using_subfolder(): void
    {
        $article = ArticleFactory::new()->make();

        Turbo::usePartialsSubfolderPattern();

        $this->assertEquals('articles.partials.article', NamesResolver::partialNameFor($article));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolves_using_custom_closure(): void
    {
        $article = ArticleFactory::new()->make();

        Turbo::resolvePartialsPathUsing(fn (Model $model): string => 'partials.article');

        $this->assertEquals('partials.article', NamesResolver::partialNameFor($article));
    }
}
