<?php

namespace Tonysm\TurboLaravel\Broadcasting;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Tonysm\TurboLaravel\NamesResolver;

class Rendering
{
    public ?string $partial = null;
    public ?array $data = [];

    public ?string $inlineContent = null;
    public bool $escapeInlineContent = true;

    public function __construct(?string $partial = null, ?array $data = [], ?string $inlineContent = null, ?bool $escapeInlineContent = true)
    {
        $this->partial = $partial;
        $this->data = $data;
        $this->inlineContent = $inlineContent;
        $this->escapeInlineContent = $escapeInlineContent;
    }

    public static function forContent(View|HtmlString|string $content)
    {
        if ($content instanceof View) {
            return new static(partial: $content->name(), data: $content->getData());
        }

        if ($content instanceof HtmlString) {
            return new static(inlineContent: $content->toHtml(), escapeInlineContent: false);
        }

        return new static(inlineContent: $content, escapeInlineContent: true);
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function forModel(Model $model): self
    {
        return new self(
            NamesResolver::partialNameFor($model),
            [
                NamesResolver::resourceVariableName($model) => $model,
            ],
        );
    }
}
