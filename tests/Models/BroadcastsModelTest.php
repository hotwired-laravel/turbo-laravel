<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Models;

use HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast;
use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Workbench\App\Models\Board;
use Workbench\App\Models\Comment;
use Workbench\App\Models\Company;
use Workbench\App\Models\ReviewStatus;
use Workbench\App\Models\Task;
use Workbench\Database\Factories\ArticleFactory;
use Workbench\Database\Factories\BoardFactory;
use Workbench\Database\Factories\CommentFactory;
use Workbench\Database\Factories\CompanyFactory;
use Workbench\Database\Factories\ContactFactory;
use Workbench\Database\Factories\TaskFactory;

class BroadcastsModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['turbo-laravel.queue' => false]);

        TurboStream::fake();
    }

    /** @test */
    public function manually_broadcast_append()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastAppend();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals('private-articles', $broadcast->channels[0]->name);
            $this->assertEquals('articles', $broadcast->target);
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('articles._article', $broadcast->partialView);
            $this->assertEquals(['article' => $article], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_append_with_overrides()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastAppend()
            ->to($channel = new Channel('hello'))
            ->target('some_other_target')
            ->partial('another_partial', ['lorem' => 'ipsum']);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('some_other_target', $broadcast->target);
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('another_partial', $broadcast->partialView);
            $this->assertEquals(['lorem' => 'ipsum'], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_before_with_overrides()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastBefore('articles_card')
            ->to($channel = new Channel('hello'))
            ->partial('articles._article_card', [
                'article' => $article,
                'lorem' => 'ipsum',
            ]);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article, $channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('articles_card', $broadcast->target);
            $this->assertEquals('before', $broadcast->action);
            $this->assertEquals('articles._article_card', $broadcast->partialView);
            $this->assertEquals(['article' => $article, 'lorem' => 'ipsum'], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_after_with_overrides()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastAfter('article_cards')
            ->to($channel = new Channel('hello'))
            ->partial('articles._article_card', [
                'article' => $article,
                'lorem' => 'ipsum',
            ]);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article, $channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('article_cards', $broadcast->target);
            $this->assertEquals('after', $broadcast->action);
            $this->assertEquals('articles._article_card', $broadcast->partialView);
            $this->assertEquals(['article' => $article, 'lorem' => 'ipsum'], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_before_to_with_overrides()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastBeforeTo($channel = new Channel('hello'), 'example_dom_id_target')
            ->partial('articles._article_card', [
                'article' => $article,
                'lorem' => 'ipsum',
            ]);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article, $channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('example_dom_id_target', $broadcast->target);
            $this->assertEquals('before', $broadcast->action);
            $this->assertEquals('articles._article_card', $broadcast->partialView);
            $this->assertEquals(['article' => $article, 'lorem' => 'ipsum'], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_after_to_with_overrides()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastAfterTo($channel = new Channel('hello'), 'example_dom_id_target')
            ->partial('articles._article_card', [
                'article' => $article,
                'lorem' => 'ipsum',
            ]);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article, $channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('example_dom_id_target', $broadcast->target);
            $this->assertEquals('after', $broadcast->action);
            $this->assertEquals('articles._article_card', $broadcast->partialView);
            $this->assertEquals(['article' => $article, 'lorem' => 'ipsum'], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_replace()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastReplace();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals(sprintf('private-%s', $article->broadcastChannel()), $broadcast->channels[0]->name);
            $this->assertEquals("article_{$article->id}", $broadcast->target);
            $this->assertEquals('replace', $broadcast->action);
            $this->assertEquals('articles._article', $broadcast->partialView);
            $this->assertEquals(['article' => $article], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_remove()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastRemove();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals(sprintf('private-%s', $article->broadcastChannel()), $broadcast->channels[0]->name);
            $this->assertEquals("article_{$article->id}", $broadcast->target);
            $this->assertEquals('remove', $broadcast->action);
            $this->assertNull($broadcast->partialView);
            $this->assertEquals([], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function can_auto_broadcast()
    {
        $comment = CommentFactory::new()->create();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($comment) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals('private-'.$comment->article->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('comments', $broadcast->target);
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('comments._comment', $broadcast->partialView);
            $this->assertEquals(['comment' => $comment], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function can_auto_broadcast_with_custom_overrides()
    {
        $company = CompanyFactory::new()->create();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($company) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals('private-custom-channel', $broadcast->channels[0]->name);
            $this->assertEquals('companies', $broadcast->target);
            $this->assertEquals('prepend', $broadcast->action);
            $this->assertEquals('companies._company', $broadcast->partialView);
            $this->assertEquals(['company' => $company], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function can_configure_auto_broadcast_to_parent_model_using_a_method()
    {
        $company = Company::withoutEvents(fn () => CompanyFactory::new()->create());

        $contact = ContactFactory::new()->create([
            'company_id' => $company,
        ]);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($company, $contact) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals(sprintf('private-%s', $company->broadcastChannel()), $broadcast->channels[0]->name);
            $this->assertEquals('contacts', $broadcast->target);
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('contacts._contact', $broadcast->partialView);
            $this->assertEquals(['contact' => $contact], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_append_targets()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastAppend()
            ->targets('.test_targets');

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals('private-articles', $broadcast->channels[0]->name);
            $this->assertNull($broadcast->target);
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('articles._article', $broadcast->partialView);
            $this->assertEquals(['article' => $article], $broadcast->partialData);
            $this->assertEquals('.test_targets', $broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function broadcasts_on_model_touching()
    {
        $oldUpdatedAt = now()->subDays(10);

        $comment = Comment::withoutEvents(fn () => CommentFactory::new()->create([
            'updated_at' => $oldUpdatedAt,
        ]));

        $this->assertTrue($comment->fresh()->updated_at->isSameDay($oldUpdatedAt));

        TurboStream::assertNothingWasBroadcasted();

        $comment->fresh()->review()->create([
            'status' => ReviewStatus::Approved,
        ]);

        // Must have updated the parent timestamps...
        $this->assertFalse($comment->fresh()->updated_at->isSameDay($oldUpdatedAt));

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($comment) {
            $this->assertCount(1, $broadcast->channels);
            // The comment model is configured to broadacst to the article's channel...
            $this->assertEquals('private-'.$comment->article->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals(dom_id($comment), $broadcast->target);
            $this->assertNull($broadcast->targets);
            $this->assertEquals('replace', $broadcast->action);
            $this->assertEquals('comments._comment', $broadcast->partialView);
            $this->assertCount(1, $broadcast->partialData);
            $this->assertArrayHasKey('comment', $broadcast->partialData);
            $this->assertTrue($comment->is($broadcast->partialData['comment']));

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_refresh()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastRefresh();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals('private-articles', $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_refresh_with_overrides()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastRefresh()->to($channel = new Channel('hello'));

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_refresh_with_current_request_id()
    {
        Turbo::setTurboTrackingRequestId('123');

        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastRefresh()->to($channel = new Channel('hello'));

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEquals(['request-id' => '123'], $broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function manually_broadcast_refresh_to()
    {
        $article = ArticleFactory::new()->create();

        TurboStream::assertNothingWasBroadcasted();

        $article->broadcastRefreshTo($channel = new Channel('hello'));

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($channel) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame($channel, $broadcast->channels[0]);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_on_create()
    {
        TurboStream::assertNothingWasBroadcasted();

        BoardFactory::new()->create();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-boards', $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_on_create_with_current_request_id()
    {
        Turbo::setTurboTrackingRequestId('123');

        TurboStream::assertNothingWasBroadcasted();

        BoardFactory::new()->create();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-boards', $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEquals(['request-id' => '123'], $broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_on_update()
    {
        TurboStream::assertNothingWasBroadcasted();

        $board = Board::withoutEvents(fn () => BoardFactory::new()->create()->fresh());

        TurboStream::assertNothingWasBroadcasted();

        $board->update(['name' => 'Updated']);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_on_delete()
    {
        TurboStream::assertNothingWasBroadcasted();

        /** @var Board $board */
        $board = Board::withoutEvents(fn () => BoardFactory::new()->create()->fresh());

        TurboStream::assertNothingWasBroadcasted();

        $board->delete();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_to_on_create()
    {
        $board = Board::withoutEvents(fn () => BoardFactory::new()->create()->fresh());

        TurboStream::assertNothingWasBroadcasted();

        TaskFactory::new()->for($board)->create();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_to_on_update()
    {
        [$board, $task] = Model::withoutEvents(fn () => [
            $board = BoardFactory::new()->create()->fresh(),
            TaskFactory::new()->for($board)->create()->fresh(),
        ]);

        TurboStream::assertNothingWasBroadcasted();

        $task->update(['title' => 'Updated']);

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_to_on_delete()
    {
        [$board, $task] = Model::withoutEvents(fn () => [
            $board = BoardFactory::new()->create()->fresh(),
            TaskFactory::new()->for($board)->create()->fresh(),
        ]);

        TurboStream::assertNothingWasBroadcasted();

        $task->delete();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function auto_broadcast_refreshes_to_on_create_debouncing()
    {
        $this->freezeTime();

        $board = Board::withoutEvents(fn () => BoardFactory::new()->create())->fresh();

        TurboStream::assertNothingWasBroadcasted();

        TaskFactory::new()
            ->times(2)
            ->for($board)
            ->create();

        TurboStream::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        }, times: 1);

        $this->travel(501)->milliseconds();

        TurboStream::clearRecordedBroadcasts();

        TaskFactory::new()
            ->times(2)
            ->for($board)
            ->create();

        TurboStream::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        }, times: 1);
    }

    /** @test */
    public function can_disable_manual_broadcasts()
    {
        /** @var Board $board */
        $board = Board::withoutEvents(fn () => BoardFactory::new()->create());

        TurboStream::assertNothingWasBroadcasted();

        Board::withoutTurboStreamBroadcasts(fn () => (
            $board->broadcastAppend()
        ));

        TurboStream::assertNothingWasBroadcasted();

        $board->broadcastAppend();

        TurboStream::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals('private-boards', $broadcast->channels[0]->name);
            $this->assertEquals('boards', $broadcast->target);
            $this->assertEquals('append', $broadcast->action);
            $this->assertEquals('boards._board', $broadcast->partialView);
            $this->assertEquals(['board' => $board], $broadcast->partialData);
            $this->assertNull($broadcast->targets);

            return true;
        }, times: 1);
    }

    /** @test */
    public function can_disable_auto_broadcasts()
    {
        $board = Board::withoutEvents(fn () => BoardFactory::new()->create()->fresh());

        TurboStream::assertNothingWasBroadcasted();

        Task::withoutTurboStreamBroadcasts(fn () => (
            TaskFactory::new()
                ->for($board)
                ->create()
        ));

        TurboStream::assertNothingWasBroadcasted();

        TaskFactory::new()
            ->for($board)
            ->create();

        TurboStream::assertBroadcastedTimes(function (PendingBroadcast $broadcast) use ($board) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertSame('private-'.$board->broadcastChannel(), $broadcast->channels[0]->name);
            $this->assertEquals('refresh', $broadcast->action);
            $this->assertNull($broadcast->target);
            $this->assertNull($broadcast->partialView);
            $this->assertEmpty($broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEmpty($broadcast->attributes);

            return true;
        }, times: 1);
    }

    /** @test */
    public function broadcasts_with_extra_attributes_to_turbo_stream()
    {
        TurboStream::fake();

        /** @var Board $board */
        $board = Board::withoutTurboStreamBroadcasts(fn () => Board::create(['name' => 'Hello World']));

        $board->broadcastActionTo(
            $channel = 'messages',
            $action = 'test',
            attributes: $attributes = ['data-foo' => 'bar'],
        );

        TurboStream::assertBroadcasted(function (PendingBroadcast $turboStream) use ($channel, $action, $attributes) {
            $this->assertEquals("private-{$channel}", $turboStream->channels[0]->name);
            $this->assertEquals($action, $turboStream->action);
            $this->assertEquals($attributes, $turboStream->attributes);

            return true;
        });
    }

    /** @test */
    public function broadcasts_with_extra_attributes_to_turbo_stream_with_rendering()
    {
        TurboStream::fake();

        /** @var Board $board */
        $board = Board::withoutTurboStreamBroadcasts(fn () => Board::create(['name' => 'Hello World']));

        $board->broadcastActionTo(
            $channel = 'messages',
            $action = 'test',
            attributes: $attributes = ['data-foo' => 'bar'],
        )->view('boards.partials.board', [
            'board' => $board,
        ]);

        TurboStream::assertBroadcasted(function (PendingBroadcast $turboStream) use ($channel, $action, $attributes) {
            $this->assertEquals("private-{$channel}", $turboStream->channels[0]->name);
            $this->assertEquals($action, $turboStream->action);
            $this->assertEquals($attributes, $turboStream->attributes);
            $this->assertStringContainsString('Hello World', $turboStream->render());

            return true;
        });
    }

    /** @test */
    public function broadcasts_replace_morph()
    {
        $article = ArticleFactory::new()->create();

        $article->broadcastReplace()->morph();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals(sprintf('private-%s', $article->broadcastChannel()), $broadcast->channels[0]->name);
            $this->assertEquals("article_{$article->id}", $broadcast->target);
            $this->assertEquals('replace', $broadcast->action);
            $this->assertEquals('articles._article', $broadcast->partialView);
            $this->assertEquals(['article' => $article], $broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEquals(['method' => 'morph'], $broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function broadcasts_update_morph()
    {
        $article = ArticleFactory::new()->create();

        $article->broadcastUpdate()->morph();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals(sprintf('private-%s', $article->broadcastChannel()), $broadcast->channels[0]->name);
            $this->assertEquals("article_{$article->id}", $broadcast->target);
            $this->assertEquals('update', $broadcast->action);
            $this->assertEquals('articles._article', $broadcast->partialView);
            $this->assertEquals(['article' => $article], $broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEquals(['method' => 'morph'], $broadcast->attributes);

            return true;
        });
    }

    /** @test */
    public function unsets_method_when_overriding_with_null()
    {
        $article = ArticleFactory::new()->create();

        $article->broadcastUpdate()->morph()->method();

        TurboStream::assertBroadcasted(function (PendingBroadcast $broadcast) use ($article) {
            $this->assertCount(1, $broadcast->channels);
            $this->assertEquals(sprintf('private-%s', $article->broadcastChannel()), $broadcast->channels[0]->name);
            $this->assertEquals("article_{$article->id}", $broadcast->target);
            $this->assertEquals('update', $broadcast->action);
            $this->assertEquals('articles._article', $broadcast->partialView);
            $this->assertEquals(['article' => $article], $broadcast->partialData);
            $this->assertNull($broadcast->targets);
            $this->assertEquals([], $broadcast->attributes);

            return true;
        });
    }
}
