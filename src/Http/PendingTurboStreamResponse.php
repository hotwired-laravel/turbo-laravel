<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use function Tonysm\TurboLaravel\dom_id;
use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\NamesResolver;
use Tonysm\TurboLaravel\Turbo;

class PendingTurboStreamResponse implements Responsable
{
    private string $useTarget;
    private string $useAction;
    private ?string $partialView = null;
    private array $partialData = [];

    public static function forModel(Model $model, string $action = null): self
    {
        $builder = new self();

        // We're treating soft-deleted models as they were deleted. In other words, we
        // will render the deleted Turbo Stream. If you need to treat a soft-deleted
        // model differently, you can do that on your deleted Turbo Stream view.

        if (! $model->exists || (method_exists($model, 'trashed') && $model->trashed())) {
            return $builder->remove($model);
        }

        if ($model->wasRecentlyCreated) {
            return $builder->inserted($model, $action ?: 'append');
        }

        return $builder->updated($model, $action ?: 'replace');
    }

    public function append(Model $model): self
    {
        return $this->inserted($model, 'append');
    }

    public function prepend(Model $model): self
    {
        return $this->inserted($model, 'prepend');
    }

    public function update(Model $model): self
    {
        return $this->updated($model, 'update');
    }

    public function replace(Model $model): self
    {
        return $this->updated($model, 'replace');
    }

    public function remove(Model $model): self
    {
        $this->useAction = 'remove';
        $this->useTarget = dom_id($model);

        return $this;
    }

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

    public function view(string $view, array $data = []): self
    {
        return $this->partial($view, $data);
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
        if ($this->useAction !== 'remove' && ! $this->partialView) {
            throw TurboStreamResponseFailedException::missingPartial();
        }

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

    private function inserted(Model $model, string $action): self
    {
        $this->useTarget = $this->getResourceNameFor($model);
        $this->useAction = $action;
        $this->partialView = $this->getPartialViewFor($model);
        $this->partialData = $this->getPartialDataFor($model);

        return $this;
    }

    private function updated(Model $model, string $action): self
    {
        $this->useTarget = dom_id($model);
        $this->useAction = $action;
        $this->partialView = $this->getPartialViewFor($model);
        $this->partialData = $this->getPartialDataFor($model);

        return $this;
    }

    private function getResourceNameFor(Model $model): string
    {
        return method_exists($model, 'hotwireTargetResourcesName')
            ? $model->hotwireTargetResourcesName()
            : Name::forModel($model)->plural;
    }

    private function getPartialViewFor(Model $model): string
    {
        return method_exists($model, 'hotwirePartialName')
            ? $model->hotwirePartialName()
            : NamesResolver::partialNameFor($model);
    }

    private function getPartialDataFor(Model $model): array
    {
        return method_exists($model, 'hotwirePartialData')
            ? $model->hotwirePartialData()
            : [NamesResolver::resourceVariableName($model) => $model];
    }
}
