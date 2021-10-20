<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class MultiplePendingTurboStreamResponse implements Responsable
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
        return TurboResponseFactory::makeStream(
            $this->pendingStreams
                ->map(function (PendingTurboStreamResponse $pendingStream) {
                    return $pendingStream->render();
                })
                ->implode(PHP_EOL)
        );
    }
}
