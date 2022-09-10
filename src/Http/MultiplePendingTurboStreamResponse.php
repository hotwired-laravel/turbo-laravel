<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MultiplePendingTurboStreamResponse implements Responsable, Htmlable, Renderable
{
    /** @var Collection|PendingTurboStreamResponse[] */
    private Collection $pendingStreams;

    /**
     * @param Collection $pendingStreams
     */
    public function __construct($pendingStreams)
    {
        $this->pendingStreams = collect($pendingStreams);
    }

    /**
     * @param array|Collection $pendingStreams
     *
     * @return self
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
            ->map(function (PendingTurboStreamResponse $pendingStream) {
                return $pendingStream->render();
            })
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
