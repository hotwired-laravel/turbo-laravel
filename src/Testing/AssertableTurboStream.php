<?php

namespace Tonysm\TurboLaravel\Testing;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;

class AssertableTurboStream
{
    public TestResponse $response;
    public Collection $parsedCollection;

    public function __construct(TestResponse $response)
    {
        $this->response = $response;
        $this->parsedCollection = (new ConvertTestResponseToTurboStreamCollection)($response);
    }

    public function has(int $expectedTurboStreamsCount): self
    {
        Assert::assertCount($expectedTurboStreamsCount, $this->parsedCollection);

        return $this;
    }

    public function hasTurboStream(Closure $callback = null): self
    {
        $attrs = collect();

        $matches = $this->parsedCollection
            ->mapInto(TurboStreamMatcher::class)
            ->filter(function ($matcher) use ($callback, $attrs) {
                if (! $matcher->matches($callback)) {
                    $attrs->add($matcher->attrs());

                    return false;
                }

                return true;
            });

        Assert::assertTrue(
            $matches->count() === 1,
            sprintf(
                'Expected to find a matching Turbo Stream for `%s`, but %s',
                $attrs->unique()->join(' '),
                trans_choice('{0} none was found.|[2,*] :count were found.', $matches->count()),
            )
        );

        return $this;
    }
}
