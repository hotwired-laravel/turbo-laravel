<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Http\TurboResponseFactory;

class TurboStreamResponseMacro
{
    private TurboStreamModelRenderer $renderer;

    public function __construct(TurboStreamModelRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function handle(Model $model, string $action = null)
    {
        if (! $model->exists ||
            (method_exists($model, 'trashed') && $model->trashed())
        ) {
            return $this->renderModelDeletedStream($model);
        }

        if ($model->wasRecentlyCreated) {
            return $this->renderModelCreatedStream($model, $action);
        }

        return $this->renderModelUpdatedStream($model, $action);
    }

    /**
     * @param Model $model
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function renderModelDeletedStream(Model $model)
    {
        return TurboResponseFactory::makeStream(
            $this->renderer->renderDeleted($model, 'remove')->render()
        );
    }

    /**
     * @param Model $model
     * @param string|null $action
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function renderModelCreatedStream(Model $model, string $action = null)
    {
        $action = $action ?: 'append';

        return TurboResponseFactory::makeStream(
            $this->renderer->renderCreated($model, $action)->render()
        );
    }

    private function renderModelUpdatedStream(Model $model, $action)
    {
        $action = $action ?: 'replace';

        return TurboResponseFactory::makeStream(
            $this->renderer->renderUpdated($model, $action)->render()
        );
    }
}
