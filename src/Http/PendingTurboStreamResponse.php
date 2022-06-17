<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

use function Tonysm\TurboLaravel\dom_id;
use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\NamesResolver;

class PendingTurboStreamResponse implements Responsable
{
    private string $useTarget;
    private string $useAction;
    private ?string $partialView = null;
    private array $partialData = [];
    private $inlineContent = null;

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

    public function append(Model|string $model, $content = null): self
    {
        return $this->inserted($model, 'append', $content);
    }

    public function prepend(Model|string $model, $content = null): self
    {
        return $this->inserted($model, 'prepend', $content);
    }

    public function before(Model|string $model, $content = null): self
    {
        return $this->updated($model, 'before', $content);
    }

    /**
     * @param Model|string $model The target DOM ID or Model instance to defer the target.
     * @param HtmlString|string $content Optional inline content. Can be string or a instance of HtmlString.
     *
     * @return self
     */
    public function after(Model|string $model, $content = null): self
    {
        return $this->updated($model, 'after', $content);
    }

    public function update(Model|string $model, $content = null): self
    {
        return $this->updated($model, 'update', $content);
    }

    public function replace(Model|string $model, $content = null): self
    {
        return $this->updated($model, 'replace', $content);
    }

    public function remove(Model|string $target): self
    {
        $this->useAction = 'remove';
        $this->useTarget = is_string($target) ? $target : dom_id($target);

        return $this;
    }

    public function target(Model|string $target): self
    {
        $this->useTarget = $this->resolveTargetFor($target, resource: true);

        return $this;
    }

    public function action(string $action): self
    {
        $this->useAction = $action;

        return $this;
    }

    public function view(?string $view = null, array $data = []): self
    {
        return $this->partial($view, $data);
    }

    public function partial(?string $view = null, array $data = []): self
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
        if ($this->useAction !== 'remove' && ! $this->partialView && ! $this->inlineContent) {
            throw TurboStreamResponseFailedException::missingPartial();
        }

        return TurboResponseFactory::makeStream(
            $this->render()
        );
    }

    public function render(): string
    {
        return view('turbo-laravel::turbo-stream', [
            'target' => $this->useTarget,
            'action' => $this->useAction,
            'partial' => $this->partialView,
            'partialData' => $this->partialData,
            'content' => $this->renderInlineContent(),
        ])->render();
    }

    /**
     * @return string|HtmlString|null
     */
    private function renderInlineContent()
    {
        if (! $this->inlineContent) {
            return null;
        }

        if ($this->inlineContent instanceof View) {
            return new HtmlString($this->inlineContent->render());
        }

        return $this->inlineContent;
    }

    private function inserted(Model|string $target, string $action, $content = null): self
    {
        $this->useTarget = $this->resolveTargetFor($target, resource: true);
        $this->useAction = $action;
        $this->partialView = $target instanceof Model ? $this->getPartialViewFor($target) : null;
        $this->partialData = $target instanceof Model ? $this->getPartialDataFor($target) : [];
        $this->inlineContent = $content;

        return $this;
    }

    private function updated(Model|string $target, string $action, $content = null): self
    {
        $this->useTarget = $this->resolveTargetFor($target);
        $this->useAction = $action;
        $this->partialView = $target instanceof Model ? $this->getPartialViewFor($target) : null;
        $this->partialData = $target instanceof Model ? $this->getPartialDataFor($target) : [];
        $this->inlineContent = $content;

        return $this;
    }

    private function resolveTargetFor(Model|string $target, bool $resource = false): string
    {
        if (is_string($target)) {
            return $target;
        }

        if ($resource) {
            return $this->getResourceNameFor($target);
        }

        return dom_id($target);
    }

    private function getResourceNameFor(Model $model): string
    {
        return Name::forModel($model)->plural;
    }

    private function getPartialViewFor(Model $model): string
    {
        return NamesResolver::partialNameFor($model);
    }

    private function getPartialDataFor(Model $model): array
    {
        return [NamesResolver::resourceVariableName($model) => $model];
    }
}
