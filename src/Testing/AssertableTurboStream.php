<?php

namespace Tonysm\TurboLaravel\Testing;

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
    }

    public function has(int $expectedTurboStreamsCount): self
    {
        Assert::assertCount($expectedTurboStreamsCount, $this->parsed());

        return $this;
    }

    public function parsed(): Collection
    {
        return $this->parsedCollection ??= collect(json_decode(json_encode(simplexml_load_string(<<<XML
<xml>{$this->response->content()}</xml>
XML)), true)['turbo-stream']);
    }
}
