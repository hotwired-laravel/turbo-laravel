<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;

class ChannelResolver
{
    public function broadcastChannelFor(Model $model): Channel
    {
        if (method_exists($model, 'hotwireBroadcastChannel')) {
            $channel = $model->hotwireBroadcastChannel();

            if (is_string($channel)) {
                return new PrivateChannel($channel);
            } else {
                return $channel;
            }
        }

        return $this->forModel($model::class, $model->id);
    }

    public function broadcastChannelForClassNames(string $modelClass, $modelId): Channel
    {
        $model = $modelClass::find($modelId);

        if (! $model) {
            return $this->forModel($modelClass, $modelId);
        }

        return $this->broadcastChannelFor($model);
    }

    private function forModel(string $modelClass, $modelId): PrivateChannel
    {
        $prefix = is_dir(app_path('Models'))
            ? 'App.Models'
            : 'App';

        $channel = class_basename($modelClass);

        return new PrivateChannel("$prefix.{$channel}.{$modelId}");
    }
}
