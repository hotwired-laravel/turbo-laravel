<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TurboStreamChannelsResolver
{
    public function hotwireBroadcastsOn(Model $model)
    {
        return Collection::wrap($this->hotwireBroadcastingTargets($model))
            ->filter()
            ->map(function ($item) {
                if ($item instanceof Channel) {
                    return $item;
                }

                return new PrivateChannel(
                    $this->broadcastingChannelForModel($item)
                );
            })
            ->all();
    }

    private function hotwireBroadcastingTargets(Model $model)
    {
        if (property_exists($model, 'broadcastsTo')) {
            return Collection::wrap($model->broadcastsTo)
                ->map(function ($attr) use ($model) {
                    return data_get($model, $attr);
                })
                ->all();
        }

        if (method_exists($model, 'broadcastsTo')) {
            return $model->broadcastsTo();
        }

        return $model;
    }

    private function broadcastingChannelForModel(Model $model): string
    {
        return resolve(NamesResolver::class)->broadcastingChannelForModel($model);
    }
}
