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
        $this->wheres[$prop] = $value;

        return $this;
    }

    public function see(string $content): self
    {
        $this->contents[] = $content;

        return $this;
    }

    public function matches(Closure $callback = null): bool
    {
        if ($callback) {
            $callback($this);
        }

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