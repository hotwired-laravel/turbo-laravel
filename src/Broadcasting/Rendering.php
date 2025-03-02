<?php

namespace HotwiredLaravel\TurboLaravel\Broadcasting;

use HotwiredLaravel\TurboLaravel\NamesResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class Rendering
{
    public function __construct(public ?string $partial = null, public ?array $data = [], public ?string $inlineContent = null, public bool $escapeInlineContent = true) {}

    public static function forContent(View|HtmlString|string $content): static
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
        return new self;
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
