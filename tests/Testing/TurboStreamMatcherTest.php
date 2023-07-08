<?php

namespace HotwiredLaravel\TurboLaravel\Tests\Testing;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\ExpectationFailedException;
use HotwiredLaravel\TurboLaravel\Testing\ConvertTestResponseToTurboStreamCollection;
use HotwiredLaravel\TurboLaravel\Testing\TurboStreamMatcher;
use HotwiredLaravel\TurboLaravel\Tests\TestCase;

class TurboStreamMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->response = new TestResponse(response(<<<HTML
        <turbo-stream action="append" target="item_1">
            <template>
                <h1>First Item</h1>
            </template>
        </turbo-stream>

        <turbo-stream action="append" target="item_2">
            <template>
                <h1>Second Item</h1>
            </template>
        </turbo-stream>

        <turbo-stream action="remove" target="item_3">
        </turbo-stream>

        <turbo-stream action="replace" targets=".items">
            <template>
                <h1>new Item</h1>
            </template>
        </turbo-stream>
        HTML));

        $this->streams = (new ConvertTestResponseToTurboStreamCollection)($this->response)->mapInto(TurboStreamMatcher::class);
    }

    /** @test */
    public function converts_streams_to_collections()
    {
        $this->assertCount(4, $this->streams);
    }

    /** @test */
    public function filters_by_attributes()
    {
        // Matches on action...
        $appends = $this->streams->filter(fn (TurboStreamMatcher $matcher) => (
            $matcher->where('action', 'append')->matches()
        ));

        $this->assertCount(2, $appends);

        // Matches on target...
        $appends = $appends->filter(fn (TurboStreamMatcher $matcher) => (
            $matcher->where('target', 'item_2')->matches()
        ));

        $this->assertCount(1, $appends);

        // Matches both on action and target...
        $remove_item_3 = $this->streams->filter(fn (TurboStreamMatcher $matcher) => (
            $matcher->where('action', 'remove')
                ->where('target', 'item_3')
                ->matches()
        ));

        $this->assertCount(1, $remove_item_3);

        // Matches on targets attribute...
        $targets = $this->streams->filter(fn (TurboStreamMatcher $matcher) => (
            $matcher->where('targets', '.items')->matches()
        ));

        $this->assertCount(1, $targets);
    }

    /** @test */
    public function can_see_text()
    {
        $firstItem = $this->streams->filter(fn (TurboStreamMatcher $matcher) => (
            $matcher->where('action', 'append')
                ->where('target', 'item_1')
                ->see('First Item')
                ->matches()
        ));

        $this->assertCount(1, $firstItem);
    }

    /** @test */
    public function fails_when_string_doesnt_match()
    {
        try {
            $this->streams->filter(fn (TurboStreamMatcher $matcher) => (
                $matcher->where('action', 'append')
                    ->where('target', 'item_1')
                    ->see('Second Item')
                    ->matches()
            ));

            $this->fail('Should have failed to match the text, but did not.');
        } catch (ExpectationFailedException $_e) {
            return;
        }
    }
}
