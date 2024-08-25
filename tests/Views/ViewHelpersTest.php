<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Workbench\App\Models\Article;
use Workbench\App\Models\ReviewStatus;
use Workbench\App\Models\User\Profile;
use Workbench\Database\Factories\ArticleFactory;
use Workbench\Database\Factories\ProfileFactory;

use function HotwiredLaravel\TurboLaravel\dom_id;

class ViewHelpersTest extends TestCase
{
    /** @test */
    public function renders_turbo_native_correctly()
    {
        $article = ArticleFactory::new()->create();

        $this->assertFalse(Turbo::isTurboNativeVisit());

        $this->get(route('articles.show', $article))
            ->assertDontSee('Visiting From Turbo Native');

        Turbo::setVisitingFromTurboNative();
        $this->assertTrue(Turbo::isTurboNativeVisit());

        $this->get(route('articles.show', $article))
            ->assertSee('Visiting From Turbo Native');
    }

    /** @test */
    public function renders_unless_turbo_native()
    {
        $article = ArticleFactory::new()->create();

        $this->assertFalse(Turbo::isTurboNativeVisit());

        $this->get(route('articles.show', $article))
            ->assertSee('Index');

        Turbo::setVisitingFromTurboNative();
        $this->assertTrue(Turbo::isTurboNativeVisit());

        $this->get(route('articles.show', $article))
            ->assertDontSee('Back');
    }

    /** @test */
    public function renders_dom_id()
    {
        $article = ArticleFactory::new()->create();

        $renderedDomId = Blade::render('<div id="@domid($article)"></div>', ['article' => $article]);
        $renderedDomIdWithPrefix = Blade::render('<div id="@domid($article, "favorites")"></div>', ['article' => $article]);
        $rendersDomIdOfNewModel = Blade::render('<div id="@domid($article)"></div>', ['article' => new Article]);

        $this->assertEquals('<div id="article_'.$article->id.'"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_article_'.$article->id.'"></div>', trim($renderedDomIdWithPrefix));
        $this->assertEquals('<div id="create_article"></div>', trim($rendersDomIdOfNewModel));
    }

    /** @test */
    public function dom_id_with_regular_classes()
    {
        $renderedDomId = Blade::render('<div id="@domid($status)"></div>', ['status' => ReviewStatus::Approved]);
        $renderedDomIdWithPrefix = Blade::render('<div id="@domid($status, "favorites")"></div>', ['status' => ReviewStatus::Approved]);

        $this->assertEquals('<div id="review_status_approved"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_review_status_approved"></div>', trim($renderedDomIdWithPrefix));
    }

    /** @test */
    public function renders_dom_class()
    {
        $article = ArticleFactory::new()->create();

        $renderedDomClass = Blade::render('<div class="@domclass($article)"></div>', ['article' => $article]);
        $renderedDomClassWithPrefix = Blade::render('<div class="@domclass($article, "favorites")"></div>', ['article' => $article]);
        $rendersDomClassOfNewModel = Blade::render('<div class="@domclass($article)"></div>', ['article' => new Article]);

        $this->assertEquals('<div class="article"></div>', trim($renderedDomClass));
        $this->assertEquals('<div class="favorites_article"></div>', trim($renderedDomClassWithPrefix));
        $this->assertEquals('<div class="article"></div>', trim($rendersDomClassOfNewModel));
    }

    /** @test */
    public function renders_streamable_dom_class()
    {
        $renderedDomClass = Blade::render('<div class="@domclass($status)"></div>', ['status' => ReviewStatus::Approved]);
        $renderedDomClassWithPrefix = Blade::render('<div class="@domclass($status, "favorites")"></div>', ['status' => ReviewStatus::Approved]);

        $this->assertEquals('<div class="review_status"></div>', trim($renderedDomClass));
        $this->assertEquals('<div class="favorites_review_status"></div>', trim($renderedDomClassWithPrefix));
    }

    /** @test */
    public function can_use_helper_function()
    {
        $article = ArticleFactory::new()->create();

        $this->assertEquals('article_'.$article->id, dom_id($article));
        $this->assertEquals('favorites_article_'.$article->id, dom_id($article, 'favorites'));
    }

    /** @test */
    public function generates_model_ids_for_models_in_nested_folders()
    {
        $profile = ProfileFactory::new()->create();

        $this->assertEquals('user_profile_'.$profile->id, dom_id($profile));
        $this->assertEquals('posts_user_profile_'.$profile->id, dom_id($profile, 'posts'));
        $this->assertEquals('create_user_profile', dom_id(new Profile));
    }

    /** @test */
    public function generates_channel_for_model()
    {
        $article = ArticleFactory::new()->create();

        $renderedChannelName = Blade::render('<x-turbo::stream-from :source="$article" />', ['article' => $article]);

        $this->assertStringContainsString(
            sprintf('channel="Workbench.App.Models.Article.%s"', $article->getKey()),
            $renderedChannelName
        );
    }

    /** @test */
    public function configure_refresh_strategy()
    {
        $this->get(route('trays.index'))
            ->assertSee('<meta name="turbo-refresh-method" content="morph">', false)
            ->assertSee('<meta name="turbo-refresh-scroll" content="preserve">', false);
    }
}
