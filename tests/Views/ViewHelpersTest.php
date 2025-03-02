<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Views;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Testing\InteractsWithTurbo;
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
    use InteractsWithTurbo;

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_hotwire_native_correctly(): void
    {
        $article = ArticleFactory::new()->create();

        $this->get(route('articles.show', $article))
            ->assertDontSee('Visiting From Hotwire Native');

        $this->turboNative()
            ->get(route('articles.show', $article))
            ->assertSee('Visiting From Hotwire Native');

        $this->hotwireNative()
            ->get(route('articles.show', $article))
            ->assertSee('Visiting From Hotwire Native');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_blade_native_helpers(): void
    {
        $this->assertEquals('Not Native', trim(Blade::render('@turbonative Yes Native @else Not Native @endturbonative')));
        $this->assertEquals('Not Native', trim(Blade::render('@hotwirenative Yes Native @else Not Native @endhotwirenative')));
        $this->assertEquals('Not Native', trim(Blade::render('@unlessturbonative Not Native @else Yes Native @endunlessturbonative')));
        $this->assertEquals('Not Native', trim(Blade::render('@unlesshotwirenative Not Native @else Yes Native @endunlesshotwirenative')));

        Turbo::setVisitingFromHotwireNative();

        $this->assertEquals('Yes Native', trim(Blade::render('@turbonative Yes Native @else Not Native @endturbonative')));
        $this->assertEquals('Yes Native', trim(Blade::render('@hotwirenative Yes Native @else Not Native @endhotwirenative')));
        $this->assertEquals('Yes Native', trim(Blade::render('@unlessturbonative Not Native @else Yes Native @endunlessturbonative')));
        $this->assertEquals('Yes Native', trim(Blade::render('@unlesshotwirenative Not Native @else Yes Native @endunlesshotwirenative')));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_unless_hotwire_native(): void
    {
        $article = ArticleFactory::new()->create();

        $this->get(route('articles.show', $article))
            ->assertSee('Index');

        $this->turboNative()
            ->get(route('articles.show', $article))
            ->assertDontSee('Back');

        $this->hotwireNative()
            ->get(route('articles.show', $article))
            ->assertDontSee('Back');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_dom_id(): void
    {
        $article = ArticleFactory::new()->create();

        $renderedDomId = Blade::render('<div id="@domid($article)"></div>', ['article' => $article]);
        $renderedDomIdWithPrefix = Blade::render('<div id="@domid($article, "favorites")"></div>', ['article' => $article]);
        $rendersDomIdOfNewModel = Blade::render('<div id="@domid($article)"></div>', ['article' => new Article]);

        $this->assertEquals('<div id="article_'.$article->id.'"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_article_'.$article->id.'"></div>', trim($renderedDomIdWithPrefix));
        $this->assertEquals('<div id="create_article"></div>', trim($rendersDomIdOfNewModel));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function dom_id_with_regular_classes(): void
    {
        $renderedDomId = Blade::render('<div id="@domid($status)"></div>', ['status' => ReviewStatus::Approved]);
        $renderedDomIdWithPrefix = Blade::render('<div id="@domid($status, "favorites")"></div>', ['status' => ReviewStatus::Approved]);

        $this->assertEquals('<div id="review_status_approved"></div>', trim($renderedDomId));
        $this->assertEquals('<div id="favorites_review_status_approved"></div>', trim($renderedDomIdWithPrefix));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_dom_class(): void
    {
        $article = ArticleFactory::new()->create();

        $renderedDomClass = Blade::render('<div class="@domclass($article)"></div>', ['article' => $article]);
        $renderedDomClassWithPrefix = Blade::render('<div class="@domclass($article, "favorites")"></div>', ['article' => $article]);
        $rendersDomClassOfNewModel = Blade::render('<div class="@domclass($article)"></div>', ['article' => new Article]);

        $this->assertEquals('<div class="article"></div>', trim($renderedDomClass));
        $this->assertEquals('<div class="favorites_article"></div>', trim($renderedDomClassWithPrefix));
        $this->assertEquals('<div class="article"></div>', trim($rendersDomClassOfNewModel));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_streamable_dom_class(): void
    {
        $renderedDomClass = Blade::render('<div class="@domclass($status)"></div>', ['status' => ReviewStatus::Approved]);
        $renderedDomClassWithPrefix = Blade::render('<div class="@domclass($status, "favorites")"></div>', ['status' => ReviewStatus::Approved]);

        $this->assertEquals('<div class="review_status"></div>', trim($renderedDomClass));
        $this->assertEquals('<div class="favorites_review_status"></div>', trim($renderedDomClassWithPrefix));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_use_helper_function(): void
    {
        $article = ArticleFactory::new()->create();

        $this->assertEquals('article_'.$article->id, dom_id($article));
        $this->assertEquals('favorites_article_'.$article->id, dom_id($article, 'favorites'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generates_model_ids_for_models_in_nested_folders(): void
    {
        $profile = ProfileFactory::new()->create();

        $this->assertEquals('user_profile_'.$profile->id, dom_id($profile));
        $this->assertEquals('posts_user_profile_'.$profile->id, dom_id($profile, 'posts'));
        $this->assertEquals('create_user_profile', dom_id(new Profile));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generates_channel_for_model(): void
    {
        $article = ArticleFactory::new()->create();

        $renderedChannelName = Blade::render('<x-turbo::stream-from :source="$article" />', ['article' => $article]);

        $this->assertStringContainsString(
            sprintf('channel="Workbench.App.Models.Article.%s"', $article->getKey()),
            $renderedChannelName
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function configure_refresh_strategy(): void
    {
        $this->get(route('trays.index'))
            ->assertSee('<meta name="turbo-refresh-method" content="morph">', false)
            ->assertSee('<meta name="turbo-refresh-scroll" content="preserve">', false);
    }
}
