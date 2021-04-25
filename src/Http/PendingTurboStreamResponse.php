<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Responsable;
use Tonysm\TurboLaravel\Turbo;

class PendingTurboStreamResponse implements Responsable
{
    private string $useTarget;
    private string $useAction;
    private ?string $partialView = null;
    private array $partialData = [];

    public function target(string $target): self
    {
        $this->useTarget = $target;

        return $this;
    }

    public function action(string $action): self
    {
        $this->useAction = $action;

        return $this;
    }

    public function partial(string $view, array $data = []): self
    {
        $this->partialView = $view;
        $this->partialData = $data;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return response(
            view('turbo-laravel::turbo-stream', [
                'target' => $this->useTarget,
                'action' => $this->useAction,
                'partial' => $this->partialView,
                'partialData' => $this->partialData,
            ])->render()
        )->withHeaders([
            'Content-Type' => Turbo::TURBO_STREAM_FORMAT,
        ]);
    }
}
