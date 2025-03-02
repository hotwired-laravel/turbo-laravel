<?php

namespace HotwiredLaravel\TurboLaravel\Testing;

use Closure;
use Illuminate\View\ComponentAttributeBag;
use PHPUnit\Framework\Assert;

class TurboStreamMatcher
{
    private array $wheres = [];

    private array $contents = [];

    public function __construct(private \DOMElement $turboStream) {}

    public function where(string $prop, string $value): self
    {
        $matcher = clone $this;

        // We're storing the props locally because we need to
        // wait for the `->matches()` call to check if this
        // Turbo Stream has all the attributes at once.

        $matcher->wheres[$prop] = $value;

        return $matcher;
    }

    public function see(string $content): self
    {
        $matcher = clone $this;

        // Similarly to how we do with the attributes, the contents
        // of the Turbo Stream tag the user wants to assert will
        // be store for latter, after the `->matches()` call.

        $matcher->contents[] = $content;

        return $matcher;
    }

    public function matches(?Closure $callback = null): bool
    {
        // We first pass the current instance to the callback given in the
        // `->assertTurboStream(fn)` call. This is where the `->where()`
        // and `->see()` methods will be called by the developers.

        if ($callback instanceof \Closure) {
            return $callback($this)->matches();
        }

        // After registering the desired attributes and contents the developers want
        // to assert against the Turbo Stream, we can check if this Turbo Stream
        // has the props first and then if the Turbo Stream's contents match.

        if (! $this->matchesProps()) {
            return false;
        }

        return $this->matchesContents();
    }

    public function attrs(): string
    {
        return $this->makeAttributes($this->wheres);
    }

    private function matchesProps(): bool
    {
        foreach ($this->wheres as $prop => $value) {
            $propValue = $this->turboStream->getAttribute($prop);

            if (! $propValue || $propValue !== $value) {
                return false;
            }
        }

        return true;
    }

    private function matchesContents(): bool
    {
        if ($this->contents === []) {
            return true;
        }

        foreach ($this->contents as $content) {
            Assert::assertStringContainsString($content, $this->renderElement());
        }

        return true;
    }

    private function renderElement(): string
    {
        $html = '';
        $children = $this->turboStream->childNodes;

        foreach ($children as $child) {
            $html .= $child->ownerDocument->saveXML($child);
        }

        return $html;
    }

    private function makeAttributes(array $attributes): string
    {
        return (new ComponentAttributeBag($attributes))->toHtml();
    }
}
