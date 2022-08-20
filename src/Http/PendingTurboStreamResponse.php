<?php

namespace Tonysm\TurboLaravel\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Tonysm\TurboLaravel\Broadcasting\Rendering;

use function Tonysm\TurboLaravel\dom_id;
use Tonysm\TurboLaravel\Models\Naming\Name;
use Tonysm\TurboLaravel\NamesResolver;

class PendingTurboStreamResponse implements Responsable
{
    private string $useAction;
    private ?string $useTarget = null;
    private ?string $useTargets = null;
    private ?string $partialView = null;
    private array $partialData = [];
    private $inlineContent = null;

    public static function forModel(Model $model, string $action = null): self
    {
        $builder = new self();

        // We're treating soft-deleted models as they were deleted. In other words, we
        // will render the remove Turbo Stream. If you need to treat a soft-deleted
        // model differently, you shouldn't rely on the conventions defined here.

        if (! $model->exists || (method_exists($model, 'trashed') && $model->trashed())) {
            return $builder->buildAction(
                action: 'remove',
                target: $builder->resolveTargetFor($model),
            );
        }

        if ($model->wasRecentlyCreated) {
            return $builder->buildAction(
                action: $action ?: 'append',
                target: $builder->resolveTargetFor($model, resource: true),
                rendering: Rendering::forModel($model),
            );
        }

        return $builder->buildAction(
            action: $action ?: 'replace',
            target: $builder->resolveTargetFor($model),
            rendering: Rendering::forModel($model),
        );
    }

    public function target($target, bool $resource = false): self
    {
        $this->useTarget = $target instanceof Model ? $this->resolveTargetFor($target, $resource) : $target;
        $this->useTargets = null;

        return $this;
    }

    public function targets($targets): self
    {
        $this->useTarget = null;
        $this->useTargets = $targets;

        return $this;
    }


    public function action(string $action): self
    {
        $this->useAction = $action;

        return $this;
    }

    public function partial(string $view, array $data = []): self
    {
        return $this->view($view, $data);
    }

    public function view(string $view, array $data = []): self
    {
        $this->partialView = $view;
        $this->partialData = $data;

        return $this;
    }

    public function append($target, $content = null): self
    {
        return $this->buildAction(
            action: 'append',
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: true) : $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function appendAll($targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'append',
            targets: $targets,
            content: $content,
        );
    }

    public function prepend($target, $content = null): self
    {
        return $this->buildAction(
            action: 'prepend',
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: true) : $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function before($target, $content = null): self
    {
        return $this->buildAction(
            action: 'before',
            target: $target instanceof Model ? $this->resolveTargetFor($target) : $target,
            content: $content,
        );
    }

    public function after($target, $content = null): self
    {
        return $this->buildAction(
            action: 'after',
            target: $target instanceof Model ? $this->resolveTargetFor($target) : $target,
            content: $content,
        );
    }

    public function update($target, $content = null): self
    {
        return $this->buildAction(
            action: 'update',
            target: $target instanceof Model ? $this->resolveTargetFor($target) : $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function replace($target, $content = null): self
    {
        return $this->buildAction(
            action: 'replace',
            target: $target instanceof Model ? $this->resolveTargetFor($target) : $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function remove($target): self
    {
        return $this->buildAction(
            action: 'remove',
            target: $target instanceof Model ? $this->resolveTargetFor($target) : $target,
        );
    }

    private function buildAction(string $action, $target, $content = null, ?Rendering $rendering = null)
    {
        $this->useAction = $action;
        $this->useTarget = $target;
        $this->partialView = $rendering?->partial;
        $this->partialData = $rendering?->data ?? [];
        $this->inlineContent = $content;

        return $this;
    }

    private function buildActionAll(string $action, $targets, $content = null)
    {
        $this->useAction = $action;
        $this->useTarget = null;
        $this->useTargets = $targets;
        $this->inlineContent = $content;

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

        return TurboResponseFactory::makeStream($this->render());
    }

    public function render(): string
    {
        return view('turbo-laravel::turbo-stream', [
            'action' => $this->useAction,
            'target' => $this->useTarget,
            'targets' => $this->useTargets,
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
