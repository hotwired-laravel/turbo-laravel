<?php

namespace HotwiredLaravel\TurboLaravel\Broadcasting;

use HotwiredLaravel\TurboLaravel\Facades\Limiter;
use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

class Factory
{
    /**
     * Indicates if we should record the Turbo Stream
     * broadcast instead of sending it.
     *
     * @var bool
     */
    protected $recording = false;

    /**
     * The recorded Turbo Streams.
     *
     * @var bool
     */
    protected $recordedStreams = [];

    public function fake()
    {
        $this->recording = true;

        return $this;
    }

    public function broadcastAppend($content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('append', $content, $target, $targets, $channel, $attributes);
    }

    public function broadcastPrepend($content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('prepend', $content, $target, $targets, $channel, $attributes);
    }

    public function broadcastBefore($content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('before', $content, $target, $targets, $channel, $attributes);
    }

    public function broadcastAfter($content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('after', $content, $target, $targets, $channel, $attributes);
    }

    public function broadcastUpdate($content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('update', $content, $target, $targets, $channel, $attributes);
    }

    public function broadcastReplace($content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('replace', $content, $target, $targets, $channel, $attributes);
    }

    public function broadcastRemove(Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        return $this->broadcastAction('remove', null, $target, $targets, $channel, $attributes);
    }

    public function broadcastRefresh(Channel|Model|Collection|array|string $channel = null)
    {
        return $this->broadcastAction(
            action: 'refresh',
            channel: $channel,
            attributes: array_filter(['request-id' => $requestId = Turbo::currentRequestId()]),
        )->lazyCancelIf(fn (PendingBroadcast $broadcast) => (
            $this->shouldLimitPageRefreshesOn($broadcast->channels, $requestId)
        ));
    }

    public function broadcastAction(string $action, $content = null, Model|string $target = null, string $targets = null, Channel|Model|Collection|array|string $channel = null, array $attributes = [])
    {
        $broadcast = new PendingBroadcast(
            channels: $channel ? $this->resolveChannels($channel) : [],
            action: $action,
            target: $target instanceof Model ? $this->resolveTargetFor($target, resource: $target->wasRecentlyCreated) : $target,
            targets: $targets,
            rendering: $this->resolveRendering($content),
            attributes: $attributes,
        );

        if ($this->recording) {
            $broadcast->fake($this);
        }

        return $broadcast;
    }

    public function record(PendingBroadcast $broadcast)
    {
        $this->recordedStreams[] = $broadcast;

        return $this;
    }

    protected function shouldLimitPageRefreshesOn(array $channels, ?string $requestId): bool
    {
        return Limiter::shouldLimit($this->pageRefreshLimiterKeyFor($channels, $requestId));
    }

    protected function pageRefreshLimiterKeyFor(array $channels, ?string $requestId): string
    {
        $keys = array_map(fn (Channel $channel) => $channel->name, $channels);

        sort($keys);

        $key = sha1(implode('/', array_values($keys) + array_filter([$requestId])));

        return 'turbo-refreshes-limiter-'.$key;
    }

    protected function resolveRendering($content)
    {
        if ($content instanceof Rendering) {
            return $content;
        }

        return $content ? Rendering::forContent($content) : Rendering::empty();
    }

    protected function resolveChannels(Channel|Model|Collection|array|string $channel)
    {
        if (is_array($channel) || $channel instanceof Collection) {
            return collect($channel)->flatMap(function ($channel) {
                return $this->resolveChannels($channel);
            })->values()->filter()->all();
        }

        if (is_string($channel)) {
            return [new Channel($channel)];
        }

        return [$channel];
    }

    protected function resolveTargetFor(Model $target, bool $resource = false): string
    {
        if ($resource) {
            return $this->getResourceNameFor($target);
        }

        return dom_id($target);
    }

    protected function getResourceNameFor(Model $model): string
    {
        return Name::forModel($model)->plural;
    }

    public function clearRecordedBroadcasts(): self
    {
        $this->recordedStreams = [];

        return $this;
    }

    public function assertBroadcasted($callback)
    {
        $result = collect($this->recordedStreams)->filter($callback);

        Assert::assertGreaterThanOrEqual(1, $result->count(), 'Expected to have broadcasted Turbo Streams, but it did not.');

        return $this;
    }

    public function assertBroadcastedTimes($callback, $times = 1, $message = null)
    {
        $result = collect($this->recordedStreams)->filter($callback);

        Assert::assertCount($times, $result, $message ?: sprintf(
            'Expected to have broadcasted %d Turbo %s, but broadcasted %d Turbo %s instead.',
            $times,
            (string) str('Stream')->plural($times),
            $result->count(),
            (string) str('Stream')->plural($result->count()),
        ));

        return $this;
    }

    public function assertNothingWasBroadcasted()
    {
        return $this->assertBroadcastedTimes(function () {
            return true;
        }, 0, sprintf('Expected to not have broadcasted any Turbo Stream, but broadcasted %d instead.', count($this->recordedStreams)));
    }
}
