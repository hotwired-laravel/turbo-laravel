<?php

namespace HotwiredLaravel\TurboLaravel\Http;

use HotwiredLaravel\TurboLaravel\Broadcasting\PendingBroadcast;
use HotwiredLaravel\TurboLaravel\Broadcasting\Rendering;
use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;

use function HotwiredLaravel\TurboLaravel\dom_id;

class PendingTurboStreamResponse implements \Stringable, Htmlable, Renderable, Responsable
{
    use Macroable;

    private array $defaultActions = ['append', 'prepend', 'update', 'replace', 'before', 'after', 'remove', 'refresh'];

    private string $useAction;

    private ?string $useTarget = null;

    private ?string $useTargets = null;

    private ?string $partialView = null;

    private array $partialData = [];

    private $inlineContent;

    private array $useCustomAttributes = [];

    public static function forModel(Model $model, ?string $action = null): self
    {
        $builder = new self;

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

    public function target(Model|string $target, bool $resource = false): self
    {
        $this->useTarget = $target instanceof Model ? $this->resolveTargetFor($target, $resource) : $target;
        $this->useTargets = null;

        return $this;
    }

    public function targets(Model|string $targets): self
    {
        $this->useTarget = null;
        $this->useTargets = $targets instanceof Model ? $this->resolveTargetFor($targets, resource: true) : $targets;

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

    public function attributes(array $attributes): self
    {
        $this->useCustomAttributes = $attributes;

        return $this;
    }

    public function morph(): self
    {
        return $this->method('morph');
    }

    public function method(?string $method = null): self
    {
        if ($method) {
            return $this->attributes(array_merge($this->useCustomAttributes, [
                'method' => $method,
            ]));
        }

        return $this->attributes(Arr::except($this->useCustomAttributes, 'method'));
    }

    public function append(Model|string $target, $content = null): self
    {
        return $this->buildAction(
            action: 'append',
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: true) : $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function appendAll(Model|string $targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'append',
            targets: $targets,
            content: $content,
        );
    }

    public function prepend(Model|string $target, $content = null): self
    {
        return $this->buildAction(
            action: 'prepend',
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: true) : $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function prependAll(Model|string $targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'prepend',
            targets: $targets,
            content: $content,
        );
    }

    public function before(Model|string $target, $content = null): self
    {
        return $this->buildAction(
            action: 'before',
            target: $target,
            content: $content,
        );
    }

    public function beforeAll(Model|string $targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'before',
            targets: $targets,
            content: $content,
        );
    }

    public function after(Model|string $target, $content = null): self
    {
        return $this->buildAction(
            action: 'after',
            target: $target,
            content: $content,
        );
    }

    public function afterAll(Model|string $targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'after',
            targets: $targets,
            content: $content,
        );
    }

    public function update(Model|string $target, $content = null): self
    {
        return $this->buildAction(
            action: 'update',
            target: $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function updateAll(Model|string $targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'update',
            targets: $targets,
            content: $content,
        );
    }

    public function replace(Model|string $target, $content = null): self
    {
        return $this->buildAction(
            action: 'replace',
            target: $target,
            content: $content,
            rendering: $target instanceof Model ? Rendering::forModel($target) : null,
        );
    }

    public function replaceAll(Model|string $targets, $content = null): self
    {
        return $this->buildActionAll(
            action: 'replace',
            targets: $targets,
            content: $content,
        );
    }

    public function remove(Model|string $target): self
    {
        return $this->buildAction(
            action: 'remove',
            target: $target,
        );
    }

    public function removeAll(Model|string $targets): self
    {
        return $this->buildActionAll(
            action: 'remove',
            targets: $targets,
        );
    }

    public function refresh(): self
    {
        return $this->buildAction('refresh')
            ->attributes(array_filter(['request-id' => Turbo::currentRequestId()]));
    }

    private function buildAction(string $action, Model|string|null $target = null, $content = null, ?Rendering $rendering = null, array $attributes = []): static
    {
        $this->useAction = $action;
        $this->useTarget = $target instanceof Model ? $this->resolveTargetFor($target) : $target;
        $this->partialView = $rendering?->partial;
        $this->partialData = $rendering?->data ?? [];
        $this->useCustomAttributes = $attributes;
        $this->inlineContent = $content;

        return $this;
    }

    private function buildActionAll(string $action, Model|string $targets, $content = null, array $attributes = []): static
    {
        $this->useAction = $action;
        $this->useTarget = null;
        $this->useTargets = $targets instanceof Model ? $this->resolveTargetFor($targets, resource: true) : $targets;
        $this->useCustomAttributes = $attributes;
        $this->inlineContent = $content;

        return $this;
    }

    public function broadcastTo($channel, ?callable $callback = null)
    {
        $callback ??= function (): void {};

        return tap($this, function () use ($channel, $callback): void {
            $callback($this->asPendingBroadcast($channel));
        });
    }

    public function broadcastToPrivateChannel($channel, ?callable $callback = null)
    {
        $callback ??= function (): void {};

        return $this->broadcastTo(null, function (PendingBroadcast $broadcast) use ($channel, $callback): void {
            $broadcast->toPrivateChannel($channel);
            $callback($broadcast);
        });
    }

    public function broadcastToPresenceChannel($channel, ?callable $callback = null)
    {
        $callback ??= function (): void {};

        return $this->broadcastTo(null, function (PendingBroadcast $broadcast) use ($channel, $callback): void {
            $callback($broadcast->toPresenceChannel($channel));
        });
    }

    private function asPendingBroadcast($channel)
    {
        return TurboStream::broadcastAction(
            action: $this->useAction,
            target: $this->useTarget,
            targets: $this->useTargets,
            channel: $channel,
            attributes: $this->useCustomAttributes,
        )->rendering($this->contentAsRendering());
    }

    private function contentAsRendering()
    {
        if ($this->inlineContent) {
            return Rendering::forContent($this->inlineContent);
        }

        return new Rendering(
            $this->partialView,
            $this->partialData,
        );
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if (! in_array($this->useAction, ['remove', 'refresh']) && in_array($this->useAction, $this->defaultActions) && ! $this->partialView && $this->inlineContent === null) {
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
            'attrs' => $this->useCustomAttributes,
        ])->render();
    }

    public function toHtml()
    {
        return new HtmlString($this->render());
    }

    public function __toString(): string
    {
        return $this->render();
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

    private function resolveTargetFor(Model $target, bool $resource = false): string
    {
        if ($resource) {
            return $this->getResourceNameFor($target);
        }

        return dom_id($target);
    }

    private function getResourceNameFor(Model $model): string
    {
        return Name::forModel($model)->plural;
    }
}
