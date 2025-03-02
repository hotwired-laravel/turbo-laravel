<?php

namespace HotwiredLaravel\TurboLaravel\Http;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MultiplePendingTurboStreamResponse implements \Stringable, Htmlable, Renderable, Responsable
{
    /** @var Collection|PendingTurboStreamResponse[] */
    private readonly Collection $pendingStreams;

    /**
     * @param  Collection  $pendingStreams
     */
    public function __construct($pendingStreams)
    {
        $this->pendingStreams = collect($pendingStreams);
    }

    /**
     * @param  array|Collection  $pendingStreams
     */
    public static function forStreams($pendingStreams): self
    {
        return new self($pendingStreams);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return TurboResponseFactory::makeStream($this->render());
    }

    public function render(): string
    {
        return $this->pendingStreams
            ->map(fn (PendingTurboStreamResponse $pendingStream): string => $pendingStream->render())
            ->implode(PHP_EOL);
    }

    public function toHtml()
    {
        return new HtmlString($this->render());
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
