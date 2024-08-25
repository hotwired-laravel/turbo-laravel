<?php

namespace HotwiredLaravel\TurboLaravel\Testing;

use DOMDocument;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;

class ConvertTestResponseToTurboStreamCollection
{
    public function __invoke(TestResponse $response): Collection
    {
        libxml_use_internal_errors(true);
        $document = tap(new DOMDocument)->loadHTML($response->content());
        $elements = $document->getElementsByTagName('turbo-stream');

        $streams = collect();

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $streams->push($element);
        }

        return $streams;
    }
}
