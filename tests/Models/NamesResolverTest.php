<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\NamesResolver;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Workbench\Database\Factories\ArticleFactory;

class NamesResolverTest extends TestCase
{
    /** @test */
    public function resolves_partial_naming()
    {
        $article = ArticleFactory::new()->make();

        $this->assertEquals('articles._article', NamesResolver::partialNameFor($article));
    }

    /** @test */
    public function resolves_partial_naming_using_subfolder()
    {
        $article = ArticleFactory::new()->make();

        Turbo::usePartialsSubfolderPattern();

        $this->assertEquals('articles.partials.article', NamesResolver::partialNameFor($article));
    }

    /** @test */
    public function resolves_using_custom_closure()
    {
        $article = ArticleFactory::new()->make();

        Turbo::resolvePartialsPathUsing(fn (Model $model) => 'partials.article');

        $this->assertEquals('partials.article', NamesResolver::partialNameFor($article));
    }
}
