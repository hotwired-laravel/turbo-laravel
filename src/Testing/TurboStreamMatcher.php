<?php

namespace Tonysm\TurboLaravel\Testing;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Illuminate\View\ComponentAttributeBag;

class TurboStreamMatcher
{
    private $turboStream;
    private array $wheres = [];
    private array $contents = [];

    public function __construct($turboStream)
    {
        $this->turboStream = $turboStream;
    }

    public function where(string $prop, string $value): self
    {
        // We're storing the props locally because we need to
        // wait for the `->matches()` call to check if this
        // Turbo Stream has all the attributes at once.

        $this->wheres[$prop] = $value;

        return $this;
    }

    public function see(string $content): self
    {
        // Similarly to how we do with the attributes, the contents
        // of the Turbo Stream tag the user wants to assert will
        // be store for latter, after the `->matches()` call.

        $this->contents[] = $content;

        return $this;
    }

    public function matches(Closure $callback = null): bool
    {
        // We first pass the current instance to the callback given in the
        // `->assertTurboStream(fn)` call. This is where the `->where()`
        // and `->see()` methods will be called by the developers.

        if ($callback) {
            $callback($this);
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

    private function matchesProps()
    {
        foreach ($this->wheres as $prop => $value) {
            $actualProp = $this->turboStream['@attributes'][$prop] ?? false;

            if ($actualProp === false || $actualProp !== $value) {
                return false;
            }
        }

        return true;
    }

    private function matchesContents()
    {
        if (empty($this->contents)) {
            return true;
        }

        // To assert that the Turbo Stream contains the desired text, we first need to
        // rebuild the markup from the response. This is because we had to parse the
        // HTML before getting here so we could assert each Turbo Stream separately.

        $content = new TestResponse(new Response($this->makeElements($this->turboStream['template'] ?? [])));

        foreach ($this->contents as $expectedContent) {
            $content->assertSee($expectedContent);
        }

        return true;
    }

    private function makeAttributes(array $attributes): string
    {
        return (new ComponentAttributeBag($attributes))->toHtml();
    }

    private function makeElements($tags)
    {
        if (is_string($tags)) {
            return $tags;
        }

        $content = '';

        foreach ($tags as $tag => $contents) {
            $attrs = $this->makeAttributes($contents['@attributes'] ?? []);

            $strContent = $this->makeElements(is_array($contents) ? Arr::except($contents, '@attributes') : $contents);
            $opening = trim(sprintf('%s %s', $tag, $attrs));

            if ($this->isSelfClosingTag($tag)) {
                $content .= "<{$opening} />";
            } else {
                $content .= "<{$opening}>{$strContent}</{$tag}>";
            }
        }

        return $content;
    }

    private function isSelfClosingTag(string $tag): bool
    {
        return in_array($tag, [
            'input',
            'img',
            'br',
            'source',
        ]);
    }
}
